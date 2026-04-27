<?php

namespace App\Livewire\Admin\Management\Makeup;

use App\Models\Attendance;
use App\Models\AttendanceMakeup;
use App\Models\MakeupSession;
use App\Models\Schedule;
use App\Models\Score;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quản lý lịch bù')]
class MakeupIndex extends Component
{
    use AuthorizesRequests;

    public ?int $selectedSessionId = null;

    public ?int $editingSessionId = null;

    public ?int $deletingSessionId = null;

    public ?int $subject_id = null;

    public ?int $teacher_id = null;

    public ?string $date = null;

    public ?string $start_time = null;

    public ?string $end_time = null;

    public ?string $date_end_spirit = null;

    public ?string $date_end_practice_theory = null;

    public string $status = MakeupSession::STATUS_PENDING;

    public string $note = '';

    public bool $showSessionModal = false;

    public bool $showDeleteModal = false;

    public bool $showAssignModal = false;

    /** @var array<int> */
    public array $selectedOriginalAttendanceIds = [];

    /** @var array<int, string> */
    public array $attendanceStatuses = [];

    /** @var array<int, string> */
    public array $attendanceNotes = [];

    /** @var array<int, string|null> */
    public array $theoryScores = [];

    /** @var array<int, string|null> */
    public array $practiceScores = [];

    /** @var array<int, list<string>> */
    public array $assignmentConflictWarnings = [];

    /** @var array<int, list<string>> */
    public array $eligibleConflictWarnings = [];

    public function mount(): void
    {
        $this->resetSessionForm();

        $this->selectedSessionId = MakeupSession::query()
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->value('id');

        $this->hydrateSessionState();
    }

    public function updatedSelectedSessionId(): void
    {
        $this->hydrateSessionState();
    }

    public function updatedDate(?string $value): void
    {
        if (blank($value)) {
            return;
        }

        $date = Carbon::parse($value);

        if (blank($this->date_end_spirit)) {
            $this->date_end_spirit = $date->copy()->addDay()->format('Y-m-d');
        }

        if (blank($this->date_end_practice_theory)) {
            $this->date_end_practice_theory = $date->copy()->addDays(7)->format('Y-m-d');
        }
    }

    public function selectSession(int $sessionId): void
    {
        $this->selectedSessionId = $sessionId;
        $this->hydrateSessionState();
    }

    public function openCreateModal(): void
    {
        $this->authorizeSessionEditing();

        $this->editingSessionId = null;
        $this->resetSessionForm();
        $this->date = now()->format('Y-m-d');
        $this->date_end_spirit = now()->addDay()->format('Y-m-d');
        $this->date_end_practice_theory = now()->addDays(7)->format('Y-m-d');
        $this->showSessionModal = true;
    }

    public function openEditModal(int $sessionId): void
    {
        $this->authorizeSessionEditing();

        $session = MakeupSession::query()->findOrFail($sessionId);

        $this->editingSessionId = $session->id;
        $this->subject_id = $session->subject_id;
        $this->teacher_id = $session->teacher_id;
        $this->date = $session->date?->format('Y-m-d');
        $this->start_time = $session->start_time ? substr($session->start_time, 0, 5) : null;
        $this->end_time = $session->end_time ? substr($session->end_time, 0, 5) : null;
        $this->date_end_spirit = $session->date_end_spirit?->format('Y-m-d');
        $this->date_end_practice_theory = $session->date_end_practice_theory?->format('Y-m-d');
        $this->status = $session->status;
        $this->note = $session->note ?? '';
        $this->resetErrorBag();
        $this->resetValidation();
        $this->showSessionModal = true;
    }

