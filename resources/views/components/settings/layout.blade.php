<div class="flex flex-col md:flex-row gap-6 md:gap-8 max-w-4xl">
    <aside class="md:w-48 flex-shrink-0">
        <nav class="ds-stack-sm">
            <a href="{{ route('settings.profile') }}" wire:navigate
               @class([
                   'flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium',
                   request()->routeIs('settings.profile') ? 'bg-[color:var(--ds-accent-soft)] text-[color:var(--ds-accent-text)]' : 'text-[color:var(--ds-text-muted)] hover:bg-[color:var(--ds-bg-subtle)] hover:text-[color:var(--ds-text)]',
               ])>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
                {{ __('Profile') }}
            </a>
            <a href="{{ route('settings.password') }}" wire:navigate
               @class([
                   'flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium',
                   request()->routeIs('settings.password') ? 'bg-[color:var(--ds-accent-soft)] text-[color:var(--ds-accent-text)]' : 'text-[color:var(--ds-text-muted)] hover:bg-[color:var(--ds-bg-subtle)] hover:text-[color:var(--ds-text)]',
               ])>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                {{ __('Password') }}
            </a>
        </nav>
    </aside>

    <div class="flex-1 min-w-0">
        <h2 class="text-xl font-semibold text-[color:var(--ds-text)]">{{ $heading ?? '' }}</h2>
        @if (!empty($subheading))
            <p class="text-sm text-[color:var(--ds-text-muted)] mt-1 mb-6">{{ $subheading }}</p>
        @else
            <div class="mb-6"></div>
        @endif

        <div class="ds-card">
            <div class="ds-card-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
