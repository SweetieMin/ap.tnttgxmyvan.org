<?php

namespace App\Livewire\Admin\Personnel\Youth;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quản lý thiếu nhi')]
class YouthIndex extends Component
{
    use AuthorizesRequests;

    #[Computed]
    public function canCreate(): bool
    {
        return Auth::user()?->can('personnel.youth.create') ?? false;
    }

    public function openCreateModal(): void
    {
        $this->authorize('personnel.youth.create');
        $this->dispatch('create-youth')->to(Action::class);
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.youth.youth-index');
    }
}
