<div>
    <section class="w-full space-y-6">
        <div
            class="flex flex-col gap-4 rounded-3xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Điểm tổng hợp theo môn') }}</flux:heading>
                <flux:text class="max-w-3xl text-zinc-500">
                    {{ __('Mỗi hàng là một thiếu nhi, mỗi cột là một môn học. Nhấn vào điểm trung bình để xem chi tiết từng buổi, người điểm danh và các cột điểm.') }}
                </flux:text>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-3 lg:min-w-[48rem]">
                <flux:input wire:model.live.debounce.300ms="search" :label="__('Tìm thiếu nhi')" :placeholder="__('Tên, tên thánh hoặc username')" />

                <flux:select wire:model.live="selectedClassroomId" variant="combobox" :label="__('Lớp')">
                    <flux:select.option value="">{{ __('Tất cả lớp') }}</flux:select.option>
                    @foreach ($this->classrooms as $classroom)
                        <flux:select.option :value="$classroom->id" wire:key="score-summary-classroom-{{ $classroom->id }}">
                            {{ $classroom->code ?: $classroom->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="selectedSubjectId" variant="combobox" :label="__('Môn')">
                    <flux:select.option value="">{{ __('Tất cả môn') }}</flux:select.option>
                    @foreach ($this->availableSubjects as $subject)
                        <flux:select.option :value="$subject->id" wire:key="score-summary-subject-filter-{{ $subject->id }}">
                            {{ $subject->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <flux:card class="bg-white dark:bg-zinc-900">
            @if ($this->youths->isEmpty())
                <div class="flex min-h-56 items-center justify-center text-center">
                    <div class="space-y-2">
                        <flux:heading size="lg" level="3">{{ __('Không tìm thấy thiếu nhi phù hợp') }}</flux:heading>
                        <flux:text>{{ __('Hãy thử đổi bộ lọc lớp hoặc từ khoá tìm kiếm.') }}</flux:text>
                    </div>
                </div>
            @elseif ($this->subjects->isEmpty())
                <div class="flex min-h-56 items-center justify-center text-center">
                    <div class="space-y-2">
                        <flux:heading size="lg" level="3">{{ __('Chưa có môn học') }}</flux:heading>
                        <flux:text>{{ __('Hãy tạo môn học trước khi xem điểm tổng hợp.') }}</flux:text>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column sticky class="min-w-64 bg-white dark:bg-zinc-900">{{ __('Thiếu nhi') }}</flux:table.column>
                            @foreach ($this->subjects as $subject)
                                <flux:table.column align="center" wire:key="score-summary-subject-column-{{ $subject->id }}">
                                    <div class="min-w-28">
                                        {{ $subject->name }}
                                    </div>
                                </flux:table.column>
                            @endforeach
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->youths as $youth)
                                @php($userScores = $this->scoreMatrix->get($youth->id))

                                <flux:table.row :key="'score-summary-youth-'.$youth->id">
                                    <flux:table.cell sticky variant="strong" class="min-w-64 bg-white dark:bg-zinc-900">
                                        <div class="space-y-1">
                                            <div>{{ $youth->holy_name ?: '—' }} {{ $youth->name }}</div>
                                            <div class="text-xs text-zinc-500">{{ $youth->username }}</div>
                                           
                                        </div>
                                    </flux:table.cell>

                                    @foreach ($this->subjects as $subject)
                                        @php($cell = $userScores?->get($subject->id))

                                        <flux:table.cell align="center" wire:key="score-summary-cell-{{ $youth->id }}-{{ $subject->id }}">
                                            @if ($cell && $cell['entries_count'] > 0)
                                                <button
                                                    type="button"
                                                    wire:click="openDetail({{ $youth->id }}, {{ $subject->id }})"
                                                    class="inline-flex min-w-24 flex-col items-center gap-1 rounded-2xl border px-3 py-2 text-sm font-semibold transition hover:-translate-y-0.5 hover:shadow-sm
                                                        {{ $cell['result_status'] === 'passed' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:border-emerald-300 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300' : '' }}
                                                        {{ $cell['result_status'] === 'failed' ? 'border-rose-200 bg-rose-50 text-rose-700 hover:border-rose-300 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-300' : '' }}
                                                        {{ $cell['result_status'] === 'pending' ? 'border-amber-200 bg-amber-50 text-amber-700 hover:border-amber-300 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-300' : '' }}"
                                                >
                                                    <span>{{ $cell['average'] !== null ? number_format((float) $cell['average'], 2) : '—' }}</span>
                                                    <span class="text-[0.68rem] font-medium opacity-80">
                                                        {{ $cell['scored_count'] }}/{{ $cell['entries_count'] }} {{ __('buổi') }}
                                                    </span>
                                                  
                                                </button>
                                            @else
                                                <span class="inline-flex min-w-24 items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-400 dark:border-zinc-700 dark:bg-zinc-800">
                                                    —
                                                </span>
                                            @endif
                                        </flux:table.cell>
                                    @endforeach
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        </flux:card>
    </section>

    <flux:modal wire:model="showDetailModal" class="w-full max-w-6xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Chi tiết điểm theo môn') }}</flux:heading>
                <flux:text>
                    {{ $this->detailUser?->name ?? '—' }}
                    @if ($this->detailSubject)
                        | {{ $this->detailSubject->name }}
                    @endif
                </flux:text>
            </div>

            @if ($this->detailRows->isEmpty())
                <div class="rounded-2xl border border-dashed border-zinc-200 px-6 py-12 text-center dark:border-zinc-700">
                    <flux:heading size="lg" level="3">{{ __('Chưa có dữ liệu điểm') }}</flux:heading>
                    <flux:text>{{ __('Thiếu nhi này chưa có buổi học hoặc lịch bù nào trong môn đang chọn.') }}</flux:text>
                </div>
            @else
                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Ngày') }}</flux:table.column>
                            <flux:table.column>{{ __('Loại') }}</flux:table.column>
                            <flux:table.column>{{ __('Lớp') }}</flux:table.column>
                            <flux:table.column>{{ __('Giáo viên / người điểm danh') }}</flux:table.column>
                            <flux:table.column>{{ __('Điểm danh') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('Tinh thần') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('Lý thuyết') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('Thực hành') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('Tổng') }}</flux:table.column>
                            <flux:table.column>{{ __('Kết quả') }}</flux:table.column>
                            <flux:table.column>{{ __('Ghi chú') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->detailRows as $row)
                                <flux:table.row :key="'score-detail-'.$row['type'].'-'.$row['date'].'-'.$loop->index">
                                    <flux:table.cell variant="strong">{{ $row['date_label'] }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="space-y-1">
                                            <flux:badge size="sm" :color="$row['type'] === 'makeup' ? 'sky' : 'zinc'">
                                                {{ $row['type_label'] }}
                                            </flux:badge>
                                            @if ($row['original_label'])
                                                <div class="text-xs text-zinc-500">
                                                    {{ __('Bù cho') }}: {{ $row['original_label'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $row['classroom_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['teacher_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $this->attendanceStatusLabel($row['attendance_status']) }}</flux:table.cell>
                                    <flux:table.cell align="center">{{ $row['spirit_score'] !== null ? number_format((float) $row['spirit_score'], 2) : '—' }}</flux:table.cell>
                                    <flux:table.cell align="center">{{ $row['theory_score'] !== null ? number_format((float) $row['theory_score'], 2) : '—' }}</flux:table.cell>
                                    <flux:table.cell align="center">{{ $row['practice_score'] !== null ? number_format((float) $row['practice_score'], 2) : '—' }}</flux:table.cell>
                                    <flux:table.cell align="center">{{ $row['final_score'] !== null ? number_format((float) $row['final_score'], 2) : '—' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge
                                            size="sm"
                                            :color="$row['result_status'] === 'passed' ? 'emerald' : ($row['result_status'] === 'failed' ? 'rose' : 'amber')"
                                        >
                                            {{ $this->resultLabel($row['result_status']) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $row['attendance_note'] ?: '—' }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button type="button" variant="ghost" wire:click="closeDetailModal">
                    {{ __('Đóng') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
