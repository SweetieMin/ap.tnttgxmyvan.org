<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['classroom_id', 'subject_id', 'status'])]
class ClassroomSubject extends Model
{
    /** @use HasFactory<ClassroomSubjectFactory> */
    use HasFactory;

    protected $table = 'classroom_subject';

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function label(): string
    {
        return collect([
            $this->classroom?->code,
            $this->subject?->name,
            $this->teachersLabel(),
        ])->filter()->implode(' - ');
    }

    public function teachersLabel(): string
    {
        return $this->teachers
            ->pluck('name')
            ->filter()
            ->implode(', ');
    }
}
