<x-layouts.app :title="__('Users')">

    <x-ds.page-header
        :title="__('Users')"
        :subtitle="__('Every person who can sign in to the platform.')"
    >
        <x-slot:actions>
            <x-ds.button variant="primary" :href="route('superadmin.users.create')" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('New user') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.card :padded="false" class="mb-4">
        <x-slot:body>
            <form method="GET" class="ds-toolbar !border-b-0 !bg-transparent">
                <div class="ds-search">
                    <svg class="ds-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" class="ds-input" placeholder="{{ __('Search by name or email...') }}">
                </div>
                <select name="role" class="ds-select" style="min-width: 140px;">
                    <option value="">{{ __('All roles') }}</option>
                    @foreach (['superadmin', 'admin', 'promoter', 'sub_promoter', 'buyer'] as $r)
                        <option value="{{ $r }}" @selected(request('role') === $r)>{{ __(ucfirst($r)) }}</option>
                    @endforeach
                </select>
                <x-ds.button variant="primary" size="sm" type="submit">{{ __('Filter') }}</x-ds.button>
            </form>
        </x-slot:body>
    </x-ds.card>

    <x-ds.table>
        <x-slot:head>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Role') }}</th>
                <th>{{ __('Festivals') }}</th>
                <th class="text-right">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>
        @forelse ($users as $user)
            <tr wire:key="u-{{ $user->id }}">
                <td>
                    <div class="flex items-center gap-2.5">
                        <x-ds.avatar :name="$user->name" size="sm" />
                        <div class="row-title">{{ $user->name }}</div>
                    </div>
                </td>
                <td class="row-meta">{{ $user->email }}</td>
                <td>
                    <x-ds.badge :variant="$user->role === 'superadmin' ? 'accent' : 'neutral'">
                        {{ __(ucfirst($user->role)) }}
                    </x-ds.badge>
                </td>
                <td class="row-meta">
                    @if ($user->festivals->isEmpty())
                        <span class="text-[color:var(--ds-text-subtle)]">—</span>
                    @else
                        {{ $user->festivals->pluck('name')->implode(', ') }}
                    @endif
                </td>
                <td>
                    <div class="row-actions">
                        <x-ds.button variant="ghost" size="sm" :href="route('superadmin.users.edit', $user)" wire:navigate>
                            {{ __('Edit') }}
                        </x-ds.button>
                        <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('{{ __('Delete this user?') }}')">
                            @csrf @method('DELETE')
                            <x-ds.button variant="danger-ghost" size="sm" type="submit">{{ __('Delete') }}</x-ds.button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-ds.empty-state
                        :title="__('No users')"
                        :message="__('Add your first user to get started.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-ds.table>

    @if ($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
    @endif
</x-layouts.app>