    public function saveSession(): void
    {
        $this->authorizeSessionEditing();
        $isEditing = $this->editingSessionId !== null;

        $validated = $this->validate($this->sessionRules());

        $this->ensureTeacherHasNoConflict(
            teacherId: (int) $validated['teacher_id'],
            date: (string) $validated['date'],
            startTime: (string) $validated['start_time'],
            endTime: (string) $validated['end_time'],
            exceptSessionId: $this->editingSessionId,
        );

        $session = DB::transaction(function () use ($validated): MakeupSession {
            if ($this->editingSessionId !== null) {
                $session = MakeupSession::query()->findOrFail($this->editingSessionId);
                $session->update($validated);

                return $session;
            }

            return MakeupSession::query()->create($validated + [
                'created_by' => Auth::id(),
            ]);
        });

        $this->selectedSessionId = $session->id;
        $this->showSessionModal = false;
        $this->editingSessionId = null;
        $this->resetSessionForm();
        $this->hydrateSessionState();

        Flux::toast(
            variant: 'success',
            text: $isEditing ? __('Đã cập nhật lịch bù.') : __('Đã tạo lịch bù mới.'),
        );
    }

    public function openDeleteModal(int $sessionId): void
    {
        $this->authorizeSessionEditing();

        $this->deletingSessionId = $sessionId;
        $this->resetErrorBag();
        $this->resetValidation();
        $this->showDeleteModal = true;
    }

    public function deleteSession(): void
    {
        $this->authorizeSessionEditing();

        $session = MakeupSession::query()
            ->with('attendanceMakeups.originalAttendance')
            ->findOrFail($this->deletingSessionId);

        DB::transaction(function () use ($session): void {
            $this->restoreOriginalAttendances($session->attendanceMakeups);
            $session->delete();
        });

        if ($this->selectedSessionId === $session->id) {
            $this->selectedSessionId = MakeupSession::query()
                ->whereKeyNot($session->id)
                ->orderByDesc('date')
                ->orderByDesc('start_time')
                ->value('id');
        }

        $this->deletingSessionId = null;
        $this->showDeleteModal = false;
        $this->hydrateSessionState();

        Flux::toast(variant: 'success', text: __('Đã xoá lịch bù.'));
    }

    public function openAssignModal(): void
    {
        $this->authorizeSessionEditing();

        if ($this->selectedSession === null) {
            Flux::toast(variant: 'warning', text: __('Hãy chọn lịch bù cần gán thiếu nhi.'));

            return;
        }

        $this->selectedOriginalAttendanceIds = [];
        $this->refreshEligibleConflictWarnings();
        $this->showAssignModal = true;
    }

    public function saveAssignments(): void
    {
        $this->authorizeSessionEditing();

        $session = $this->selectedSession;

        if ($session === null) {
            Flux::toast(variant: 'warning', text: __('Hãy chọn lịch bù trước khi gán thiếu nhi.'));

            return;
        }

        $validated = Validator::make(
            ['attendance_ids' => $this->selectedOriginalAttendanceIds],
            [
                'attendance_ids' => ['required', 'array', 'min:1'],
                'attendance_ids.*' => ['integer', 'exists:attendances,id'],
            ],
            [],
            [
                'attendance_ids' => __('danh sách thiếu nhi học bù'),
            ],
        )->validate();

        /** @var Collection<int, Attendance> $eligibleAttendances */
        $eligibleAttendances = $this->eligibleOriginalAttendances->keyBy('id');

        $selectedAttendances = collect($validated['attendance_ids'])
            ->map(fn (int $attendanceId): ?Attendance => $eligibleAttendances->get($attendanceId))
            ->filter();

        if ($selectedAttendances->count() !== count($validated['attendance_ids'])) {
            throw ValidationException::withMessages([
                'attendance_ids' => __('Có thiếu nhi không còn hợp lệ để gán vào lịch bù này.'),
            ]);
        }

        DB::transaction(function () use ($selectedAttendances, $session): void {
            foreach ($selectedAttendances as $attendance) {
                AttendanceMakeup::query()->create([
                    'original_attendance_id' => $attendance->id,
                    'makeup_session_id' => $session->id,
                    'user_id' => $attendance->user_id,
                    'original_attendance_status' => $attendance->status,
                    'status' => AttendanceMakeup::STATUS_SCHEDULED,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ]);
            }
        });

        $this->showAssignModal = false;
        $this->selectedOriginalAttendanceIds = [];
        $this->hydrateSessionState();

        Flux::toast(variant: 'success', text: __('Đã gán thiếu nhi vào lịch bù.'));
    }

