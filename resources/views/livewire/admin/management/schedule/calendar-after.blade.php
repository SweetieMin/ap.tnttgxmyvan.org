<flux:card class="rounded-2xl border border-zinc-200/80 bg-white/95 p-4 dark:border-zinc-700/80 dark:bg-zinc-900/95">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="sm">{{ __('Chú thích loại lịch') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Màu trên lịch thể hiện từng loại lịch khác nhau.') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1.5 dark:border-cyan-900/60 dark:bg-cyan-950/40">
                <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                <flux:text class="text-sm font-medium text-cyan-700 dark:text-cyan-300">
                    {{ __('Lịch học') }}
                </flux:text>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 dark:border-red-900/60 dark:bg-red-950/40">
                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                <flux:text class="text-sm font-medium text-red-700 dark:text-red-300">
                    {{ __('Lịch thi') }}
                </flux:text>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-orange-50 px-3 py-1.5 dark:border-orange-900/60 dark:bg-orange-950/40">
                <span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span>
                <flux:text class="text-sm font-medium text-orange-700 dark:text-orange-300">
                    {{ __('Dặn dò') }}
                </flux:text>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full border border-yellow-200 bg-yellow-50 px-3 py-1.5 dark:border-yellow-900/60 dark:bg-yellow-950/40">
                <span class="h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                <flux:text class="text-sm font-medium text-yellow-700 dark:text-yellow-300">
                    {{ __('Lịch đi trại') }}
                </flux:text>
            </div>

        </div>
    </div>
</flux:card>

