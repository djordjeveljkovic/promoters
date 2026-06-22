<x-layouts.app :title="__('dashboard.page_title')">

    <x-ds.page-header
        :title="$festival?->displayName() ?? __('dashboard.main_heading')"
        :subtitle="$festival ? $festival->location . ' · ' . __(ucfirst($festival->status)) : null"
    >
        @if ($festival)
            <x-slot:actions>
                <x-ds.button variant="primary" :href="route('promoter.orders.create', $festival)" wire:navigate>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    {{ __('New order') }}
                </x-ds.button>
            </x-slot:actions>
        @endif
    </x-ds.page-header>

    {{-- P-044: quick-action panel — what the admin does 90% of the time --}}
    @if ($festival)
        <x-ds.card class="mb-5">
            <x-slot:body>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a href="{{ route('promoter.orders.create', $festival) }}" wire:navigate class="ds-btn ds-btn-primary justify-center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        {{ __('New order') }}
                    </a>
                    <a href="{{ route('admin.ticket-types.create', $festival) }}" wire:navigate class="ds-btn ds-btn-secondary justify-center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/></svg>
                        {{ __('New ticket type') }}
                    </a>
                    <a href="{{ route('admin.promoters.create', $festival) }}" wire:navigate class="ds-btn ds-btn-secondary justify-center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        {{ __('Invite promoter') }}
                    </a>
                    <a href="{{ route('admin.scan.index', $festival) }}" wire:navigate class="ds-btn ds-btn-secondary justify-center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        {{ __('Scan tickets') }}
                    </a>
                </div>
            </x-slot:body>
        </x-ds.card>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-ds.stat
            :label="__('Total revenue (all time)')"
            :value="number_format($totalRevenueAllTime, 0) . ' RSD'"
            :hint="__('All completed orders')"
        />
        <x-ds.stat
            :label="__('Total orders')"
            :value="number_format($totalOrdersAllTime)"
            :hint="number_format($totalOrdersLast30Days) . ' ' . __('last 30d')"
        />
        <x-ds.stat
            :label="__('Tickets sold')"
            :value="number_format($totalTicketsEffectivelySoldAllTime)"
            :hint="number_format($totalTicketsSoldLast30Days) . ' ' . __('last 30d')"
        />
        <x-ds.stat
            :label="__('Revenue (last 30d)')"
            :value="number_format($totalRevenueLast30Days, 0) . ' RSD'"
        />
    </div>

    {{-- Two-column section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">

        {{-- Top ticket types (2/3 width) --}}
        <x-ds.card :title="__('Top ticket types')" class="lg:col-span-2">
            @if($ticketTypePerformance->isEmpty())
                <x-ds.empty-state
                    :title="__('No ticket sales yet')"
                    :message="__('Once orders are created they will appear here.')"
                />
            @else
                <x-ds.table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Type') }}</th>
                            <th class="text-right">{{ __('Sold') }}</th>
                            <th class="text-right">{{ __('Revenue') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach($ticketTypePerformance as $type)
                        <tr>
                            <td class="row-title">{{ $type->name }}</td>
                            <td class="text-right num">{{ number_format($type->total_quantity_sold) }}</td>
                            <td class="text-right num">{{ number_format($type->total_revenue, 0) }} RSD</td>
                        </tr>
                    @endforeach
                </x-ds.table>
            @endif
        </x-ds.card>

        {{-- Roles + status (1/3 width) --}}
        <div class="space-y-4">
            <x-ds.card :title="__('Users')">
                <ul class="space-y-2.5">
                    @foreach($userCountsByRole as $role => $count)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-[color:var(--ds-text-muted)]">{{ __(ucfirst($role)) }}{{ __('dashboard.user_ticket_stats.role_count_suffix') }}</span>
                            <span class="font-semibold text-[color:var(--ds-text)] num">{{ $count }}</span>
                        </li>
                    @endforeach
                    <li class="border-t border-[color:var(--ds-divider)] pt-2.5 flex items-center justify-between text-sm">
                        <span class="text-[color:var(--ds-text-muted)]">{{ __('Active tickets') }}</span>
                        <span class="font-semibold text-emerald-600 num">{{ $activeTicketsCount }}</span>
                    </li>
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-[color:var(--ds-text-muted)]">{{ __('Inactive tickets') }}</span>
                        <span class="font-semibold text-rose-600 num">{{ $inactiveTicketsCount }}</span>
                    </li>
                </ul>
            </x-ds.card>

            <x-ds.card :title="__('Order statuses')">
                <ul class="space-y-2">
                    @foreach($orderStatusCounts as $status => $count)
                        <li class="flex items-center justify-between text-sm">
                            <x-ds.badge :variant="match($status) { 'completed' => 'success', 'sent' => 'accent', 'failed' => 'danger', 'processing' => 'info', 'pending' => 'warning', default => 'neutral' }" dot>
                                {{ __(ucfirst($status)) }}
                            </x-ds.badge>
                            <span class="font-semibold text-[color:var(--ds-text)] num">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ds.card>
        </div>
    </div>

    {{-- Top promoter --}}
    @php $topPromoters = $promoterPerformance->where('total_orders_generated', '>', 0)->take(5); @endphp
    <x-ds.card :title="__('Top promoter performance')" class="mt-6">
        <x-slot:actions>
            <x-ds.button variant="ghost" size="sm" :href="route('admin.promoters.leaderboard', $festival)" wire:navigate>
                {{ __('View all') }} →
            </x-ds.button>
        </x-slot:actions>
        @if($topPromoters->isEmpty())
            <x-ds.empty-state
                :title="__('No promoter activity yet')"
                :message="__('Top performers will appear here once promoters start selling tickets.')"
            />
        @else
            <x-ds.table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Promoter') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th class="text-right">{{ __('Orders') }}</th>
                        <th class="text-right">{{ __('Revenue') }}</th>
                    </tr>
                </x-slot:head>
                @foreach($topPromoters as $promoter)
                    <tr>
                        <td class="row-title">{{ $promoter->name }}</td>
                        <td class="row-meta">{{ $promoter->email }}</td>
                        <td class="text-right num">{{ number_format($promoter->total_orders_generated) }}</td>
                        <td class="text-right num">{{ number_format($promoter->total_revenue_generated, 0) }} RSD</td>
                    </tr>
                @endforeach
            </x-ds.table>
        @endif
    </x-ds.card>

    {{-- Recent orders --}}
    <x-ds.card :title="__('Recent orders')" class="mt-6">
        @if($recentOrders->isEmpty())
            <x-ds.empty-state
                :title="__('No recent orders')"
                :message="__('Orders will appear here as soon as promoters create them.')"
            />
        @else
            <x-ds.table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Order') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="hidden md:table-cell">{{ __('Promoter') }}</th>
                        <th class="hidden lg:table-cell">{{ __('Date') }}</th>
                        <th>{{ __('Items') }}</th>
                        <th class="text-right">{{ __('Total') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </x-slot:head>
                @foreach($recentOrders as $order)
                    <tr>
                        <td class="row-title">#{{ $order->order_number ?? $order->id }}</td>
                        <td>
                            <div class="text-sm text-[color:var(--ds-text)] truncate" style="max-width: 180px;">{{ $order->email }}</div>
                        </td>
                        <td class="hidden md:table-cell text-sm text-[color:var(--ds-text-muted)]">{{ $order->requestedBy->name ?? '—' }}</td>
                        <td class="hidden lg:table-cell text-sm text-[color:var(--ds-text-muted)] num">{{ $order->created_at->format('Y-m-d') }}</td>
                        <td class="text-sm">
                            @foreach($order->items as $item)
                                <div>{{ $item->quantity }}× {{ $item->ticketType->name }}</div>
                            @endforeach
                        </td>
                        <td class="text-right num">{{ number_format($order->total, 0) }} RSD</td>
                        <td>
                            <x-ds.badge :variant="match($order->job_status) { 'completed' => 'success', 'sent' => 'accent', 'failed' => 'danger', 'processing' => 'info', 'pending' => 'warning', default => 'neutral' }" dot>
                                {{ __(ucfirst($order->job_status)) }}
                            </x-ds.badge>
                        </td>
                    </tr>
                @endforeach
            </x-ds.table>
        @endif
    </x-ds.card>
</x-layouts.app>