    public function saveMakeupRecord(int $attendanceMakeupId): void
    {
        $session = $this->selectedSession;

        if ($session === null) {
            Flux::toast(variant: 'warning', text: __('Hãy chọn lịch bù cần nhập kết quả.'));

            return;
        }

        if (! $this->canManageSelectedSession) {
            Flux::toast(variant: 'danger', text: __('Bạn chưa được phép nhập điểm cho lịch bù này.'));

            return;
        }

        $attendanceMakeup = AttendanceMakeup::query()
            ->with('originalAttendance')
            ->findOrFail($attendanceMakeupId);

        $payload = $this->validateAssignmentRow($attendanceMakeupId);

        if ($this->attendanceMakeupPayloadWasChanged($attendanceMakeup, $payload) && $session->isSpiritScoreLocked()) {
            Flux::toast(variant: 'danger', text: __('Đã quá hạn điểm danh và điểm tinh thần cho lịch bù này.'));

            return;
        }

        if ($this->scorePayloadWasChanged($attendanceMakeup, $payload) && $session->areTheoryPracticeScoresLocked()) {
            Flux::toast(variant: 'danger', text: __('Đã quá hạn nhập điểm lý thuyết và thực hành cho lịch bù này.'));

            return;
        }

        DB::transaction(function () use ($attendanceMakeup, $payload): void {
            $attendanceMakeup->attendance_status = $payload['attendance_status'];
            $attendanceMakeup->attendance_note = $payload['attendance_note'];
            $attendanceMakeup->marked_by = Auth::id();
            $attendanceMakeup->marked_at = now();
            $attendanceMakeup->spirit_score = $this->spiritScoreForStatus($payload['attendance_status']);
            $attendanceMakeup->theory_score = $payload['theory_score'];
            $attendanceMakeup->practice_score = $payload['practice_score'];
            $attendanceMakeup->final_score = Score::computeFinalScore(
                $attendanceMakeup->spirit_score !== null ? (float) $attendanceMakeup->spirit_score : null,
                $payload['theory_score'] !== null ? (float) $payload['theory_score'] : null,
                $payload['practice_score'] !== null ? (float) $payload['practice_score'] : null,
            );
            $attendanceMakeup->result_status = Score::resultStatusForFinalScore($attendanceMakeup->final_score);
            $attendanceMakeup->status = $this->countsAsAttended($payload['attendance_status'])
                ? AttendanceMakeup::STATUS_COMPLETED
                : AttendanceMakeup::STATUS_MISSED;
            $attendanceMakeup->completed_at = $attendanceMakeup->status === AttendanceMakeup::STATUS_COMPLETED
                ? now()
                : null;
            $attendanceMakeup->save();

            $this->syncOriginalAttendanceFromMakeup($attendanceMakeup);
        });

        $this->attendanceStatuses[$attendanceMakeupId] = $payload['attendance_status'];
        $this->attendanceNotes[$attendanceMakeupId] = $payload['attendance_note'] ?? '';
        $this->theoryScores[$attendanceMakeupId] = $payload['theory_score'];
        $this->practiceScores[$attendanceMakeupId] = $payload['practice_score'];
        $this->hydrateSessionState();

        Flux::toast(variant: 'success', text: __('Đã lưu kết quả học bù.'));
    }

    public function closeSessionModal(): void
    {
        $this->showSessionModal = false;
        $this->editingSessionId = null;
        $this->resetSessionForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingSessionId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->selectedOriginalAttendanceIds = [];
        $this->eligibleConflictWarnings = [];
        $this->resetErrorBag();
        $this->resetValidation();
    }

    #[Computed]
    public function canCreate(): bool
    {
        return Auth::user()?->can('management.schedule.create') ?? false;
    }

    #[Computed]
    public function canManageSelectedSession(): bool
    {
        $session = $this->selectedSession;
        $user = Auth::user();

        if ($session === null || $user === null) {
            return false;
        }

        if (! $session->isAttendanceOpen()) {
            return false;
        }

        return $session->teacher_id === $user->id
            || $user->can('management.schedule.create')
            || $user->can('management.schedule.update');
    }

