<div>
    <flux:modal class="max-w-3xl" name="showFormModal">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingSubjectId ? __('Cập nhật môn học') : __('Thêm môn học') }}
                </flux:heading>
                <flux:text>
                    {{ __('Nhập thông tin môn học để dùng trong phân công giảng dạy và lịch học.') }}
                </flux:text>
            </div>

            <form wire:submit="saveSubject" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:items-start">
                    <div class="md:col-span-1">
                        <flux:input wire:model="name" :label="__('Tên môn học')" type="text" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:input wire:model="code" :label="__('Mã môn')" type="text" />
                    </div>

                    <div class="md:col-span-2">
                        <flux:textarea wire:model="description" :label="__('Mô tả')" rows="4" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:select wire:model="status" :label="__('Trạng thái')">
                            @foreach ($this->availableStatuses as $value => $label)
                                <flux:select.option :value="$value" wire:key="subject-status-{{ $value }}">
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
                        {{ $editingSubjectId ? __('Lưu thay đổi') : __('Tạo môn học') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal class="max-w-md" name="showDeleteModal">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Xoá môn học') }}</flux:heading>
                <flux:text>
                    {{ __('Bạn có chắc muốn xoá môn học này không? Những phân công và lịch học liên quan cũng sẽ bị xoá.') }}
                </flux:text>
            </div>

            @if ($this->subjectPendingDeletion)
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">
                    {{ $this->subjectPendingDeletion->name }} ({{ $this->subjectPendingDeletion->code ?? '—' }})
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="ghost" type="button" wire:click="closeDeleteModal">
                    {{ __('Huỷ') }}
                </flux:button>
                <flux:button variant="danger" type="button" wire:click="deleteSubject">
                    {{ __('Xoá') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
