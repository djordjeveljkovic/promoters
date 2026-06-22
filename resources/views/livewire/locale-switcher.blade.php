<div x-data="{ open: false }" @click.outside="open = false" @locale-changed.window="open = false" class="relative">
    <button
        type="button"
        @click="open = !open"
        class="flex items-center gap-1.5 px-2 py-1 rounded-md border border-[color:var(--ds-border)] bg-[color:var(--ds-surface)] hover:bg-[color:var(--ds-bg-subtle)] text-xs font-medium text-[color:var(--ds-text)] transition-colors"
        :aria-expanded="open"
        aria-haspopup="true"
        title="{{ __('Switch language') }}"
    >
        {{-- Globe glyph (inline SVG, no library) --}}
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <path d="M2 12h20"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        <span class="uppercase tracking-wider">{{ $current }}</span>
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-[color:var(--ds-text-muted)]" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </button>

    <ul
        x-show="open"
        x-transition.opacity.duration.120ms
        class="absolute right-0 mt-1 min-w-[160px] rounded-md border border-[color:var(--ds-border)] bg-[color:var(--ds-surface)] shadow-lg z-50 overflow-hidden"
        role="menu"
    >
        @foreach ($available as $code => $label)
            <li>
                <button
                    type="button"
                    wire:click="switch('{{ $code }}')"
                    @class([
                        'w-full text-left px-3 py-1.5 text-sm flex items-center justify-between gap-3 transition-colors',
                        'bg-[color:var(--ds-accent-soft)] text-[color:var(--ds-accent-text)] font-semibold' => $current === $code,
                        'text-[color:var(--ds-text)] hover:bg-[color:var(--ds-bg-subtle)]' => $current !== $code,
                    ])
                    role="menuitem"
                >
                    <span>{{ $label }}</span>
                    <span class="text-[10px] font-mono text-[color:var(--ds-text-muted)] uppercase">{{ $code }}</span>
                </button>
            </li>
        @endforeach
    </ul>
</div>
