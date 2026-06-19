<x-layouts.app :title="__('Superadmin — Global overview')">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">🌐 {{ __('Superadmin Dashboard') }}</h1>
                <p class="text-sm text-gray-500">{{ __('Global view across every festival on the platform.') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('superadmin.festivals.create') }}" class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">
                    + {{ __('New festival') }}
                </a>
                <a href="{{ route('superadmin.users.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                    {{ __('Manage users') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $cards = [
                    ['label' => __('Festivals'),     'value' => $totalFestivals,     'hint' => "$activeFestivals active"],
                    ['label' => __('Users'),         'value' => $totalUsers,         'hint' => "$totalAdmins admins · $totalPromoters promoters"],
                    ['label' => __('Orders'),        'value' => $totalOrders,        'hint' => "$completedOrders completed"],
                    ['label' => __('Revenue'),       'value' => number_format($totalRevenue, 0) . ' RSD', 'hint' => __('from completed orders')],
                ];
            @endphp
            @foreach ($cards as $card)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
                    <div class="text-xs uppercase text-gray-500">{{ $card['label'] }}</div>
                    <div class="text-2xl font-bold mt-1">{{ $card['value'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $card['hint'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
                <h2 class="font-semibold mb-3">{{ __('Top festivals by revenue') }}</h2>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($perFestivalRevenue as $f)
                        <li class="py-2 flex items-center justify-between">
                            <div>
                                <span class="font-semibold">{{ $f->name }} {{ $f->year }}</span>
                                <span class="ml-2 text-xs px-2 py-0.5 rounded
                                    @switch($f->status)
                                        @case('active')   bg-green-100 text-green-800 @break
                                        @case('draft')    bg-yellow-100 text-yellow-800 @break
                                        @case('archived') bg-gray-200 text-gray-700 @break
                                    @endswitch
                                ">{{ __($f->status) }}</span>
                            </div>
                            <span class="font-mono">{{ number_format($f->completed_revenue ?? 0, 0) }} RSD</span>
                        </li>
                    @empty
                        <li class="py-4 text-gray-500 text-sm">{{ __('No revenue yet — create a festival and assign an admin to get started.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
                <h2 class="font-semibold mb-3">{{ __('Recent festivals') }}</h2>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($recentFestivals as $f)
                        <li class="py-2 flex items-center justify-between">
                            <a href="{{ route('superadmin.festivals.edit', $f) }}" class="hover:underline">
                                {{ $f->displayName() }}
                            </a>
                            <span class="text-xs text-gray-500">{{ $f->created_at->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="py-4 text-gray-500 text-sm">{{ __('No festivals yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-layouts.app>