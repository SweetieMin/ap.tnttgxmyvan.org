<?php

namespace App\Livewire\Admin\Personnel\Youth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class YouthList extends Component
{
    public string $sortBy = 'id';

    public string $sortDirection = 'desc';

    #[On('youth-updated')]
    public function render(): View
    {
        return view('livewire.admin.personnel.youth.youth-list');
    }

    #[Computed]
    public function youth()
    {
        return User::query()
            ->with('roles')
            ->whereHas('roles', fn ($query) => $query->where('name', 'thiếu nhi'))
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
        $this->dispatch('edit-youth', id: $userId)->to(Action::class);
    }

    public function openDeleteModal(int $userId): void
    {
        $this->dispatch('delete-youth', id: $userId)->to(Action::class);
    }
}
