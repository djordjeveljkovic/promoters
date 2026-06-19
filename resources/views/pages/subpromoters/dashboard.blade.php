<x-layouts.app :title="__('Sub-promoter dashboard')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        @isset($festival)
            <div class="rounded-lg p-1" style="background: linear-gradient(90deg, {{ $festival->primary_color }} 0%, {{ $festival->secondary_color }} 100%);">
                <div class="rounded-md p-3 bg-white dark:bg-zinc-800">
                    <span class="text-xs uppercase tracking-wider text-gray-500">{{ __('Festival') }}</span>
                    <span class="ml-2 font-semibold">{{ $festival->displayName() }}</span>
                    <span class="ml-2 text-xs text-gray-500">· {{ $festival->location }}</span>
                </div>
            </div>
        @endisset

        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
            {{ __('Sub-promoter dashboard') }}
        </h1>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('You operate as a sub-promoter. To place an order, open the promoter dashboard and use the "New order" button.') }}
        </p>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="font-semibold mb-3">{{ __('Recent parent-promoter orders') }}</h2>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($recentOrders ?? [] as $order)
                    <li class="py-2 flex items-center justify-between">
                        <div>
                            <span class="font-mono text-sm">{{ $order->order_number ?? '#' . $order->id }}</span>
                            <span class="ml-2 text-sm">{{ $order->email }}</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700">
                            {{ __($order->job_status) }}
                        </span>
                    </li>
                @empty
                    <li class="py-4 text-sm text-gray-500">{{ __('No orders yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-layouts.app>