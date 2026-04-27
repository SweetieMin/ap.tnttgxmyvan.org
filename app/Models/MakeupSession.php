<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\MakeupSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'subject_id',
    'teacher_id',
    'date',
    'start_time',
    'end_time',
    'status',
    'date_end_spirit',
    'date_end_practice_theory',
    'have_record',
    'note',
    'created_by',
])]
class MakeupSession extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    /** @use HasFactory<MakeupSessionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'date_end_spirit' => 'date',
            'date_end_practice_theory' => 'date',
            'have_record' => 'boolean',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendanceMakeups(): HasMany
    {
        return $this->hasMany(AttendanceMakeup::class);
    }

    public function startsAt(): ?CarbonInterface
    {
        return $this->resolveDateTime($this->date);
    }

    public function isAttendanceOpen(?CarbonInterface $at = null): bool
    {
        $startsAt = $this->startsAt();

        if (! $this->have_record || $startsAt === null) {
            return false;
        }

        return ($at ?? now())->greaterThanOrEqualTo($startsAt);
    }

    public function spiritScoreDeadlineAt(): ?CarbonInterface
    {
        return $this->resolveDateTime($this->date_end_spirit);
    }

    public function theoryPracticeScoreDeadlineAt(): ?CarbonInterface
    {
        return $this->resolveDateTime($this->date_end_practice_theory);
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

    public function subjectName(): string
    {
        return $this->subject?->name ?? '';
    }

    public function teacherName(): string
    {
        return $this->teacher?->name ?? '';
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => __('Sắp diễn ra'),
            self::STATUS_IN_PROGRESS => __('Đang học'),
            self::STATUS_RESOLVED => __('Hoàn tất'),
            self::STATUS_CLOSED => __('Đã huỷ'),
        ];
    }

    protected function resolveDateTime(?CarbonInterface $date): ?CarbonInterface
    {
        if ($date === null) {
            return null;
        }

        [$hour, $minute, $second] = array_pad(explode(':', (string) $this->start_time), 3, '00');

        return $date->copy()->setTime((int) $hour, (int) $minute, (int) $second);
    }
}
