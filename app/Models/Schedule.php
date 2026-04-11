<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['classroom_subject_id', 'date', 'start_time', 'end_time', 'type', 'status', 'have_record', 'date_end_spirit', 'date_end_practice_theory'])]
class Schedule extends Model
{
    public const TYPE_STUDY = 'study';

    public const TYPE_EXAM = 'exam';

    public const TYPE_CAMP = 'camp';

    public const TYPE_REMINDER = 'reminder';

    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'have_record' => 'boolean',
            'date' => 'date',
            'date_end_spirit' => 'date',
            'date_end_practice_theory' => 'date',
        ];
    }

    public function classroomSubject(): BelongsTo
    {
        return $this->belongsTo(ClassroomSubject::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function isSpiritScoreLocked(?CarbonInterface $at = null): bool
    {
        $deadline = $this->spiritScoreDeadlineAt();

        if (! $this->have_record || $deadline === null) {
            return false;
        }

        return ($at ?? now())->greaterThan($deadline);
    }

    public function areTheoryPracticeScoresLocked(?CarbonInterface $at = null): bool
    {
        $deadline = $this->theoryPracticeScoreDeadlineAt();

        if (! $this->have_record || $deadline === null) {
            return false;
        }

        return ($at ?? now())->greaterThan($deadline);
    }

    public function spiritScoreDeadlineAt(): ?CarbonInterface
    {
        return $this->resolveScoreDeadline($this->date_end_spirit);
    }

    public function startsAt(): ?CarbonInterface
    {
        return $this->resolveScoreDeadline($this->date);
    }

    public function theoryPracticeScoreDeadlineAt(): ?CarbonInterface
    {
        return $this->resolveScoreDeadline($this->date_end_practice_theory);
    }

    public function isAttendanceOpen(?CarbonInterface $at = null): bool
    {
        $startsAt = $this->startsAt();

        if (! $this->have_record || $startsAt === null) {
            return false;
        }

        return ($at ?? now())->greaterThanOrEqualTo($startsAt);
    }

    protected function resolveScoreDeadline(?CarbonInterface $date): ?CarbonInterface
    {
        if ($date === null) {
            return null;
        }

        if (blank($this->start_time)) {
            return $date->copy()->endOfDay();
        }

        [$hour, $minute, $second] = array_pad(explode(':', $this->start_time), 3, '00');

        return $date->copy()->setTime((int) $hour, (int) $minute, (int) $second);
    }

    public function subjectName(): string
    {
        return $this->classroomSubject?->subject?->name ?? '';
    }

    public function classroomName(): string
    {
        return $this->classroomSubject?->classroom?->code ?? '';
    }

    public function teacherName(): string
    {
        return $this->classroomSubject?->teachersLabel() ?? '';
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_STUDY => __('Lịch học'),
            self::TYPE_EXAM => __('Lịch thi'),
            self::TYPE_CAMP => __('Lịch đi trại'),
            self::TYPE_REMINDER => __('Dặn dò'),
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? $this->type;
    }

    /**
     * @return array{background_class: string, border_class: string, hover_class: string, dot_class: string}
     */
    public function calendarColorClasses(): array
    {
        return match ($this->type) {
            self::TYPE_EXAM => [
                'background_class' => 'bg-red-50/95 dark:bg-red-950/40',
                'border_class' => 'border-red-200 dark:border-red-900/70',
                'hover_class' => 'hover:border-red-400 hover:shadow-sm hover:shadow-red-100/40 dark:hover:bg-red-950/55',
                'dot_class' => 'bg-red-500',
            ],
            self::TYPE_CAMP => [
                'background_class' => 'bg-yellow-50/95 dark:bg-yellow-950/35',
                'border_class' => 'border-yellow-200 dark:border-yellow-900/70',
                'hover_class' => 'hover:border-yellow-400 hover:shadow-sm hover:shadow-yellow-100/40 dark:hover:bg-yellow-950/45',
                'dot_class' => 'bg-yellow-500',
            ],
            self::TYPE_REMINDER => [
                'background_class' => 'bg-orange-50/95 dark:bg-orange-950/35',
                'border_class' => 'border-orange-200 dark:border-orange-900/70',
                'hover_class' => 'hover:border-orange-400 hover:shadow-sm hover:shadow-orange-100/40 dark:hover:bg-orange-950/45',
                'dot_class' => 'bg-orange-500',
            ],
            default => [
                'background_class' => 'bg-cyan-50/95 dark:bg-cyan-950/35',
                'border_class' => 'border-cyan-200 dark:border-cyan-900/70',
                'hover_class' => 'hover:border-cyan-400 hover:shadow-sm hover:shadow-cyan-100/40 dark:hover:bg-cyan-950/45',
                'dot_class' => 'bg-cyan-500',
            ],
        };
    }
}