    #[Computed]
    public function sessions(): Collection
    {
        return MakeupSession::query()
            ->with(['subject', 'teacher'])
            ->withCount('attendanceMakeups')
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->get();
    }

    #[Computed]
    public function selectedSession(): ?MakeupSession
    {
        if ($this->selectedSessionId === null) {
            return null;
        }

        return MakeupSession::query()
            ->with(['subject', 'teacher'])
            ->find($this->selectedSessionId);
    }

    #[Computed]
    public function selectedSessionAssignments(): Collection
    {
        $session = $this->selectedSession;

        if ($session === null) {
            return collect();
        }

        return AttendanceMakeup::query()
            ->with([
                'user.classrooms',
                'originalAttendance.schedule.classroomSubject.classroom',
                'originalAttendance.schedule.classroomSubject.subject',
                'makeupSession.subject',
            ])
            ->where('makeup_session_id', $session->id)
            ->get()
            ->sortBy(fn (AttendanceMakeup $attendanceMakeup): string => $attendanceMakeup->user?->name ?? '')
            ->values();
    }

    #[Computed]
    public function eligibleOriginalAttendances(): Collection
    {
        $session = $this->selectedSession;

        if ($session === null) {
            return collect();
        }

        return Attendance::query()
            ->with([
                'user.classrooms',
                'schedule.classroomSubject.classroom',
                'schedule.classroomSubject.subject',
                'schedule.classroomSubject.teachers',
            ])
            ->whereIn('status', [
                Attendance::STATUS_ABSENT_EXCUSED,
                Attendance::STATUS_ABSENT_UNEXCUSED,
            ])
            ->whereDoesntHave('attendanceMakeup')
            ->whereHas('schedule.classroomSubject', function ($query) use ($session): void {
                $query->where('subject_id', $session->subject_id)
                    ->whereHas('teachers', fn ($teacherQuery) => $teacherQuery->whereKey($session->teacher_id));
            })
            ->get()
            ->sortBy([
                fn (Attendance $attendance): string => (string) $attendance->schedule?->date?->format('Y-m-d'),
                fn (Attendance $attendance): string => (string) $attendance->user?->name,
            ])
            ->values();
    }

