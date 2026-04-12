<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible
        class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 w-75 data-flux-sidebar-collapsed-desktop:w-17 z-10 flex h-screen flex-col overflow-hidden">
        <flux:sidebar.header>
            <flux:sidebar.brand href="{{ route('dashboard') }}"
                logo="{{ asset('storage/images/sites/FAVICON_default.png') }}"
                logo:dark="{{ asset('storage/images/sites/FAVICON_default.png') }}" name="Đoàn TNTT GX Mỹ Vân"
                wire:navigate alt="Đoàn TNTT GX Mỹ Vân" />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2 hidden lg:flex" />
        </flux:sidebar.header>



        <div class="hidden in-data-flux-sidebar-collapsed-desktop:block">
            <flux:dropdown x-data align="end">
                <flux:button variant="subtle" square class="group" aria-label="Preferred color scheme">
                    <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini"
                        class="text-zinc-500 dark:text-white" />
                    <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini"
                        class="text-zinc-500 dark:text-white" />
                    <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" />
                    <flux:icon.sun x-show="$flux.appearance === 'system' && ! $flux.dark" variant="mini" />
                </flux:button>

                <flux:menu>
                    <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Light</flux:menu.item>
                    <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Dark</flux:menu.item>
                    <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">System
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>

        <div class="in-data-flux-sidebar-collapsed-desktop:hidden">
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun" />
                <flux:radio value="dark" icon="moon" />
                <flux:radio value="system" icon="computer-desktop" />
            </flux:radio.group>
        </div>

        <flux:sidebar.nav>
            <flux:separator :text="__('General')" class="my-4" />
            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>

            @can('general.score.view')
                <flux:sidebar.item icon="academic-cap" :href="route('admin.general.dashboard-score')"
                    :current="request()->routeIs('admin.general.dashboard-score')" wire:navigate>
                    {{ __('My score') }}
                </flux:sidebar.item>
            @endcan

            @canany(['management.schedule.view', 'management.classroom.view', 'management.subject.view'])
                <flux:separator :text="__('Management')" class="my-4" />

                @can('management.schedule.view')
                    <flux:sidebar.item icon="calendar" :href="route('admin.management.schedule.index')"
                        :current="request()->routeIs('admin.management.schedule.index')" wire:navigate>
                        {{ __('Schedule Management') }}
                    </flux:sidebar.item>
                @endcan

                @can('management.classroom.view')
                    <flux:sidebar.item icon="academic-cap" :href="route('admin.management.classroom.index')"
                        :current="request()->routeIs('admin.management.classroom.index')" wire:navigate>
                        {{ __('Classroom Management') }}
                    </flux:sidebar.item>
                @endcan

                @can('management.subject.view')
                    <flux:sidebar.item icon="book-open" :href="route('admin.management.subject.index')"
                        :current="request()->routeIs('admin.management.subject.index')" wire:navigate>
                        {{ __('Subject Management') }}
                    </flux:sidebar.item>
                @endcan
            @endcanany

            @can('attendance.view')
                <flux:separator :text="__('Attendance')" class="my-4" />
                <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.attendance.index')"
                    :current="request()->routeIs('admin.attendance.index')" wire:navigate>
                    {{ __('Attendance & Scoring') }}
                </flux:sidebar.item>
            @endcan

            @canany(['personnel.teacher.view', 'personnel.youth.view'])
                <flux:separator :text="__('Personnel')" class="my-4" />

                @can('personnel.teacher.view')
                    <flux:sidebar.item icon="book-open" :href="route('admin.personnel.teacher.index')"
                        :current="request()->routeIs('admin.personnel.teacher.index')" wire:navigate>
                        {{ __('Teacher Management') }}
                    </flux:sidebar.item>
                @endcan

                @can('personnel.youth.view')
                    <flux:sidebar.item icon="user-group" :href="route('admin.personnel.youth.index')"
                        :current="request()->routeIs('admin.personnel.youth.index')" wire:navigate>
                        {{ __('Youth Management') }}
                    </flux:sidebar.item>
                @endcan
            @endcanany

        </flux:sidebar.nav>

        <flux:spacer />


        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
    @livewireCalendarScripts
</body>

</html>
