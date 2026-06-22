@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'required' => false,
    'error' => null,
])

<div {{ $attributes->merge(['class' => 'ds-field']) }}>
    @if ($label)
        <label class="ds-label" for="{{ $name }}">
            {{ $label }}
            @if ($required)
                <span class="req">*</span>
            @endif
        </label>
    @endif
    {{ $slot }}
    @if ($hint && !$error)
        <div class="ds-hint">{{ $hint }}</div>
    @endif
    @if ($error)
        <div class="ds-error">{{ $error }}</div>
    @endif
</div>
