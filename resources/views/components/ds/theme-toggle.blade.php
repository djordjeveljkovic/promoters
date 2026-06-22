@props([
    'size' => 'sm',  // sm | md
])

@php
    $sizeClass = $size === 'md' ? 'w-9 h-9' : 'w-8 h-8';
@endphp

<button
    type="button"
    x-data="{
        mode: localStorage.getItem('flux.appearance') || 'system',
        label() {
            return {
                light:  @json(__('navigation.theme.light')),
                dark:   @json(__('navigation.theme.dark')),
                system: @json(__('navigation.theme.system')),
            }[this.mode];
        },
        cycle() {
            const order = ['light', 'system', 'dark'];
            const next = order[(order.indexOf(this.mode) + 1) % order.length];
            this.mode = next;
            window.Flux?.applyAppearance?.(next);
        }
    }"
    @click="cycle()"
    :title="label()"
    :aria-label="@js(__('navigation.theme.toggle_aria'))"
    {{ $attributes->merge(['class' => 'ds-btn ds-btn-ghost ds-btn-icon ' . $sizeClass]) }}
>
    {{-- Sun (light) --}}
    <svg x-show="mode === 'light'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="4"/>
        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
    </svg>
    {{-- Moon (dark) --}}
    <svg x-show="mode === 'dark'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
    </svg>
    {{-- Half circle (system) --}}
    <svg x-show="mode === 'system'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9"/>
        <path d="M12 3v18M3 12h18" stroke-dasharray="2 2"/>
    </svg>
</button>
