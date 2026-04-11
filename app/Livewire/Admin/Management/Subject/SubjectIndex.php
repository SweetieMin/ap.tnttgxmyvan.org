<?php

namespace App\Livewire\Admin\Management\Subject;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quản lý môn học')]
class SubjectIndex extends Component
{
    public function openCreateModal(): void
    {
        $this->dispatch('create-subject')->to(Action::class);
    }

    public function render(): View
    {
        return view('livewire.admin.management.subject.subject-index');
    }
}
