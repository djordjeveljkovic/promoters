<x-layouts.auth.simple :title="$promoter->name">
    <div class="min-h-screen flex flex-col">
        {{-- Hero ----------------------------------------------------------- --}}
        <div class="relative overflow-hidden"
             style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #c026d3 100%);">
            <div class="absolute inset-0 opacity-25 pointer-events-none"
                 style="background: radial-gradient(ellipse at top right, rgba(255,255,255,0.5), transparent 60%);"></div>
            <div class="max-w-4xl mx-auto px-6 py-14 sm:py-20 relative">
                <a href="/" class="inline-flex items-center gap-2 text-white/80 hover:text-white text-sm mb-6 transition-colors">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
                    {{ __('All festivals') }}
                </a>

                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    @if ($promoter->avatar_path)
                        <img src="{{ asset($promoter->avatar_path) }}"
                             alt="{{ $promoter->name }}"
                             class="h-24 w-24 sm:h-28 sm:w-28 rounded-2xl object-cover shadow-2xl bg-white/10 backdrop-blur">
                    @else
                        <div class="h-24 w-24 sm:h-28 sm:w-28 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-2xl bg-white/15 backdrop-blur">
                            {{ mb_substr($promoter->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h1 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">{{ $promoter->name }}</h1>
                        <p class="text-white/85 text-sm mt-1">{{ __('Promoter') }}</p>
                        <p class="text-white/70 text-xs font-mono mt-1">{{ $promoter->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bio ----------------------------------------------------------- --}}
        <main class="flex-1 max-w-4xl mx-auto px-6 py-10 w-full">
            <x-ds.card :title="__('About')" class="mb-8">
                <x-slot:body>
                    @if ($promoter->bio)
                        <p class="whitespace-pre-line text-[color:var(--ds-text)] leading-relaxed">{{ $promoter->bio }}</p>
                    @else
                        <p class="text-sm text-[color:var(--ds-text-muted)] italic">
                            {{ __('This promoter hasn’t written a bio yet.') }}
                        </p>
                    @endif
                </x-slot:body>
            </x-ds.card>

            {{-- Festivals ----------------------------------------------------- --}}
            <h2 class="text-xl font-semibold text-[color:var(--ds-text)] mb-4 flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ __('Festivals') }}
                <span class="text-sm font-normal text-[color:var(--ds-text-muted)]">({{ $festivals->count() }})</span>
            </h2>

            @if ($festivals->isEmpty())
                <x-ds.empty-state
                    :title="__('No festivals published yet')"
                    :message="__('This promoter isn’t assigned to any active festival at the moment.')"
                />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($festivals as $f)
                        <a href="{{ route('public.festival', $f->slug) }}"
                           class="ds-card hover:border-[color:var(--ds-accent)] transition-colors block overflow-hidden">
                            <div class="h-1.5" style="background: linear-gradient(90deg, {{ $f->primaryColor() }} 0%, {{ $f->secondaryColor() }} 100%);"></div>
                            <div class="ds-card-body">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-base font-semibold text-[color:var(--ds-text)] truncate">{{ $f->displayName() }}</h3>
                                        <p class="text-xs text-[color:var(--ds-text-muted)] truncate">{{ $f->location ?: '—' }}</p>
                                    </div>
                                    <x-ds.badge :variant="match($f->status) { 'active' => 'success', 'draft' => 'warning', default => 'neutral' }" size="sm" dot>
                                        {{ __(ucfirst($f->status)) }}
                                    </x-ds.badge>
                                </div>
                                @if ($f->start_date)
                                    <div class="mt-3 text-xs text-[color:var(--ds-text-muted)]">
                                        {{ $f->start_date->format('d M Y') }}
                                        @if ($f->end_date && $f->end_date->ne($f->start_date))
                                            — {{ $f->end_date->format('d M Y') }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </main>

        <footer class="border-t border-[color:var(--ds-border)] py-6 text-center text-xs text-[color:var(--ds-text-muted)]">
            {{ __('Powered by Promoteri') }}
        </footer>
    </div>
</x-layouts.auth.simple>
