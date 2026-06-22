<x-layouts.app :title="__('Your festivals')">
    <x-ds.page-header
        :title="__('Your festivals')"
        :subtitle="__('Pick a festival to start selling tickets for.')"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($festivals as $f)
            <a href="{{ route('promoter.dashboard', ['festival' => $f->slug]) }}" wire:navigate
               class="ds-card hover:border-indigo-300 transition-colors block overflow-hidden">
                <div class="h-1.5" style="background: linear-gradient(90deg, {{ $f->primary_color }} 0%, {{ $f->secondary_color }} 100%);"></div>
                <div class="ds-card-body">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-[color:var(--ds-text)] truncate">{{ $f->displayName() }}</h2>
                            <p class="text-xs text-[color:var(--ds-text-muted)] truncate">{{ $f->location ?: '—' }}</p>
                        </div>
                        <x-ds.badge :variant="match($f->status) { 'active' => 'success', 'draft' => 'warning', default => 'neutral' }" size="sm" dot>
                            {{ __(ucfirst($f->status)) }}
                        </x-ds.badge>
                    </div>
                    @if ($f->tagline)
                        <p class="mt-3 text-sm text-[color:var(--ds-text-muted)] line-clamp-2">{{ $f->tagline }}</p>
                    @endif
                    <div class="mt-3 flex items-center gap-4 text-xs text-[color:var(--ds-text-muted)]">
                        <span>📦 {{ $f->ticket_types_count }} {{ __('ticket types') }}</span>
                        <span>🧾 {{ $f->orders_count }} {{ __('navigation.sidebar.sales') }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full">
                <x-ds.empty-state
                    :title="__('You have not been assigned to any festival yet.')"
                    :message="__('Contact your admin to be added to a festival.')"
                />
            </div>
        @endforelse
    </div>
</x-layouts.app>
