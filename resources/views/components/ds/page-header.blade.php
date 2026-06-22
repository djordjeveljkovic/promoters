@props([
    'title',
    'subtitle' => null,
    'back' => null,         // back url
    'breadcrumbs' => null,  // array of [label, href?]
])

<div {{ $attributes->merge(['class' => 'ds-page-header']) }}>
    <div class="min-w-0">
        @if (!empty($breadcrumbs))
            <nav class="ds-breadcrumbs">
                @foreach ($breadcrumbs as $i => $crumb)
                    @if ($i > 0)
                        <span class="sep">/</span>
                    @endif
                    @if (!empty($crumb['href']))
                        <a href="{{ $crumb['href'] }}" wire:navigate>{{ $crumb['label'] }}</a>
                    @else
                        <span>{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <h1 class="ds-page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="ds-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if (trim((string) $slot) !== '')
        <div class="ds-page-actions">
            {{ $slot }}
        </div>
    @endif
</div>
