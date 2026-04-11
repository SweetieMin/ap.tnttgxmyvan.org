<div>
    <flux:modal class="max-w-3xl" name="showFormModal">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingClassroomId ? __('Cập nhật lớp học') : __('Thêm lớp học') }}
                </flux:heading>
                <flux:text>
                    {{ __('Nhập thông tin lớp học để dùng cho phân công giảng dạy và lịch học.') }}
                </flux:text>
            </div>

            <form wire:submit="saveClassroom" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:items-start">
                    <div class="md:col-span-1">
                        <flux:input wire:model="name" :label="__('Tên lớp')" type="text" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:input wire:model="code" :label="__('Mã lớp')" type="text" />
                    </div>

                    <div class="md:col-span-2">
                        <flux:textarea wire:model="description" :label="__('Mô tả')" rows="4" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:date-picker wire:model="start_date" :label="__('Ngày bắt đầu')" locale="vi-VN" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:date-picker wire:model="end_date" :label="__('Ngày kết thúc')" locale="vi-VN" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:select variant="combobox" wire:model="status" :label="__('Trạng thái')">
                            @foreach ($this->availableStatuses as $value => $label)
                                <flux:select.option :value="$value"
                                    wire:key="classroom-status-{{ $value }}">
                                    {{ $label }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <flux:button variant="ghost" type="button" wire:click="closeFormModal">
                        {{ __('Huỷ') }}
                    </flux:button>
                    <flux:button variant="primary" type="submit">
                        {{ $editingClassroomId ? __('Lưu thay đổi') : __('Tạo lớp học') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal class="max-w-md" name="showDeleteModal">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Xoá lớp học') }}</flux:heading>
                <flux:text>
                    {{ __('Bạn có chắc muốn xoá lớp học này không? Những phân công và lịch học liên quan cũng sẽ bị xoá.') }}
                </flux:text>
            </div>

            @if ($this->classroomPendingDeletion)
                <div
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">
                    {{ $this->classroomPendingDeletion->name }} ({{ $this->classroomPendingDeletion->code ?? '—' }})
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="ghost" type="button" wire:click="closeDeleteModal">
                    {{ __('Huỷ') }}
                </flux:button>
                <flux:button variant="danger" type="button" wire:click="deleteClassroom">
                    {{ __('Xoá') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="showAssignmentModal" class="w-full max-w-5xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingAssignmentId ? __('Cập nhật phân công môn học') : __('Thêm môn vào lớp') }}
                </flux:heading>

                <flux:text>
                    @if ($selectedClassroomName)
                        {{ __('Phân công môn học cho lớp') }}:
                        <span class="font-medium text-zinc-900 dark:text-white">
                            {{ $selectedClassroomName }}
                        </span>
                    @endif
                </flux:text>
            </div>

            <form wire:submit="saveAssignment" class="space-y-5">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                    <div>
                        <flux:input wire:model.live.debounce.500ms="assignment_name" :label="__('Lọc theo tên')" />
                    </div>

                    <div>
                        <flux:select variant="combobox" wire:model="subject_id" :label="__('Tên môn học')">
                            <flux:select.option value="">
                                {{ __('Chọn môn học') }}
                            </flux:select.option>

                            @foreach ($this->availableSubjects as $subject)
                                <flux:select.option :value="$subject->id"
                                    wire:key="subject-option-{{ $subject->id }}">
                                    {{ $subject->name }}
                                    @if (filled($subject->code))
                                        ({{ $subject->code }})
                                    @endif
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:select variant="combobox" wire:model="assignment_status" :label="__('Trạng thái')">
                            @foreach ($this->availableAssignmentStatuses as $value => $label)
                                <flux:select.option :value="$value"
                                    wire:key="assignment-status-{{ $value }}">
                                    {{ $label }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                    <div
                        class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ __('Giáo viên giảng dạy') }}
                            </div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Có thể chọn một hoặc nhiều giáo viên cho cùng một môn học') }}
                            </div>
                        </div>

                        <flux:badge color="zinc">
                            {{ count($teacher_ids ?? []) }} {{ __('đã chọn') }}
                        </flux:badge>
                    </div>

                    <div class="max-h-96 overflow-y-auto p-4">
                        @if ($this->availableTeachers->isNotEmpty())
                            <flux:checkbox.group wire:model="teacher_ids" variant="cards">
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                    @foreach ($this->availableTeachers as $teacher)
                                        <flux:checkbox :value="$teacher->id"
                                            wire:key="assignment-teacher-option-{{ $teacher->id }}">
                                            <div class="space-y-3">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="min-w-0 flex-1">

                                                        @if (filled($teacher->holy_name))
                                                            <div
                                                                class="mt-1 truncate text-sm text-zinc-500 dark:text-zinc-400">
                                                                {{ $teacher->holy_name }}
                                                            </div>
                                                        @endif
                                                        <div
                                                            class="truncate text-base font-semibold text-zinc-900 dark:text-white">
                                                            {{ trim(($teacher->lastName ?? '') . ' ' . ($teacher->name ?? '')) ?: $teacher->name }}
                                                        </div>


                                                    </div>


                                                </div>
                                                @if (filled($teacher->username))
                                                    <flux:badge size="sm" color="zinc">
                                                        {{ $teacher->username }}
                                                    </flux:badge>
                                                @endif

                                            </div>
                                        </flux:checkbox>
                                    @endforeach
                                </div>
                            </flux:checkbox.group>
                        @else
                            <div
                                class="flex min-h-48 items-center justify-center rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                                <div class="text-center">
                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ __('Không tìm thấy giáo viên phù hợp') }}
                                    </div>
                                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('Hiện chưa có giáo viên khả dụng để phân công.') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:button variant="ghost" type="button" wire:click="closeAssignmentModal">
                        {{ __('Huỷ') }}
                    </flux:button>

                    <flux:button variant="primary" type="submit">
                        {{ $editingAssignmentId ? __('Lưu thay đổi') : __('Thêm môn vào lớp') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal class="max-w-md" name="showAssignmentDeleteModal">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Xoá phân công môn học') }}</flux:heading>
                <flux:text>
                    {{ __('Bạn có chắc muốn xoá môn học này khỏi lớp không? Các lịch học thuộc phân công này cũng sẽ bị xoá.') }}
                </flux:text>
            </div>

            @if ($this->assignmentPendingDeletion)
                <div
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">
                    <div class="font-medium">
                        {{ $this->assignmentPendingDeletion->subject?->name ?? '—' }}
                    </div>
                    <div>
                        {{ $this->assignmentPendingDeletion->teachersLabel() ?: '—' }}
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="ghost" type="button" wire:click="closeAssignmentDeleteModal">
                    {{ __('Huỷ') }}
                </flux:button>
                <flux:button variant="danger" type="button" wire:click="deleteAssignment">
                    {{ __('Xoá') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="showYouthModal" class="w-full max-w-6xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Cập nhật thiếu nhi của lớp') }}</flux:heading>

                <flux:text>
                    @if ($selectedClassroomName)
                        {{ __('Chọn các thiếu nhi đang học trong lớp') }}:
                        <span class="font-medium text-zinc-900 dark:text-white">
                            {{ $selectedClassroomName }}
                        </span>
                    @endif
                </flux:text>
            </div>

            <form wire:submit="saveYouthAssignments" class="space-y-5">
                <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                    <div>
                        <flux:input wire:model.live.debounce.300ms="youthSearch" :label="__('Tìm thiếu nhi')"
                            type="text" :placeholder="__('Nhập tên, tên thánh hoặc username...')" />
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button type="button" size="sm" variant="ghost" wire:click="selectAllYouths">
                            {{ __('Chọn tất cả') }}
                        </flux:button>

                        <flux:button type="button" size="sm" variant="ghost" wire:click="clearYouthSelection">
                            {{ __('Bỏ chọn') }}
                        </flux:button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                    <div
                        class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ __('Danh sách thiếu nhi') }}
                            </div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Chọn một hoặc nhiều thiếu nhi để gán vào lớp') }}
                            </div>
                        </div>

                        <flux:badge color="zinc">
                            {{ count($youth_ids ?? []) }} {{ __('đã chọn') }}
                        </flux:badge>
                    </div>

                    <div class="max-h-104 overflow-y-auto p-4">
                        @if ($this->availableYouths->isNotEmpty())
                            <flux:checkbox.group wire:model="youth_ids" variant="cards">
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                                    @foreach ($this->availableYouths as $youth)
                                        <flux:checkbox :value="$youth->id"
                                            wire:key="classroom-youth-option-{{ $youth->id }}">
                                            <div class="space-y-3">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0 flex-1">
                                                        @if ($youth->holy_name)
                                                            <div
                                                                class="mt-1 truncate text-sm text-zinc-500 dark:text-zinc-400">
                                                                {{ $youth->holy_name }}
                                                            </div>
                                                        @endif
                                                        <div
                                                            class="truncate text-base font-semibold text-zinc-900 dark:text-white">
                                                            {{ $youth->name }}
                                                        </div>


                                                    </div>

                                                </div>

                                                <div class="flex flex-wrap gap-2">
                                                    @php
                                                        $codes = $youth->classrooms->pluck('code')->filter();
                                                    @endphp

                                                    @if ($codes->isNotEmpty())
                                                        @foreach ($codes as $code)
                                                            <flux:badge size="sm" color="sky">
                                                                {{ $code }}
                                                            </flux:badge>
                                                        @endforeach
                                                    @else
                                                        <span class="text-sm text-zinc-400">
                                                            {{ __('Chưa có lớp') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </flux:checkbox>
                                    @endforeach
                                </div>
                            </flux:checkbox.group>
                        @else
                            <div
                                class="flex min-h-48 items-center justify-center rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                                <div class="text-center">
                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ __('Không tìm thấy thiếu nhi phù hợp') }}
                                    </div>
                                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('Thử thay đổi từ khoá tìm kiếm.') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:button variant="ghost" type="button" wire:click="closeYouthModal">
                        {{ __('Huỷ') }}
                    </flux:button>

                    <flux:button variant="primary" type="submit">
                        {{ __('Lưu thiếu nhi của lớp') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
