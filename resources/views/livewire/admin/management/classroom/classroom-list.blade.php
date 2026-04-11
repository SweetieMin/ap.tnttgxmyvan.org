<div>
    <div class="space-y-6">
        <flux:card class="bg-white dark:bg-zinc-900">
            @if ($this->classrooms->isEmpty())
                <div class="flex flex-col items-center justify-center gap-2 py-12">
                    <flux:heading size="lg" level="3" class="text-center">{{ __('Chưa có lớp học nào') }}</flux:heading>
                    <flux:text class="text-center">{{ __('Hãy tạo lớp học đầu tiên cho khu quản lý.') }}</flux:text>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sortBy('name')">{{ __('Tên lớp') }}</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'code'" :direction="$sortDirection" wire:click="sortBy('code')">{{ __('Mã lớp') }}</flux:table.column>
                        <flux:table.column>{{ __('Số môn') }}</flux:table.column>
                        <flux:table.column>{{ __('Mô tả') }}</flux:table.column>
                        <flux:table.column>{{ __('Ngày bắt đầu') }}</flux:table.column>
                        <flux:table.column>{{ __('Ngày kết thúc') }}</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sortBy('status')">{{ __('Trạng thái') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('Hành động') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->classrooms as $classroom)
                            <flux:table.row :key="$classroom->id">
                                <flux:table.cell variant="strong">{{ $classroom->name }}</flux:table.cell>
                                <flux:table.cell>{{ $classroom->code ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $classroom->classroom_subjects_count }}</flux:table.cell>
                                <flux:table.cell>{{ filled($classroom->description) ? $classroom->description : '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $classroom->start_date?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $classroom->end_date?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm">{{ $this->availableStatuses[$classroom->status] ?? $classroom->status }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button size="sm" variant="{{ $this->selectedClassroom?->id === $classroom->id ? 'primary' : 'subtle' }}" icon="book-open" wire:click="selectClassroom({{ $classroom->id }})">
                                        {{ __('Quản lý môn') }}
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="openEditClassroomModal({{ $classroom->id }})" />
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="openDeleteClassroomModal({{ $classroom->id }})" class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

        @if ($this->selectedClassroom)
            <flux:card class="space-y-6 bg-white dark:bg-zinc-900" >
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-2">
                        <flux:heading size="lg">{{ __('Môn học của lớp') }} </flux:heading>
                        <flux:text>
                            {{ __('Đang quản lý môn học cho lớp') }}:
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $this->selectedClassroom->name }}</span>
                            @if (filled($this->selectedClassroom->code))
                                ({{ $this->selectedClassroom->code }})
                            @endif
                        </flux:text>
                    </div>

                    <flux:button variant="primary" icon="plus" wire:click="openCreateAssignmentModal" :disabled="! $this->hasAvailableSubjects || ! $this->hasAvailableTeachers">
                        {{ __('Thêm môn vào lớp') }}
                    </flux:button>
                </div>

                @if (! $this->hasAvailableSubjects)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                        {{ __('Hãy tạo môn học trước khi gán vào lớp.') }}
                    </div>
                @endif

                @if (! $this->hasAvailableTeachers)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                        {{ __('Hãy tạo hoặc gán role giáo viên trước khi phân công môn học.') }}
                    </div>
                @endif

                @if ($this->classroomAssignments->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-zinc-200 px-6 py-12 text-center dark:border-zinc-700">
                        <flux:heading size="lg" level="3">{{ __('Chưa có môn học nào trong lớp này') }}</flux:heading>
                        <flux:text>{{ __('Hãy thêm môn đầu tiên và gán giáo viên phụ trách.') }}</flux:text>
                    </div>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Tên môn học') }}</flux:table.column>
                            <flux:table.column>{{ __('Mã môn') }}</flux:table.column>
                            <flux:table.column>{{ __('Giáo viên giảng dạy') }}</flux:table.column>
                            <flux:table.column>{{ __('Trạng thái') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Hành động') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->classroomAssignments as $assignment)
                                <flux:table.row :key="$assignment->id">
                                    <flux:table.cell variant="strong">{{ $assignment->subject?->name ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>{{ $assignment->subject?->code ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex flex-wrap gap-2">
                                            @forelse ($assignment->teachers as $teacher)
                                                <flux:badge size="sm" color="zinc" wire:key="assignment-teacher-{{ $assignment->id }}-{{ $teacher->id }}">
                                                    {{ $teacher->name }}
                                                </flux:badge>
                                            @empty
                                                <span>—</span>
                                            @endforelse
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm">
                                            {{ $this->availableAssignmentStatuses[$assignment->status] ?? $assignment->status }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="end">
                                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="openEditAssignmentModal({{ $assignment->id }})" />
                                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="openDeleteAssignmentModal({{ $assignment->id }})" class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300" />
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </flux:card>

            <flux:card class="space-y-6 bg-white dark:bg-zinc-900">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-2">
                        <flux:heading size="lg">{{ __('Thiếu nhi của lớp') }}</flux:heading>
                        <flux:text>
                            {{ __('Danh sách thiếu nhi đang thuộc lớp') }}:
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $this->selectedClassroom->name }}</span>
                        </flux:text>
                    </div>

                    <flux:button variant="primary" icon="user-plus" wire:click="openYouthModal">
                        {{ __('Cập nhật thiếu nhi của lớp') }}
                    </flux:button>
                </div>

                @if (! $this->hasAvailableYouths)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                        {{ __('Hãy tạo thiếu nhi trước khi gán vào lớp.') }}
                    </div>
                @elseif ($this->selectedClassroom->youths->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-zinc-200 px-6 py-12 text-center dark:border-zinc-700">
                        <flux:heading size="lg" level="3">{{ __('Chưa có thiếu nhi nào trong lớp này') }}</flux:heading>
                        <flux:text>{{ __('Hãy thêm thiếu nhi đầu tiên cho lớp này.') }}</flux:text>
                    </div>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Tên thánh') }}</flux:table.column>
                            <flux:table.column>{{ __('Họ và tên') }}</flux:table.column>
                            <flux:table.column>{{ __('Username') }}</flux:table.column>
                            <flux:table.column>{{ __('Ngày sinh') }}</flux:table.column>
                            <flux:table.column>{{ __('Đang thuộc các lớp') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->selectedClassroom->youths as $youth)
                                <flux:table.row :key="$youth->id">
                                    <flux:table.cell variant="strong">{{ $youth->holy_name ?: '—' }}</flux:table.cell>
                                    <flux:table.cell>{{ $youth->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $youth->username }}</flux:table.cell>
                                    <flux:table.cell>{{ $youth->birthday?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($youth->classrooms as $classroom)
                                                <flux:badge size="sm" :color="$classroom->id === $this->selectedClassroom->id ? 'blue' : null" wire:key="youth-classroom-{{ $youth->id }}-{{ $classroom->id }}">
                                                    {{ $classroom->code ?: $classroom->name }}
                                                </flux:badge>
                                            @endforeach
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </flux:card>
        @else
            <flux:card>
                <div class="flex flex-col items-center justify-center gap-2 py-12 text-center">
                    <flux:heading size="lg" level="3">{{ __('Chọn lớp để quản lý môn học') }}</flux:heading>
                    <flux:text>{{ __('Chọn một lớp trong danh sách để gắn môn học và giáo viên phụ trách.') }}</flux:text>
                </div>
            </flux:card>
        @endif
    </div>
</div>
