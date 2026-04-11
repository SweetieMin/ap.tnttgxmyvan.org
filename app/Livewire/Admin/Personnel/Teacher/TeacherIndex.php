<?php

namespace App\Livewire\Admin\Personnel\Teacher;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quản lý giáo viên')]
class TeacherIndex extends Component
{
    use AuthorizesRequests;

    #[Computed]
    public function canCreate(): bool
    {
        return Auth::user()?->can('personnel.teacher.create') ?? false;
    }

    public function openCreateModal(): void
    {
        $this->authorize('personnel.teacher.create');
        $this->dispatch('create-teacher')->to(Action::class);
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.teacher.teacher-index');
    }
}