    #[Computed]
    public function availableSubjects(): Collection
    {
        return Subject::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableTeachers(): EloquentCollection
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'giáo viên'))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function statusOptions(): array
    {
        return MakeupSession::statusOptions();
    }

    #[Computed]
    public function attendanceStatusOptions(): array
    {
        return [
            Attendance::STATUS_ON_TIME => __('Đi đúng giờ'),
            Attendance::STATUS_LATE_EXCUSED => __('Trễ có phép'),
            Attendance::STATUS_LATE_UNEXCUSED => __('Trễ không phép'),
            Attendance::STATUS_ABSENT_EXCUSED => __('Vắng có phép'),
            Attendance::STATUS_ABSENT_UNEXCUSED => __('Vắng không phép'),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.management.makeup.makeup-index');
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function sessionRules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'date_end_spirit' => ['nullable', 'date'],
            'date_end_practice_theory' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys($this->statusOptions))],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array{attendance_status: string, attendance_note: ?string, theory_score: ?string, practice_score: ?string}
     */
    protected function validateAssignmentRow(int $attendanceMakeupId): array
    {
        return Validator::make(
            [
                'attendance_status' => $this->attendanceStatuses[$attendanceMakeupId] ?? '',
                'attendance_note' => $this->attendanceNotes[$attendanceMakeupId] ?? null,
                'theory_score' => $this->normalizeOptionalNumeric($this->theoryScores[$attendanceMakeupId] ?? null),
                'practice_score' => $this->normalizeOptionalNumeric($this->practiceScores[$attendanceMakeupId] ?? null),
            ],
            [
                'attendance_status' => ['required', Rule::in(array_keys($this->attendanceStatusOptions))],
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

    protected function resetSessionForm(): void
    {
        $this->subject_id = null;
        $this->teacher_id = null;
        $this->date = null;
        $this->start_time = null;
        $this->end_time = null;
        $this->date_end_spirit = null;
        $this->date_end_practice_theory = null;
        $this->status = MakeupSession::STATUS_PENDING;
        $this->note = '';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    protected function resetAssignmentState(): void
    {
        $this->attendanceStatuses = [];
        $this->attendanceNotes = [];
        $this->theoryScores = [];
        $this->practiceScores = [];
        $this->assignmentConflictWarnings = [];
        $this->eligibleConflictWarnings = [];
    }

    protected function hydrateSessionState(): void
    {
        $this->resetAssignmentState();

        foreach ($this->selectedSessionAssignments as $assignment) {
            $this->attendanceStatuses[$assignment->id] = $assignment->attendance_status ?? '';
            $this->attendanceNotes[$assignment->id] = $assignment->attendance_note ?? '';
            $this->theoryScores[$assignment->id] = $assignment->theory_score !== null
                ? number_format((float) $assignment->theory_score, 2, '.', '')
                : null;
            $this->practiceScores[$assignment->id] = $assignment->practice_score !== null
                ? number_format((float) $assignment->practice_score, 2, '.', '')
                : null;
            $this->assignmentConflictWarnings[$assignment->id] = $this->buildUserConflictWarnings(
                userId: $assignment->user_id,
                session: $assignment->makeupSession,
                exceptAttendanceMakeupId: $assignment->id,
            );
        }
    }

    protected function refreshEligibleConflictWarnings(): void
    {
        $session = $this->selectedSession;

        if ($session === null) {
            $this->eligibleConflictWarnings = [];

            return;
        }

        $this->eligibleConflictWarnings = $this->eligibleOriginalAttendances
            ->mapWithKeys(fn (Attendance $attendance): array => [
                $attendance->id => $this->buildUserConflictWarnings(
                    userId: $attendance->user_id,
                    session: $session,
                ),
            ])
            ->all();
    }

    protected function ensureTeacherHasNoConflict(
        int $teacherId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $exceptSessionId = null,
    ): void {
        $hasRegularConflict = Schedule::query()
            ->whereDate('date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereHas('classroomSubject.teachers', fn ($query) => $query->whereKey($teacherId))
            ->exists();

        $hasMakeupConflict = MakeupSession::query()
            ->where('teacher_id', $teacherId)
            ->whereDate('date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when(
                $exceptSessionId !== null,
                fn ($query) => $query->whereKeyNot($exceptSessionId)
            )
            ->exists();

        if ($hasRegularConflict || $hasMakeupConflict) {
            throw ValidationException::withMessages([
                'teacher_id' => __('Giáo viên đã có lịch trùng trong khung giờ này.'),
            ]);
        }
    }

    /**
     * @return list<string>
     */
    protected function buildUserConflictWarnings(
        int $userId,
        MakeupSession $session,
        ?int $exceptAttendanceMakeupId = null,
    ): array {
        $scheduleWarnings = Schedule::query()
            ->with(['classroomSubject.classroom', 'classroomSubject.subject'])
            ->whereDate('date', $session->date)
            ->where('start_time', '<', $session->end_time)
            ->where('end_time', '>', $session->start_time)
            ->whereHas('classroomSubject.classroom.youths', fn ($query) => $query->whereKey($userId))
            ->get()
            ->map(function (Schedule $schedule): string {
                return __('Trùng lịch: :subject (:classroom)', [
                    'subject' => $schedule->subjectName(),
                    'classroom' => $schedule->classroomName(),
                ]);
            });

        $makeupWarnings = AttendanceMakeup::query()
            ->with('makeupSession.subject')
            ->where('user_id', $userId)
            ->when(
                $exceptAttendanceMakeupId !== null,
                fn ($query) => $query->whereKeyNot($exceptAttendanceMakeupId)
            )
            ->whereHas('makeupSession', function ($query) use ($session): void {
                $query->whereDate('date', $session->date)
                    ->where('start_time', '<', $session->end_time)
                    ->where('end_time', '>', $session->start_time)
                    ->whereKeyNot($session->id);
            })
            ->get()
            ->map(function (AttendanceMakeup $attendanceMakeup): string {
                return __('Trùng lịch bù: :subject', [
                    'subject' => $attendanceMakeup->makeupSession?->subjectName() ?? __('Không rõ môn'),
                ]);
            });

        return $scheduleWarnings
            ->merge($makeupWarnings)
            ->unique()
            ->values()
            ->all();
    }

    protected function syncOriginalAttendanceFromMakeup(AttendanceMakeup $attendanceMakeup): void
    {
        $originalAttendance = $attendanceMakeup->originalAttendance;

        if ($originalAttendance === null) {
            return;
        }

        if ($attendanceMakeup->countsAsAttended()) {
            $originalAttendance->status = Attendance::STATUS_MAKEUP_COMPLETED;
            $originalAttendance->makeup_completed_at = $attendanceMakeup->completed_at ?? now();
        } else {
            $originalAttendance->status = $attendanceMakeup->original_attendance_status;
            $originalAttendance->makeup_completed_at = null;
        }

        $originalAttendance->save();
    }

    /**
     * @param  EloquentCollection<int, AttendanceMakeup>  $attendanceMakeups
     */
    protected function restoreOriginalAttendances(EloquentCollection $attendanceMakeups): void
    {
        foreach ($attendanceMakeups as $attendanceMakeup) {
            $originalAttendance = $attendanceMakeup->originalAttendance;

            if ($originalAttendance === null) {
                continue;
            }

            $originalAttendance->status = $attendanceMakeup->original_attendance_status;
            $originalAttendance->makeup_completed_at = null;
            $originalAttendance->save();
        }
    }

    protected function authorizeSessionEditing(): void
    {
        abort_unless(
            Auth::user()?->can('management.schedule.create')
                || Auth::user()?->can('management.schedule.update'),
            403,
        );
    }

    protected function normalizeOptionalNumeric(mixed $value): ?string
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
        $normalized = $this->normalizeOptionalNumeric($value);

        if ($normalized === null || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
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

    protected function countsAsAttended(?string $status): bool
    {
        return in_array($status, [
            Attendance::STATUS_ON_TIME,
            Attendance::STATUS_LATE_EXCUSED,
            Attendance::STATUS_LATE_UNEXCUSED,
        ], true);
    }

    public function previewFinalScore(int $attendanceMakeupId): ?float
    {
        return Score::computeFinalScore(
            $this->previewSpiritScore($attendanceMakeupId),
            $this->normalizeOptionalFloat($this->theoryScores[$attendanceMakeupId] ?? null),
            $this->normalizeOptionalFloat($this->practiceScores[$attendanceMakeupId] ?? null),
        );
    }

    public function previewSpiritScore(int $attendanceMakeupId): ?float
    {
        return $this->spiritScoreForStatus($this->attendanceStatuses[$attendanceMakeupId] ?? null);
    }

    public function previewResultStatus(int $attendanceMakeupId): string
    {
        return Score::resultStatusForFinalScore($this->previewFinalScore($attendanceMakeupId));
    }

    /**
     * @param  array{attendance_status: string, attendance_note: ?string, theory_score: ?string, practice_score: ?string}  $payload
     */
    protected function attendanceMakeupPayloadWasChanged(AttendanceMakeup $attendanceMakeup, array $payload): bool
    {
        return $attendanceMakeup->attendance_status !== $payload['attendance_status']
            || (string) ($attendanceMakeup->attendance_note ?? '') !== (string) ($payload['attendance_note'] ?? '');
    }

    /**
     * @param  array{attendance_status: string, attendance_note: ?string, theory_score: ?string, practice_score: ?string}  $payload
     */
    protected function scorePayloadWasChanged(AttendanceMakeup $attendanceMakeup, array $payload): bool
    {
        return $this->normalizeOptionalNumeric($attendanceMakeup->theory_score) !== $payload['theory_score']
            || $this->normalizeOptionalNumeric($attendanceMakeup->practice_score) !== $payload['practice_score'];
    }
}
