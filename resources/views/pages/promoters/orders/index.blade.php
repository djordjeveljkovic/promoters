<x-layouts.app :title="__('orders.page_title')">

    <x-ds.page-header
        :title="$festival?->displayName() ? $festival->displayName() . ' — ' . __('orders.main_heading') : __('orders.main_heading')"
        :subtitle="$festival?->location"
    >
        <x-slot:actions>
            @if ($lastOrder ?? null)
                {{-- P-046: duplicate the most recent order --}}
                <x-ds.button variant="secondary" :href="route('promoter.orders.create', $festival) . '?from=' . $lastOrder->id" wire:navigate title="{{ __('Duplicate last order') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    {{ __('Duplicate last') }}
                </x-ds.button>
            @endif
            {{-- P-047: bulk resend --}}
            <form method="POST" action="{{ route('promoter.orders.resend-last', $festival) }}" class="inline">
                @csrf
                <input type="hidden" name="count" value="5">
                <x-ds.button variant="secondary" type="submit" title="{{ __('Resend the last 5 order emails') }}" onclick="return confirm('{{ __('Re-send the last 5 order emails? This will dispatch new emails to those customers.') }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><polyline points="22 6 12 13 2 6"/></svg>
                    {{ __('Resend last 5') }}
                </x-ds.button>
            </form>
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
