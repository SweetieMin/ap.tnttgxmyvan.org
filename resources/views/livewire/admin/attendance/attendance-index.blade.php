<div>
    <section class="w-full space-y-2">
        <div class="grid gap-2 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,0.9fr)]">
            <div class="space-y-2">
                <flux:card class="space-y-4 rounded-3xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="grid gap-4 md:grid-cols-3">
                        <flux:field>
                            <flux:date-picker wire:model.live="selectedDate" :label="__('Lọc theo ngày học')" locale="vi-VN" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Phạm vi lớp') }}</flux:label>
                            <flux:select variant="combobox" wire:model.live="scheduleScope">
                                <flux:select.option value="all">{{ __('Tất cả') }}</flux:select.option>
                                <flux:select.option value="mine">{{ __('Lớp của tôi') }}</flux:select.option>
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Buổi học cần nhập') }}</flux:label>
                            <flux:select variant="combobox" wire:model.live="selectedScheduleId" wire:key="attendance-schedule-options-{{ $scheduleScope }}-{{ $selectedDate ?? 'all' }}">
                                @forelse ($this->scheduleOptions as $schedule)
                                    <flux:select.option :value="$schedule->id" wire:key="attendance-schedule-{{ $schedule->id }}">
                                        {{ $schedule->date?->format('d/m/Y') }} · {{ substr((string) $schedule->start_time, 0, 5) }} · {{ $schedule->classroomName() }} · {{ $schedule->subjectName() }}
                                    </flux:select.option>
                                @empty
                                    <flux:select.option value="">{{ __('Không có lịch phù hợp') }}</flux:select.option>
                                @endforelse
                            </flux:select>
                        </flux:field>
                    </div>
                </flux:card>

                @if ($this->selectedSchedule)
                    <flux:card class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="grid gap-4 lg:grid-cols-4">
                            <div class="space-y-1">
                                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Lớp học') }}</flux:text>
                                <flux:heading size="sm">{{ $this->selectedSchedule->classroomName() }}</flux:heading>
                            </div>

                            <div class="space-y-1">
                                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Môn học') }}</flux:text>
                                <flux:heading size="sm">{{ $this->selectedSchedule->subjectName() }}</flux:heading>
                            </div>

                            <div class="space-y-1">
                                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Giáo viên') }}</flux:text>
                                <flux:heading size="sm">{{ $this->selectedSchedule->teacherName() ?: '—' }}</flux:heading>
                            </div>

                            <div class="space-y-1">
                                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Khung giờ') }}</flux:text>
                                <flux:heading size="sm">
                                    {{ $this->selectedSchedule->date?->format('d/m/Y') }} · {{ substr((string) $this->selectedSchedule->start_time, 0, 5) }} - {{ substr((string) $this->selectedSchedule->end_time, 0, 5) }}
                                </flux:heading>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium {{ $this->selectedSchedule->isSpiritScoreLocked() ? 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-300' : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                                {{ __('Hạn tinh thần & điểm danh: :time', ['time' => $this->selectedSchedule->spiritScoreDeadlineAt()?->format('d/m/Y H:i') ?? '—']) }}
                            </span>

                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium {{ $this->selectedSchedule->areTheoryPracticeScoresLocked() ? 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-300' : 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900/70 dark:bg-sky-950/40 dark:text-sky-300' }}">
                                {{ __('Hạn lý thuyết / thực hành: :time', ['time' => $this->selectedSchedule->theoryPracticeScoreDeadlineAt()?->format('d/m/Y H:i') ?? '—']) }}
                            </span>
                        </div>
                    </flux:card>

                    @php($canManageSelectedSchedule = $this->canManageSelectedSchedule)
                    <flux:card>
                        <flux:table container:class="max-h-[calc(100vh-380px)]">
                            <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
                                <flux:table.column sticky class="bg-white dark:bg-zinc-900">{{ __('Thiếu nhi') }}</flux:table.column>
                                <flux:table.column>{{ __('Điểm danh') }}</flux:table.column>
                                <flux:table.column>{{ __('TT') }}</flux:table.column>
                                <flux:table.column>{{ __('LT') }}</flux:table.column>
                                <flux:table.column>{{ __('TH') }}</flux:table.column>
                                <flux:table.column>{{ __('Tổng') }}</flux:table.column>
                                <flux:table.column>{{ __('Kết quả') }}</flux:table.column>
                                <flux:table.column>{{ __('Ghi chú') }}</flux:table.column>
                                <flux:table.column align="end">{{ __('Lưu') }}</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @forelse ($this->rosterRows as $row)
                                    @php($user = $row['user'])
                                    <flux:table.row :key="'attendance-row-'.$user->id">
                                        <flux:table.cell variant="strong" sticky class="bg-white dark:bg-zinc-900">
                                            <div class="space-y-1">
                                                <div>{{ $user->holy_name ?: '—' }} {{ $user->name }}</div>
                                                <div class="text-xs text-zinc-500">{{ $user->username }}</div>
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="space-y-2 min-w-48">
                                                @if ($canManageSelectedSchedule)
                                                    <flux:select variant="combobox" wire:model.live="attendanceStatuses.{{ $user->id }}" :disabled="$this->selectedSchedule->isSpiritScoreLocked()">
                                                        <flux:select.option value="">{{ __('Chọn trạng thái') }}</flux:select.option>
                                                        @foreach ($this->attendanceStatusOptions() as $value => $label)
                                                            <flux:select.option :value="$value" wire:key="attendance-status-{{ $user->id }}-{{ $value }}">
                                                                {{ $label }}
                                                            </flux:select.option>
                                                        @endforeach
                                                    </flux:select>
                                                @else
                                                    <span class="inline-flex min-w-32 items-center rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                                        {{ $this->attendanceStatusOptions()[$this->attendanceStatuses[$user->id] ?? ''] ?? $this->attendanceAccessMessage() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <span class="inline-flex min-w-16 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                {{ $row['spirit_score'] !== null ? number_format($row['spirit_score'], 2) : '—' }}
                                            </span>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="min-w-24">
                                                <flux:input type="number" step="0.01" min="0" max="10" wire:model.live.debounce.400ms="theoryScores.{{ $user->id }}" :disabled="(! $canManageSelectedSchedule) || $this->selectedSchedule->areTheoryPracticeScoresLocked()" />
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="min-w-24">
                                                <flux:input type="number" step="0.01" min="0" max="10" wire:model.live.debounce.400ms="practiceScores.{{ $user->id }}" :disabled="(! $canManageSelectedSchedule) || $this->selectedSchedule->areTheoryPracticeScoresLocked()" />
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="min-w-20 text-sm font-semibold text-zinc-700 dark:text-zinc-100">
                                                {{ $row['preview_final_score'] !== null ? number_format($row['preview_final_score'], 2) : '—' }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium
                                                {{ $row['preview_result_status'] === 'passed' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300' : '' }}
                                                {{ $row['preview_result_status'] === 'failed' ? 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-300' : '' }}
                                                {{ $row['preview_result_status'] === 'pending' ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-300' : '' }}">
                                                {{ $row['preview_result_status'] === 'passed' ? __('Đạt') : ($row['preview_result_status'] === 'failed' ? __('Chưa đạt') : __('Chờ đủ điểm')) }}
                                            </span>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="min-w-56">
                                                <flux:input wire:model.live.debounce.400ms="attendanceNotes.{{ $user->id }}" :disabled="! $canManageSelectedSchedule" />
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell align="end">
                                            @if ($canManageSelectedSchedule)
                                                <flux:button variant="primary" wire:click="saveRecord({{ $user->id }})" wire:loading.attr="disabled" wire:target="saveRecord({{ $user->id }})">
                                                    {{ __('Lưu') }}
                                                </flux:button>
                                            @else
                                                <span class="text-sm text-zinc-400 dark:text-zinc-500">—</span>
                                            @endif
                                        </flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="9">
                                            <div class="py-10 text-center text-sm text-zinc-500">
                                                {{ __('Lớp của buổi học này chưa có thiếu nhi nào để điểm danh.') }}
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </flux:card>
                @else
                    <flux:card class="rounded-3xl border border-dashed border-zinc-300 bg-white p-10 text-center shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:heading size="sm">{{ __('Chưa có lịch học phù hợp') }}</flux:heading>
                        <flux:text class="mt-2 text-zinc-500">{{ __('Hãy điều chỉnh bộ lọc hoặc tạo lịch học có bật ghi nhận điểm danh.') }}</flux:text>
                    </flux:card>
                @endif
            </div>

            <div class="space-y-6">
                <flux:card class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('Nội quy điểm tinh thần') }}</flux:heading>
                        <div class="space-y-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/60">
                                <div>{{ __('Đi học đúng giờ: 10 điểm') }}</div>
                                <div>{{ __('Đi trễ có phép: 7 điểm') }}</div>
                                <div>{{ __('Đi trễ không phép: 5 điểm') }}</div>
                                <div>{{ __('Vắng có phép: 0 điểm') }}</div>
                                <div>{{ __('Vắng không phép: -10 điểm') }}</div>
                                <div>{{ __('Học bù hoàn tất: tối đa 8 điểm') }}</div>
                            </div>

                            <div>{{ __('Đi trễ phải xin phép trước giờ học ít nhất 1 tiếng. Sau mốc đó xem như trễ không phép.') }}</div>
                            <div>{{ __('Vắng học phải xin phép trước ít nhất 1 ngày. Nếu báo muộn hơn thì tính là vắng không phép.') }}</div>
                            <div>{{ __('Vắng có phép phải chủ động liên hệ trưởng dạy khóa để học bù.') }}</div>
                            <div>{{ __('Vắng không phép thì không được bù.') }}</div>
                            <div>{{ __('Nếu nghỉ học thì phải học bù lại buổi đã vắng. Khi buổi đó có 3 người vắng, 3 người tự thống nhất thời gian và liên hệ trưởng dạy khóa để bù.') }}</div>
                        </div>
                    </div>
                </flux:card>

                @if ($this->selectedSchedule)
                    <flux:card class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="space-y-3">
                            <flux:heading size="sm">{{ __('Trạng thái khóa nhập') }}</flux:heading>

                            <div class="rounded-2xl border p-4 {{ $this->selectedSchedule->isSpiritScoreLocked() ? 'border-rose-200 bg-rose-50 dark:border-rose-900/70 dark:bg-rose-950/30' : 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/70 dark:bg-emerald-950/30' }}">
                                <div class="text-sm font-medium {{ $this->selectedSchedule->isSpiritScoreLocked() ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                                    {{ __('Điểm danh và tinh thần') }}
                                </div>
                                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $this->selectedSchedule->isSpiritScoreLocked() ? __('Đã khóa sau hạn nhập.') : __('Đang cho phép cập nhật.') }}
                                </div>
                            </div>

                            <div class="rounded-2xl border p-4 {{ $this->selectedSchedule->areTheoryPracticeScoresLocked() ? 'border-rose-200 bg-rose-50 dark:border-rose-900/70 dark:bg-rose-950/30' : 'border-sky-200 bg-sky-50 dark:border-sky-900/70 dark:bg-sky-950/30' }}">
                                <div class="text-sm font-medium {{ $this->selectedSchedule->areTheoryPracticeScoresLocked() ? 'text-rose-700 dark:text-rose-300' : 'text-sky-700 dark:text-sky-300' }}">
                                    {{ __('Lý thuyết và thực hành') }}
                                </div>
                                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $this->selectedSchedule->areTheoryPracticeScoresLocked() ? __('Đã khóa sau hạn nhập.') : __('Đang cho phép cập nhật.') }}
                                </div>
                            </div>
                        </div>
                    </flux:card>
                @endif
            </div>
        </div>
    </section>
</div>
