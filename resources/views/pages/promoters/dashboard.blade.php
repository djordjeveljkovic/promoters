<x-layouts.app :title="__('promoter_dashboard.page_title')"> {{-- Or use a general 'dashboard' key if preferred for all dashboards --}}
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @isset($festival)
            <div class="rounded-lg p-1 mb-6" style="background: linear-gradient(90deg, {{ $festival->primary_color }} 0%, {{ $festival->secondary_color }} 100%);">
                <div class="rounded-md p-3 bg-white dark:bg-zinc-800">
                    <span class="text-xs uppercase tracking-wider text-gray-500">{{ __('Festival') }}</span>
                    <span class="ml-2 font-semibold">{{ $festival->displayName() }}</span>
                    <span class="ml-2 text-xs text-gray-500">· {{ $festival->location }}</span>
                </div>
            </div>
        @endisset
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-8">{{ __('promoter_dashboard.main_heading') }}</h1>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('promoter_dashboard.financial_overview.heading') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Card for Total Earned Commission --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('promoter_dashboard.financial_overview.total_earnings_commission') }}</h3>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($promoterTotalEarnedCommissionAllTime, 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('promoter_dashboard.financial_overview.all_time_label') }}</p>
                </div>

                {{-- Card for Gross Sales Value --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('promoter_dashboard.financial_overview.gross_sales_value') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($promoterGrossSalesAllTime, 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('promoter_dashboard.financial_overview.gross_sales_subtext') }}</p>
                </div>

                {{-- Card for Amount Owed to Organizers --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('promoter_dashboard.financial_overview.amount_owed_to_organizers') }}</h3>
                    @if($amountOwedToOrganizersByPromoter >= 0)
                        <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($amountOwedToOrganizersByPromoter, 2) }}</p>
                    @else
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">-{{ number_format(abs($amountOwedToOrganizersByPromoter), 2) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('promoter_dashboard.financial_overview.organizer_owes_credit_subtext') }}</p>
                    @endif
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('promoter_dashboard.financial_overview.amount_owed_calculation_subtext') }}</p>
                </div>

                {{-- Optional: Card for Amount Already Paid to Organizers --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('promoter_dashboard.financial_overview.amount_paid_to_organizers') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($amountAlreadyPaidByPromoter, 2) }}</p>
                </div>

                 {{-- Card for Commission Earned Last 30 Days --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('promoter_dashboard.financial_overview.earnings_last_30_days') }}</h3>
                    <p class="text-3xl font-bold text-green-500 dark:text-green-300 mt-1">{{ number_format($promoterTotalEarnedCommissionLast30Days, 2) }}</p>
                </div>
            </div>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('promoter_dashboard.general_performance.heading') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('promoter_dashboard.general_performance.total_orders_all_time') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($promoterTotalOrdersAllTime) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('promoter_dashboard.general_performance.tickets_sold_all_time') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($promoterTotalTicketsSoldAllTime) }}</p>
                </div>
                 <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('promoter_dashboard.general_performance.orders_last_30_days') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($promoterTotalOrdersLast30Days) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('promoter_dashboard.general_performance.tickets_sold_last_30_days') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($promoterTotalTicketsSoldLast30Days) }}</p>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-8 mb-8">
            <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('promoter_dashboard.top_ticket_sales_by_type.heading') }}</h2>
                @if($promoterTicketTypePerformance->isEmpty())
                    <p class="text-gray-600 dark:text-gray-400">{{ __('promoter_dashboard.top_ticket_sales_by_type.no_data') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('promoter_dashboard.top_ticket_sales_by_type.table_header_ticket_type') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('promoter_dashboard.top_ticket_sales_by_type.table_header_quantity_sold') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('promoter_dashboard.top_ticket_sales_by_type.table_header_gross_revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($promoterTicketTypePerformance as $type)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $type->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($type->total_quantity_sold) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($type->total_revenue_generated, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-layouts.app>
