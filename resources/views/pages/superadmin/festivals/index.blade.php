<x-layouts.app :title="__('Festivals')">

    <x-ds.page-header
        :title="__('Festivals')"
        :subtitle="__('All festival editions on the platform.')"
    >
        <x-slot:actions>
            <x-ds.button variant="primary" :href="route('superadmin.festivals.create')" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('New festival') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.card :padded="false">
        <x-slot:body>
            {{-- Toolbar --}}
            <form method="GET" class="ds-toolbar !border-b-0 !bg-transparent">
                <div class="ds-search">
                    <svg class="ds-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" class="ds-input" placeholder="{{ __('Search festivals...') }}">
                </div>
                <select name="status" class="ds-select" style="min-width: 140px;">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (['draft', 'active', 'archived'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ __(ucfirst($s)) }}</option>
                    @endforeach
                </select>
                <x-ds.button variant="primary" size="sm" type="submit">{{ __('Filter') }}</x-ds.button>
                @if (request('search') || request('status'))
                    <x-ds.button variant="ghost" size="sm" :href="route('superadmin.festivals.index')" wire:navigate>{{ __('Clear') }}</x-ds.button>
                @endif
            </form>
        </x-slot:body>
    </x-ds.card>

    <x-ds.table class="mt-4">
        <x-slot:head>
            <tr>
                <th>{{ __('Festival') }}</th>
                <th>{{ __('Year') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="text-right">{{ __('Ticket types') }}</th>
                <th class="text-right">{{ __('Orders') }}</th>
                <th class="text-right">{{ __('Tickets') }}</th>
                <th class="text-right">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>
        @forelse ($festivals as $f)
            <tr>
                <td>
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-md border border-[color:var(--ds-border)] flex-shrink-0" title="{{ __('festivals.card_theme_swatch') }}" style="background: linear-gradient(135deg, {{ $f->primaryColor() }} 0%, {{ $f->secondaryColor() }} 100%);"></span>
                        <div class="min-w-0">
                            <div class="row-title truncate">{{ $f->displayName() }}</div>
                            <div class="row-meta truncate">{{ $f->location ?: '—' }}</div>
                        </div>
                    </div>
                </td>
                <td class="num">{{ $f->year }}</td>
                <td>
                    <x-ds.badge :variant="match($f->status) { 'active' => 'success', 'draft' => 'warning', default => 'neutral' }" dot>
                        {{ __(ucfirst($f->status)) }}
                    </x-ds.badge>
                </td>
                <td class="text-right num">{{ number_format($f->ticket_types_count) }}</td>
                <td class="text-right num">{{ number_format($f->orders_count) }}</td>
                <td class="text-right num">{{ number_format($f->tickets_count) }}</td>
                <td>
                    <div class="row-actions">
                        <x-ds.button variant="ghost" size="sm" :href="route('superadmin.festivals.assignments', $f)" wire:navigate>
                            {{ __('Users') }}
                        </x-ds.button>
                        <x-ds.button variant="ghost" size="sm" :href="route('superadmin.festivals.edit', $f)" wire:navigate>
                            {{ __('Edit') }}
                        </x-ds.button>
                        {{-- P-024: public visibility toggle --}}
                        <form action="{{ route('superadmin.festivals.toggle-public', $f) }}" method="POST">
                            @csrf
                            <x-ds.button variant="ghost" size="sm" type="submit" title="{{ $f->is_public ? __('Visible on public landing') : __('Hidden from public landing') }}">
                                @if ($f->is_public)
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                @else
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                @endif
                            </x-ds.button>
                        </form>
                        {{-- P-022: archive / restore --}}
                        @if ($f->status === 'archived')
                            <form action="{{ route('superadmin.festivals.restore', $f) }}" method="POST">
                                @csrf
                                <x-ds.button variant="ghost" size="sm" type="submit" title="{{ __('Restore') }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                </x-ds.button>
                            </form>
                        @elseif ($f->status === 'active')
                            <form action="{{ route('superadmin.festivals.archive', $f) }}" method="POST">
                                @csrf
                                <x-ds.button variant="ghost" size="sm" type="submit" title="{{ __('Archive') }}" onclick="return confirm('{{ __('Archive this festival? It will be hidden from the active picker but historical data is kept.') }}')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                                </x-ds.button>
                            </form>
                        @endif
                        @if ($f->status === 'draft')
                            <form action="{{ route('superadmin.festivals.destroy', $f) }}" method="POST" onsubmit="return confirm('{{ __('Delete this draft festival?') }}')">
                                @csrf @method('DELETE')
                                <x-ds.button variant="danger-ghost" size="sm" type="submit">
                                    {{ __('Delete') }}
                                </x-ds.button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-ds.empty-state
                        :title="__('No festivals yet')"
                        :message="__('Click the New festival button to create the first one.')"
                    >
                        <x-ds.button variant="primary" :href="route('superadmin.festivals.create')" wire:navigate>
                            {{ __('Create festival') }}
                        </x-ds.button>
                    </x-ds.empty-state>
                </td>
            </tr>
        @endforelse
    </x-ds.table>

    @if ($festivals->hasPages())
        <div class="mt-4">{{ $festivals->links() }}</div>
    @endif
</x-layouts.app>
