@props([
    'href',
    'active' => false,
    'icon' => null,
])

@php
    $icons = [
        'home'              => 'M3 12l9-9 9 9M5 10v10a1 1 0 0 0 1 1h3v-6h6v6h3a1 1 0 0 0 1-1V10',
        'calendar'          => 'M3 4h18M3 4v16a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V4M3 4v16M8 2v4M16 2v4M3 10h18',
        'users'             => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M22 21v-2a4 4 0 0 0-3-3.87M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM16 3.13a4 4 0 0 1 0 7.75',
        'envelope'          => 'M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zM22 6 12 13 2 6',
        'user'              => 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',
        'ticket'            => 'M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9zM3 9V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2M3 15v2a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-2',
        'shopping-bag'      => 'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0',
        'plus-circle'       => 'M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20zM12 8v8M8 12h8',
        'question-mark-circle' => 'M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20zM9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01',
    ];
@endphp

<li>
    <a
        href="{{ $href }}"
        wire:navigate
        @class([
            'group flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-[13px] font-medium transition-colors',
            'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' => $active,
            'text-[color:var(--ds-sidebar-text)] hover:bg-[color:var(--ds-bg-subtle)] hover:text-[color:var(--ds-text)]' => !$active,
        ])
    >
        @if ($icon && isset($icons[$icon]))
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" @class(['flex-shrink-0', $active ? 'text-indigo-600 dark:text-indigo-300' : 'text-[color:var(--ds-text-muted)] group-hover:text-[color:var(--ds-text)]'])" aria-hidden="true">
                <path d="{{ $icons[$icon] }}"></path>
            </svg>
        @endif
        <span class="flex-1 truncate">{{ $slot }}</span>
        @if ($active)
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
        @endif
    </a>
</li>
