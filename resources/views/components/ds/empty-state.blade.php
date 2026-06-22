@props([
    'title',
    'message' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'ds-empty']) }}>
    <div class="ds-empty-icon">
        @if ($icon)
            {!! $icon !!}
        @else
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                <path d="M3 10h18M9 14h6"></path>
            </svg>
        @endif
    </div>
    <div class="ds-empty-title">{{ $title }}</div>
    @if ($message)
        <div class="ds-empty-message">{{ $message }}</div>
    @endif
    @if (trim((string) $slot) !== '')
        <div class="ds-empty-action">{{ $slot }}</div>
    @endif
</div>
