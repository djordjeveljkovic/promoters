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
                    <div class="row-title">{{ $f->displayName() }}</div>
                    <div class="row-meta">{{ $f->location ?: '—' }}</div>
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
