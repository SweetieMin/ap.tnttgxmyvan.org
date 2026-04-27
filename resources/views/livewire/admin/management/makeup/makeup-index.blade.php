<div>
    <section class="w-full space-y-6">
        <div
            class="flex flex-col gap-4 rounded-3xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-end md:justify-between">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Quản lý lịch bù') }}</flux:heading>
                <flux:text class="max-w-3xl text-zinc-500">
                    {{ __('Tạo lịch bù theo môn và giáo viên, gán thiếu nhi vắng học vào đúng ca bù, rồi nhập điểm danh và điểm số như một buổi học bình thường.') }}
                </flux:text>
            </div>

            @if ($this->canCreate)
                <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                    {{ __('Tạo lịch bù') }}
                </flux:button>
            @endif
        </div>

        <flux:card class="bg-white dark:bg-zinc-900">
            @if ($this->sessions->isEmpty())
                <div class="flex flex-col items-center justify-center gap-2 py-12 text-center">
                    <flux:heading size="lg" level="3">{{ __('Chưa có lịch bù nào') }}</flux:heading>
                    <flux:text>{{ __('Tạo lịch bù đầu tiên để bắt đầu gán các buổi nghỉ cần học bù.') }}</flux:text>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Ngày') }}</flux:table.column>
                        <flux:table.column>{{ __('Môn') }}</flux:table.column>
                        <flux:table.column>{{ __('Giáo viên') }}</flux:table.column>
                        <flux:table.column>{{ __('Khung giờ') }}</flux:table.column>
                        <flux:table.column>{{ __('Số thiếu nhi') }}</flux:table.column>
                        <flux:table.column>{{ __('Trạng thái') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('Hành động') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->sessions as $session)
                            <flux:table.row :key="'makeup-session-'.$session->id">
                                <flux:table.cell variant="strong">{{ $session->date?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $session->subjectName() ?: '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $session->teacherName() ?: '—' }}</flux:table.cell>
                                <flux:table.cell>{{ substr((string) $session->start_time, 0, 5) }} - {{ substr((string) $session->end_time, 0, 5) }}</flux:table.cell>
                                <flux:table.cell>{{ $session->attendance_makeups_count }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm">
                                        {{ $this->statusOptions[$session->status] ?? $session->status }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <div class="flex justify-end gap-2">
                                        <flux:button
                                            size="sm"
                                            variant="{{ $this->selectedSession?->id === $session->id ? 'primary' : 'subtle' }}"
                                            icon="eye"
                                            wire:click="selectSession({{ $session->id }})"
                                        >
                                            {{ __('Chọn') }}
                                        </flux:button>

                                        @if ($this->canCreate)
                                            <flux:button size="sm" variant="ghost" icon="pencil-square"
                                                wire:click="openEditModal({{ $session->id }})" />
                                            <flux:button size="sm" variant="ghost" icon="trash"
                                                wire:click="openDeleteModal({{ $session->id }})"
                                                class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300" />
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

        @if ($this->selectedSession)
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
                <flux:card class="space-y-6 bg-white dark:bg-zinc-900">
                    <div class="space-y-2">
                        <flux:heading size="lg">{{ __('Thông tin lịch bù') }}</flux:heading>
                        <flux:text>{{ __('Thông tin điều hành và trạng thái tổng quan của lịch bù đang chọn.') }}</flux:text>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="text-sm text-zinc-500">{{ __('Môn học') }}</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $this->selectedSession->subjectName() ?: '—' }}</div>
                        </div>

                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="text-sm text-zinc-500">{{ __('Giáo viên') }}</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $this->selectedSession->teacherName() ?: '—' }}</div>
                        </div>

                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="text-sm text-zinc-500">{{ __('Thời gian') }}</div>
                            <div class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                {{ $this->selectedSession->date?->format('d/m/Y') ?? '—' }}
                                |
                                {{ substr((string) $this->selectedSession->start_time, 0, 5) }} - {{ substr((string) $this->selectedSession->end_time, 0, 5) }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="text-sm text-zinc-500">{{ __('Tổng quan') }}</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <flux:badge color="sky">{{ $this->selectedSessionAssignments->count() }} {{ __('đã gán') }}</flux:badge>
                                <flux:badge color="emerald">{{ $this->selectedSessionAssignments->where('status', 'completed')->count() }} {{ __('hoàn tất') }}</flux:badge>
                                <flux:badge color="amber">{{ $this->selectedSessionAssignments->where('status', 'scheduled')->count() }} {{ __('chờ học bù') }}</flux:badge>
                                <flux:badge color="rose">{{ $this->selectedSessionAssignments->where('status', 'missed')->count() }} {{ __('vắng buổi bù') }}</flux:badge>
                            </div>
                        </div>

                        @if (filled($this->selectedSession->note))
                            <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="text-sm text-zinc-500">{{ __('Ghi chú') }}</div>
                                <div class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $this->selectedSession->note }}</div>
                            </div>
                        @endif
                    </div>

                    @if ($this->canCreate)
                        <flux:button variant="primary" icon="user-plus" wire:click="openAssignModal">
                            {{ __('Gán thiếu nhi vào lịch bù') }}
                        </flux:button>
                    @endif
                </flux:card>

                <flux:card class="space-y-6 bg-white dark:bg-zinc-900">
                    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                        <div class="space-y-2">
                            <flux:heading size="lg">{{ __('Điểm danh và chấm điểm lịch bù') }}</flux:heading>
                            <flux:text>{{ __('Danh sách này là những thiếu nhi đã được gán vào lịch bù đang chọn.') }}</flux:text>
                        </div>

                        <div class="text-sm text-zinc-500">
                            {{ $this->canManageSelectedSession ? __('Bạn có thể nhập kết quả cho lịch bù này.') : __('Chỉ giáo viên phụ trách hoặc người có quyền quản lý mới được nhập kết quả khi lịch đã tới giờ.') }}
                        </div>
                    </div>

                    @if ($this->selectedSessionAssignments->isEmpty())
                        <div class="flex min-h-56 items-center justify-center rounded-2xl border border-dashed border-zinc-200 text-center dark:border-zinc-700">
                            <div class="space-y-2 px-6">
                                <flux:heading size="lg" level="3">{{ __('Chưa có thiếu nhi nào trong lịch bù') }}</flux:heading>
                                <flux:text>{{ __('Hãy gán thiếu nhi từ các buổi nghỉ phù hợp vào lịch bù này trước.') }}</flux:text>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Thiếu nhi') }}</flux:table.column>
                                    <flux:table.column>{{ __('Buổi nghỉ gốc') }}</flux:table.column>
                                    <flux:table.column>{{ __('Cảnh báo') }}</flux:table.column>
                                    <flux:table.column>{{ __('Điểm danh') }}</flux:table.column>
                                    <flux:table.column>{{ __('Tinh thần') }}</flux:table.column>
                                    <flux:table.column>{{ __('Lý thuyết') }}</flux:table.column>
                                    <flux:table.column>{{ __('Thực hành') }}</flux:table.column>
                                    <flux:table.column>{{ __('Tổng') }}</flux:table.column>
                                    <flux:table.column>{{ __('Kết quả') }}</flux:table.column>
                                    <flux:table.column>{{ __('Ghi chú') }}</flux:table.column>
                                    <flux:table.column align="end">{{ __('Lưu') }}</flux:table.column>
                                </flux:table.columns>

                                <flux:table.rows>
                                    @foreach ($this->selectedSessionAssignments as $assignment)
                                        <flux:table.row :key="'makeup-assignment-'.$assignment->id">
                                            <flux:table.cell variant="strong" class="min-w-52">
                                                <div class="space-y-1">
                                                    <div>{{ $assignment->user?->holy_name ?: '—' }} {{ $assignment->user?->name }}</div>
                                                    <div class="text-xs text-zinc-500">{{ $assignment->user?->username }}</div>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach ($assignment->user?->classrooms ?? [] as $classroom)
                                                            <flux:badge size="sm" color="zinc" wire:key="makeup-user-classroom-{{ $assignment->id }}-{{ $classroom->id }}">
                                                                {{ $classroom->code ?: $classroom->name }}
                                                            </flux:badge>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </flux:table.cell>

                                            <flux:table.cell class="min-w-48">
                                                <div class="space-y-1 text-sm">
                                                    <div>{{ $assignment->originalAttendance?->schedule?->date?->format('d/m/Y') ?? '—' }}</div>
                                                    <div class="text-zinc-500">
                                                        {{ $assignment->originalAttendance?->schedule?->subjectName() ?: '—' }}
                                                        @if (filled($assignment->originalAttendance?->schedule?->classroomName()))
                                                            | {{ $assignment->originalAttendance?->schedule?->classroomName() }}
                                                        @endif
                                                    </div>
                                                    <flux:badge size="sm" color="amber">
                                                        {{ $assignment->original_attendance_status }}
                                                    </flux:badge>
                                                </div>
                                            </flux:table.cell>

                                            <flux:table.cell class="min-w-48">
                                                <div class="flex flex-col gap-2">
                                                    @forelse ($assignmentConflictWarnings[$assignment->id] ?? [] as $warning)
                                                        <flux:badge size="sm" color="amber">{{ $warning }}</flux:badge>
                                                    @empty
                                                        <span class="text-sm text-zinc-400">{{ __('Không trùng lịch') }}</span>
                                                    @endforelse
                                                </div>
                                            </flux:table.cell>

                                            <flux:table.cell class="min-w-48">
                                                <flux:select
                                                    variant="combobox"
                                                    wire:model.live="attendanceStatuses.{{ $assignment->id }}"
                                                    :disabled="(! $this->canManageSelectedSession) || $this->selectedSession->isSpiritScoreLocked()"
                                                >
                                                    <flux:select.option value="">{{ __('Chọn trạng thái') }}</flux:select.option>
                                                    @foreach ($this->attendanceStatusOptions as $value => $label)
                                                        <flux:select.option :value="$value" wire:key="makeup-status-{{ $assignment->id }}-{{ $value }}">
                                                            {{ $label }}
                                                        </flux:select.option>
                                                    @endforeach
                                                </flux:select>
                                            </flux:table.cell>

                                            <flux:table.cell align="center">
                                                <span class="inline-flex min-w-16 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                    {{ $this->previewSpiritScore($assignment->id) !== null ? number_format((float) $this->previewSpiritScore($assignment->id), 2) : '—' }}
                                                </span>
                                            </flux:table.cell>

                                            <flux:table.cell class="min-w-24">
                                                <flux:input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="10"
                                                    wire:model.live.debounce.400ms="theoryScores.{{ $assignment->id }}"
                                                    :disabled="(! $this->canManageSelectedSession) || $this->selectedSession->areTheoryPracticeScoresLocked()"
                                                />
                                            </flux:table.cell>

                                            <flux:table.cell class="min-w-24">
                                                <flux:input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="10"
                                                    wire:model.live.debounce.400ms="practiceScores.{{ $assignment->id }}"
                                                    :disabled="(! $this->canManageSelectedSession) || $this->selectedSession->areTheoryPracticeScoresLocked()"
                                                />
                                            </flux:table.cell>

                                            <flux:table.cell align="center">
                                                <div class="min-w-16 text-sm font-semibold text-zinc-700 dark:text-zinc-100">
                                                    {{ $this->previewFinalScore($assignment->id) !== null ? number_format((float) $this->previewFinalScore($assignment->id), 2) : '—' }}
                                                </div>
                                            </flux:table.cell>

                                            <flux:table.cell>
                                                @php($previewStatus = $this->previewResultStatus($assignment->id))
                                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium
                                                    {{ $previewStatus === 'passed' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300' : '' }}
                                                    {{ $previewStatus === 'failed' ? 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-300' : '' }}
                                                    {{ $previewStatus === 'pending' ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-300' : '' }}">
                                                    {{ $previewStatus === 'passed' ? __('Đạt') : ($previewStatus === 'failed' ? __('Chưa đạt') : __('Chờ đủ điểm')) }}
                                                </span>
                                            </flux:table.cell>

                                            <flux:table.cell class="min-w-48">
                                                <flux:input
                                                    wire:model.live.debounce.400ms="attendanceNotes.{{ $assignment->id }}"
                                                    :disabled="! $this->canManageSelectedSession"
                                                />
                                            </flux:table.cell>

                                            <flux:table.cell align="end">
                                                <flux:button
                                                    size="sm"
                                                    variant="primary"
                                                    wire:click="saveMakeupRecord({{ $assignment->id }})"
                                                    :disabled="! $this->canManageSelectedSession"
                                                >
                                                    {{ __('Lưu') }}
                                                </flux:button>
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        </div>
                    @endif
                </flux:card>
            </div>
        @else
            <flux:card class="bg-white dark:bg-zinc-900">
                <div class="flex flex-col items-center justify-center gap-2 py-12 text-center">
                    <flux:heading size="lg" level="3">{{ __('Chọn một lịch bù để tiếp tục') }}</flux:heading>
                    <flux:text>{{ __('Sau khi chọn lịch bù, bạn có thể gán thiếu nhi, xem cảnh báo trùng lịch và nhập kết quả học bù ngay trên trang này.') }}</flux:text>
                </div>
            </flux:card>
        @endif
    </section>

    <flux:modal wire:model="showSessionModal" class="md:w-200">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading>{{ $editingSessionId ? __('Cập nhật lịch bù') : __('Tạo lịch bù') }}</flux:heading>
                <flux:text>{{ __('Lịch bù chỉ thuộc một môn và một giáo viên, nhưng có thể gán nhiều thiếu nhi từ các lớp khác nhau.') }}</flux:text>
            </div>

            <form class="space-y-6" wire:submit="saveSession">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <flux:select wire:model="subject_id" variant="combobox" :label="__('Môn học')">
                            <flux:select.option value="">{{ __('Chọn môn học') }}</flux:select.option>
                            @foreach ($this->availableSubjects as $subject)
                                <flux:select.option :value="$subject->id" wire:key="makeup-subject-{{ $subject->id }}">
                                    {{ $subject->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:select wire:model="teacher_id" variant="combobox" :label="__('Giáo viên')">
                            <flux:select.option value="">{{ __('Chọn giáo viên') }}</flux:select.option>
                            @foreach ($this->availableTeachers as $teacher)
                                <flux:select.option :value="$teacher->id" wire:key="makeup-teacher-{{ $teacher->id }}">
                                    {{ $teacher->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:date-picker wire:model="date" :label="__('Ngày học bù')" locale="vi-VN" />
                    </div>

                    <div>
                        <flux:select wire:model="status" variant="combobox" :label="__('Trạng thái')">
                            @foreach ($this->statusOptions as $value => $label)
                                <flux:select.option :value="$value" wire:key="makeup-status-option-{{ $value }}">
                                    {{ $label }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:time-picker wire:model="start_time" :label="__('Bắt đầu')" locale="vi-VN"
                            min="08:00" max="21:00" interval="15" />
                    </div>

                    <div>
                        <flux:time-picker wire:model="end_time" :label="__('Kết thúc')" locale="vi-VN"
                            min="08:00" max="21:00" interval="15" />
                    </div>

                    <div>
                        <flux:date-picker wire:model="date_end_spirit" :label="__('Hạn điểm tinh thần')" locale="vi-VN" />
                    </div>

                    <div>
                        <flux:date-picker wire:model="date_end_practice_theory" :label="__('Hạn điểm lý thuyết và thực hành')" locale="vi-VN" />
                    </div>

                    <div class="md:col-span-2">
                        <flux:textarea wire:model="note" :label="__('Ghi chú')" rows="3" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button type="button" variant="ghost" wire:click="closeSessionModal">
                        {{ __('Huỷ') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ __('Lưu') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model="showAssignModal" class="md:w-[72rem]">
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <flux:heading>{{ __('Gán thiếu nhi vào lịch bù') }}</flux:heading>
                    <flux:text>
                        {{ __('Chỉ hiện các buổi vắng cùng môn và cùng giáo viên, chưa được gán vào lịch bù nào khác.') }}
                    </flux:text>
                </div>

                <flux:badge color="zinc">
                    {{ count($selectedOriginalAttendanceIds) }} {{ __('đã chọn') }}
                </flux:badge>
            </div>

            <div class="max-h-[32rem] overflow-y-auto rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                @if ($this->eligibleOriginalAttendances->isNotEmpty())
                    <flux:checkbox.group wire:model="selectedOriginalAttendanceIds" variant="cards">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @foreach ($this->eligibleOriginalAttendances as $attendance)
                                <flux:checkbox :value="$attendance->id" wire:key="eligible-makeup-attendance-{{ $attendance->id }}">
                                    <div class="space-y-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-base font-semibold text-zinc-900 dark:text-white">
                                                    {{ $attendance->user?->holy_name ?: '—' }} {{ $attendance->user?->name }}
                                                </div>
                                                <div class="text-sm text-zinc-500">
                                                    {{ $attendance->schedule?->date?->format('d/m/Y') ?? '—' }}
                                                    |
                                                    {{ $attendance->schedule?->classroomName() ?: __('Chưa rõ lớp') }}
                                                </div>
                                                <div class="text-sm text-zinc-500">
                                                    {{ $attendance->schedule?->subjectName() ?: '—' }}
                                                    |
                                                    {{ $attendance->status }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($attendance->user?->classrooms ?? [] as $classroom)
                                                <flux:badge size="sm" color="sky">
                                                    {{ $classroom->code ?: $classroom->name }}
                                                </flux:badge>
                                            @endforeach
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            @forelse ($eligibleConflictWarnings[$attendance->id] ?? [] as $warning)
                                                <flux:badge size="sm" color="amber">{{ $warning }}</flux:badge>
                                            @empty
                                                <span class="text-sm text-zinc-400">{{ __('Không trùng lịch trong khung giờ bù') }}</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </flux:checkbox>
                            @endforeach
                        </div>
                    </flux:checkbox.group>
                @else
                    <div class="flex min-h-48 items-center justify-center text-center">
                        <div class="space-y-2">
                            <flux:heading size="lg" level="3">{{ __('Không còn buổi nghỉ phù hợp để gán') }}</flux:heading>
                            <flux:text>{{ __('Hãy kiểm tra lại môn học, giáo viên hoặc xem các buổi nghỉ này đã được gán vào lịch bù khác chưa.') }}</flux:text>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <flux:button type="button" variant="ghost" wire:click="closeAssignModal">
                    {{ __('Huỷ') }}
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="saveAssignments">
                    {{ __('Gán vào lịch bù') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="md:w-96">
        <div class="space-y-4">
            <flux:heading>{{ __('Xoá lịch bù') }}</flux:heading>
            <flux:text>
                {{ __('Khi xoá lịch bù, các gán học bù trong lịch này cũng bị xoá và trạng thái buổi nghỉ gốc sẽ trở về như ban đầu.') }}
            </flux:text>

            <div class="flex justify-end gap-3">
                <flux:button type="button" variant="ghost" wire:click="closeDeleteModal">
                    {{ __('Huỷ') }}
                </flux:button>
                <flux:button type="button" variant="danger" wire:click="deleteSession">
                    {{ __('Xoá') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
