@props([
    'title' => null,
    'subtitle' => null,
    'footer' => null,
    'padded' => true,
])

<div {{ $attributes->merge(['class' => 'ds-card']) }}>
    @if ($title || $subtitle || isset($actions))
        <div class="ds-card-header">
            <div>
                @if ($title)
                    <div class="ds-card-title">{{ $title }}</div>
                @endif
                @if ($subtitle)
                    <div class="ds-card-subtitle">{{ $subtitle }}</div>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class(['ds-card-body' => $padded && !isset($body) && trim($slot) !== ''])>
        @isset($body)
            <div class="ds-card-body">{{ $body }}</div>
        @else
            {{ $slot }}
        @endisset
    </div>

    @if ($footer)
        <div class="ds-card-footer">{{ $footer }}</div>
    @endif
</div>
