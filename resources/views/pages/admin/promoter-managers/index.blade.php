<x-layouts.app :title="__('promoter_managers.page_title')">

    <x-ds.page-header
        :title="$festival->displayName() . ' — ' . __('promoter_managers.page_title')"
        :subtitle="__('promoter_managers.index_intro')"
    />

    <x-ds.card :padded="false">
        <x-ds.table>
            <x-slot:head>
                <tr>
                    <th>{{ __('promoter_managers.list.header_manager') }}</th>
                    <th>{{ __('promoter_managers.list.header_email') }}</th>
                    <th class="text-right">{{ __('promoter_managers.list.header_overrides') }}</th>
                    <th class="text-right">{{ __('promoter_managers.list.header_default_count') }}</th>
                    <th class="text-right">{{ __('promoter_managers.list.header_actions') }}</th>
                </tr>
            </x-slot:head>
            @forelse ($managers as $m)
                <tr wire:key="m-{{ $m->id }}">
                    <td>
                        <div class="flex items-center gap-2.5">
                            <x-ds.avatar :name="$m->name" size="sm" />
                            <div class="row-title">{{ $m->name }}</div>
                        </div>
                    </td>
                    <td class="row-meta">{{ $m->email }}</td>
                    <td class="text-right num">
                        <x-ds.badge :variant="($overrideCounts[$m->pivot->id] ?? 0) > 0 ? 'accent' : 'neutral'">
                            {{ $overrideCounts[$m->pivot->id] ?? 0 }} / {{ $ticketTypeCount }}
                        </x-ds.badge>
                    </td>
                    <td class="text-right num text-[color:var(--ds-text-muted)]">
                        {{ $ticketTypeCount }}
                    </td>
                    <td>
                        <div class="row-actions">
                            <x-ds.button variant="ghost" size="sm" :href="route('admin.promoter-managers.show', ['festival' => $festival->slug, 'manager' => $m->id])" wire:navigate>
                                {{ __('promoter_managers.list.set_commissions') }}
                            </x-ds.button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-ds.empty-state
                            :title="__('promoter_managers.list.no_managers_title')"
                            :message="__('promoter_managers.list.no_managers_message')"
                        >
                            <x-ds.button variant="primary" :href="route('admin.promoters.index', $festival)" wire:navigate>
                                {{ __('promoter_managers.list.go_to_promoters') }}
                            </x-ds.button>
                        </x-ds.empty-state>
                    </td>
                </tr>
            @endforelse
        </x-ds.table>
    </x-ds.card>
</x-layouts.app>
