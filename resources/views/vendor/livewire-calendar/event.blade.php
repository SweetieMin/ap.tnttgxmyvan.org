<div class="relative">
    <div
        @if($eventClickEnabled)
            wire:click.stop="onEventClick('{{ $event['id'] }}')"
            wire:loading.class="opacity-75 pointer-events-none scale-[0.99]"
            wire:target="onEventClick('{{ $event['id'] }}')"
        @endif
        class="relative transition duration-150 {{ $eventClickEnabled ? 'cursor-pointer' : '' }}"
    >
        <div class="rounded-xl border p-1.5 shadow-xs shadow-zinc-200/30 transition-all dark:shadow-none lg:rounded-2xl lg:p-2.5 {{ $event['border_class'] ?? 'border-zinc-200/70 dark:border-zinc-700/70' }} {{ $event['background_class'] ?? 'bg-white/95 dark:bg-zinc-900/95' }} {{ $event['hover_class'] ?? 'hover:border-accent hover:shadow-sm hover:shadow-cyan-100/40 dark:hover:bg-zinc-800/95' }}">
            <div class="flex items-start gap-1.5 lg:gap-2.5">
                <div class="relative shrink-0">
                    <span class="mt-0.5 block h-7 w-1.5 rounded-full lg:h-8 lg:w-1.5 {{ $event['dot_class'] ?? 'bg-accent' }}"></span>

                    <span
                        wire:loading
                        wire:target="onEventClick('{{ $event['id'] }}')"
                        class="absolute inset-0 block animate-pulse rounded-full bg-white/40 dark:bg-white/10"
                    ></span>
                </div>

                <div class="min-w-0 flex-1 space-y-0.5 lg:space-y-1">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <flux:text class="truncate text-[10px] leading-3.5 font-semibold text-zinc-900 dark:text-zinc-50 lg:hidden">
                                {{ $event['mobile_label'] ?? $event['title'] }}
                            </flux:text>

                            <div class="hidden lg:block">
                                <flux:text class="truncate text-[10px] font-semibold text-zinc-900 dark:text-zinc-50 lg:text-xs">
                                    {{ $event['title'] }}
                                </flux:text>
                            </div>
                        </div>

                        <div
                            wire:loading.flex
                            wire:target="onEventClick('{{ $event['id'] }}')"
                            class="mt-0.5 shrink-0 items-center"
                        >
                            <svg
                                class="h-3.5 w-3.5 animate-spin text-cyan-500"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="3"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M12 2a10 10 0 0 1 10 10h-3a7 7 0 0 0-7-7V2z"
                                ></path>
                            </svg>
                        </div>
                    </div>

                    @if(filled($event['description'] ?? null))
                        <flux:text class="truncate text-[9px] leading-3 text-zinc-500 dark:text-zinc-300 lg:hidden">
                            {{ $event['description'] }}
                        </flux:text>
                    @endif

                    <div class="hidden lg:block">
                        @if(filled($event['description'] ?? null))
                            <flux:text class="truncate text-[11px] leading-4 text-zinc-500 dark:text-zinc-300">
                                {{ $event['description'] }}
                            </flux:text>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>