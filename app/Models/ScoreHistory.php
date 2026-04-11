<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'score_id',
    'schedule_id',
    'user_id',
    'field_name',
    'old_value',
    'new_value',
    'changed_by',
    'changed_at',
    'deadline_at',
    'is_late',
    'late_by_minutes',
    'note',
])]
class ScoreHistory extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
            'deadline_at' => 'datetime',
            'is_late' => 'boolean',
        ];
    }

    public function score(): BelongsTo
    {
        return $this->belongsTo(Score::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
