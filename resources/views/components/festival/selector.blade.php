@php
    $user = auth()->user();
    $accessible = $user?->accessibleFestivals() ?? collect();
    $current = $current ?? request()->route('festival');
    if (is_numeric($current)) {
        $current = $accessible->firstWhere('id', (int) $current);
    } elseif (is_string($current)) {
        $current = $accessible->firstWhere('slug', $current);
    }
@endphp

@if ($accessible->count() > 0 || $user?->isSuperAdmin())
    <div class="relative" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center gap-2 px-2.5 py-2 rounded-md border border-[color:var(--ds-border)] bg-[color:var(--ds-surface)] hover:bg-[color:var(--ds-bg-subtle)] text-[13px] font-medium text-[color:var(--ds-text)] transition-colors">
            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $current?->primary_color ?? '#cbd5e1' }}"></span>
            <span class="flex-1 text-left truncate">
                {{ $current?->displayName() ?? __('Choose festival') }}
            </span>
            <svg class="w-4 h-4 text-[color:var(--ds-text-muted)] flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 8l5 5 5-5H5z"/></svg>
        </button>

        <div x-show="open" @click.outside="open = false" x-transition.opacity
             class="absolute left-0 right-0 mt-1.5 rounded-lg border border-[color:var(--ds-border)] bg-[color:var(--ds-surface)] shadow-lg z-50 overflow-hidden">
            <div class="p-1.5 text-[10px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold border-b border-[color:var(--ds-divider)]">
                {{ __('Switch festival') }}
            </div>
            <ul class="max-h-72 overflow-y-auto py-1">
                @forelse ($accessible as $f)
                    <li>
                        @php
                            $href = match (true) {
                                $user->isSuperAdmin() => route('admin.dashboard', $f),
                                $user->isAdmin()     => route('admin.dashboard', $f),
                                default              => route('promoter.dashboard', $f),
                            };
                        @endphp
                        <a href="{{ $href }}" wire:navigate
                           @class([
                               'flex items-center gap-2.5 px-3 py-2 text-[13px] hover:bg-[color:var(--ds-bg-subtle)]',
                               'bg-[color:var(--ds-accent-soft)] text-[color:var(--ds-accent-text)]' => $current && $current->id === $f->id,
                           ])>
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $f->primary_color }}"></span>
                            <span class="flex-1">
                                <span class="font-medium block text-[color:var(--ds-text)]">{{ $f->displayName() }}</span>
                                <span class="text-[11px] text-[color:var(--ds-text-muted)]">{{ __($f->status) }}{{ $f->location ? ' · ' . $f->location : '' }}</span>
                            </span>
                            @if ($current && $current->id === $f->id)
                                <svg class="w-4 h-4 text-[color:var(--ds-accent-text)]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4L8.5 12l6.8-6.7a1 1 0 011.4 0z"/></svg>
                            @endif
                        </a>
                    </li>
                @empty
                    <li class="px-3 py-2 text-[13px] text-[color:var(--ds-text-muted)]">{{ __('No festivals available.') }}</li>
                @endforelse
            </ul>
            @if ($user?->isSuperAdmin())
                <div class="p-1.5 border-t border-[color:var(--ds-divider)]">
                    <a href="{{ route('superadmin.festivals.create') }}" wire:navigate
                       class="block px-3 py-2 text-[13px] text-[color:var(--ds-accent-text)] hover:bg-[color:var(--ds-accent-soft)] rounded">
                        + {{ __('Create new festival') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif
