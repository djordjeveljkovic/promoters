<x-layouts.app :title="__('Pick a festival')">
    <div class="p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-bold">{{ __('Pick a festival') }}</h1>
            <p class="text-sm text-gray-500">{{ __('You have admin access to the festivals below. Click one to manage it.') }}</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($festivals as $f)
                <a href="{{ route('admin.dashboard', ['festival' => $f->slug]) }}"
                   class="block rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:shadow-md transition overflow-hidden">
                    <div class="h-2" style="background: linear-gradient(90deg, {{ $f->primary_color }} 0%, {{ $f->secondary_color }} 100%);"></div>
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold">{{ $f->displayName() }}</h2>
                                <p class="text-xs text-gray-500">{{ $f->location }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded
                                @switch($f->status)
                                    @case('active')   bg-green-100 text-green-800 @break
                                    @case('draft')    bg-yellow-100 text-yellow-800 @break
                                    @case('archived') bg-gray-200 text-gray-700 @break
                                @endswitch
                            ">{{ __($f->status) }}</span>
                        </div>
                        @if ($f->tagline)
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $f->tagline }}</p>
                        @endif
                        <div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
                            <span>📦 {{ $f->ticket_types_count }} {{ __('navigation.sidebar.ticket_types') }}</span>
                            <span>🧾 {{ $f->orders_count }} {{ __('navigation.sidebar.sales') }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center text-gray-500 py-12">
                    {{ __('You have no festival assignments yet.') }}
                    <a href="{{ route('superadmin.festivals.index') }}" class="text-pink-600 hover:underline ml-1">{{ __('Manage festivals') }} →</a>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>