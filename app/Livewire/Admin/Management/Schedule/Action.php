<?php

namespace App\Livewire\Admin\Management\Schedule;

use App\Models\ClassroomSubject;
use App\Models\Schedule;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Action extends Component
{
    use AuthorizesRequests;

    public ?int $editingScheduleId = null;

    public ?int $deletingScheduleId = null;

    public ?int $classroom_subject_id = null;

    public ?string $date = null;

    public ?string $start_time = null;

    public ?string $end_time = null;

    public ?string $date_end_spirit = null;

    public ?string $date_end_practice_theory = null;

    public string $type = Schedule::TYPE_STUDY;

    public string $status = 'pending';

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public function mount(): void
    {
        $this->resetForm();
    }

    #[On('create-schedule')]
    public function openCreateModal($date = null): void
    {

        $this->authorize('management.schedule.create');

        $this->editingScheduleId = null;
        $this->resetForm();

        $date = filled($date) ? Carbon::parse($date) : null;

        $this->date = $date?->format('Y-m-d');
        $this->date_end_spirit = $date?->copy()->addDay()?->format('Y-m-d');
        $this->date_end_practice_theory = $date?->copy()->addDays(7)?->format('Y-m-d');

        $this->showFormModal = true;
    }

    #[On('edit-schedule')]
    public function openEditModal($eventId): void
    {
        $this->authorize('management.schedule.update');

        $schedule = Schedule::query()->with('classroomSubject')->findOrFail($eventId);

        $this->editingScheduleId = $schedule->id;
        $this->classroom_subject_id = $schedule->classroom_subject_id;
        $this->date = $schedule->date ? $schedule->date->format('Y-m-d') : null;
        $this->date_end_spirit = $schedule->date_end_spirit ? $schedule->date_end_spirit->format('Y-m-d') : null;
        $this->date_end_practice_theory = $schedule->date_end_practice_theory ? $schedule->date_end_practice_theory->format('Y-m-d') : null;

        $this->start_time = $schedule->start_time ? substr($schedule->start_time, 0, 5) : null;
        $this->end_time = $schedule->end_time ? substr($schedule->end_time, 0, 5) : null;
        $this->type = $schedule->type;
        $this->status = $schedule->status;

        $this->resetErrorBag();
        $this->resetValidation();
        $this->showFormModal = true;
    }

    #[On('delete-schedule')]
    public function openDeleteModal(array $params): void
    {
        // $this->authorize('management.schedule.delete');

        $this->deletingScheduleId = $params['id'];
        $this->resetErrorBag();
        $this->resetValidation();
        $this->showDeleteModal = true;
    }

    #[On('drag-schedule')]
    public function dragSchedule($eventId, $year, $month, $day): void
    {
        $schedule = Schedule::query()->findOrFail($eventId);
        $schedule->update([
            'date' => Carbon::createFromDate($year, $month, $day)->format('Y-m-d'),
        ]);

        $this->dispatch('schedule-updated');
    }

    public function saveSchedule(): void
    {
        $isEditing = $this->editingScheduleId !== null;

        // $this->authorize($isEditing ? 'management.schedule.update' : 'management.schedule.create');

        $validated = $this->validate($this->rules());

        if ($isEditing) {
            $schedule = Schedule::query()->findOrFail($this->editingScheduleId);
            $schedule->update($validated);
        } else {
            $schedule = Schedule::query()->create($validated);
        }

        $this->showFormModal = false;
        $this->editingScheduleId = null;
        $this->resetForm();

        $this->dispatch('schedule-updated');

        Flux::toast(
            variant: 'success',
            text: $isEditing ? __('Đã cập nhật lịch học.') : __('Đã tạo lịch học mới.'),
        );
    }

    public function deleteSchedule(): void
    {
        // $this->authorize('management.schedule.delete');

        $schedule = Schedule::query()->findOrFail($this->deletingScheduleId);
        $schedule->delete();

        $this->showDeleteModal = false;
        $this->deletingScheduleId = null;

        $this->dispatch('schedule-updated');

        Flux::toast(variant: 'success', text: __('Đã xoá lịch học.'));
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->editingScheduleId = null;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingScheduleId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    #[Computed]
    public function availableAssignments(): Collection
    {
        return ClassroomSubject::query()
            ->with(['classroom', 'subject', 'teachers'])
            ->where('status', 'active')
            ->orderBy('classroom_id')
            ->orderBy('subject_id')
            ->get();
    }

    #[Computed]
    public function schedulePendingDeletion(): ?Schedule
    {
        if ($this->deletingScheduleId === null) {
            return null;
        }

        return Schedule::query()->find($this->deletingScheduleId);
    }

    #[Computed]
    public function availableTypes(): array
    {
        return Schedule::typeOptions();
    }

    protected function rules(): array
    {
        return [
            'classroom_subject_id' => ['required', 'exists:classroom_subject,id'],
            'date' => ['required', 'date'],
            'date_end_spirit' => ['nullable', 'date'],
            'date_end_practice_theory' => ['nullable', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'type' => ['required', 'in:study,exam,camp,reminder'],
            'status' => ['required', 'in:pending,in_progress,resolved,closed'],
        ];
    }

    protected function resetForm(): void
    {
        $this->classroom_subject_id = null;
        $this->date = null;
        $this->date_end_spirit = null;
        $this->date_end_practice_theory = null;
        $this->start_time = null;
        $this->end_time = null;
        $this->type = Schedule::TYPE_STUDY;
        $this->status = 'pending';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.admin.management.schedule.action');
    }
}
