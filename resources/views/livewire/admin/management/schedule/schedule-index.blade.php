<div>
    <section class="w-full space-y-6">
        <div class="flex flex-col gap-4 rounded-3xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-end md:justify-between">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Quản lý lịch học') }}</flux:heading>
                <flux:text class="max-w-2xl text-zinc-500">{{ __('Theo dõi, thêm hoặc chỉnh sửa lịch học và phân công phụ trách giảng dạy.') }}</flux:text>
            </div>

            @if ($this->canCreate)
                <flux:button variant="primary" icon="plus" wire:click="$dispatch('create-schedule')">
                    {{ __('Thêm lịch học') }}
                </flux:button>
            @endif
        </div>

        <livewire:admin.management.schedule.schedule-list />
        <livewire:admin.management.schedule.action />
    </section>
</div>
