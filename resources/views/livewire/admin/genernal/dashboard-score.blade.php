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
            <div class="col-span-12 space-y-4 xl:col-span-4">
                @if ($this->subjectSummaries->isNotEmpty())
                    @php
                        $overview = $this->scoreOverview;
                    @endphp

                    <flux:card
                        class="overflow-hidden rounded-3xl border border-cyan-100 bg-linear-to-br from-cyan-50 via-white to-emerald-50 p-0 shadow-xs dark:border-cyan-900/60 dark:from-cyan-950/40 dark:via-zinc-900 dark:to-emerald-950/30">
                        <div class="space-y-6 p-5">
                            <div class="space-y-2">
                                <flux:text class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700 dark:text-cyan-300">
                                    {{ __('Điểm trung bình tổng quan') }}
                                </flux:text>
                                <div class="flex items-end justify-between gap-4">
                                    <div>
                                        <div class="text-4xl font-black tracking-tight text-zinc-950 dark:text-white">
                                            {{ $overview['average_score'] !== null ? number_format($overview['average_score'], 2) : '—' }}
                                        </div>
                                        <flux:text class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                                            {{ __('Tổng hợp từ tất cả các buổi đã có đủ điểm.') }}
                                        </flux:text>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-white/80 bg-white/80 px-4 py-3 text-right shadow-sm backdrop-blur dark:border-white/10 dark:bg-zinc-950/50">
                                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">
                                            {{ __('Đạt chuẩn') }}
                                        </div>
                                        <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-300">
                                            {{ $overview['passed_subject_count'] }}/{{ $overview['tracked_subject_count'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>`

                            <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1 2xl:grid-cols-3">
                                <div class="rounded-2xl border border-white/80 bg-white/80 p-4 backdrop-blur dark:border-white/10 dark:bg-zinc-950/50">
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">
                                        {{ __('Môn đang theo dõi') }}
                                    </div>
                                    <div class="mt-2 text-2xl font-bold text-zinc-950 dark:text-white">
                                        {{ $overview['tracked_subject_count'] }}
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-white/80 bg-white/80 p-4 backdrop-blur dark:border-white/10 dark:bg-zinc-950/50">
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">
                                        {{ __('Buổi đã có điểm') }}
                                    </div>
                                    <div class="mt-2 text-2xl font-bold text-zinc-950 dark:text-white">
                                        {{ $overview['completed_lesson_count'] }}/{{ $overview['total_lesson_count'] }}
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-white/80 bg-white/80 p-4 backdrop-blur dark:border-white/10 dark:bg-zinc-950/50">
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">
                                        {{ __('Mức hoàn thành') }}
                                    </div>
                                    <div class="mt-2 text-2xl font-bold text-zinc-950 dark:text-white">
                                        {{ $overview['total_lesson_count'] > 0 ? number_format(($overview['completed_lesson_count'] / $overview['total_lesson_count']) * 100, 0) : 0 }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>


                @else
                    <flux:card
                        class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="space-y-2 text-center">
                            <flux:heading size="sm">{{ __('Chưa có dữ liệu trung bình') }}</flux:heading>
                            <flux:text class="text-zinc-500">
                                {{ __('Khi các buổi học được nhập điểm, phần tổng quan theo môn sẽ hiện ở đây.') }}
                            </flux:text>
                        </div>
                    </flux:card>
                @endif
            </div>

            <div class="col-span-12 xl:col-span-8">
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
