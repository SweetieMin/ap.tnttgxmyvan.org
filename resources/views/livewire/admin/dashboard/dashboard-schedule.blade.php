@include('admin.management.schedule.calendar-after')

@if ($selectedSchedule)

    <flux:modal name="show-schedule" class="md:w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Chi tiết {{ $selectedSchedule->typeLabel() }} </flux:heading>
                <flux:text class="mt-2">
                    Thông tin chi tiết của buổi học.
                </flux:text>
            </div>



            @php
                $teachers = $selectedSchedule->classroomSubject?->teachers ?? collect();
                $colors = $selectedSchedule->calendarColorClasses();
            @endphp

            <div class="grid gap-4">
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">Lớp</div>
                    <div class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">
                        {{ $selectedSchedule->classroomSubject?->classroom?->name ?? '—' }}
                    </div>
                </div>

                <div
                    class="rounded-2xl border p-4 {{ $colors['background_class'] }} {{ $colors['border_class'] }} {{ $colors['hover_class'] }}">
                    <div class="text-sm text-zinc-500">Môn học</div>
                    <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                        {{ $selectedSchedule->classroomSubject?->subject?->name ?? '—' }}
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">


                    <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Ngày học</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($selectedSchedule->date)->translatedFormat('l, d/m/Y') }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Giờ học</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ \Illuminate\Support\Str::of($selectedSchedule->start_time)->substr(0, 5) }}
                            -
                            {{ \Illuminate\Support\Str::of($selectedSchedule->end_time)->substr(0, 5) }}
                        </div>
                    </div>


                </div>

                <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">Người dạy</div>


                    @if ($teachers->isNotEmpty())
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach ($teachers as $teacher)
                                <div class="rounded-xl bg-cyan-50/95 px-4 py-3 dark:bg-zinc-800/60">
                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ trim(($teacher->lastName ?? '') . ' ' . ($teacher->name ?? '')) ?: $teacher->name ?? '—' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-2 font-medium text-zinc-900 dark:text-white">
                            —
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </flux:modal>
@endif
