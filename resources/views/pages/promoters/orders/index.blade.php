<x-layouts.app :title="__('orders.page_title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="max-w-full rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        @isset($festival) {{ $festival->displayName() }} — @endif{{ __('orders.main_heading') }}
                    </h1>
                    @isset($festival)
                        <p class="text-sm text-gray-500 mt-1">{{ $festival->location }}</p>
                    @endisset
                </div>
                <a href="{{ route('promoter.orders.create', $festival) }}"
                   class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    {{ __('orders.create_new_order_button') }}
                </a>
            </div>

            {{-- Flash Messages for success/error/info --}}
            {{-- Content of flash messages should be translated in the controller when setting them --}}
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-700 dark:text-green-100">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700 dark:bg-red-700 dark:text-red-100">
                    {{ session('error') }}
                </div>
            @endif
             @if (session('info'))
                <div class="mb-4 rounded-md bg-blue-50 p-4 text-sm text-blue-700 dark:bg-blue-700 dark:text-blue-100">
                    {{ session('info') }}
                </div>
            @endif


            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('orders.table.header_order_id') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('orders.table.header_customer_email') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('orders.table.header_order_date') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('orders.table.header_items') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('orders.table.header_total_price') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('orders.table.header_commission_earned') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('orders.table.header_job_status') }}</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('orders.table.header_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($orders as $order)
                            @php
                                $isJobFailed = $order->job_status === 'failed';
                                // Translate status text
                                $statusKey = $order->job_status ?? 'unknown';
                                $statusText = __('orders.statuses.' . $statusKey, [], App::getLocale());
                                // Fallback if specific status key doesn't exist, then use ucfirst
                                if ($statusText === 'orders.statuses.' . $statusKey) {
                                    $statusText = Illuminate\Support\Str::ucfirst($order->job_status ?? __('orders.statuses.unknown'));
                                }

                                $statusClass = $jobStatusColors[$order->job_status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
                                if ($isJobFailed && !empty($order->job_failure_reason)) {
                                    $statusClass .= ' job-status-trigger cursor-pointer';
                                }
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">#{{ $order->order_number }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $order->email }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $order->created_at->format('M d, Y H:i') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                    @foreach($order->items as $item)
                                        {{ $item->quantity }}x {{ $item->ticketType->name }}<br>
                                    @endforeach
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ number_format($order->total, 2) }} RSD</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                    {{ (in_array($order->job_status, ['completed', 'sent']) && isset($order->total_commission_earned)) ? number_format($order->total_commission_earned, 2). ' RSD' : __('orders.table.commission_not_calculated') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        @if($isJobFailed && !empty($order->job_failure_reason))
                                            data-target-row="error-row-{{ $order->id }}"
                                            title="{{ __('orders.table.status_error_tooltip_prefix') }} {{ Str::limit($order->job_failure_reason, 100) }}"
                                        @endif
                                        class="px-2 inline-flex lowercase items-center text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ $statusText }}
                                        @if($isJobFailed && !empty($order->job_failure_reason))
                                            <svg class="ml-1 w-3 h-3 transform transition-transform duration-150 status-icon" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 flex flex-col text-center text-sm font-medium space-x-1"> {{-- Consider items-center for alignment --}}
                                    @if ($order->job_status === 'failed')
                                        <form action="{{ route('orders.rerunImageJob', $order->id) }}" method="POST" class="inline-block mb-1 sm:mb-0 sm:mr-1">
                                            @csrf
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-200 px-2 py-1 text-xs" title="{{ __('orders.table.actions_retry_images_tooltip_prefix') }} {{ Str::limit($order->job_failure_reason, 100) }}">
                                                {{ __('orders.table.actions_retry_images_button') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('orders.rerunEmailJob', $order->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 px-2 py-1 text-xs" title="{{ __('orders.table.actions_retry_email_tooltip_prefix') }} {{ Str::limit($order->job_failure_reason, 100) }}">
                                                {{ __('orders.table.actions_retry_email_button') }}
                                            </button>
                                        </form>
                                    @elseif (in_array($order->job_status, ['completed', 'sent']))
                                        <form action="{{ route('orders.rerunEmailJob', $order->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200 px-2 py-1 text-xs" title="{{ __('orders.table.actions_resend_email_tooltip') }}">
                                                {{ __('orders.table.actions_resend_email_button') }}
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Add a view details button/link if needed --}}
                                    {{-- <a href="{{ route('promoter.orders.show', ['festival' => $festival->slug, 'id' => $order->id]) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200 px-2 py-1 text-xs">View</a> --}}
                                </td>
                            </tr>
                            {{-- Row for displaying the error message --}}
                            @if($isJobFailed && !empty($order->job_failure_reason))
                            <tr id="error-row-{{ $order->id }}" class="error-message-row bg-red-50 dark:bg-red-800 dark:bg-opacity-20" style="display: none;">
                                <td colspan="8" class="px-6 py-3">
                                    <div class="text-sm text-red-700 dark:text-red-200">
                                        <strong class="font-semibold block mb-1">{{ __('orders.table.job_failure_reason_label') }}</strong>
                                        <pre class="whitespace-pre-wrap text-xs font-mono p-2 bg-red-100 dark:bg-red-700 dark:text-red-100 rounded border border-red-200 dark:border-red-600">{{ $order->job_failure_reason }}</pre>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"> {{-- Adjusted colspan to 8 --}}
                                    {{ __('orders.table.no_orders_message') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Pure JavaScript for toggling error messages (ensure this is loaded) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusTriggers = document.querySelectorAll('.job-status-trigger');
            statusTriggers.forEach(trigger => {
                trigger.addEventListener('click', function () {
                    const targetRowId = this.dataset.targetRow;
                    if (!targetRowId) return;
                    const errorRow = document.getElementById(targetRowId);
                    const icon = this.querySelector('.status-icon');
                    if (errorRow) {
                        const isHidden = errorRow.style.display === 'none' || errorRow.style.display === '';
                        errorRow.style.display = isHidden ? 'table-row' : 'none';
                        if (icon) {
                            icon.classList.toggle('rotate-180', isHidden);
                        }
                    }
                });
            });
        });
    </script>
</x-layouts.app>
