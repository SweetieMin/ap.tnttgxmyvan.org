<?php

namespace App\Livewire\Admin\Management\Subject;

use App\Models\Subject;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class SubjectList extends Component
{
    public string $sortBy = 'id';

    public string $sortDirection = 'desc';

    #[On('subject-updated')]
    public function render(): View
    {
        return view('livewire.admin.management.subject.subject-list');
    }

    #[Computed]
    public function subjects()
    {
        return Subject::query()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->get();
    }

    #[Computed]
    public function availableStatuses(): array
    {
        return [
            'active' => __('Đang sử dụng'),
            'inactive' => __('Ngưng sử dụng'),
        ];
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

    public function openEditModal(int $subjectId): void
    {
        $this->dispatch('edit-subject', $subjectId);
    }

    public function openDeleteModal(int $subjectId): void
    {
        $this->dispatch('delete-subject', $subjectId);
    }
}
