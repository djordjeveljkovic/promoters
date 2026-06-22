@props([
    'label',
    'value' => null,
    'hint' => null,
    'trend' => null,           // 'up' | 'down' | null
    'trendLabel' => null,      // text shown next to the trend chip
    'icon' => null,            // optional svg path
])

<div {{ $attributes->merge(['class' => 'ds-stat']) }}>
    <div class="flex items-center justify-between gap-2">
        <div class="ds-stat-label">{{ $label }}</div>
        @if ($icon)
            <div class="text-[var(--ds-text-subtle)]">{!! $icon !!}</div>
        @endif
    </div>
    <div class="ds-stat-value num">{{ $value ?? '—' }}</div>
    @if ($hint)
        <div class="ds-stat-hint">{{ $hint }}</div>
    @endif
    @if ($trend)
        <div class="mt-2">
            <span @class(['ds-stat-trend', $trend === 'up' ? 'ds-stat-trend-up' : 'ds-stat-trend-down'])>
                @if ($trend === 'up')
                    <svg width="10" height="10" viewBox="0 0 12 12" fill="currentColor"><path d="M6 2l4 5H7v3H5V7H2l4-5z"/></svg>
                @else
                    <svg width="10" height="10" viewBox="0 0 12 12" fill="currentColor"><path d="M6 10L2 5h3V2h2v3h3l-4 5z"/></svg>
                @endif
                {{ $trendLabel }}
            </span>
        </div>
    @endif
</div>
