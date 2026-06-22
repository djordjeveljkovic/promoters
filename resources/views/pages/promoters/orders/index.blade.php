<x-layouts.app :title="__('orders.page_title')">

    <x-ds.page-header
        :title="$festival?->displayName() ? $festival->displayName() . ' — ' . __('orders.main_heading') : __('orders.main_heading')"
        :subtitle="$festival?->location"
    >
        <x-slot:actions>
            <x-ds.button variant="primary" :href="route('promoter.orders.create', $festival)" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('orders.create_new_order_button') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.table>
        <x-slot:head>
            <tr>
                <th>{{ __('orders.table.header_order_id') }}</th>
                <th>{{ __('orders.table.header_customer_email') }}</th>
                <th class="hidden md:table-cell">{{ __('orders.table.header_order_date') }}</th>
                <th>{{ __('orders.table.header_items') }}</th>
                <th class="text-right">{{ __('orders.table.header_total_price') }}</th>
                <th class="text-right">{{ __('orders.table.header_commission_earned') }}</th>
                <th>{{ __('orders.table.header_job_status') }}</th>
                <th class="text-right">{{ __('orders.table.header_actions') }}</th>
            </tr>
        </x-slot:head>
        @forelse ($orders as $order)
            @php
                $statusVariant = match($order->job_status) {
                    'completed' => 'success', 'sent' => 'accent', 'failed' => 'danger',
                    'processing' => 'info', 'pending' => 'warning', default => 'neutral',
                };
            @endphp
            <tr wire:key="o-{{ $order->id }}">
                <td class="row-title">#{{ $order->order_number ?? $order->id }}</td>
                <td class="text-sm">{{ $order->email }}</td>
                <td class="hidden md:table-cell text-sm text-[color:var(--ds-text-muted)] num">{{ $order->created_at->format('M d, Y H:i') }}</td>
                <td class="text-sm">
                    @foreach($order->items as $item)
                        <div class="truncate" style="max-width: 200px;">{{ $item->quantity }}× {{ $item->ticketType->name }}</div>
                    @endforeach
                </td>
                <td class="text-right num font-medium">{{ number_format($order->total, 2) }} RSD</td>
                <td class="text-right num">
                    @if (in_array($order->job_status, ['completed', 'sent']) && isset($order->total_commission_earned))
                        {{ number_format($order->total_commission_earned, 2) }} RSD
                    @else
                        <span class="text-[color:var(--ds-text-subtle)] text-xs">{{ __('orders.table.commission_not_calculated') }}</span>
                    @endif
                </td>
                <td>
                    <x-ds.badge :variant="$statusVariant" dot>
                        {{ __(ucfirst($order->job_status ?? 'unknown')) }}
                    </x-ds.badge>
                </td>
                <td>
                    <div class="row-actions">
                        @if ($order->job_status === 'failed')
                            <form action="{{ route('orders.rerunImageJob', $order->id) }}" method="POST">
                                @csrf
                                <x-ds.button variant="ghost" size="sm" type="submit">
                                    {{ __('orders.table.actions_retry_images_button') }}
                                </x-ds.button>
                            </form>
                        @endif
                        @if (in_array($order->job_status, ['completed', 'sent', 'failed']))
                            <form action="{{ route('orders.rerunEmailJob', $order->id) }}" method="POST">
                                @csrf
                                <x-ds.button variant="ghost" size="sm" type="submit">
                                    {{ __('orders.table.actions_resend_email_button') }}
                                </x-ds.button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">
                    <x-ds.empty-state
                        :title="__('No orders yet')"
                        :message="__('Click "Create new order" to start selling tickets.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-ds.table>

    @if ($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</x-layouts.app>
