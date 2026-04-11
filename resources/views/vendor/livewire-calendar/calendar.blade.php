<div
    @if($pollMillis !== null && $pollAction !== null)
        wire:poll.{{ $pollMillis }}ms="{{ $pollAction }}"
    @elseif($pollMillis !== null)
        wire:poll.{{ $pollMillis }}ms
    @endif
    class="space-y-4"
>
    <flux:card class="overflow-hidden rounded-3xl border border-zinc-200 bg-white p-2 shadow-sm ring-1 ring-black/5 dark:border-zinc-700 dark:bg-zinc-900 dark:ring-white/10">
        @if($beforeCalendarView)
            <div class="p-2 pb-4">
                @include($beforeCalendarView)
            </div>
        @endif

        <div class="overflow-hidden rounded-[1.35rem] bg-zinc-100/80 dark:bg-zinc-950">
            <div class="flex">
                <div class="w-full overflow-x-auto">
                    <div class="inline-block min-w-full overflow-hidden space-y-px">

                        <div class="flex w-full flex-row gap-px">
                        @foreach($monthGrid->first() as $day)
                            @include($dayOfWeekView, ['day' => $day])
                        @endforeach
                        </div>

                        @foreach($monthGrid as $week)
                            <div class="flex w-full flex-row gap-px">
                                @foreach($week as $day)
                                    @include($dayView, [
                                            'componentId' => $componentId,
                                            'day' => $day,
                                            'dayInMonth' => $day->isSameMonth($startsAt),
                                            'isToday' => $day->isToday(),
                                            'events' => $getEventsForDay($day, $events),
                                        ])
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if($afterCalendarView)
            <div class="p-2 pt-4">
                @include($afterCalendarView)
            </div>
        @endif
    </flux:card>
</div>
