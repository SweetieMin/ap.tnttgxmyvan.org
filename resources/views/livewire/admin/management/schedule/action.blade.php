<div>
    <flux:modal wire:model="showFormModal" class="md:w-200">
        <div class="space-y-6">
            <div>
                <flux:heading>
                    {{ $editingScheduleId ? __('Cập nhật lịch học') : __('Thêm lịch học') }}
                </flux:heading>
            </div>

            <flux:separator />

            <form class="space-y-6" wire:submit="saveSchedule">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:items-start">
                    <div class="md:col-span-1">
                        <flux:date-picker wire:model="date" :label="__('Ngày học')" locale="vi-VN" />
                    </div>


                    <div class="md:col-span-1">
                        <flux:select wire:model="type" variant="combobox" :label="__('Loại lịch')">
                            @foreach ($this->availableTypes as $value => $label)
                                <flux:select.option :value="$value" wire:key="schedule-type-{{ $value }}">
                                    {{ $label }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="md:col-span-1">
                        <flux:select wire:model="classroom_subject_id" variant="combobox"
                            :label="__('Phân công giảng dạy')">
                            <flux:select.option value="">{{ __('Chọn lớp, môn và giáo viên') }}
                            </flux:select.option>
                            @foreach ($this->availableAssignments as $assignment)
                                <flux:select.option :value="$assignment->id"
                                    wire:key="assignment-opt-{{ $assignment->id }}">
                                    {{ $assignment->label() }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="md:col-span-1">
                        <flux:select wire:model="status" variant="combobox" :label="__('Trạng thái')">
                            <flux:select.option value="pending">{{ __('Sắp diễn ra') }}</flux:select.option>
                            <flux:select.option value="in_progress">{{ __('Đang học') }}</flux:select.option>
                            <flux:select.option value="resolved">{{ __('Hoàn tất') }}</flux:select.option>
                            <flux:select.option value="closed">{{ __('Đã huỷ') }}</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="md:col-span-1">
                        <flux:time-picker wire:model="start_time" :label="__('Thời gian bắt đầu')" locale="vi-VN"
                            min="08:00" max="21:00" interval="15" />

                    </div>

                    <div class="md:col-span-1">
                        <flux:time-picker wire:model="end_time" :label="__('Thời gian kết thúc')" locale="vi-VN"
                            min="08:00" max="21:00" interval="15" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:date-picker wire:model="date_end_spirit" :label="__('Date end spirit')" locale="vi-VN" />
                    </div>

                     <div class="md:col-span-1">
                        <flux:date-picker wire:model="date_end_practice_theory" :label="__('Date end practice theory')" locale="vi-VN" />
                    </div>

                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeFormModal">
                        {{ __('Hủy') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ __('Lưu') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="md:w-96">
        <flux:heading>
            {{ __('Xác nhận xoá') }}
        </flux:heading>

        @if ($this->schedulePendingDeletion)
            <div class="space-y-4">
                <div class="rounded-lg bg-red-50 p-4 dark:bg-red-950">
                    <flux:text size="sm" class="text-red-800 dark:text-red-200">
                        {{ __('Bạn sắp xoá lịch học:') }}
                        <strong>{{ $this->schedulePendingDeletion->subjectName() }}</strong>
                    </flux:text>
                    <flux:text size="sm" class="text-red-700 dark:text-red-300">
                        {{ $this->schedulePendingDeletion->date?->format('d/m/Y') }} |
                        {{ $this->schedulePendingDeletion->typeLabel() }} @if (filled($this->schedulePendingDeletion->classroomName()))
                            | {{ $this->schedulePendingDeletion->classroomName() }}
                        @endif
                    </flux:text>
                </div>

                <flux:error name="delete" />

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeDeleteModal">
                        {{ __('Hủy') }}
                    </flux:button>
                    <flux:button type="button" variant="danger" wire:click="deleteSchedule">
                        {{ __('Xoá') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
