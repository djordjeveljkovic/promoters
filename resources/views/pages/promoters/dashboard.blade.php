<x-layouts.app :title="__('promoter_dashboard.page_title')">

    <x-ds.page-header
        :title="__('promoter_dashboard.main_heading')"
        :subtitle="$festival?->displayName() ? __('Festival') . ' · ' . $festival->displayName() : null"
    >
        <x-slot:actions>
            <x-ds.button variant="primary" :href="route('promoter.orders.create', $festival)" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('New order') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    {{-- Financial overview --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-ds.stat
            :label="__('promoter_dashboard.financial_overview.total_earnings_commission')"
            :value="number_format($promoterTotalEarnedCommissionAllTime, 2)"
            :hint="__('promoter_dashboard.financial_overview.all_time_label')"
        />
        <x-ds.stat
            :label="__('promoter_dashboard.financial_overview.gross_sales_value')"
            :value="number_format($promoterGrossSalesAllTime, 2)"
            :hint="__('promoter_dashboard.financial_overview.gross_sales_subtext')"
        />
        <x-ds.stat
            :label="__('promoter_dashboard.financial_overview.amount_owed_to_organizers')"
            :value="number_format(abs($amountOwedToOrganizersByPromoter), 2) . ($amountOwedToOrganizersByPromoter < 0 ? ' (' . __('Credit') . ')' : '')"
            :hint="__('promoter_dashboard.financial_overview.amount_owed_calculation_subtext')"
        />
        <x-ds.stat
            :label="__('promoter_dashboard.financial_overview.amount_paid_to_organizers')"
            :value="number_format($amountAlreadyPaidByPromoter, 2)"
        />
        <x-ds.stat
            :label="__('promoter_dashboard.financial_overview.earnings_last_30_days')"
            :value="number_format($promoterTotalEarnedCommissionLast30Days, 2)"
        />
    </div>

    {{-- General performance --}}
    <x-ds.card :title="__('promoter_dashboard.general_performance.heading')" class="mt-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="ds-stat-label">{{ __('promoter_dashboard.general_performance.total_orders_all_time') }}</div>
                <div class="text-xl font-semibold mt-1 num">{{ number_format($promoterTotalOrdersAllTime) }}</div>
            </div>
            <div>
                <div class="ds-stat-label">{{ __('promoter_dashboard.general_performance.tickets_sold_all_time') }}</div>
                <div class="text-xl font-semibold mt-1 num">{{ number_format($promoterTotalTicketsSoldAllTime) }}</div>
            </div>
            <div>
                <div class="ds-stat-label">{{ __('promoter_dashboard.general_performance.orders_last_30_days') }}</div>
                <div class="text-xl font-semibold mt-1 num">{{ number_format($promoterTotalOrdersLast30Days) }}</div>
            </div>
            <div>
                <div class="ds-stat-label">{{ __('promoter_dashboard.general_performance.tickets_sold_last_30_days') }}</div>
                <div class="text-xl font-semibold mt-1 num">{{ number_format($promoterTotalTicketsSoldLast30Days) }}</div>
            </div>
        </div>
    </x-ds.card>

    {{-- Top ticket types --}}
    <x-ds.card :title="__('promoter_dashboard.top_ticket_sales_by_type.heading')" class="mt-6">
        @if($promoterTicketTypePerformance->isEmpty())
            <x-ds.empty-state
                :title="__('No data')"
                :message="__('Start selling tickets to see your top performers here.')"
            />
        @else
            <x-ds.table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('promoter_dashboard.top_ticket_sales_by_type.table_header_ticket_type') }}</th>
                        <th class="text-right">{{ __('promoter_dashboard.top_ticket_sales_by_type.table_header_quantity_sold') }}</th>
                        <th class="text-right">{{ __('promoter_dashboard.top_ticket_sales_by_type.table_header_gross_revenue') }}</th>
                    </tr>
                </x-slot:head>
                @foreach($promoterTicketTypePerformance as $type)
                    <tr>
                        <td class="row-title">{{ $type->name }}</td>
                        <td class="text-right num">{{ number_format($type->total_quantity_sold) }}</td>
                        <td class="text-right num">{{ number_format($type->total_revenue_generated, 2) }}</td>
                    </tr>
                @endforeach
            </x-ds.table>
        @endif
    </x-ds.card>
</x-layouts.app>
