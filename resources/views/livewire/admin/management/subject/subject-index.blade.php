<div>
    <section class="w-full space-y-6">
        <div class="flex flex-col gap-4 rounded-3xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-end md:justify-between">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('Quản lý môn học') }}</flux:heading>
                <flux:text class="max-w-2xl">
                    {{ __('Tạo và quản lý danh sách môn học dùng trong phân công giảng dạy.') }}
                </flux:text>
            </div>

            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                {{ __('Thêm môn học') }}
            </flux:button>
        </div>

        <livewire:admin.management.subject.subject-list />
        <livewire:admin.management.subject.action />
    </section>
</div>
