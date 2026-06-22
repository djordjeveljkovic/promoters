@props([
    'variant' => 'neutral',  // neutral | success | warning | danger | info | accent
    'size' => 'md',          // sm | md
    'dot' => false,
])

<span {{ $attributes->merge(['class' => 'ds-badge ds-badge-' . $variant . ' ' . ($size === 'sm' ? 'ds-badge-sm' : '') . ($dot ? ' ds-badge-dot' : '')]) }}>
    {{ $slot }}
</span>
