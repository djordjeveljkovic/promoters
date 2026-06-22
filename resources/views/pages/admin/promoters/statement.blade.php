{{-- P-027: printable commission statement.
     Render is print-friendly (white background, no sidebar, full-width table). --}}
<x-layouts.app :title="__('promoters.statement.page_title', ['name' => $promoter->name])">
    <x-ds.page-header
        :title="__('promoters.statement.page_title', ['name' => $promoter->name])"
        :subtitle="$festival->displayName() . ' · ' . __('Generated') . ' ' . $generatedAt->setTimezone('Europe/Belgrade')->format('d.m.Y H:i')"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('admin.promoters.index', $festival)" wire:navigate>
                ← {{ __('Back to promoters') }}
            </x-ds.button>
            <button type="button" onclick="window.print()" class="ds-btn ds-btn-primary ds-btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                {{ __('Print / Save as PDF') }}
            </button>
        </x-slot:actions>
    </x-ds.page-header>

    {{-- Promoter / festival header card --}}
    <x-ds.card class="mb-5">
        <x-slot:body>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <div class="text-[11px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold mb-1">
                        {{ __('Promoter') }}
                    </div>
                    <div class="text-base font-semibold">{{ $promoter->name }}</div>
                    <div class="text-sm text-[color:var(--ds-text-muted)]">{{ $promoter->email }}</div>
                    <div class="mt-2 inline-flex items-center gap-1.5 text-xs">
                        <span class="text-[color:var(--ds-text-muted)]">{{ __('Role on this festival:') }}</span>
                        <x-ds.badge variant="accent" size="sm">{{ __("promoter_managers.role." . ($promoter->roleInFestival($festival->id) ?? 'promoter')) }}</x-ds.badge>
                    </div>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold mb-1">
                        {{ __('Festival') }}
                    </div>
                    <div class="text-base font-semibold">{{ $festival->displayName() }}</div>
                    @if ($festival->location)
                        <div class="text-sm text-[color:var(--ds-text-muted)]">{{ $festival->location }}</div>
                    @endif
                    @if ($festival->start_date)
                        <div class="text-sm text-[color:var(--ds-text-muted)]">
                            {{ $festival->start_date->format('d.m.Y') }}
                            @if ($festival->end_date && $festival->end_date->ne($festival->start_date))
                                — {{ $festival->end_date->format('d.m.Y') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </x-slot:body>
    </x-ds.card>

    {{-- Summary stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <x-ds.stat
            :label="__('promoters.statement.stat_orders')"
            :value="number_format($totals['orders_count'])"
        />
        <x-ds.stat
            :label="__('promoters.statement.stat_tickets')"
            :value="number_format($totals['tickets_count'])"
        />
        <x-ds.stat
            :label="__('promoters.statement.stat_gross')"
            :value="number_format($totals['gross_revenue'], 2) . ' RSD'"
        />
        <x-ds.stat
            :label="__('promoters.statement.stat_commission')"
            :value="number_format($totals['commission_total'], 2) . ' RSD'"
        />
    </div>

    {{-- Per-ticket-type breakdown --}}
    @if ($byTicketType->isNotEmpty())
        <x-ds.card :title="__('promoters.statement.by_ticket_type')" :padded="false" class="mb-5">
            <x-ds.table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Ticket type') }}</th>
                        <th class="text-right">{{ __('Quantity') }}</th>
                        <th class="text-right">{{ __('Gross') }} (RSD)</th>
                    </tr>
                </x-slot:head>
                @foreach ($byTicketType as $row)
                    <tr>
                        <td class="row-title">{{ $row['name'] }}</td>
                        <td class="text-right num">{{ number_format($row['quantity']) }}</td>
                        <td class="text-right num">{{ number_format($row['gross'], 2) }}</td>
                    </tr>
                @endforeach
            </x-ds.table>
        </x-ds.card>
    @endif

    {{-- Order-by-order ledger --}}
    <x-ds.card :title="__('promoters.statement.ledger')" :padded="false" class="mb-5">
        @if ($orders->isEmpty())
            <div class="p-6 text-center text-sm text-[color:var(--ds-text-muted)]">
                {{ __('promoters.statement.no_orders') }}
            </div>
        @else
            <x-ds.table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Order #') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-right">{{ __('Items') }}</th>
                        <th class="text-right">{{ __('Total') }}</th>
                        <th class="text-right">{{ __('Commission') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($orders as $order)
                    <tr wire:key="so-{{ $order->id }}">
                        <td class="row-title font-mono">#{{ $order->order_number ?? $order->id }}</td>
                        <td class="text-sm text-[color:var(--ds-text-muted)] num">{{ $order->created_at->setTimezone('Europe/Belgrade')->format('d.m.Y') }}</td>
                        <td class="text-sm">{{ $order->email }}</td>
                        <td class="text-right num">{{ $order->items->sum('quantity') }}</td>
                        <td class="text-right num font-medium">{{ number_format((float) $order->total, 2) }}</td>
                        <td class="text-right num font-medium">
                            {{ number_format((float) ($order->total_commission_earned ?? 0), 2) }}
                        </td>
                        <td>
                            <x-ds.badge :variant="match($order->job_status) { 'sent' => 'accent', 'completed' => 'success', default => 'neutral' }" size="sm" dot>
                                {{ __(ucfirst($order->job_status)) }}
                            </x-ds.badge>
                        </td>
                    </tr>
                @endforeach
                <x-slot:foot>
                    <tr>
                        <td colspan="4" class="text-right font-semibold">{{ __('Totals') }}</td>
                        <td class="text-right num font-bold text-base">{{ number_format($totals['gross_revenue'], 2) }} RSD</td>
                        <td class="text-right num font-bold text-base" style="color: var(--festival-primary, var(--ds-accent-text));">
                            {{ number_format($totals['commission_total'], 2) }} RSD
                        </td>
                        <td></td>
                    </tr>
                </x-slot:foot>
            </x-ds.table>
        @endif
    </x-ds.card>

    {{-- Settlement / balance card --}}
    <x-ds.card :title="__('promoters.statement.settlement')" class="mb-5">
        <x-slot:body>
            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <dt class="text-[11px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">
                        {{ __('promoters.statement.settlement_total_commission') }}
                    </dt>
                    <dd class="mt-1 text-2xl font-bold num" style="color: var(--festival-primary, var(--ds-accent-text));">
                        {{ number_format($totals['commission_total'], 2) }} RSD
                    </dd>
                    <p class="text-xs text-[color:var(--ds-text-muted)] mt-1">
                        {{ __('What the promoter earned.') }}
                    </p>
                </div>
                <div>
                    <dt class="text-[11px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">
                        {{ __('promoters.statement.settlement_paid') }}
                    </dt>
                    <dd class="mt-1 text-2xl font-bold num">{{ number_format($totals['paid_to_organizer'], 2) }} RSD</dd>
                    <p class="text-xs text-[color:var(--ds-text-muted)] mt-1">
                        {{ __('Already paid to the organisers.') }}
                    </p>
                </div>
                <div>
                    <dt class="text-[11px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">
                        {{ __('promoters.statement.settlement_owed') }}
                    </dt>
                    <dd @class([
                        'mt-1 text-2xl font-bold num',
                        'text-rose-600 dark:text-rose-400' => $totals['owed_to_organizer'] > 0,
                        'text-emerald-600 dark:text-emerald-400' => $totals['owed_to_organizer'] <= 0,
                    ])>
                        {{ number_format($totals['owed_to_organizer'], 2) }} RSD
                    </dd>
                    <p class="text-xs text-[color:var(--ds-text-muted)] mt-1">
                        {{ __('Still owed to the organisers (gross − commission − paid).') }}
                    </p>
                </div>
            </dl>
        </x-slot:body>
    </x-ds.card>

    {{-- Print-friendly footer --}}
    <p class="text-center text-xs text-[color:var(--ds-text-muted)] print:mt-8">
        {{ __('Generated by') }} {{ config('app.name') }} ·
        {{ $generatedAt->setTimezone('Europe/Belgrade')->format('d.m.Y H:i:s') }}
    </p>

    {{-- Print-specific CSS — hide everything except the statement card. --}}
    @push('styles')
        <style>
            @media print {
                aside, header, .row-actions, button[onclick="window.print()"], .ds-page-header { display: none !important; }
                body { background: #fff !important; color: #000 !important; }
                .ds-card { border: 1px solid #d4d4d8 !important; box-shadow: none !important; page-break-inside: avoid; }
                table { font-size: 11pt; }
            }
        </style>
    @endpush
</x-layouts.app>
