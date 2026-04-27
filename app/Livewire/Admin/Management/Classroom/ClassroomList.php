<?php

namespace App\Livewire\Admin\Management\Classroom;

use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ClassroomList extends Component
{
    use AuthorizesRequests;

    public ?int $selectedClassroomId = null;

    public string $sortBy = 'id';

    public string $sortDirection = 'desc';

    public function mount(?int $selectedClassroomId = null): void
    {
        $this->selectedClassroomId = $selectedClassroomId ?? $this->defaultSelectedClassroomId();
    }

    public function updatedSelectedClassroomId(?int $value): void
    {
        $this->selectedClassroomId = $value;
    }

    #[On('classroom-data-changed')]
    public function refreshList(?int $selectedClassroomId = null): void
    {
        $this->selectedClassroomId = $selectedClassroomId ?? $this->defaultSelectedClassroomId();

        unset($this->classrooms, $this->selectedClassroom, $this->classroomAssignments);
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortBy = $column;
        $this->sortDirection = 'asc';
    }

    public function selectClassroom(int $classroomId): void
    {
        $this->selectedClassroomId = $classroomId;

        $this->dispatch('classroom-selected', $classroomId);
    }

    public function openEditClassroomModal(int $classroomId): void
    {
        $this->dispatch('open-edit-classroom-modal', $classroomId);
    }

    public function openDeleteClassroomModal(int $classroomId): void
    {
        $this->dispatch('open-delete-classroom-modal', $classroomId);
    }

    public function openCreateAssignmentModal(): void
    {
        $this->dispatch('open-create-assignment-modal', $this->selectedClassroom?->id);
    }

    public function openEditAssignmentModal(int $assignmentId): void
    {
        $this->dispatch('open-edit-assignment-modal', $assignmentId);
    }

    public function openDeleteAssignmentModal(int $assignmentId): void
    {
        $this->dispatch('open-delete-assignment-modal', $assignmentId);
    }

    public function openYouthModal(): void
    {
        $this->dispatch('open-youth-modal', $this->selectedClassroom?->id);
    }

    #[Computed]
    public function classrooms()
    {
        return Classroom::query()
            ->withCount('classroomSubjects')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->get();
    }

    #[Computed]
    public function selectedClassroom(): ?Classroom
    {
        $query = Classroom::query()
            ->with([
                'youths.classrooms:id,name,code',
                'classroomSubjects.subject',
                'classroomSubjects.teachers' => fn ($teacherQuery) => $teacherQuery->orderBy('name'),
            ])
            ->orderBy('name');

        if ($this->selectedClassroomId !== null) {
            $selectedClassroom = (clone $query)->find($this->selectedClassroomId);

            if ($selectedClassroom !== null) {
                return $selectedClassroom;
            }
        }

        return $query->first();
    }

    protected function defaultSelectedClassroomId(): ?int
    {
        return Classroom::query()
            ->orderBy('name')
            ->value('id');
    }

    #[Computed]
    public function classroomAssignments(): Collection
    {
        return $this->selectedClassroom?->classroomSubjects
            ->sortBy(fn (ClassroomSubject $assignment): string => Str::lower($assignment->subject?->name ?? ''))
            ->values()
            ?? new Collection;
    }

    #[Computed]
    public function availableStatuses(): array
    {
        return [
            'pending' => __('Chờ mở lớp'),
            'open' => __('Đang mở'),
            'closed' => __('Đã đóng'),
        ];
    }

    #[Computed]
    public function availableAssignmentStatuses(): array
    {
        return [
            'active' => __('Đang sử dụng'),
            'inactive' => __('Ngưng sử dụng'),
        ];
    }

    #[Computed]
    public function hasAvailableSubjects(): bool
    {
        return Subject::query()->exists();
    }

    #[Computed]
    public function hasAvailableTeachers(): bool
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'giáo viên'))
            ->exists();
    }

    #[Computed]
    public function hasAvailableYouths(): bool
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'thiếu nhi'))
            ->exists();
    }

    public function render(): View
    {
        return view('livewire.admin.management.classroom.classroom-list');
    }
}
