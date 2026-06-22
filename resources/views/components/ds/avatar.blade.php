@props([
    'name' => '',
    'size' => 'md',  // sm | md | lg
    'neutral' => false,
])

@php
    $initials = \Illuminate\Support\Str::of($name)
        ->explode(' ')
        ->take(2)
        ->map(fn ($w) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($w, 0, 1)))
        ->implode('');
@endphp

<span @class([
    'ds-avatar',
    'ds-avatar-sm' => $size === 'sm',
    'ds-avatar-lg' => $size === 'lg',
    'ds-avatar-neutral' => $neutral,
])>{{ $initials ?: '?' }}</span>
