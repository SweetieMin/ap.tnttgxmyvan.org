<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 ">
            <livewire:admin.dashboard.dashboard-schedule :day-click-enabled="false" :drag-and-drop-enabled="false" />
        </div>

    </div>
</x-layouts::app>
