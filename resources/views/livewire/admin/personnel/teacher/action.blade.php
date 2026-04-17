<div>
    <flux:modal class="max-w-3xl" name="showFormModal">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingUserId ? __('Cập nhật người dùng') : __('Thêm người dùng') }}
                </flux:heading>
                <flux:text>
                    {{ __('Nhập thông tin tài khoản và chọn vai trò phù hợp.') }}
                </flux:text>
            </div>

            <form class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:items-start">
                    @if (!$editingUserId)
                        <div class="space-y-4 md:col-span-2">
                            <div class="space-y-2">
                                <flux:text class="text-sm font-medium">
                                    {{ __('Nguồn dữ liệu') }}
                                </flux:text>

                                <flux:radio.group wire:model.live="accountSource" variant="segmented">
                                    <flux:radio value="manual">{{ __('Nhập tay') }}</flux:radio>
                                    <flux:radio value="account_code">{{ __('Lấy theo mã tài khoản') }}</flux:radio>
                                </flux:radio.group>
                            </div>

                            <div class="{{ $accountSource === 'account_code' ? '' : 'invisible h-0 overflow-hidden' }}">
                                <div
                                    class="space-y-3 rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700 dark:bg-zinc-900/60">
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                                        <flux:input wire:model="accountCode" :label="__('Mã tài khoản')" type="text"
                                            placeholder="MV19019797"
                                            x-on:input="$event.target.value = $event.target.value.toUpperCase()" />

                                        <flux:button type="button" variant="primary"
                                            wire:click="fetchUserByAccountCode">
                                            {{ __('Lấy dữ liệu') }}
                                        </flux:button>
                                    </div>

                                    <flux:text size="sm">
                                        {{ __('Nhập mã tài khoản từ trang chính để tự động điền họ tên, email, ngày sinh và username.') }}
                                    </flux:text>

                                    @error('accountCode')
                                        <flux:callout variant="danger" icon="x-circle" :heading="$message" />
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="md:col-span-1">
                        <flux:input wire:model="holy_name" :label="__('Tên thánh')" type="text" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:input wire:model="name" :label="__('Họ và tên')" type="text" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:input wire:model="username" :label="__('Username')" type="text" readonly />
                    </div>

                    <div class="md:col-span-1">
                        <flux:input wire:model="email" :label="__('Email')" type="email" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:date-picker wire:model.live="birthday" :label="__('Ngày sinh')" locale="vi-VN" />
                    </div>

                    <div class="md:col-span-1">
                        <flux:select wire:model="role" :label="__('Vai trò')">
                            @foreach ($this->availableRoles as $roleName)
                                <flux:select.option :value="$roleName" wire:key="teacher-role-{{ $roleName }}">
                                    {{ $roleName }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    @if ($editingUserId)
                        <div class="md:col-span-1">
                            <flux:input wire:model="password"
                                :label="$editingUserId ? __('Mật khẩu mới') : __('Mật khẩu')" type="password"
                                viewable />
                        </div>

                        <div class="md:col-span-1">
                            <flux:input wire:model="password_confirmation" :label="__('Xác nhận mật khẩu')"
                                type="password" viewable />
                        </div>
                    @endif

                </div>

                <div class="flex items-center justify-end gap-3">
                    <flux:button variant="ghost" type="button" wire:click="closeFormModal">
                        {{ __('Huỷ') }}
                    </flux:button>

                    @if ($editingUserId)
                        <flux:button variant="primary" wire:click="saveAndClose">
                            {{ __('Lưu') }}
                        </flux:button>
                    @else
                        <flux:button variant="primary" wire:click="saveAndClose">
                            {{ __('Lưu') }}
                        </flux:button>
                        <flux:button variant="primary" wire:click="saveAndCreate">
                            {{ __('Lưu và thêm mới') }}
                        </flux:button>
                    @endif
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal class="max-w-md" name="showDeleteModal">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Xoá người dùng') }}</flux:heading>
                <flux:text>
                    {{ __('Bạn có chắc muốn xoá tài khoản này không? Hành động này không thể hoàn tác.') }}
                </flux:text>
            </div>

            @if ($this->userPendingDeletion)
                <div
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">
                    {{ $this->userPendingDeletion->name }} 
                    @if (filled($this->userPendingDeletion->email))
                        ({{ $this->userPendingDeletion->email }})
                    @endif
                </div>
            @endif

            @error('delete')
                <flux:callout variant="danger" icon="x-circle" :heading="$message" />
            @enderror

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="ghost" type="button" wire:click="closeDeleteModal">
                    {{ __('Huỷ') }}
                </flux:button>

                <flux:button variant="danger" type="button" wire:click="deleteUser">
                    {{ __('Xoá') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
