<x-layouts.app :title="__('Sub-promoter dashboard')">

    <x-ds.page-header
        :title="__('Sub-promoter dashboard')"
        :subtitle="$festival?->displayName() ? __('Festival') . ' · ' . $festival->displayName() : null"
    />

    <x-ds.alert variant="info">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                {{-- P-001: explicitly call out the parent promoter --}}
                @if ($parent)
                    {{ __('You operate as a sub-promoter for :name on :festival. Orders you place will be attributed to your parent promoter.', [
                        'name'    => $parent->name,
                        'festival'=> $festival?->displayName() ?? '—',
                    ]) }}
                @else
                    {{ __('You operate as a sub-promoter. To place an order, open the promoter dashboard and use the "New order" button.') }}
                @endif
            </div>
            @if ($festival)
                <x-ds.button variant="primary" size="sm" :href="route('promoter.orders.create', ['festival' => $festival->slug])" wire:navigate>
                    + {{ __('New order') }}
                </x-ds.button>
            @endif
        </div>
    </x-ds.alert>

    <x-ds.card :title="__('Recent parent-promoter orders')" class="mt-6">
        @if (empty($recentOrders) || $recentOrders->isEmpty())
            <x-ds.empty-state
                :title="__('No orders yet')"
                :message="__('Orders placed by your parent promoter will appear here.')"
            />
        @else
            <x-ds.table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Order') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-right">{{ __('Date') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($recentOrders as $order)
                    <tr wire:key="sp-order-{{ $order->id }}">
                        <td class="row-title">#{{ $order->order_number ?? $order->id }}</td>
                        <td>{{ $order->email }}</td>
                        <td class="text-right num text-sm text-[color:var(--ds-text-muted)]">{{ \App\Support\Format::datetime($order->created_at) }}</td>
                        <td>
                            <x-ds.badge :variant="match($order->job_status) { 'completed' => 'success', 'sent' => 'accent', 'failed' => 'danger', default => 'neutral' }" dot>
                                {{ __(ucfirst($order->job_status)) }}
                            </x-ds.badge>
                        </td>
                    </tr>
                @endforeach
            </x-ds.table>
        @endif
    </x-ds.card>
</x-layouts.app>
