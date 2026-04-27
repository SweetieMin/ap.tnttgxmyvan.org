<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['schedule_id', 'user_id', 'status', 'note', 'marked_by', 'marked_at', 'makeup_completed_at'])]
class Attendance extends Model
{
    use HasFactory;

    public const STATUS_ON_TIME = 'on_time';

    public const STATUS_LATE_EXCUSED = 'late_excused';

    public const STATUS_LATE_UNEXCUSED = 'late_unexcused';

    public const STATUS_ABSENT_EXCUSED = 'absent_excused';

    public const STATUS_ABSENT_UNEXCUSED = 'absent_unexcused';

    public const STATUS_MAKEUP_COMPLETED = 'makeup_completed';

    protected function casts(): array
    {
        return [
            'marked_at' => 'datetime',
            'makeup_completed_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function attendanceMakeup(): HasOne
    {
        return $this->hasOne(AttendanceMakeup::class, 'original_attendance_id');
    }

    public function suggestedSpiritScore(): float
    {
        return match ($this->status) {
            self::STATUS_ON_TIME => 10.0,
            self::STATUS_LATE_EXCUSED => 7.0,
            self::STATUS_LATE_UNEXCUSED => 5.0,
            self::STATUS_ABSENT_EXCUSED => 0.0,
            self::STATUS_ABSENT_UNEXCUSED => -10.0,
            self::STATUS_MAKEUP_COMPLETED => 8.0,
            default => 0.0,
        };
    }
}
