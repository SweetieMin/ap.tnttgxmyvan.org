<?php

namespace App\Livewire\Admin\Management\Classroom;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quản lý lớp học')]
class ClassroomIndex extends Component
{
    public function openCreateModal(): void
    {
        $this->dispatch('open-create-classroom-modal')->to(Action::class);
    }

    public function render(): View
    {
        return view('livewire.admin.management.classroom.classroom-index');
    }
}
