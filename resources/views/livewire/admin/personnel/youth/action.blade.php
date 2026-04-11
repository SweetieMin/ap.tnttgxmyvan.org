<div>

    <flux:modal name="showFormModal" class="md:w-200">
        <flux:heading>
            {{ $editingUserId ? __('Cập nhật thiếu nhi') : __('Thêm thiếu nhi') }}
        </flux:heading>

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
                            <div class="space-y-3 rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700 dark:bg-zinc-900/60">
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                                    <flux:input
                                        wire:model="accountCode"
                                        :label="__('Mã tài khoản')"
                                        type="text"
                                        placeholder="MV19019797"
                                        x-on:input="$event.target.value = $event.target.value.toUpperCase()"
                                    />
                        
                                    <flux:button type="button" variant="primary" wire:click="fetchUserByAccountCode">
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
                            <flux:select.option :value="$roleName" wire:key="youth-role-{{ $roleName }}">
                                {{ $roleName }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                @if ($editingUserId)
                    <div class="md:col-span-1">
                        <flux:input wire:model="password" :label="$editingUserId ? __('Mật khẩu mới') : __('Mật khẩu')"
                            type="password" viewable />
                    </div>

                    <div class="md:col-span-1">
                        <flux:input wire:model="password_confirmation" :label="__('Xác nhận mật khẩu')" type="password"
                            viewable />
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeFormModal">
                    {{ __('Hủy') }}
                </flux:button>
                <flux:button variant="primary" wire:click="saveAndCreate">
                    {{ $editingUserId ? __('Lưu') : __('Lưu và thêm mới') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="showDeleteModal" class="md:w-96">
        <flux:heading>
            {{ __('Xác nhận xoá') }}
        </flux:heading>

        @if ($this->userPendingDeletion)
            <div class="space-y-4">
                <div class="rounded-lg bg-red-50 p-4 dark:bg-red-950">
                    <flux:text size="sm" class="text-red-800 dark:text-red-200">
                        {{ __('Bạn sắp xoá thiếu nhi:') }} <strong>{{ $this->userPendingDeletion->name }}</strong>
                    </flux:text>
                    <flux:text size="sm" class="text-red-700 dark:text-red-300">
                        {{ $this->userPendingDeletion->email }}
                    </flux:text>
                </div>

                <flux:error name="delete" />

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeDeleteModal">
                        {{ __('Hủy') }}
                    </flux:button>
                    <flux:button type="button" variant="danger" wire:click="deleteUser">
                        {{ __('Xoá') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
