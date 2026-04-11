<?php

namespace App\Livewire\Admin\Dashboard;

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
            ->sortByDesc(fn (Score $score): int => $score->schedule?->date?->getTimestamp() ?? 0)
            ->values();
    }

    #[Computed]
    public function subjectSummaries(): Collection
    {
        return $this->scoreRows
            ->groupBy(fn (Score $score): string => (string) $score->schedule?->classroomSubject?->subject?->id)
            ->map(function (Collection $scores): array {
                /** @var Score $firstScore */
                $firstScore = $scores->first();

                $completedScores = $scores->filter(fn (Score $score): bool => $score->final_score !== null);
                $averageScore = $completedScores->isNotEmpty()
                    ? round($completedScores->avg(fn (Score $score): float => (float) $score->final_score), 2)
                    : null;

                return [
                    'subject_name' => $firstScore->schedule?->subjectName() ?: __('Chưa có môn học'),
                    'classroom_name' => $firstScore->schedule?->classroomName() ?: '—',
                    'lesson_count' => $scores->count(),
                    'completed_count' => $completedScores->count(),
                    'average_score' => $averageScore,
                ];
            })
            ->values();
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard.dashboard-score', [
            'currentUser' => Auth::user(),
        ]);
    }
}
