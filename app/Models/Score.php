<?php

namespace App\Models;

use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

#[Fillable([
    'schedule_id',
    'user_id',
    'spirit_score',
    'theory_score',
    'practice_score',
    'final_score',
    'result_status',
])]
class Score extends Model
{
    use HasFactory;

    public const RESULT_PENDING = 'pending';

    public const RESULT_PASSED = 'passed';

    public const RESULT_FAILED = 'failed';

    public const PASSING_SCORE = 7.5;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'spirit_score' => 'decimal:2',
            'theory_score' => 'decimal:2',
            'practice_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'spirit_updated_at' => 'datetime',
            'theory_updated_at' => 'datetime',
            'practice_updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $score): void {
            $score->loadMissing('schedule');
            $score->ensureEditable();
            $score->stampScoreUpdates();
            $score->syncComputedFields();
        });

        static::created(function (self $score): void {
            $score->writeCreatedHistoryEntries();
        });

        static::updated(function (self $score): void {
            $score->writeUpdatedHistoryEntries();
        });
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function spiritUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'spirit_updated_by');
    }

    public function theoryUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'theory_updated_by');
    }

    public function practiceUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'practice_updated_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ScoreHistory::class);
    }

    public function hasCompleteScores(): bool
    {
        return $this->spirit_score !== null
            && $this->theory_score !== null
            && $this->practice_score !== null;
    }

    /**
     * Điểm tổng buổi: trung bình của (trung bình LT + TH) và điểm tinh thần — tức TT 50%, LT 25%, TH 25%.
     */
    public static function computeFinalScore(?float $spirit, ?float $theory, ?float $practice): ?float
    {
        if ($spirit === null || $theory === null || $practice === null) {
            return null;
        }

        $theoryAndPracticeAverage = ($theory + $practice) / 2;

        return round((($theoryAndPracticeAverage + $spirit) / 2), 2);
    }

    public function calculateFinalScore(): ?float
    {
        if (! $this->hasCompleteScores()) {
            return null;
        }

        return self::computeFinalScore(
            (float) $this->spirit_score,
            (float) $this->theory_score,
            (float) $this->practice_score,
        );
    }

    public static function resultStatusForFinalScore(?float $finalScore): string
    {
        if ($finalScore === null) {
            return self::RESULT_PENDING;
        }

        return $finalScore >= self::PASSING_SCORE
            ? self::RESULT_PASSED
            : self::RESULT_FAILED;
    }

    public function determineResultStatus(): string
    {
        return self::resultStatusForFinalScore($this->calculateFinalScore());
    }

    public function syncComputedFields(): void
    {
        $this->final_score = $this->calculateFinalScore();
        $this->result_status = $this->determineResultStatus();
    }

    protected function ensureEditable(): void
    {
        if ($this->isDirty('spirit_score') && $this->schedule?->isSpiritScoreLocked()) {
            throw new DomainException('Tinh than da qua han cap nhat.');
        }

        if (($this->isDirty('theory_score') || $this->isDirty('practice_score')) && $this->schedule?->areTheoryPracticeScoresLocked()) {
            throw new DomainException('Ly thuyet va thuc hanh da qua han cap nhat.');
        }
    }

    protected function stampScoreUpdates(): void
    {
        $editorId = Auth::id();
        $timestamp = Carbon::now();

        if ($this->isDirty('spirit_score')) {
            $this->spirit_updated_by = $editorId;
            $this->spirit_updated_at = $timestamp;
        }

        if ($this->isDirty('theory_score')) {
            $this->theory_updated_by = $editorId;
            $this->theory_updated_at = $timestamp;
        }

        if ($this->isDirty('practice_score')) {
            $this->practice_updated_by = $editorId;
            $this->practice_updated_at = $timestamp;
        }
    }

    protected function writeCreatedHistoryEntries(): void
    {
        $fields = collect($this->auditableFields())
            ->filter(fn (string $field): bool => $this->getAttribute($field) !== null)
            ->all();

        $this->createHistoryEntries($fields, []);
    }

    protected function writeUpdatedHistoryEntries(): void
    {
        $changes = $this->getChanges();

        $fields = collect($this->auditableFields())
            ->filter(fn (string $field): bool => array_key_exists($field, $changes))
            ->all();

        $this->createHistoryEntries($fields, $this->getPrevious());
    }

    /**
     * @param  list<string>  $fields
     * @param  array<string, mixed>  $previous
     */
    protected function createHistoryEntries(array $fields, array $previous): void
    {
        if ($fields === []) {
            return;
        }

        $this->loadMissing('schedule');

        $changedAt = Carbon::now();
        $changedBy = Auth::id();

        $entries = collect($fields)
            ->map(function (string $field) use ($previous, $changedAt, $changedBy): array {
                $deadline = $this->deadlineForField($field);
                $isLate = $deadline !== null && $changedAt->greaterThan($deadline);

                return [
                    'schedule_id' => $this->schedule_id,
                    'user_id' => $this->user_id,
                    'field_name' => $field,
                    'old_value' => $this->stringifyHistoryValue($previous[$field] ?? null),
                    'new_value' => $this->stringifyHistoryValue($this->getAttribute($field)),
                    'changed_by' => $changedBy,
                    'changed_at' => $changedAt,
                    'deadline_at' => $deadline,
                    'is_late' => $isLate,
                    'late_by_minutes' => $isLate && $deadline !== null ? $deadline->diffInMinutes($changedAt) : null,
                ];
            })
            ->all();

        $this->histories()->createMany($entries);
    }

    /**
     * @return list<string>
     */
    protected function auditableFields(): array
    {
        return [
            'spirit_score',
            'theory_score',
            'practice_score',
            'final_score',
            'result_status',
        ];
    }

    protected function deadlineForField(string $field): ?CarbonInterface
    {
        return match ($field) {
            'spirit_score' => $this->schedule?->spiritScoreDeadlineAt(),
            'theory_score', 'practice_score', 'final_score', 'result_status' => $this->schedule?->theoryPracticeScoreDeadlineAt(),
            default => null,
        };
    }

    protected function stringifyHistoryValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }
}
