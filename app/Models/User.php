<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['holy_name', 'name', 'birthday', 'username', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the schedules associated with the user.
     */
    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'attendances')
            ->withPivot(['status', 'note', 'marked_by', 'marked_at', 'makeup_completed_at'])
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class)->withTimestamps();
    }

    public function taughtClassroomSubjects(): BelongsToMany
    {
        return $this->belongsToMany(ClassroomSubject::class)->withTimestamps();
    }

    public function teachingAssignments(): BelongsToMany
    {
        return $this->belongsToMany(ClassroomSubject::class)->withTimestamps();
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function attendanceMakeups(): HasMany
    {
        return $this->hasMany(AttendanceMakeup::class);
    }

    public function makeupSessions(): HasMany
    {
        return $this->hasMany(MakeupSession::class, 'teacher_id');
    }

    public function scoreHistories(): HasMany
    {
        return $this->hasMany(ScoreHistory::class, 'changed_by');
    }

    protected static function booted()
    {
        static::saving(function ($user) {
            if (blank($user->email)) {
                $user->email = null;
            }
        });
    }
}
