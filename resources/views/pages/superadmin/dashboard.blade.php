<x-layouts.app :title="__('Superadmin — Global overview')">

    <x-ds.page-header
        :title="__('Superadmin Dashboard')"
        :subtitle="__('Global view across every festival on the platform.')"
    >
        <x-slot:actions>
            <x-ds.button variant="secondary" :href="route('superadmin.users.index')" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                {{ __('Manage users') }}
            </x-ds.button>
            <x-ds.button variant="primary" :href="route('superadmin.festivals.create')" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('New festival') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-ds.stat
            :label="__('Festivals')"
            :value="number_format($totalFestivals)"
            :hint="__('dashboard.stat_active_festivals', ['count' => $activeFestivals])"
        />
        <x-ds.stat
            :label="__('Users')"
            :value="number_format($totalUsers)"
            :hint="__('dashboard.stat_users_breakdown', ['admins' => $totalAdmins, 'promoters' => $totalPromoters])"
        />
        <x-ds.stat
            :label="__('Orders')"
            :value="number_format($totalOrders)"
            :hint="__('dashboard.stat_orders_completed', ['count' => $completedOrders])"
        />
        <x-ds.stat
            :label="__('Revenue')"
            :value="number_format($totalRevenue, 0) . ' RSD'"
            :hint="__('dashboard.stat_revenue_hint')"
        />
    </div>

    {{-- Two-column: top revenue + recent festivals --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        <x-ds.card :title="__('Top festivals by revenue')">
            @if ($perFestivalRevenue->isEmpty())
                <x-ds.empty-state
                    :title="__('No revenue yet')"
                    :message="__('Create a festival and assign an admin to get started.')"
                />
            @else
                <ul class="divide-y divide-[color:var(--ds-divider)]">
                    @foreach ($perFestivalRevenue as $f)
                        <li class="flex items-center justify-between py-2.5 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $f->primary_color ?? '#cbd5e1' }}"></span>
                                <div class="min-w-0">
                                    <div class="font-medium text-sm text-[color:var(--ds-text)] truncate">{{ $f->name }} {{ $f->year }}</div>
                                    <x-ds.badge :variant="match($f->status) { 'active' => 'success', 'draft' => 'warning', default => 'neutral' }" size="sm" class="mt-0.5">
                                        {{ __($f->status) }}
                                    </x-ds.badge>
                                </div>
                            </div>
                            <span class="num text-sm font-semibold text-[color:var(--ds-text)]">
                                {{ number_format($f->completed_revenue ?? 0, 0) }} RSD
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ds.card>

        <x-ds.card :title="__('Recent festivals')">
            @if ($recentFestivals->isEmpty())
                <x-ds.empty-state
                    :title="__('No festivals yet')"
                    :message="__('Click "New festival" to create the first one.')"
                />
            @else
                <ul class="divide-y divide-[color:var(--ds-divider)]">
                    @foreach ($recentFestivals as $f)
                        <li class="flex items-center justify-between py-2.5 first:pt-0 last:pb-0">
                            <a href="{{ route('superadmin.festivals.edit', $f) }}" wire:navigate class="text-sm font-medium text-[color:var(--ds-text)] hover:text-indigo-600 truncate">
                                {{ $f->displayName() }}
                            </a>
                            <span class="text-xs text-[color:var(--ds-text-muted)] flex-shrink-0 ml-3">{{ $f->created_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ds.card>
    </div>
</x-layouts.app>
