<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            /** @var \App\Models\User $u */
            $u = auth()->user();
            $currentFestival = request()->route('festival');
            if (is_numeric($currentFestival)) {
                $currentFestival = \App\Models\Festival::find((int) $currentFestival);
            } elseif (is_string($currentFestival) && !($currentFestival instanceof \App\Models\Festival)) {
                $currentFestival = \App\Models\Festival::where('slug', $currentFestival)->first();
            }

            // Resolve the home route for the logo based on role.
            $homeRoute = match (true) {
                $u?->isSuperAdmin() => 'superadmin.dashboard',
                $u?->isAdmin()      => 'admin.festivals.index',
                $u?->isPromoter()   => 'promoter.festivals.index',
                default             => 'login',
            };

            // If we have a current festival in scope, links go there.
            $festivalParam = $currentFestival ? ['festival' => $currentFestival->slug] : [];
        @endphp

        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route($homeRoute) }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            {{-- Festival selector (only when there's something to switch to) --}}
            <div class="px-3 mb-3">
                <x-festival.selector :current="$currentFestival" />
            </div>

            <flux:navlist variant="outline">
                {{-- ===== Superadmin navigation ===== --}}
                @if ($u?->isSuperAdmin())
                    <flux:navlist.group :heading="__('navigation.sidebar.group_platform')" class="grid">
                        <flux:navlist.item icon="home" :href="route('superadmin.dashboard')" :current="request()->routeIs('superadmin.dashboard')" wire:navigate>{{ __('Superadmin') }}</flux:navlist.item>
                        <flux:navlist.item icon="calendar" :href="route('superadmin.festivals.index')" :current="request()->routeIs('superadmin.festivals.*')" wire:navigate>{{ __('Festivals') }}</flux:navlist.item>
                        <flux:navlist.item icon="users" :href="route('superadmin.users.index')" :current="request()->routeIs('superadmin.users.*')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                        <flux:navlist.item icon="envelope" :href="route('superadmin.mail-templates.index')" :current="request()->routeIs('superadmin.mail-templates.*')" wire:navigate>{{ __('Mail templates') }}</flux:navlist.item>
                    </flux:navlist.group>
                @endif

                {{-- ===== Admin navigation (requires a festival) ===== --}}
                @if ($u?->isAdmin())
                    @if ($currentFestival)
                        <flux:navlist.group :heading="$currentFestival->displayName()" class="grid">
                            <flux:navlist.item icon="home" :href="route('admin.dashboard', $festivalParam)" :current="request()->routeIs('admin.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                            <flux:navlist.item icon="ticket" :href="route('admin.ticket-types.index', $festivalParam)" :current="request()->routeIs('admin.ticket-types.*')" wire:navigate>{{ __('Ticket types') }}</flux:navlist.item>
                            <flux:navlist.item icon="user" :href="route('admin.promoters.index', $festivalParam)" :current="request()->routeIs('admin.promoters.*')" wire:navigate>{{ __('Promoters') }}</flux:navlist.item>
                            <flux:navlist.item icon="shopping-bag" :href="route('admin.orders.index', $festivalParam)" :current="request()->routeIs('admin.orders.*')" wire:navigate>{{ __('Orders') }}</flux:navlist.item>
                            <flux:navlist.item icon="envelope" :href="route('admin.mail-templates.index', $festivalParam)" :current="request()->routeIs('admin.mail-templates.*')" wire:navigate>{{ __('Mail templates') }}</flux:navlist.item>
                        </flux:navlist.group>
                    @else
                        <flux:navlist.group :heading="__('Pick a festival')" class="grid">
                            <flux:navlist.item icon="calendar" :href="route('admin.festivals.index')" :current="request()->routeIs('admin.festivals.*')" wire:navigate>{{ __('All festivals') }}</flux:navlist.item>
                        </flux:navlist.group>
                    @endif
                @endif

                {{-- ===== Promoter navigation ===== --}}
                @if ($u?->isPromoter() && !$u->isAdmin())
                    @if ($currentFestival)
                        <flux:navlist.group :heading="$currentFestival->displayName()" class="grid">
                            <flux:navlist.item icon="home" :href="route('promoter.dashboard', $festivalParam)" :current="request()->routeIs('promoter.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                            <flux:navlist.item icon="plus-circle" :href="route('promoter.orders.create', $festivalParam)" :current="request()->routeIs('promoter.orders.create')" wire:navigate>{{ __('New order') }}</flux:navlist.item>
                            <flux:navlist.item icon="shopping-bag" :href="route('promoter.orders.index', $festivalParam)" :current="request()->routeIs('promoter.orders.*')" wire:navigate>{{ __('My orders') }}</flux:navlist.item>
                            <flux:navlist.item icon="question-mark-circle" :href="route('promoter.help', $festivalParam)" :current="request()->routeIs('promoter.help')" wire:navigate>{{ __('Help') }}</flux:navlist.item>
                        </flux:navlist.group>
                    @else
                        <flux:navlist.group :heading="__('Pick a festival')" class="grid">
                            <flux:navlist.item icon="calendar" :href="route('promoter.festivals.index')" :current="request()->routeIs('promoter.festivals.*')" wire:navigate>{{ __('All festivals') }}</flux:navlist.item>
                        </flux:navlist.group>
                    @endif
                @endif
            </flux:navlist>

            <flux:spacer />

            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />
                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    <span class="truncate text-[10px] text-zinc-500">{{ __(auth()->user()->role) }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('navigation.usermenu.settings') }}</flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('navigation.usermenu.logout') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />
                <flux:menu>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('navigation.usermenu.settings') }}</flux:menu.item>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('navigation.usermenu.logout') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot ?? '' }}
    </body>
</html>