<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceMakeup;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Score;
use App\Models\Subject;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Điểm tổng hợp')]
class ScoreSummaryIndex extends Component
{
    public ?int $selectedClassroomId = null;

    public ?int $selectedSubjectId = null;

    public string $search = '';

    public ?int $detailUserId = null;

    public ?int $detailSubjectId = null;

    public bool $showDetailModal = false;

    public function updatedSelectedClassroomId(): void
    {
        unset($this->subjects, $this->youths, $this->scoreEntries, $this->scoreMatrix);
    }

    public function updatedSelectedSubjectId(): void
    {
        unset($this->subjects, $this->scoreEntries, $this->scoreMatrix);
    }

    public function updatedSearch(): void
    {
        unset($this->youths, $this->scoreMatrix);
    }

    public function openDetail(int $userId, int $subjectId): void
    {
        $this->detailUserId = $userId;
        $this->detailSubjectId = $subjectId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailUserId = null;
        $this->detailSubjectId = null;
    }

    #[Computed]
    public function classrooms(): Collection
    {
        return Classroom::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function subjects(): Collection
    {
        $subjects = Subject::query()
            ->when(
                $this->selectedSubjectId !== null,
                fn ($query) => $query->whereKey($this->selectedSubjectId)
            )
            ->get(['id', 'name', 'code']);

        $schedulePositions = $this->subjectSchedulePositions();

        return $subjects
            ->sortBy(
                fn (Subject $subject): string => $schedulePositions->get($subject->id, '9999-12-31|23:59:59|9999999999').'|'.$subject->name
            )
            ->values();
    }

    #[Computed]
    public function availableSubjects(): Collection
    {
        return Subject::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function youths(): Collection
    {
        return User::query()
            ->role('thiếu nhi')
            ->with('classrooms:id,name,code')
            ->when(
                $this->selectedClassroomId !== null,
                fn ($query) => $query->whereHas(
                    'classrooms',
                    fn ($classroomQuery) => $classroomQuery->whereKey($this->selectedClassroomId)
                )
            )
            ->when(filled($this->search), function ($query): void {
                $search = trim($this->search);

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('holy_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function scoreEntries(): Collection
    {
        return $this->regularScoreEntries()
            ->merge($this->makeupScoreEntries())
            ->sortBy(fn (array $entry): string => ($entry['date'] ?? '').'|'.$entry['type'])
            ->values();
    }

    #[Computed]
    public function scoreMatrix(): Collection
    {
        return $this->scoreEntries
            ->groupBy('user_id')
            ->map(function (Collection $userEntries): Collection {
                return $userEntries
                    ->groupBy('subject_id')
                    ->map(function (Collection $subjectEntries): array {
                        $scoredEntries = $subjectEntries->filter(fn (array $entry): bool => $entry['final_score'] !== null);

                        return [
                            'average' => $scoredEntries->isNotEmpty()
                                ? round($scoredEntries->avg('final_score'), 2)
                                : null,
                            'entries_count' => $subjectEntries->count(),
                            'scored_count' => $scoredEntries->count(),
                            'missing_count' => $subjectEntries->whereNull('final_score')->count(),
                            'result_status' => Score::resultStatusForFinalScore(
                                $scoredEntries->isNotEmpty() ? (float) $scoredEntries->avg('final_score') : null,
                            ),
                        ];
                    });
            });
    }

    #[Computed]
    public function detailRows(): Collection
    {
        if ($this->detailUserId === null || $this->detailSubjectId === null) {
            return collect();
        }

        return $this->scoreEntries
            ->where('user_id', $this->detailUserId)
            ->where('subject_id', $this->detailSubjectId)
            ->values();
    }

    #[Computed]
    public function detailUser(): ?User
    {
        if ($this->detailUserId === null) {
            return null;
        }

        return User::query()->find($this->detailUserId);
    }

    #[Computed]
    public function detailSubject(): ?Subject
    {
        if ($this->detailSubjectId === null) {
            return null;
        }

        return Subject::query()->find($this->detailSubjectId);
    }

    public function resultLabel(?string $status): string
    {
        return match ($status) {
            Score::RESULT_PASSED => __('Đạt'),
            Score::RESULT_FAILED => __('Chưa đạt'),
            default => __('Thiếu điểm'),
        };
    }

    public function attendanceStatusLabel(?string $status): string
    {
        return match ($status) {
            Attendance::STATUS_ON_TIME => __('Đi đúng giờ'),
            Attendance::STATUS_LATE_EXCUSED => __('Trễ có phép'),
            Attendance::STATUS_LATE_UNEXCUSED => __('Trễ không phép'),
            Attendance::STATUS_ABSENT_EXCUSED => __('Vắng có phép'),
            Attendance::STATUS_ABSENT_UNEXCUSED => __('Vắng không phép'),
            Attendance::STATUS_MAKEUP_COMPLETED => __('Đã học bù'),
            default => $status ?: '—',
        };
    }

    public function render(): View
    {
        return view('livewire.admin.attendance.score-summary-index');
    }

    protected function regularScoreEntries(): Collection
    {
        $scores = Score::query()
            ->with([
                'user',
                'schedule.classroomSubject.classroom',
                'schedule.classroomSubject.subject',
                'schedule.classroomSubject.teachers',
            ])
            ->whereHas('user.roles', fn ($query) => $query->where('name', 'thiếu nhi'))
            ->whereHas('schedule.classroomSubject.subject')
            ->when(
                $this->selectedSubjectId !== null,
                fn ($query) => $query->whereHas(
                    'schedule.classroomSubject',
                    fn ($assignmentQuery) => $assignmentQuery->where('subject_id', $this->selectedSubjectId)
                )
            )
            ->when(
                $this->selectedClassroomId !== null,
                fn ($query) => $query->whereHas(
                    'schedule.classroomSubject',
                    fn ($assignmentQuery) => $assignmentQuery->where('classroom_id', $this->selectedClassroomId)
                )
            )
            ->get();

        $attendances = Attendance::query()
            ->with('markedBy')
            ->whereIn('schedule_id', $scores->pluck('schedule_id')->unique())
            ->whereIn('user_id', $scores->pluck('user_id')->unique())
            ->get()
            ->keyBy(fn (Attendance $attendance): string => "{$attendance->schedule_id}:{$attendance->user_id}");

        return $scores
            ->map(function (Score $score) use ($attendances): array {
                $schedule = $score->schedule;
                $assignment = $schedule?->classroomSubject;
                $subject = $assignment?->subject;
                /** @var Attendance|null $attendance */
                $attendance = $attendances->get("{$score->schedule_id}:{$score->user_id}");

                return [
                    'type' => 'regular',
                    'type_label' => __('Lịch học'),
                    'user_id' => $score->user_id,
                    'subject_id' => $subject?->id,
                    'date' => $schedule?->date?->format('Y-m-d'),
                    'date_label' => $schedule?->date?->format('d/m/Y') ?? '—',
                    'classroom_label' => $assignment?->classroom?->code ?: ($assignment?->classroom?->name ?? '—'),
                    'teacher_label' => $attendance?->markedBy?->name
                        ?? $assignment?->teachers?->pluck('name')->filter()->implode(', ')
                        ?: '—',
                    'attendance_status' => $attendance?->status,
                    'attendance_note' => $attendance?->note,
                    'spirit_score' => $score->spirit_score !== null ? (float) $score->spirit_score : null,
                    'theory_score' => $score->theory_score !== null ? (float) $score->theory_score : null,
                    'practice_score' => $score->practice_score !== null ? (float) $score->practice_score : null,
                    'final_score' => $score->final_score !== null ? (float) $score->final_score : null,
                    'result_status' => $score->result_status,
                    'original_label' => null,
                ];
            })
            ->filter(fn (array $entry): bool => $entry['subject_id'] !== null)
            ->values();
    }

    protected function makeupScoreEntries(): Collection
    {
        return AttendanceMakeup::query()
            ->with([
                'user',
                'markedBy',
                'makeupSession.subject',
                'makeupSession.teacher',
                'originalAttendance.schedule.classroomSubject.classroom',
                'originalAttendance.schedule.classroomSubject.subject',
            ])
            ->whereHas('user.roles', fn ($query) => $query->where('name', 'thiếu nhi'))
            ->whereHas('makeupSession.subject')
            ->when(
                $this->selectedSubjectId !== null,
                fn ($query) => $query->whereHas(
                    'makeupSession',
                    fn ($sessionQuery) => $sessionQuery->where('subject_id', $this->selectedSubjectId)
                )
            )
            ->when(
                $this->selectedClassroomId !== null,
                fn ($query) => $query->whereHas(
                    'originalAttendance.schedule.classroomSubject',
                    fn ($assignmentQuery) => $assignmentQuery->where('classroom_id', $this->selectedClassroomId)
                )
            )
            ->get()
            ->map(function (AttendanceMakeup $attendanceMakeup): array {
                $session = $attendanceMakeup->makeupSession;
                $originalSchedule = $attendanceMakeup->originalAttendance?->schedule;
                $originalAssignment = $originalSchedule?->classroomSubject;

                return [
                    'type' => 'makeup',
                    'type_label' => __('Lịch bù'),
                    'user_id' => $attendanceMakeup->user_id,
                    'subject_id' => $session?->subject_id,
                    'date' => $session?->date?->format('Y-m-d'),
                    'date_label' => $session?->date?->format('d/m/Y') ?? '—',
                    'classroom_label' => $originalAssignment?->classroom?->code ?: ($originalAssignment?->classroom?->name ?? '—'),
                    'teacher_label' => $attendanceMakeup->markedBy?->name
                        ?? $session?->teacher?->name
                        ?: '—',
                    'attendance_status' => $attendanceMakeup->attendance_status,
                    'attendance_note' => $attendanceMakeup->attendance_note,
                    'spirit_score' => $attendanceMakeup->spirit_score !== null ? (float) $attendanceMakeup->spirit_score : null,
                    'theory_score' => $attendanceMakeup->theory_score !== null ? (float) $attendanceMakeup->theory_score : null,
                    'practice_score' => $attendanceMakeup->practice_score !== null ? (float) $attendanceMakeup->practice_score : null,
                    'final_score' => $attendanceMakeup->final_score !== null ? (float) $attendanceMakeup->final_score : null,
                    'result_status' => $attendanceMakeup->result_status,
                    'original_label' => $originalSchedule?->date?->format('d/m/Y').' | '.$originalSchedule?->subjectName(),
                ];
            })
            ->filter(fn (array $entry): bool => $entry['subject_id'] !== null)
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    protected function subjectSchedulePositions(): Collection
    {
        return $this->schedulePositionRows()
            ->reduce(function (Collection $positions, array $row): Collection {
                $subjectId = $row['subject_id'];
                $position = $this->schedulePositionKey(
                    $row['date'],
                    $row['start_time'],
                    $row['id']
                );

                if (! $positions->has($subjectId) || $position < $positions->get($subjectId)) {
                    $positions->put($subjectId, $position);
                }

                return $positions;
            }, collect());
    }

    protected function schedulePositionRows(): Collection
    {
        return Schedule::query()
            ->join('classroom_subject', 'schedules.classroom_subject_id', '=', 'classroom_subject.id')
            ->when(
                $this->selectedClassroomId !== null,
                fn ($query) => $query->where('classroom_subject.classroom_id', $this->selectedClassroomId)
            )
            ->when(
                $this->selectedSubjectId !== null,
                fn ($query) => $query->where('classroom_subject.subject_id', $this->selectedSubjectId)
            )
            ->get([
                'schedules.id',
                'schedules.date',
                'schedules.start_time',
                'classroom_subject.subject_id as subject_id',
            ])
            ->map(fn (Schedule $schedule): array => [
                'id' => (int) $schedule->id,
                'subject_id' => (int) $schedule->getAttribute('subject_id'),
                'date' => $this->dateString($schedule->date),
                'start_time' => $schedule->start_time,
            ]);
    }

    protected function schedulePositionKey(string $date, ?string $startTime, int $id): string
    {
        return sprintf(
            '%s|%s|%010d',
            $date,
            filled($startTime) ? $startTime : '23:59:59',
            $id
        );
    }

    protected function dateString(CarbonInterface|string|null $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->toDateString();
        }

        return (string) $date;
    }
}
