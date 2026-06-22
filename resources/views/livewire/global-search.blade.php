{{-- The `&&` from the previous version was short-circuiting: assigning
     `open = false` yields `false`, and `false && anything` never calls
     `$wire.close()`. The Livewire property stayed `true`, so on the
     next render @entangle snapped the panel right back open.  Use a
     sequence (semicolon) so both effects run every time. --}}
<div x-data="{ open: @entangle('open') }"
     @click.outside="open = false; $wire.close()"
     @keydown.escape.window="open = false; $wire.close()"
     class="relative">
    {{-- Search trigger — opens the panel on click and focuses the input --}}
    <button
        type="button"
        wire:click="$toggle('open')"
        class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-[color:var(--ds-border)] bg-[color:var(--ds-surface)] hover:bg-[color:var(--ds-bg-subtle)] text-xs text-[color:var(--ds-text-muted)] min-w-[200px] transition-colors"
        :aria-expanded="open"
        aria-haspopup="dialog"
    >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <span class="flex-1 text-left">{{ __('search.quick_search') }}</span>
        <kbd class="hidden md:inline-flex px-1.5 py-0.5 rounded text-[10px] font-mono border border-[color:var(--ds-border)] bg-[color:var(--ds-bg-subtle)]">⌘K</kbd>
    </button>

    {{-- Panel --}}
    <div
        x-show="open"
        x-transition.opacity.duration.100ms
        class="absolute right-0 mt-2 w-[min(92vw,560px)] rounded-lg border border-[color:var(--ds-border)] bg-[color:var(--ds-surface)] shadow-2xl z-50 overflow-hidden"
        role="dialog"
        aria-label="{{ __('Quick search') }}"
    >
        <div class="border-b border-[color:var(--ds-divider)] p-2">
            <input
                type="search"
                wire:model.live.debounce.250ms="q"
                placeholder="{{ __('search.placeholder') }}"
                autofocus
                class="w-full px-3 py-2 rounded-md bg-[color:var(--ds-bg-subtle)] border border-[color:var(--ds-border)] text-sm text-[color:var(--ds-text)] placeholder:text-[color:var(--ds-text-muted)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-accent)]"
            />
        </div>

        <div class="max-h-[60vh] overflow-y-auto">
            @php
                $groups = $this->results();
                $hasAny = $groups->isNotEmpty();
            @endphp

            @if (mb_strlen(trim($q)) < 2)
                <p class="p-6 text-center text-sm text-[color:var(--ds-text-muted)]">
                    {{ __('search.too_short') }}
                </p>
            @elseif (!$hasAny)
                <p class="p-6 text-center text-sm text-[color:var(--ds-text-muted)]">
                    {{ __('search.no_results', ['q' => $q]) }}
                </p>
            @else
                @foreach ($groups as $category => $items)
                    <div class="border-b border-[color:var(--ds-divider)] last:border-b-0">
                        <div class="px-3 py-1.5 text-[10px] uppercase tracking-wider font-semibold text-[color:var(--ds-text-muted)] bg-[color:var(--ds-bg-subtle)]">
                            {{ __('search.group_' . $category) }}
                            <span class="ml-1 text-[color:var(--ds-text-subtle)]">{{ $items->count() }}</span>
                        </div>
                        <ul class="py-1">
                            @foreach ($items as $row)
                                @php $href = $this->urlFor($category, $row); @endphp
                                <li>
                                    <a
                                        href="{{ $href ?? '#' }}"
                                        @if ($href) wire:navigate @endif
                                        @class([
                                            'flex items-center gap-3 px-3 py-2 text-sm hover:bg-[color:var(--ds-bg-subtle)] text-[color:var(--ds-text)]',
                                            'opacity-50 cursor-not-allowed' => !$href,
                                        ])
                                    >
                                        {{-- Category icon --}}
                                        @switch($category)
                                            @case('festivals')
                                                <span class="w-7 h-7 rounded-md flex items-center justify-center text-white font-semibold text-xs"
                                                      style="background: linear-gradient(135deg, {{ $row->primary_color ?? '#6366f1' }} 0%, #6366f1 100%);">
                                                    {{ mb_substr($row->name, 0, 1) }}
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-medium truncate">{{ $row->displayName() }}</div>
                                                    <div class="text-xs text-[color:var(--ds-text-muted)] truncate">{{ $row->location ?: '—' }}</div>
                                                </div>
                                                @break
                                            @case('promoters')
                                                <x-ds.avatar :name="$row->name" size="sm" />
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-medium truncate">{{ $row->name }}</div>
                                                    <div class="text-xs text-[color:var(--ds-text-muted)] truncate">{{ $row->email }}</div>
                                                </div>
                                                @break
                                            @case('orders')
                                                <span class="w-7 h-7 rounded-md flex items-center justify-center bg-[color:var(--ds-bg-subtle)] text-[color:var(--ds-text-muted)] font-mono text-[10px]">
                                                    #
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-mono text-xs font-medium truncate">#{{ $row->order_number ?? $row->id }}</div>
                                                    <div class="text-xs text-[color:var(--ds-text-muted)] truncate">
                                                        {{ $row->email }}
                                                        @if ($row->festival)
                                                            · {{ $row->festival->name }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="text-xs num text-[color:var(--ds-text-muted)] tabular-nums">
                                                    {{ number_format((float) $row->total, 0) }} RSD
                                                </span>
                                                @break
                                            @case('ticket_types')
                                                <span class="w-7 h-7 rounded-md flex items-center justify-center bg-[color:var(--ds-bg-subtle)] text-[color:var(--ds-text-muted)] font-semibold">
                                                    T
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-medium truncate">{{ $row->name }}</div>
                                                    <div class="text-xs text-[color:var(--ds-text-muted)] truncate">
                                                        @if ($row->festival)
                                                            {{ $row->festival->name }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="text-xs num text-[color:var(--ds-text-muted)] tabular-nums">
                                                    {{ number_format((float) $row->price, 0) }} RSD
                                                </span>
                                                @break
                                        @endswitch
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="border-t border-[color:var(--ds-divider)] px-3 py-1.5 text-[10px] text-[color:var(--ds-text-muted)] flex items-center justify-between">
            <span>{{ __('search.press_esc_to_close') }}</span>
            <span>
                @if (auth()->user()?->isSuperAdmin())
                    {{ __('search.scope_global') }}
                @else
                    {{ __('search.scope_scoped') }}
                @endif
            </span>
        </div>
    </div>
</div>
