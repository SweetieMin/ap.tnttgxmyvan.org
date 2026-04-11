<div>
    <section class="space-y-4">
        <flux:card
            class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Bảng điểm') }}</flux:heading>
                <flux:text class="text-zinc-500">
                    {{ __('Theo dõi điểm tinh thần, lý thuyết, thực hành và kết quả của từng buổi học.') }}
                </flux:text>
            </div>
        </flux:card>

        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-4 ">
                @if ($this->subjectSummaries->isNotEmpty())
                   
                        @foreach ($this->subjectSummaries as $summary)
                            <flux:card
                                class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900"
                                wire:key="subject-summary-{{ $summary['subject_name'] }}-{{ $summary['classroom_name'] }}">
                                <div class="space-y-3">
                                    <div>
                                        <flux:text class="text-xs uppercase tracking-wide text-zinc-500">
                                            {{ __('Môn học') }}
                                        </flux:text>
                                        <flux:heading size="sm">{{ $summary['subject_name'] }}</flux:heading>
                                    </div>

                                    <div class="flex flex-wrap gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                                        <span
                                            class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 dark:border-zinc-700 dark:bg-zinc-800">
                                            {{ __('Lớp: :classroom', ['classroom' => $summary['classroom_name']]) }}
                                        </span>
                                        <span
                                            class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 dark:border-zinc-700 dark:bg-zinc-800">
                                            {{ __('Buổi đã có điểm: :count', ['count' => $summary['completed_count']]) }}
                                        </span>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/70 dark:bg-emerald-950/30">
                                        <flux:text
                                            class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                                            {{ __('Điểm trung bình') }}</flux:text>
                                        <flux:heading size="lg" class="text-emerald-700 dark:text-emerald-300">
                                            {{ $summary['average_score'] !== null ? number_format($summary['average_score'], 2) : '—' }}
                                        </flux:heading>
                                    </div>
                                </div>
                            </flux:card>
                        @endforeach

                @endif
            </div>

            <div class="col-span-8 ">
                <flux:card
                    class="rounded-3xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    @if ($this->scoreRows->isEmpty())
                        <div class="p-10 text-center">
                            <flux:heading size="sm">{{ __('Chưa có điểm nào được ghi nhận') }}</flux:heading>
                            <flux:text class="mt-2 text-zinc-500">
                                {{ __('Khi giáo viên nhập điểm cho các buổi học của con, bảng điểm sẽ hiện ở đây.') }}
                            </flux:text>
                        </div>
                    @else
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Ngày học') }}</flux:table.column>
                                <flux:table.column>{{ __('Lớp') }}</flux:table.column>
                                <flux:table.column>{{ __('Môn học') }}</flux:table.column>
                                <flux:table.column>{{ __('TT') }}</flux:table.column>
                                <flux:table.column>{{ __('LT') }}</flux:table.column>
                                <flux:table.column>{{ __('TH') }}</flux:table.column>
                                <flux:table.column>{{ __('Tổng') }}</flux:table.column>
                                <flux:table.column>{{ __('Kết quả') }}</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->scoreRows as $score)
                                    <flux:table.row :key="$score->id">
                                        <flux:table.cell>{{ $score->schedule?->date?->format('d/m/Y') ?? '—' }}
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $score->schedule?->classroomName() ?? '—' }}
                                        </flux:table.cell>
                                        <flux:table.cell variant="strong">{{ $score->schedule?->subjectName() ?? '—' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $score->spirit_score !== null ? number_format((float) $score->spirit_score, 2) : '—' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $score->theory_score !== null ? number_format((float) $score->theory_score, 2) : '—' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $score->practice_score !== null ? number_format((float) $score->practice_score, 2) : '—' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $score->final_score !== null ? number_format((float) $score->final_score, 2) : '—' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <span
                                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium
                                        {{ $score->result_status === 'passed' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300' : '' }}
                                        {{ $score->result_status === 'failed' ? 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-300' : '' }}
                                        {{ $score->result_status === 'pending' ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-300' : '' }}">
                                                {{ $score->result_status === 'passed' ? __('Đạt') : ($score->result_status === 'failed' ? __('Chưa đạt') : __('Chờ đủ điểm')) }}
                                            </span>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    @endif
                </flux:card>
            </div>
        </div>





    </section>
</div>
