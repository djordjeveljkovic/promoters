<x-layouts.app :title="__('promoter_dashboard.page_title')">

    <x-ds.page-header
        :title="__('promoter_dashboard.main_heading')"
        :subtitle="$festival?->displayName() ? __('Festival') . ' · ' . $festival->displayName() : null"
    >
        <x-slot:actions>
            {{-- U-007: bulk resend — handy when SMTP bounces pile up. --}}
            @auth
                @if (($recentOrderCount ?? 0) > 0)
                    <form method="POST" action="{{ route('promoter.orders.resend-last', $festival) }}" class="inline"
                          onsubmit="return confirm('{{ __('Re-send the last 5 order emails?') }}')">
                        @csrf
                        <input type="hidden" name="count" value="5">
                        <x-ds.button variant="secondary" type="submit" title="{{ __('Resend the last 5 order emails') }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><polyline points="22 6 12 13 2 6"/></svg>
                            {{ __('Resend last 5') }}
                        </x-ds.button>
                    </form>
                @endif
            @endauth
            <x-ds.button variant="primary" :href="route('promoter.orders.create', $festival)" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('New order') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    @auth
        @if (auth()->user()->isPromoterManager($festival?->id))
            <x-ds.alert variant="accent" class="mb-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <div class="font-semibold">{{ __('You are a promoter manager on this festival') }}</div>
                        <div class="text-sm">{{ __('You can create sub-promoters and set their commission. Your payout is reduced by what you pay them.') }}</div>
                    </div>
                    <x-ds.button variant="primary" :href="route('promoter.sub-promoters.index', $festival)" wire:navigate>
                        {{ __('Manage sub-promoters') }}
                    </x-ds.button>
                </div>
            </x-ds.alert>
        @endif
    @endauth

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
