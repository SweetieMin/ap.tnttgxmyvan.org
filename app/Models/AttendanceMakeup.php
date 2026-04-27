<?php

namespace App\Models;

use Database\Factories\AttendanceMakeupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'original_attendance_id',
    'makeup_session_id',
    'user_id',
    'original_attendance_status',
    'status',
    'attendance_status',
    'attendance_note',
    'assigned_by',
    'assigned_at',
    'marked_by',
    'marked_at',
    'completed_at',
    'spirit_score',
    'theory_score',
    'practice_score',
    'final_score',
    'result_status',
    'note',
])]
class AttendanceMakeup extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_MISSED = 'missed';

    /** @use HasFactory<AttendanceMakeupFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'marked_at' => 'datetime',
            'completed_at' => 'datetime',
            'spirit_score' => 'decimal:2',
            'theory_score' => 'decimal:2',
            'practice_score' => 'decimal:2',
            'final_score' => 'decimal:2',
        ];
    }

    public function originalAttendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'original_attendance_id');
    }

    public function makeupSession(): BelongsTo
    {
        return $this->belongsTo(MakeupSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function suggestedSpiritScore(): ?float
    {
        if (blank($this->attendance_status)) {
            return null;
        }

        $attendance = new Attendance([
            'status' => $this->attendance_status,
        ]);

        return $attendance->suggestedSpiritScore();
    }

    public function countsAsAttended(): bool
    {
        return in_array($this->attendance_status, [
            Attendance::STATUS_ON_TIME,
            Attendance::STATUS_LATE_EXCUSED,
            Attendance::STATUS_LATE_UNEXCUSED,
        ], true);
    }
}
