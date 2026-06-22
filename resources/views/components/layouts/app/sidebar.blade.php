<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="ds-app">

@php
    /** @var \App\Models\User $u */
    $u = auth()->user();
    $currentFestival = request()->route('festival');
    if (is_numeric($currentFestival)) {
        $currentFestival = \App\Models\Festival::find((int) $currentFestival);
    } elseif (is_string($currentFestival) && !($currentFestival instanceof \App\Models\Festival)) {
        $currentFestival = \App\Models\Festival::where('slug', $currentFestival)->first();
    }

    $homeRoute = match (true) {
        $u?->isSuperAdmin() => 'superadmin.dashboard',
        $u?->isAdmin()      => 'admin.festivals.index',
        $u?->isPromoter()   => 'promoter.festivals.index',
        default             => 'login',
    };

    $festivalParam = $currentFestival ? ['festival' => $currentFestival->slug] : [];
@endphp

<div class="min-h-screen flex">

    {{-- ===================== Sidebar ===================== --}}
    <aside class="hidden lg:flex w-64 flex-col border-r border-[color:var(--ds-border)] bg-[color:var(--ds-sidebar)] sticky top-0 h-screen">

        {{-- Brand --}}
        <a href="{{ route($homeRoute) }}" wire:navigate class="flex items-center gap-2 px-5 h-16 border-b border-[color:var(--ds-divider)]">
            <div class="w-8 h-8 rounded-md bg-indigo-600 text-white flex items-center justify-center font-semibold">P</div>
            <div class="leading-tight">
                <div class="text-sm font-semibold text-[color:var(--ds-text)]">{{ config('app.name', 'Promoteri') }}</div>
                <div class="text-[11px] text-[color:var(--ds-text-muted)] uppercase tracking-wider">Festival OS</div>
            </div>
        </a>

        {{-- Festival selector --}}
        @if ($currentFestival || $u?->isSuperAdmin() || $u?->accessibleFestivals()?->count())
            <div class="px-3 pt-3">
                <x-festival.selector :current="$currentFestival" />
            </div>
        @endif

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">

            @if ($u?->isSuperAdmin())
                <div>
                    <div class="px-2 mb-2 text-[10px] font-semibold tracking-wider text-[color:var(--ds-text-muted)] uppercase">
                        {{ __('navigation.sidebar.group_platform') }}
                    </div>
                    <ul class="space-y-0.5">
                        <x-ds.nav-item :href="route('superadmin.dashboard')" :active="request()->routeIs('superadmin.dashboard')" icon="home">{{ __('Superadmin') }}</x-ds.nav-item>
                        <x-ds.nav-item :href="route('superadmin.festivals.index')" :active="request()->routeIs('superadmin.festivals.*')" icon="calendar">{{ __('Festivals') }}</x-ds.nav-item>
                        <x-ds.nav-item :href="route('superadmin.users.index')" :active="request()->routeIs('superadmin.users.*')" icon="users">{{ __('Users') }}</x-ds.nav-item>
                        <x-ds.nav-item :href="route('superadmin.mail-templates.index')" :active="request()->routeIs('superadmin.mail-templates.*')" icon="envelope">{{ __('Mail templates') }}</x-ds.nav-item>
                    </ul>
                </div>
            @endif

            @if ($u?->isAdmin())
                <div>
                    <div class="px-2 mb-2 text-[10px] font-semibold tracking-wider text-[color:var(--ds-text-muted)] uppercase">
                        {{ $currentFestival?->displayName() ?? __('Pick a festival') }}
                    </div>
                    <ul class="space-y-0.5">
                        @if ($currentFestival)
                            <x-ds.nav-item :href="route('admin.dashboard', $festivalParam)" :active="request()->routeIs('admin.dashboard')" icon="home">{{ __('Dashboard') }}</x-ds.nav-item>
                            <x-ds.nav-item :href="route('admin.ticket-types.index', $festivalParam)" :active="request()->routeIs('admin.ticket-types.*')" icon="ticket">{{ __('Ticket types') }}</x-ds.nav-item>
                            <x-ds.nav-item :href="route('admin.promoters.index', $festivalParam)" :active="request()->routeIs('admin.promoters.*')" icon="user">{{ __('Promoters') }}</x-ds.nav-item>
                            <x-ds.nav-item :href="route('admin.orders.index', $festivalParam)" :active="request()->routeIs('admin.orders.*')" icon="shopping-bag">{{ __('Orders') }}</x-ds.nav-item>
                            <x-ds.nav-item :href="route('admin.mail-templates.index', $festivalParam)" :active="request()->routeIs('admin.mail-templates.*')" icon="envelope">{{ __('Mail templates') }}</x-ds.nav-item>
                        @else
                            <x-ds.nav-item :href="route('admin.festivals.index')" :active="request()->routeIs('admin.festivals.*')" icon="calendar">{{ __('All festivals') }}</x-ds.nav-item>
                        @endif
                    </ul>
                </div>
            @endif

            @if ($u?->isPromoter() && !$u->isAdmin())
                <div>
                    <div class="px-2 mb-2 text-[10px] font-semibold tracking-wider text-[color:var(--ds-text-muted)] uppercase">
                        {{ $currentFestival?->displayName() ?? __('Pick a festival') }}
                    </div>
                    <ul class="space-y-0.5">
                        @if ($currentFestival)
                            <x-ds.nav-item :href="route('promoter.dashboard', $festivalParam)" :active="request()->routeIs('promoter.dashboard')" icon="home">{{ __('Dashboard') }}</x-ds.nav-item>
                            <x-ds.nav-item :href="route('promoter.orders.create', $festivalParam)" :active="request()->routeIs('promoter.orders.create')" icon="plus-circle">{{ __('New order') }}</x-ds.nav-item>
                            <x-ds.nav-item :href="route('promoter.orders.index', $festivalParam)" :active="request()->routeIs('promoter.orders.*')" icon="shopping-bag">{{ __('My orders') }}</x-ds.nav-item>
                            <x-ds.nav-item :href="route('promoter.help', $festivalParam)" :active="request()->routeIs('promoter.help')" icon="question-mark-circle">{{ __('Help') }}</x-ds.nav-item>
                        @else
                            <x-ds.nav-item :href="route('promoter.festivals.index')" :active="request()->routeIs('promoter.festivals.*')" icon="calendar">{{ __('All festivals') }}</x-ds.nav-item>
                        @endif
                    </ul>
                </div>
            @endif
        </nav>

        {{-- User menu --}}
        @auth
            <div class="border-t border-[color:var(--ds-divider)] p-3">
                <details class="group" x-data>
                    <summary class="list-none cursor-pointer flex items-center gap-3 px-2 py-2 rounded-md hover:bg-[color:var(--ds-bg-subtle)]">
                        <x-ds.avatar :name="$u->name" />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate text-[color:var(--ds-text)]">{{ $u->name }}</div>
                            <div class="text-xs text-[color:var(--ds-text-muted)] truncate">{{ $u->email }}</div>
                        </div>
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" class="text-[color:var(--ds-text-muted)]"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                    </summary>
                    <div class="mt-2 ds-card p-1.5">
                        <x-ds.badge variant="accent" size="sm" class="w-full justify-center mb-1">{{ __(ucfirst($u->role)) }}</x-ds.badge>
                        <a href="{{ route('settings.profile') }}" wire:navigate class="flex items-center gap-2 px-2 py-1.5 rounded text-sm hover:bg-[color:var(--ds-bg-subtle)] text-[color:var(--ds-text)]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            {{ __('navigation.usermenu.settings') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-2 py-1.5 rounded text-sm hover:bg-[color:var(--ds-bg-subtle)] text-[color:var(--ds-text)]">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                {{ __('navigation.usermenu.logout') }}
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        @endauth
    </aside>

    {{-- ===================== Main column ===================== --}}
    <div class="flex-1 min-w-0 flex flex-col">

        {{-- Mobile top bar --}}
        <header class="lg:hidden h-14 px-4 flex items-center gap-3 border-b border-[color:var(--ds-border)] bg-[color:var(--ds-topbar)] sticky top-0 z-20">
            <button type="button" onclick="document.getElementById('mobileNav')?.classList.toggle('hidden')" class="ds-btn ds-btn-ghost ds-btn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="font-semibold text-sm">{{ config('app.name') }}</div>
            <div class="ml-auto">
                <x-ds.avatar :name="auth()->user()?->name ?? 'Guest'" neutral />
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 min-w-0">
            <x-flash-messages />
            {{ $slot ?? '' }}
        </main>
    </div>
</div>
</body>
</html>
