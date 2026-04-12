<?php

namespace App\Livewire\Admin\Genernal;

use App\Models\Score;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DashboardScore extends Component
{

    #[Computed]
    public function scoreRows(): Collection
    {
        $user = Auth::user();

        if ($user === null) {
            return collect();
        }

        return Score::query()
            ->whereBelongsTo($user)
            ->with(['schedule.classroomSubject.classroom', 'schedule.classroomSubject.subject'])
            ->whereHas('schedule')
            ->latest('id')
            ->get()
            ->sortByDesc(fn(Score $score): int => $score->schedule?->date?->getTimestamp() ?? 0)
            ->values();
    }

    #[Computed]
    public function subjectSummaries(): Collection
    {
        return $this->scoreRows
            ->groupBy(fn(Score $score): string => (string) ($score->schedule?->classroom_subject_id ?? 'unknown'))
            ->map(function (Collection $scores): array {
                /** @var Score $firstScore */
                $firstScore = $scores->first();

                $completedScores = $scores->filter(fn(Score $score): bool => $score->final_score !== null);
                $averageScore = $completedScores->isNotEmpty()
                    ? round($completedScores->avg(fn(Score $score): float => (float) $score->final_score), 2)
                    : null;
                $lessonCount = $scores->count();
                $completedCount = $completedScores->count();

                return [
                    'key' => (string) ($firstScore->schedule?->classroom_subject_id ?? 'unknown'),
                    'subject_name' => $firstScore->schedule?->subjectName() ?: __('Chưa có môn học'),
                    'classroom_name' => $firstScore->schedule?->classroomName() ?: '—',
                    'lesson_count' => $lessonCount,
                    'completed_count' => $completedCount,
                    'completion_rate' => $lessonCount > 0
                        ? (int) round(($completedCount / $lessonCount) * 100)
                        : 0,
                    'average_score' => $averageScore,
                ];
            })
            ->sortByDesc(fn(array $summary): float => $summary['average_score'] ?? -1)
            ->values();
    }

    /**
     * @return array{
     *     average_score: ?float,
     *     tracked_subject_count: int,
     *     completed_lesson_count: int,
     *     total_lesson_count: int,
     *     passed_subject_count: int
     * }
     */
    #[Computed]
    public function scoreOverview(): array
    {
        $completedScores = $this->scoreRows->filter(fn(Score $score): bool => $score->final_score !== null);
        $averageScore = $completedScores->isNotEmpty()
            ? round($completedScores->avg(fn(Score $score): float => (float) $score->final_score), 2)
            : null;
        $subjectSummaries = $this->subjectSummaries;

        return [
            'average_score' => $averageScore,
            'tracked_subject_count' => $subjectSummaries->count(),
            'completed_lesson_count' => $completedScores->count(),
            'total_lesson_count' => $this->scoreRows->count(),
            'passed_subject_count' => $subjectSummaries
                ->filter(fn(array $summary): bool => $summary['average_score'] !== null && $summary['average_score'] >= Score::PASSING_SCORE)
                ->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.genernal.dashboard-score', [
            'currentUser' => Auth::user(),
        ]);
    }
}
