<x-layouts.app :title="__('Manage festival users')">

    <x-ds.page-header
        :title="$festival->displayName() . ' — ' . __('Users')"
        :subtitle="__('Assign admins, promoters and sub-promoters to this festival.')"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('superadmin.festivals.edit', $festival)" wire:navigate>
                ← {{ __('Back to festival') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <div class="grid lg:grid-cols-3 gap-4 mb-6">
        @php
            $sections = [
                ['title' => __('Admins'),        'users' => $festival->admins],
                ['title' => __('Promoters'),     'users' => $festival->promoters],
                ['title' => __('Sub-promoters'), 'users' => $festival->subPromoters],
            ];
        @endphp
        @foreach ($sections as $section)
            <x-ds.card :title="$section['title'] . ' (' . $section['users']->count() . ')'">
                @forelse ($section['users'] as $u)
                    <div class="flex items-center justify-between gap-2 py-2 border-b border-[color:var(--ds-divider)] last:border-b-0">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <x-ds.avatar :name="$u->name" size="sm" />
                            <div class="min-w-0">
                                <div class="text-sm font-medium truncate text-[color:var(--ds-text)]">{{ $u->name }}</div>
                                <div class="text-xs text-[color:var(--ds-text-muted)] truncate">{{ $u->email }}</div>
                            </div>
                        </div>
                        <form action="{{ route('superadmin.festivals.assignments.destroy', [$festival, $u]) }}" method="POST" onsubmit="return confirm('{{ __('Remove this user?') }}')">
                            @csrf @method('DELETE')
                            <x-ds.button variant="danger-ghost" size="sm" type="submit">{{ __('Remove') }}</x-ds.button>
                        </form>
                    </div>
                @empty
                    <div class="text-sm text-[color:var(--ds-text-muted)] text-center py-4">{{ __('No one yet.') }}</div>
                @endforelse
            </x-ds.card>
        @endforeach
    </div>

    <x-ds.card :title="__('Add a user to this festival')">
        <form action="{{ route('superadmin.festivals.assignments.store', $festival) }}" method="POST" class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[220px]">
                <x-ds.field :label="__('User')" name="user_id" :required="true">
                    <select name="user_id" required class="ds-select">
                        <option value="">— {{ __('Choose a user') }} —</option>
                        @foreach ($candidates as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }} ({{ __(ucfirst($u->role)) }})</option>
                        @endforeach
                    </select>
                </x-ds.field>
            </div>
            <div>
                <x-ds.field :label="__('Role on this festival')" name="role_in_festival" :required="true">
                    <select name="role_in_festival" required class="ds-select">
                        <option value="admin">{{ __('Admin') }}</option>
                        <option value="promoter">{{ __('Promoter') }}</option>
                        <option value="sub_promoter">{{ __('Sub-promoter') }}</option>
                    </select>
                </x-ds.field>
            </div>
            <x-ds.button variant="primary" type="submit">{{ __('Assign') }}</x-ds.button>
        </form>
    </x-ds.card>
</x-layouts.app>
