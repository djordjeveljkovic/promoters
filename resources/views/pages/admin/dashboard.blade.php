<x-layouts.app :title="__('dashboard.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-start justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $festival?->displayName() ?? __('dashboard.main_heading') }}</h1>
                @if ($festival)
                    <p class="text-sm text-gray-500 mt-1">{{ $festival->location }} · {{ __($festival->status) }}</p>
                @endif
            </div>
            <x-festival.selector :current="$festival" />
        </div>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('dashboard.overall_performance.heading') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('dashboard.overall_performance.total_revenue_all_time') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($totalRevenueAllTime, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('dashboard.overall_performance.total_orders_all_time') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($totalOrdersAllTime) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('dashboard.overall_performance.tickets_sold_completed_all_time') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($totalTicketsEffectivelySoldAllTime) }}</p>
                </div>
                 <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('dashboard.overall_performance.revenue_last_30_days') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($totalRevenueLast30Days, 2) }}</p>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <section class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('dashboard.top_ticket_types.heading') }}</h2>
                @if($ticketTypePerformance->isEmpty())
                    <p class="text-gray-600 dark:text-gray-400">{{ __('dashboard.top_ticket_types.no_data') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.top_ticket_types.table_header_type_name') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.top_ticket_types.table_header_quantity_sold') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.top_ticket_types.table_header_est_revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($ticketTypePerformance as $type)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $type->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($type->total_quantity_sold) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($type->total_revenue, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('dashboard.user_ticket_stats.heading') }}</h2>
                <div class="space-y-3">
                    @foreach($userCountsByRole as $role => $count)
                    <div class="flex justify-between text-sm">
                        {{-- You might want specific keys per role if `ucfirst($role)` isn't always desired or if role names need translation --}}
                        {{-- Option 1: Specific keys per role (e.g., __('dashboard.user_ticket_stats.role_admin')) --}}
                        {{-- Option 2: Translate role then append generic suffix --}}
                        <span class="text-gray-600 dark:text-gray-400">{{ ucfirst($role) }}{{ __('dashboard.user_ticket_stats.role_count_suffix') }}</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $count }}</span>
                    </div>
                    @endforeach
                    <hr class="dark:border-gray-700">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('dashboard.user_ticket_stats.active_tickets') }}</span>
                        <span class="font-semibold text-green-600">{{ $activeTicketsCount }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('dashboard.user_ticket_stats.inactive_tickets') }}</span>
                        <span class="font-semibold text-red-600">{{ $inactiveTicketsCount }}</span>
                    </div>
                </div>

                 <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mt-6 mb-4">{{ __('dashboard.order_statuses.heading') }}</h2>
                 <div class="space-y-2">
                    @foreach($orderStatusCounts as $status => $count)
                        <div class="flex justify-between items-center text-sm">
                            {{-- If status slugs (e.g., 'pending', 'completed') need translation, use something like __('statuses.' . $status) --}}
                            <span class="text-gray-600 dark:text-gray-400">{{ ucfirst($status) }}:</span>
                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $count }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="mb-8 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('dashboard.top_promoter_performance.heading') }}</h2>
            @if($promoterPerformance->isEmpty() || $promoterPerformance->every(fn($p) => $p->total_orders_generated == 0))
                <p class="text-gray-600 dark:text-gray-400">{{ __('dashboard.top_promoter_performance.no_data') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.top_promoter_performance.table_header_promoter') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.top_promoter_performance.table_header_email') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.top_promoter_performance.table_header_orders_generated') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.top_promoter_performance.table_header_revenue_generated') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($promoterPerformance as $promoter)
                                @if($promoter->total_orders_generated > 0) {{-- Only show promoters with activity --}}
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $promoter->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $promoter->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($promoter->total_orders_generated) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($promoter->total_revenue_generated, 2) }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('dashboard.recent_orders.heading') }}</h2>
            @if($recentOrders->isEmpty())
                 <p class="text-gray-600 dark:text-gray-400">{{ __('dashboard.recent_orders.no_data') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.recent_orders.table_header_order_id') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.recent_orders.table_header_customer_email') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.recent_orders.table_header_promoter') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.recent_orders.table_header_items') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.recent_orders.table_header_total') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.recent_orders.table_header_status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('dashboard.recent_orders.table_header_date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($recentOrders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <a href="{{-- route('admin.orders.show', ['festival' => $festival->slug, 'order' => $order->id]) --}}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                        #{{ $order->id }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $order->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $order->requestedBy->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    @foreach($order->items as $item)
                                        {{ $item->quantity }}x {{ $item->ticketType->name }} <br>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$order->job_status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{-- If status slugs need translation, use e.g. __('statuses.' . $order->job_status) --}}
                                        {{ ucfirst($order->job_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $order->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
