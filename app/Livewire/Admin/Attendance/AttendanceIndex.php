<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Score;
use App\Models\User;
use DomainException;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Điểm danh và chấm điểm')]
class AttendanceIndex extends Component
{
    public ?string $selectedDate = null;

    public string $scheduleScope = 'all';

    public ?int $selectedScheduleId = null;

    /** @var array<int, string> */
    public array $attendanceStatuses = [];

    /** @var array<int, string> */
    public array $attendanceNotes = [];

    /** @var array<int, string|null> */
    public array $theoryScores = [];

    /** @var array<int, string|null> */
    public array $practiceScores = [];

    public ?string $statusMessage = null;

    protected bool $isHydratingRoster = false;

    public function mount(): void
    {
        $this->syncSelectedSchedule();
    }

    public function updatedSelectedDate(): void
    {
        $this->selectedScheduleId = null;
        $this->syncSelectedSchedule();
    }

    public function updatedScheduleScope(): void
    {
        $this->selectedScheduleId = null;
        $this->syncSelectedSchedule();
    }

    public function updatedSelectedScheduleId(): void
    {
        $this->hydrateRosterState();
    }

    #[Computed]
    public function scheduleOptions(): Collection
    {
        return Schedule::query()
            ->with(['classroomSubject.classroom', 'classroomSubject.subject', 'classroomSubject.teachers'])
            ->where('have_record', true)
            ->when(
                filled($this->selectedDate),
                fn ($query) => $query->whereDate('date', $this->selectedDate)
            )
            ->when(
                $this->scheduleScope === 'mine' && Auth::id() !== null,
                fn ($query) => $query->whereHas(
                    'classroomSubject.teachers',
                    fn ($teacherQuery) => $teacherQuery->whereKey(Auth::id())
                )
            )
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    #[Computed]
    public function canManageSelectedSchedule(): bool
    {
        $schedule = $this->selectedSchedule;
        $userId = Auth::id();

        if ($schedule === null || $userId === null) {
            return false;
        }

        $schedule->loadMissing('classroomSubject.teachers');

        return $schedule->classroomSubject?->teachers->contains('id', $userId) === true
            && $schedule->isAttendanceOpen();
    }

    #[Computed]
    public function selectedSchedule(): ?Schedule
    {
        return $this->scheduleOptions->firstWhere('id', $this->selectedScheduleId);
    }

    #[Computed]
    public function rosterRows(): Collection
    {
        $schedule = $this->selectedSchedule;

        if ($schedule === null) {
            return collect();
        }

        $schedule->loadMissing([
            'classroomSubject.classroom.youths',
            'classroomSubject.subject',
            'classroomSubject.teachers',
            'attendances',
            'scores',
        ]);

        /** @var EloquentCollection<int, Attendance> $attendances */
        $attendances = $schedule->attendances;
        /** @var EloquentCollection<int, Score> $scores */
        $scores = $schedule->scores;

        $attendancesByUser = $attendances->keyBy('user_id');
        $scoresByUser = $scores->keyBy('user_id');

        return $schedule->classroomSubject?->classroom?->youths
            ?->sortBy('name')
            ->values()
            ->map(function (User $youth) use ($attendancesByUser, $scoresByUser): array {
                /** @var Attendance|null $attendance */
                $attendance = $attendancesByUser->get($youth->id);
                /** @var Score|null $score */
                $score = $scoresByUser->get($youth->id);

                $attendanceStatus = $attendance?->status ?? $this->attendanceStatuses[$youth->id] ?? null;

                return [
                    'user' => $youth,
                    'attendance' => $attendance,
                    'score' => $score,
                    'spirit_score' => $this->spiritScoreForStatus($attendanceStatus),
                    'preview_final_score' => $this->previewFinalScore($youth->id, $attendanceStatus),
                    'preview_result_status' => $this->previewResultStatus($youth->id, $attendanceStatus),
                ];
            })
            ?? collect();
    }

    /**
     * @return array<string, string>
     */
    public function attendanceStatusOptions(): array
    {
        return [
            Attendance::STATUS_ON_TIME => 'Đi đúng giờ',
            Attendance::STATUS_LATE_EXCUSED => 'Trễ có phép',
            Attendance::STATUS_LATE_UNEXCUSED => 'Trễ không phép',
            Attendance::STATUS_ABSENT_EXCUSED => 'Vắng có phép',
            Attendance::STATUS_ABSENT_UNEXCUSED => 'Vắng không phép',
            Attendance::STATUS_MAKEUP_COMPLETED => 'Đã học bù',
        ];
    }

    public function attendanceAccessMessage(): string
    {
        $schedule = $this->selectedSchedule;
        $userId = Auth::id();

        if ($schedule === null || $userId === null) {
            return __('Chưa mở điểm danh');
        }

        $schedule->loadMissing('classroomSubject.teachers');

        if ($schedule->classroomSubject?->teachers->contains('id', $userId) !== true) {
            return __('Bạn không được gán vào lịch này');
        }

        if (! $schedule->isAttendanceOpen()) {
            return __('Chưa tới giờ bắt đầu');
        }

        return __('Chưa chọn trạng thái');
    }

    public function saveRecord(int $userId): void
    {
        $schedule = $this->selectedSchedule;

        if ($schedule === null) {
            Flux::toast(variant: 'warning', text: __('Hãy chọn lịch học cần nhập điểm.'));

            return;
        }

        if (! $this->canManageSelectedSchedule) {
            Flux::toast(variant: 'danger', text: __('Bạn chưa được phép điểm danh cho lịch này.'));

            return;
        }

        $payload = $this->validateRow($userId);

        /** @var Attendance|null $attendance */
        $attendance = Attendance::query()
            ->where('schedule_id', $schedule->id)
            ->where('user_id', $userId)
            ->first();

        /** @var Score|null $score */
        $score = Score::query()
            ->where('schedule_id', $schedule->id)
            ->where('user_id', $userId)
            ->first();

        if ($this->attendancePayloadWasChanged($attendance, $payload) && $schedule->isSpiritScoreLocked()) {
            Flux::toast(variant: 'danger', text: __('Đã quá hạn điểm danh và điểm tinh thần cho lịch này.'));

            return;
        }

        $attendanceRecord = $attendance ?? new Attendance([
            'schedule_id' => $schedule->id,
            'user_id' => $userId,
        ]);

        $attendanceRecord->status = $payload['attendance_status'];
        $attendanceRecord->note = $payload['attendance_note'];
        $attendanceRecord->marked_by = Auth::id();
        $attendanceRecord->marked_at = now();
        $attendanceRecord->makeup_completed_at = $payload['attendance_status'] === Attendance::STATUS_MAKEUP_COMPLETED
            ? ($attendanceRecord->makeup_completed_at ?? now())
            : null;
        $attendanceRecord->save();

        // Update memory immediately after save
        $this->attendanceStatuses[$userId] = $payload['attendance_status'];
        $this->attendanceNotes[$userId] = $payload['attendance_note'];
        $this->theoryScores[$userId] = $payload['theory_score'];
        $this->practiceScores[$userId] = $payload['practice_score'];

        try {
            $scoreRecord = $score ?? new Score([
                'schedule_id' => $schedule->id,
                'user_id' => $userId,
            ]);

            $scoreRecord->spirit_score = $this->spiritScoreForUser($userId);
            $scoreRecord->theory_score = $payload['theory_score'];
            $scoreRecord->practice_score = $payload['practice_score'];
            $scoreRecord->save();
        } catch (DomainException $exception) {
            Flux::toast(variant: 'danger', text: $exception->getMessage());

            return;
        }

        $this->hydrateRosterState();

        $this->statusMessage = __('Đã lưu');
        session()->now('status', __('Đã lưu'));

        Flux::toast(variant: 'success', text: __('Đã lưu điểm danh và điểm số.'));

    }

    public function updatedAttendanceStatuses($value, $key): void
    {
        if ($this->isHydratingRoster) {
            return;
        }

        $this->saveRecord((int) $key);
    }

    public function updatedAttendanceNotes($value, $key): void
    {
        if ($this->isHydratingRoster) {
            return;
        }

        $this->saveRecord((int) $key);

    }

    public function updatedTheoryScores($value, $key): void
    {
        if ($this->isHydratingRoster) {
            return;
        }

        // Validate theory score
        $validator = Validator::make(
            ['theory_score' => $this->normalizeOptionalNumeric($value)],
            ['theory_score' => ['nullable', 'numeric', 'between:0,10']],
            [],
            ['theory_score' => __('điểm lý thuyết')]
        );

        if ($validator->fails()) {
            $this->addError("theoryScores.{$key}", $validator->errors()->first());

            return;
        }

        // Clear any existing errors for this field
        $this->resetErrorBag("theoryScores.{$key}");

        $this->saveRecord((int) $key);
    }

    public function updatedPracticeScores($value, $key): void
    {
        if ($this->isHydratingRoster) {
            return;
        }

        // Validate practice score
        $validator = Validator::make(
            ['practice_score' => $this->normalizeOptionalNumeric($value)],
            ['practice_score' => ['nullable', 'numeric', 'between:0,10']],
            [],
            ['practice_score' => __('điểm thực hành')]
        );

        if ($validator->fails()) {
            $this->addError("practiceScores.{$key}", $validator->errors()->first());

            return;
        }

        // Clear any existing errors for this field
        $this->resetErrorBag("practiceScores.{$key}");

        $this->saveRecord((int) $key);
    }

    public function render(): View
    {
        return view('livewire.admin.attendance.attendance-index');
    }

    protected function syncSelectedSchedule(): void
    {
        $this->selectedScheduleId = $this->preferredSchedule()?->id;
        $this->hydrateRosterState();
    }

    protected function preferredSchedule(): ?Schedule
    {
        $scheduleOptions = $this->scheduleOptions;
        $today = now()->toDateString();

        return $scheduleOptions->first(fn (Schedule $schedule): bool => $schedule->date?->toDateString() === $today)
            ?? $scheduleOptions->first(fn (Schedule $schedule): bool => ($schedule->date?->toDateString() ?? '') > $today)
            ?? $scheduleOptions->reverse()->first();
    }

    protected function hydrateRosterState(): void
    {
        $this->isHydratingRoster = true;
        $this->statusMessage = null;

        $this->attendanceStatuses = [];
        $this->attendanceNotes = [];
        $this->theoryScores = [];
        $this->practiceScores = [];

        foreach ($this->rosterRows as $row) {
            /** @var User $user */
            $user = $row['user'];
            /** @var Attendance|null $attendance */
            $attendance = $row['attendance'];
            /** @var Score|null $score */
            $score = $row['score'];

            $this->attendanceStatuses[$user->id] = $attendance?->status ?? '';
            $this->attendanceNotes[$user->id] = $attendance?->note ?? '';
            $this->theoryScores[$user->id] = $score?->theory_score !== null ? (string) $score->theory_score : null;
            $this->practiceScores[$user->id] = $score?->practice_score !== null ? (string) $score->practice_score : null;
        }

        // rosterRows is #[Computed] and caches per request; it was first evaluated while theory/practice
        // arrays were still empty above, so clear cache so render sees previews from the hydrated state.
        unset($this->rosterRows);

        $this->isHydratingRoster = false;
    }

    /**
     * @return array{attendance_status: string, attendance_note: ?string, theory_score: ?string, practice_score: ?string}
     */
    protected function validateRow(int $userId): array
    {
        return Validator::make(
            [
                'attendance_status' => $this->attendanceStatuses[$userId] ?? '',
                'attendance_note' => $this->attendanceNotes[$userId] ?? null,
                'theory_score' => $this->normalizeOptionalNumeric($this->theoryScores[$userId] ?? null),
                'practice_score' => $this->normalizeOptionalNumeric($this->practiceScores[$userId] ?? null),
            ],
            [
                'attendance_status' => ['required', Rule::in(array_keys($this->attendanceStatusOptions()))],
                'attendance_note' => ['nullable', 'string', 'max:1000'],
                'theory_score' => ['nullable', 'numeric', 'between:0,10'],
                'practice_score' => ['nullable', 'numeric', 'between:0,10'],
            ],
            [],
            [
                'attendance_status' => __('trạng thái điểm danh'),
                'attendance_note' => __('ghi chú điểm danh'),
                'theory_score' => __('điểm lý thuyết'),
                'practice_score' => __('điểm thực hành'),
            ],
        )->validate();
    }

    protected function spiritScoreForUser(int $userId): ?float
    {
        $status = $this->attendanceStatuses[$userId] ?? null;

        return $this->spiritScoreForStatus($status);
    }

    protected function spiritScoreForStatus(?string $status): ?float
    {
        if (blank($status)) {
            return null;
        }

        $attendance = new Attendance([
            'status' => $status,
        ]);

        return $attendance->suggestedSpiritScore();
    }

    protected function previewFinalScore(int $userId, ?string $attendanceStatus): ?float
    {
        $spirit = $this->spiritScoreForStatus($attendanceStatus);
        $theory = $this->normalizeOptionalFloat($this->theoryScores[$userId] ?? null);
        $practice = $this->normalizeOptionalFloat($this->practiceScores[$userId] ?? null);

        return Score::computeFinalScore($spirit, $theory, $practice);
    }

    protected function previewResultStatus(int $userId, ?string $attendanceStatus): string
    {
        return Score::resultStatusForFinalScore($this->previewFinalScore($userId, $attendanceStatus));
    }

    protected function normalizeOptionalNumeric(mixed $value): ?string
    {
        return $this->normalizeDecimalString($value);
    }

    protected function normalizeDecimalString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return null;
        }

        return str_replace(',', '.', $trimmed);
    }

    protected function normalizeOptionalFloat(mixed $value): ?float
    {
        $normalized = $this->normalizeDecimalString($value);

        if ($normalized === null || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    /**
     * @param  array{attendance_status: string, attendance_note: ?string, theory_score: ?string, practice_score: ?string}  $payload
     */
    protected function attendancePayloadWasChanged(?Attendance $attendance, array $payload): bool
    {
        if ($attendance === null) {
            return true;
        }

        return $attendance->status !== $payload['attendance_status']
            || (string) ($attendance->note ?? '') !== (string) ($payload['attendance_note'] ?? '');
    }
}
