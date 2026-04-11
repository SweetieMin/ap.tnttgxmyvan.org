<div>
    <section class="w-full space-y-6">
        <div
            class="flex flex-col gap-4 rounded-3xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-end md:justify-between">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('Quản lý giáo viên') }}</flux:heading>
                <flux:text class="max-w-2xl">
                    {{ __('CRUD người dùng trong khu vực nhân sự, có gán vai trò và phân quyền theo personnel.teacher.*.') }}
                </flux:text>
            </div>

            @if ($this->canCreate)
                <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                    {{ __('Thêm người dùng') }}
                </flux:button>
            @endif
        </div>

        <livewire:admin.personnel.teacher.teacher-list />
        <livewire:admin.personnel.teacher.action />

    </section>
</div>
