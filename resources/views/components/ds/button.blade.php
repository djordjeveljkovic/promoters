@props([
    'variant' => 'secondary',    // primary | secondary | ghost | danger | danger-ghost | link
    'size' => 'md',              // sm | md | lg
    'icon' => false,
    'href' => null,
    'type' => 'button',
    'disabled' => false,
])

@php
    $classes = collect(['ds-btn'])
        ->push('ds-btn-' . $variant)
        ->push($size !== 'md' ? 'ds-btn-' . $size : null)
        ->push($icon ? 'ds-btn-icon' : null)
        ->filter()
        ->implode(' ');

    $attrs = $attributes->except(['variant', 'size', 'icon', 'href', 'type', 'disabled']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attrs->merge(['class' => $classes]) }} @if($disabled) aria-disabled="true" @endif>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attrs->merge(['class' => $classes]) }} @disabled($disabled)>
        {{ $slot }}
    </button>
@endif
