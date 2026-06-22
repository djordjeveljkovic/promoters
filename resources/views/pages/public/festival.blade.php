<x-layouts.auth.simple :title="$festival->name">
    <div class="min-h-screen flex flex-col">
        {{-- Hero ----------------------------------------------------------- --}}
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, {{ $festival->primaryColor() }} 0%, {{ $festival->secondaryColor() }} 100%);">
            <div class="absolute inset-0 opacity-20 pointer-events-none" style="background: radial-gradient(ellipse at top right, rgba(255,255,255,0.5), transparent 60%);"></div>
            <div class="max-w-5xl mx-auto px-6 py-16 sm:py-24 relative">
                <a href="/" class="inline-flex items-center gap-2 text-white/80 hover:text-white text-sm mb-8 transition-colors">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
                    {{ __('All festivals') }}
                </a>
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6">
                    <div>
                        @if ($festival->tagline)
                            <div class="text-white/80 text-sm font-medium uppercase tracking-wider mb-2">{{ $festival->tagline }}</div>
                        @endif
                        <h1 class="text-4xl sm:text-6xl font-bold text-white tracking-tight">{{ $festival->name }} <span class="text-white/70">{{ $festival->year }}</span></h1>
                        @if ($festival->location)
                            <div class="text-white/90 text-base mt-3 flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $festival->location }}
                            </div>
                        @endif
                        @if ($festival->start_date)
                            <div class="text-white/80 text-sm mt-1.5 flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $festival->start_date->format('d M Y') }}@if ($festival->end_date && $festival->end_date->ne($festival->start_date)) &mdash; {{ $festival->end_date->format('d M Y') }}@endif
                            </div>
                        @endif
                    </div>
                    @if ($festival->logo_path)
                        <img src="{{ asset($festival->logo_path) }}" alt="{{ $festival->name }}" class="h-24 w-24 sm:h-32 sm:w-32 rounded-2xl object-cover shadow-2xl bg-white/10 backdrop-blur">
                    @endif
                </div>
            </div>
        </div>

        {{-- Content --------------------------------------------------------- --}}
        <main class="flex-1 max-w-5xl mx-auto px-6 py-12 w-full">
            @if ($festival->description)
                <div class="prose max-w-none mb-12">
                    <p class="text-lg text-[color:var(--ds-text)] leading-relaxed">{{ $festival->description }}</p>
                </div>
            @endif

            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="color: {{ $festival->primaryColor() }};"><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/><path d="M3 9V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2M3 15v2a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-2"/></svg>
                {{ __('Tickets') }}
            </h2>

            @if ($ticketTypes->isEmpty())
                <x-ds.empty-state
                    :title="__('Tickets will be available soon')"
                    :message="__('The organiser hasn\'t published any ticket types yet.')"
                />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($ticketTypes as $tt)
                        <div class="ds-card overflow-hidden group hover:shadow-lg transition-shadow">
                            @if ($tt->photo_path)
                                <div class="aspect-[4/3] overflow-hidden bg-[color:var(--ds-bg-subtle)]">
                                    <img src="{{ asset($tt->photo_path) }}" alt="{{ $tt->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            @else
                                <div class="aspect-[4/3] flex items-center justify-center" style="background: linear-gradient(135deg, {{ $festival->primaryColor() }}22 0%, {{ $festival->secondaryColor() }}22 100%);">
                                    <span class="text-3xl font-bold" style="color: {{ $festival->primaryColor() }};">{{ mb_substr($tt->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="ds-card-body">
                                <h3 class="text-lg font-semibold text-[color:var(--ds-text)]">{{ $tt->name }}</h3>
                                <p class="text-xs text-[color:var(--ds-text-muted)] mt-0.5">{{ $tt->commissions->whereNull('valid_to')->count() }} {{ __('commission tiers') }}</p>
                                <div class="mt-4 flex items-center justify-between">
                                    <div>
                                        <div class="text-2xl font-bold" style="color: {{ $festival->primaryColor() }};">
                                            {{ number_format($tt->price, 0, ',', '.') }} <span class="text-sm font-normal text-[color:var(--ds-text-muted)]">RSD</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('login') }}" class="ds-btn ds-btn-primary text-sm">
                                        {{ __('Buy') }} →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </main>

        <footer class="border-t border-[color:var(--ds-border)] py-6 text-center text-xs text-[color:var(--ds-text-muted)]">
            {{ __('Powered by Promoteri') }}
        </footer>
    </div>
</x-layouts.auth.simple>
