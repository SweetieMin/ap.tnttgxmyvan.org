<div>
    <flux:card>
        @if ($this->teachers->isEmpty())
            <div class="flex flex-col items-center justify-center gap-2 py-12">
                <flux:heading size="lg" level="3" class="text-center">{{ __('Chưa có giáo viên nào') }}
                </flux:heading>
                <flux:text class="text-center">{{ __('Hãy tạo giáo viên đầu tiên cho khu vực nhân sự.') }}</flux:text>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'holy_name'" :direction="$sortDirection"
                        wire:click="sortBy('holy_name')">{{ __('Tên thánh') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                        wire:click="sortBy('name')">{{ __('Họ và tên') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'username'" :direction="$sortDirection"
                        wire:click="sortBy('username')">{{ __('Username') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Vai trò') }}</flux:table.column>
                    <flux:table.column>{{ __('Ngày sinh') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Hành động') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->teachers as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell variant="strong">{{ $user->holy_name }}</flux:table.cell>
                            <flux:table.cell>{{ $user->name }}</flux:table.cell>
                            <flux:table.cell>{{ $user->username }}</flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>
                                @foreach ($user->roles as $userRole)
                                    <flux:badge size="sm">{{ $userRole->name }}</flux:badge>
                                @endforeach
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($user->birthday)
                                    {{ $user->birthday->format('d/m/Y') }}
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button size="sm" variant="ghost" icon="pencil-square"
                                   wire:click="openEditModal({{ $user->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash"
                                   wire:click="openDeleteModal({{ $user->id }})"
                                    class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300" />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
