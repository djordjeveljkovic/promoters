<x-layouts.app :title="__('admin_orders.page_title')">
    <div class="container mx-auto px-2 sm:px-4 lg:px-6 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white">
                    @isset($festival) {{ $festival->displayName() }} — @endif{{ __('admin_orders.main_heading') }}
                </h1>
                @isset($festival)
                    <p class="text-sm text-gray-500 mt-1">{{ $festival->location }} · {{ __($festival->status) }}</p>
                @endisset
            </div>

            <a href="{{ route('promoter.orders.create', $festival) }}" class="inline-flex items-center bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors whitespace-nowrap">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ __('admin_orders.create_order_button') }}
            </a>
        </div>

        <form method="GET" action="{{ route('admin.orders.index', $festival) }}" class="mb-6 flex flex-col w-fit sm:flex-row items-center gap-3">
            <select name="status_filter" onchange="this.form.submit()" class="w-full sm:w-auto rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 text-xs sm:text-sm">
                <option value="">{{ __('admin_orders.filters.all_job_statuses_option') }}</option>
                @foreach($jobStatusColors as $statusKey => $details)
                    @if(!in_array($statusKey, ['N/A', 'failed_clickable'])) {{-- These keys are internal, not for display name --}}
                        <option value="{{ $statusKey }}" {{ request('status_filter') == $statusKey ? 'selected' : '' }}>
                            {{-- Translate status display names --}}
                            {{ __('admin_orders.statuses.' . ($statusKey ?? 'unknown'), [], app()->getLocale()) !== 'admin_orders.statuses.' . ($statusKey ?? 'unknown') ? __('admin_orders.statuses.' . ($statusKey ?? 'unknown')) : Illuminate\Support\Str::ucfirst($statusKey) }}
                        </option>
                    @endif
                @endforeach
            </select>
            <input type="text" name="search" id="search_orders" value="{{ request('search') }}"
                   class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 pl-9 pr-3 text-xs sm:text-sm"
                   placeholder="{{ __('admin_orders.filters.search_placeholder') }}">
            <button type="submit" class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors">
                {{ __('admin_orders.filters.search_button') }}
            </button>
            <a href="{{ route('admin.orders.index', $festival) }}" class="w-full sm:w-auto text-center px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                {{ __('admin_orders.filters.clear_button') }}
            </a>
        </form>

        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table id="orders-table" class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-3 py-3">{{ __('admin_orders.table.header_id') }}</th>
                            <th scope="col" class="px-3 py-3">{{ __('admin_orders.table.header_customer') }}</th>
                            <th scope="col" class="px-3 py-3 hidden md:table-cell">{{ __('admin_orders.table.header_promoter') }}</th>
                            <th scope="col" class="px-3 py-3 hidden lg:table-cell">{{ __('admin_orders.table.header_date') }}</th>
                            <th scope="col" class="px-3 py-3">{{ __('admin_orders.table.header_items') }}</th>
                            <th scope="col" class="px-3 py-3 text-right">{{ __('admin_orders.table.header_total') }}</th>
                            <th scope="col" class="px-3 py-3 text-right hidden sm:table-cell">{{ __('admin_orders.table.header_paid') }}</th>
                            <th scope="col" class="px-3 py-3 text-right hidden md:table-cell">{{ __('admin_orders.table.header_commission') }}</th>
                            <th scope="col" class="px-3 py-3 text-center">{{ __('admin_orders.table.header_job_status') }}</th>
                            <th scope="col" class="px-3 py-3 text-center">{{ __('admin_orders.table.header_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($orders as $order)
                            @php
                                $jobStatusSlug = $order->job_status ?? 'unknown';
                                $statusText = __('admin_orders.statuses.' . $jobStatusSlug, [], app()->getLocale());
                                if ($statusText === 'admin_orders.statuses.' . $jobStatusSlug) { // Fallback if translation not found
                                    $statusText = Illuminate\Support\Str::ucfirst($jobStatusSlug === 'unknown' ? __('admin_orders.statuses.unknown') : $jobStatusSlug);
                                }
                                $hasFailureReason = $jobStatusSlug === 'failed' && !empty($order->job_failure_reason);
                                $cssOrder = $hasFailureReason ? ($jobStatusColors['failed_clickable'] ?? $jobStatusColors['failed']) : ($jobStatusColors[$jobStatusSlug] ?? $jobStatusColors['unknown']);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-xs sm:text-sm">
                                <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-900 dark:text-white">#{{ $order->order_number }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="truncate w-32 sm:w-auto" title="{{ $order->email }}">{{ $order->email }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap hidden md:table-cell">
                                    <div class="truncate w-28 sm:w-auto" title="{{ $order->requestedBy->name ?? __('admin_orders.table.promoter_not_available') }}">{{ $order->requestedBy->name ?? __('admin_orders.table.promoter_not_available') }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap hidden lg:table-cell">{{ $order->created_at->format('Y-m-d') }}</td>
                                <td class="px-3 py-2 text-xs">
                                    @foreach($order->items as $item)
                                        {{ $item->quantity }}x {{ Str::limit($item->ticketType->name, 20) }}<br>
                                    @endforeach
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right">{{ number_format($order->total, 2) }} <span class="text-gray-400 text-xs">RSD</span></td>
                                <td class="px-3 py-2 whitespace-nowrap text-right hidden sm:table-cell">{{ number_format($order->paid, 2) }} <span class="text-gray-400 text-xs">RSD</span></td>
                                <td class="px-3 py-2 whitespace-nowrap text-right hidden md:table-cell">
                                    {{ (in_array($order->job_status, ['completed', 'sent']) && isset($order->total_commission_earned)) ? number_format($order->total_commission_earned, 2) : __('admin_orders.table.commission_not_calculated') }} <span class="text-gray-400 text-xs">RSD</span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 inline-flex text-xs leading-tight font-semibold rounded-full {{ $cssOrder }}"
                                        @if($hasFailureReason)
                                            data-target-row="error-row-{{ $order->id }}"
                                            title="{{ __('admin_orders.table.status_tooltip_failure_prefix') }} {{ Str::limit($order->job_failure_reason, 100) }}"
                                        @endif
                                    >
                                        {{ $statusText }}
                                        @if($hasFailureReason)
                                            <svg class="ml-1 w-3 h-3 status-icon" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-3 py-2 flex flex-col whitespace-nowrap text-center font-medium">
                                    <a href="{{ route('admin.orders.show', ['festival' => $festival->slug, 'order' => $order->id]) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors mr-2">
                                        {{ __('admin_orders.table.action_view') }}
                                    </a>

                                    @if ($order->job_status === 'failed')
                                        <form action="{{ route('orders.rerunImageJob', $order->id) }}" method="POST" class="inline-block mr-1" title="{{ __('admin_orders.table.action_generate_images_tooltip_failure_prefix') }} {{ Str::limit($order->job_failure_reason, 70) }}">
                                            @csrf
                                            <button type="submit" class="text-yellow-500 hover:text-yellow-700 dark:text-yellow-400 dark:hover:text-yellow-300">{{ __('admin_orders.table.action_generate_images') }}</button>
                                        </form>
                                    @endif

                                    @if (in_array($order->job_status, ['completed', 'sent', 'failed']))
                                        <form action="{{ route('orders.rerunEmailJob', $order->id) }}" method="POST" class="inline-block" title="{{ __('admin_orders.table.action_send_mail_tooltip_base') }} {{ $order->job_status === 'failed' ? __('admin_orders.table.action_send_mail_tooltip_additional_failure_prefix') . ' ' . Str::limit($order->job_failure_reason, 70) : '' }}">
                                            @csrf
                                            <button type="submit" class="text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">
                                                {{ __('admin_orders.table.action_send_mail') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            {{-- Row for displaying the error message --}}
                            @if($hasFailureReason)
                            <tr id="error-row-{{ $order->id }}" class="error-message-row bg-red-50 dark:bg-red-800 dark:bg-opacity-20" style="display: none;">
                                <td colspan="10" class="px-4 py-3"> {{-- Updated colspan to 10 --}}
                                    <div class="text-xs text-red-700 dark:text-red-200">
                                        <strong class="font-semibold block mb-1">{{ __('admin_orders.table.job_failure_reason_label') }}</strong>
                                        <pre class="whitespace-pre-wrap font-mono p-2 bg-red-100 dark:bg-red-700 dark:text-red-100 rounded border border-red-200 dark:border-red-600">{{ $order->job_failure_reason }}</pre>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"> {{-- Updated colspan to 10 --}}
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('admin_orders.table.no_orders_header') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('admin_orders.table.no_orders_message') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($orders->hasPages())
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
