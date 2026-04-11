<?php

namespace App\Livewire\Admin\Management\Schedule;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Omnia\LivewireCalendar\LivewireCalendar;

#[Lazy]
class ScheduleList extends LivewireCalendar
{

    public $dayClickEnabled;

    #[Url]
    public int $month;

    #[Url]
    public int $year;

    /**
     * @param  array<string, mixed>  $extras
     */
    public function afterMount($extras = []): void
    {
        $this->beforeCalendarView = 'livewire.admin.management.schedule.calendar-before';
        $this->afterCalendarView = 'livewire.admin.management.schedule.calendar-after';
        $this->weekStartsAt = Carbon::MONDAY;
        $this->weekEndsAt = Carbon::SUNDAY;

        $this->month = $this->month ?? now()->month;
        $this->year = $this->year ?? now()->year;

        $this->startsAt = \Carbon\Carbon::create($this->year, $this->month, 1);
        $this->endsAt = $this->startsAt->copy()->endOfMonth();

        $this->calculateGridStartsEnds();

    }

    public function goToPreviousMonth()
    {
        $this->startsAt->subMonthNoOverflow();
        $this->endsAt->subMonthNoOverflow();

        $this->month = $this->startsAt->month;
        $this->year = $this->startsAt->year;

        $this->calculateGridStartsEnds();
    }

    public function goToNextMonth()
    {
        $this->startsAt->addMonthNoOverflow();
        $this->endsAt->addMonthNoOverflow();

        $this->month = $this->startsAt->month;
        $this->year = $this->startsAt->year;

        $this->calculateGridStartsEnds();
    }

    public function goToCurrentMonth()
    {
        $this->startsAt = Carbon::today()->startOfMonth()->startOfDay();
        $this->endsAt = $this->startsAt->clone()->endOfMonth()->startOfDay();

        $this->month = $this->startsAt->month;
        $this->year = $this->startsAt->year;

        $this->calculateGridStartsEnds();
    }

    public function placeholder(): View
    {
        return view('components.placeholder.calendar');
    }

    #[On('schedule-updated')]
    public function refreshCalendar(): void
    {
        // Re-triggers a Livewire sync when action completes.
    }

    public function events(): Collection
    {
        return Schedule::query()
            ->with(['classroomSubject.classroom', 'classroomSubject.subject', 'classroomSubject.teachers'])
            ->get()
            ->map(function ($schedule) {
                $timeRange = substr($schedule->start_time, 0, 5) . ' - ' . substr($schedule->end_time, 0, 5);
                $context = collect([
                    $schedule->classroomName(),
                ])->filter()->implode('|');

                return [
                    'id' => $schedule->id,
                    'title' => $schedule->subjectName(),
                    'description' => trim($timeRange . ($context !== '' ? '|' . $context : '')),
                    'mobile_label' => $schedule->subjectName(),
                    'date' => $schedule->date,
                    ...$schedule->calendarColorClasses(),
                ];
            });
    }

    public function onDayClick($year, $month, $day)
    {
        $this->dispatch('create-schedule',  Carbon::createFromDate($year, $month, $day)->format('Y-m-d'));
    }

    public function onEventClick($eventId)
    {
        $this->dispatch('edit-schedule', $eventId);
    }

    public function onEventDropped($eventId, $year, $month, $day)
    {
        $this->dispatch('drag-schedule',  $eventId,  $year,  $month, $day);
    }
}
