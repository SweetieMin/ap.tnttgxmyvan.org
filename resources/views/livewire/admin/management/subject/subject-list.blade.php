<div>
    <flux:card class="bg-white dark:bg-zinc-900">
        @if ($this->subjects->isEmpty())
            <div class="flex flex-col items-center justify-center gap-2 py-12">
                <flux:heading size="lg" level="3" class="text-center">{{ __('Chưa có môn học nào') }}</flux:heading>
                <flux:text class="text-center">{{ __('Hãy tạo môn học đầu tiên cho khu quản lý.') }}</flux:text>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sortBy('name')">{{ __('Tên môn học') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'code'" :direction="$sortDirection" wire:click="sortBy('code')">{{ __('Mã môn') }}</flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sortBy('status')">{{ __('Trạng thái') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Hành động') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->subjects as $subject)
                        <flux:table.row :key="$subject->id">
                            <flux:table.cell variant="strong">{{ $subject->name }}</flux:table.cell>
                            <flux:table.cell>{{ $subject->code ?? '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm">{{ $this->availableStatuses[$subject->status] ?? $subject->status }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="openEditModal({{ $subject->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="openDeleteModal({{ $subject->id }})" class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300" />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
