<?php

namespace App\Livewire\Admin\Management\Schedule;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quản lý lịch học')]
class ScheduleIndex extends Component
{
    use AuthorizesRequests;

    #[Computed]
    public function canCreate(): bool
    {
        return Auth::user()?->can('management.schedule.create') ?? true;
    }

    public function render(): View
    {
        return view('livewire.admin.management.schedule.schedule-index');
    }
}
