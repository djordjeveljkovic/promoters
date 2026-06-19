@php
    /**
     * Festival selector dropdown.
     *
     * Usage:
     *   <x-festival.selector :current="$festival ?? null" />
     *
     * The component renders nothing for users who only have access to one
     * festival and are already on that festival's page (no value to switch to).
     */
    $user = auth()->user();
    $accessible = $user?->accessibleFestivals() ?? collect();
    $current = $current ?? request()->route('festival');
    if (is_numeric($current)) {
        $current = $accessible->firstWhere('id', (int) $current);
    } elseif (is_string($current)) {
        $current = $accessible->firstWhere('slug', $current);
    }
@endphp

@if ($accessible->count() > 1 || $user?->isSuperAdmin())
    <div class="relative" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm">
            <span class="w-2 h-2 rounded-full"
                  style="background: {{ $current?->primary_color ?? '#9ca3af' }}"></span>
            <span class="font-medium">
                {{ $current?->displayName() ?? __('Choose festival') }}
            </span>
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 8l5 5 5-5H5z"/></svg>
        </button>

        <div x-show="open" @click.outside="open = false" x-transition
             class="absolute right-0 mt-2 w-72 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg z-50 overflow-hidden">
            <div class="p-2 text-xs uppercase text-gray-500 border-b border-gray-100 dark:border-gray-800">
                {{ __('Switch festival') }}
            </div>
            <ul class="max-h-80 overflow-y-auto py-1">
                @foreach ($accessible as $f)
                    <li>
                        @php
                            $href = match (true) {
                                $user->isSuperAdmin() => route('admin.dashboard', $f),
                                $user->isAdmin()     => route('admin.dashboard', $f),
                                default              => route('promoter.dashboard', $f),
                            };
                        @endphp
                        <a href="{{ $href }}"
                           class="flex items-center gap-3 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 {{ $current && $current->id === $f->id ? 'bg-pink-50 dark:bg-pink-900/20' : '' }}">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $f->primary_color }}"></span>
                            <span class="flex-1">
                                <span class="font-medium block">{{ $f->displayName() }}</span>
                                <span class="text-xs text-gray-500">{{ __($f->status) }} · {{ $f->location }}</span>
                            </span>
                            @if ($current && $current->id === $f->id)
                                <span class="text-xs text-pink-600">✓</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
            @if ($user?->isSuperAdmin())
                <div class="p-2 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('superadmin.festivals.create') }}"
                       class="block px-3 py-2 text-sm text-pink-600 hover:bg-pink-50 rounded">
                        + {{ __('Create new festival') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif