@props([
    'striped' => false,
    'hover' => true,
    'compact' => false,
])

@php
    // Append a "compact" modifier hook used by the row views when needed.
    $tableClasses = collect(['ds-table'])
        ->when($compact, fn ($c) => $c->push('ds-table-compact'))
        ->implode(' ');
@endphp

<div {{ $attributes->merge(['class' => 'ds-table-wrap']) }}>
    <div class="ds-table-scroll">
        <table class="{{ $tableClasses }}">
            {{ $slot }}
        </table>
    </div>
</div>
