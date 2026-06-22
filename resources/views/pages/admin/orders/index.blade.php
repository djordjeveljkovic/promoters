<x-layouts.app :title="__('admin_orders.page_title')">

    <x-ds.page-header
        :title="$festival?->displayName() ? $festival->displayName() . ' — ' . __('admin_orders.main_heading') : __('admin_orders.main_heading')"
        :subtitle="$festival?->location"
    >
        <x-slot:actions>
            <x-ds.button variant="primary" :href="route('promoter.orders.create', $festival)" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('admin_orders.create_order_button') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    {{-- Filter bar --}}
    <x-ds.card :padded="false" class="mb-4">
        <x-slot:body>
            <form method="GET" action="{{ route('admin.orders.index', $festival) }}" class="ds-toolbar !border-b-0 !bg-transparent">
                <select name="status_filter" onchange="this.form.submit()" class="ds-select" style="min-width: 160px;">
                    <option value="">{{ __('admin_orders.filters.all_job_statuses_option') }}</option>
                    @foreach($jobStatusColors as $statusKey => $details)
                        @if(!in_array($statusKey, ['N/A', 'failed_clickable']))
                            <option value="{{ $statusKey }}" @selected(request('status_filter') == $statusKey)>
                                @php
                                    $label = __('admin_orders.statuses.' . $statusKey, [], app()->getLocale());
                                    if ($label === 'admin_orders.statuses.' . $statusKey) $label = \Illuminate\Support\Str::ucfirst($statusKey);
                                @endphp
                                {{ $label }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <div class="ds-search">
                    <svg class="ds-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" class="ds-input" placeholder="{{ __('admin_orders.filters.search_placeholder') }}">
                </div>
                <x-ds.button variant="primary" size="sm" type="submit">{{ __('admin_orders.filters.search_button') }}</x-ds.button>
                @if (request('search') || request('status_filter'))
                    <x-ds.button variant="ghost" size="sm" :href="route('admin.orders.index', $festival)" wire:navigate>{{ __('admin_orders.filters.clear_button') }}</x-ds.button>
                @endif
            </form>
        </x-slot:body>
    </x-ds.card>

    {{-- Table --}}
    <x-ds.table>
        <x-slot:head>
            <tr>
                <th>{{ __('admin_orders.table.header_id') }}</th>
                <th>{{ __('admin_orders.table.header_customer') }}</th>
                <th class="hidden md:table-cell">{{ __('admin_orders.table.header_promoter') }}</th>
                <th class="hidden lg:table-cell">{{ __('admin_orders.table.header_date') }}</th>
                <th>{{ __('admin_orders.table.header_items') }}</th>
                <th class="text-right">{{ __('admin_orders.table.header_total') }}</th>
                <th class="text-right hidden sm:table-cell">{{ __('admin_orders.table.header_paid') }}</th>
                <th class="text-right hidden md:table-cell">{{ __('admin_orders.table.header_commission') }}</th>
                <th>{{ __('admin_orders.table.header_job_status') }}</th>
                <th class="text-right">{{ __('admin_orders.table.header_actions') }}</th>
            </tr>
        </x-slot:head>
        @forelse ($orders as $order)
            @php
                $statusKey = $order->job_status ?? 'unknown';
                $statusText = __('admin_orders.statuses.' . $statusKey, [], app()->getLocale());
                if ($statusText === 'admin_orders.statuses.' . $statusKey) {
                    $statusText = \Illuminate\Support\Str::ucfirst($statusKey);
                }
                $hasFailureReason = $statusKey === 'failed' && !empty($order->job_failure_reason);
                $statusVariant = match ($statusKey) {
                    'completed' => 'success', 'sent' => 'accent', 'failed' => 'danger',
                    'processing' => 'info', 'pending' => 'warning', default => 'neutral',
                };
            @endphp
            <tr>
                <td>
                    <div class="row-title">#{{ $order->order_number }}</div>
                </td>
                <td>
                    <div class="text-sm truncate" style="max-width: 200px;" title="{{ $order->email }}">{{ $order->email }}</div>
                </td>
                <td class="hidden md:table-cell text-sm text-[color:var(--ds-text-muted)]">
                    {{ $order->requestedBy->name ?? __('admin_orders.table.promoter_not_available') }}
                </td>
                <td class="hidden lg:table-cell text-sm text-[color:var(--ds-text-muted)] num">{{ $order->created_at->format('Y-m-d') }}</td>
                <td class="text-sm">
                    @foreach($order->items as $item)
                        <div class="truncate" style="max-width: 220px;">{{ $item->quantity }}× {{ $item->ticketType->name }}</div>
                    @endforeach
                </td>
                <td class="text-right num font-medium">{{ number_format($order->total, 2) }} <span class="text-[color:var(--ds-text-subtle)] text-xs">RSD</span></td>
                <td class="text-right num hidden sm:table-cell">{{ number_format($order->paid, 2) }} <span class="text-[color:var(--ds-text-subtle)] text-xs">RSD</span></td>
                <td class="text-right num hidden md:table-cell">
                    @if (in_array($order->job_status, ['completed', 'sent']) && isset($order->total_commission_earned))
                        {{ number_format($order->total_commission_earned, 2) }} <span class="text-[color:var(--ds-text-subtle)] text-xs">RSD</span>
                    @else
                        <span class="text-[color:var(--ds-text-subtle)] text-xs">—</span>
                    @endif
                </td>
                <td>
                    <x-ds.badge :variant="$statusVariant" :dot="!$hasFailureReason">
                        {{ $statusText }}
                    </x-ds.badge>
                    @if ($hasFailureReason)
                        <div class="text-[10px] text-rose-600 mt-0.5 max-w-[140px] truncate" title="{{ $order->job_failure_reason }}">
                            {{ $order->job_failure_reason }}
                        </div>
                    @endif
                </td>
                <td>
                    <div class="row-actions">
                        <x-ds.button variant="ghost" size="sm" :href="route('admin.orders.show', ['festival' => $festival->slug, 'order' => $order->id])" wire:navigate>
                            {{ __('admin_orders.table.action_view') }}
                        </x-ds.button>
                        @if (in_array($order->job_status, ['failed', 'pending', 'processing', 'blocked']))
                            <form action="{{ route('admin.orders.rerun-image-generation', ['festival' => $festival->slug, 'order' => $order->id]) }}" method="POST">
                                @csrf
                                <x-ds.button variant="ghost" size="sm" type="submit" title="{{ __('admin_orders.table.action_generate_images') }}">
                                    {{ __('admin_orders.table.action_generate_images') }}
                                </x-ds.button>
                            </form>
                        @endif
                        @if (in_array($order->job_status, ['completed', 'sent', 'failed']))
                            <form action="{{ route('admin.orders.rerun-email-sending', ['festival' => $festival->slug, 'order' => $order->id]) }}" method="POST">
                                @csrf
                                <x-ds.button variant="ghost" size="sm" type="submit" title="{{ __('admin_orders.table.action_resend_email') }}">
                                    {{ __('admin_orders.table.action_resend_email') }}
                                </x-ds.button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10">
                    <x-ds.empty-state
                        :title="__('admin_orders.table.no_orders_header')"
                        :message="__('admin_orders.table.no_orders_message')"
                    >
                        <x-ds.button variant="primary" :href="route('promoter.orders.create', $festival)" wire:navigate>
                            {{ __('admin_orders.create_order_button') }}
                        </x-ds.button>
                    </x-ds.empty-state>
                </td>
            </tr>
        @endforelse
    </x-ds.table>

    @if ($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</x-layouts.app>
