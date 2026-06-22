<x-layouts.app :title="__('Pick a festival')">
    <x-ds.page-header
        :title="__('Pick a festival')"
        :subtitle="__('You have admin access to the festivals below. Click one to manage it.')"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($festivals as $f)
            <a href="{{ route('admin.dashboard', ['festival' => $f->slug]) }}" wire:navigate
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
                    :title="__('You have no festival assignments yet.')"
                    :message="__('Ask a superadmin to assign you to a festival.')"
                >
                    <x-ds.button variant="primary" :href="route('superadmin.festivals.index')" wire:navigate>
                        {{ __('Manage festivals') }} →
                    </x-ds.button>
                </x-ds.empty-state>
            </div>
        @endforelse
    </div>
</x-layouts.app>
