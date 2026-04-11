<?php

namespace App\Livewire\Admin\Personnel\Teacher;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TeacherList extends Component
{
    public string $sortBy = 'id';

    public string $sortDirection = 'desc';

    #[On('teacher-updated')]
    public function render(): View
    {
        return view('livewire.admin.personnel.teacher.teacher-list');
    }

    #[Computed]
    public function teachers()
    {
        return User::query()
            ->with('roles')
            ->whereHas('roles', fn ($query) => $query->where('name', 'giáo viên'))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->get();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function openEditModal(int $userId): void
    {
        $this->dispatch('edit-teacher', $userId);
    }

    public function openDeleteModal(int $userId): void
    {
        $this->dispatch('delete-teacher', $userId);
    }
}
