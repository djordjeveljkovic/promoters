<x-layouts.app :title="__('promoter_managers.show.subtitle')">

    <x-ds.page-header
        :title="$manager->name"
        :subtitle="__('promoter_managers.show.subtitle')"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('admin.promoter-managers.index', $festival)" wire:navigate>
                ← {{ __('promoter_managers.show.back') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    @if (session('success'))
        <x-ds.alert variant="success" class="mb-4">{{ session('success') }}</x-ds.alert>
    @endif

    <x-ds.alert variant="info" class="mb-4">
        <div>
            <div class="font-semibold mb-1">{{ __('promoter_managers.show.how_works_title') }}</div>
            <p class="text-sm">{{ __('promoter_managers.show.how_works_body') }}</p>
        </div>
    </x-ds.alert>

    <form method="POST" action="{{ route('admin.promoter-managers.update', ['festival' => $festival->slug, 'manager' => $manager->id]) }}">
        @csrf
        @method('PUT')

        <x-ds.card :padded="false">
            <x-ds.table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('promoter_managers.show.header_ticket_type') }}</th>
                        <th class="text-right">{{ __('promoter_managers.show.header_default') }}</th>
                        <th class="text-right">{{ __('promoter_managers.show.header_override') }}</th>
                    </tr>
                </x-slot:head>
                @forelse ($ticketTypes as $tt)
                    @php
                        $default = $defaults[$tt->id]->commission_amount ?? 0;
                        $current = $overrides[$tt->id]->commission_amount ?? null;
                    @endphp
                    <tr wire:key="tt-{{ $tt->id }}">
                        <td class="row-title">{{ $tt->name }}</td>
                        <td class="text-right num text-[color:var(--ds-text-muted)]">
                            {{ number_format((float) $default, 2) }}
                        </td>
                        <td class="text-right" style="width: 220px;">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="99999.99"
                                name="commissions[{{ $tt->id }}]"
                                value="{{ $current !== null ? number_format((float) $current, 2, '.', '') : '' }}"
                                placeholder="{{ number_format((float) $default, 2, '.', '') }}"
                                class="ds-input num"
                                style="max-width: 200px; text-align: right;"
                            />
                            @error("commissions.{$tt->id}")
                                <div class="ds-error text-right">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <x-ds.empty-state
                                :title="__('promoter_managers.show.no_ticket_types')"
                                :message="__('promoter_managers.show.no_ticket_types_message')"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-ds.table>
        </x-ds.card>

        @if ($ticketTypes->isNotEmpty())
            <div class="mt-4 flex items-center justify-end gap-2">
                <x-ds.button variant="primary" type="submit">
                    {{ __('promoter_managers.show.save_button') }}
                </x-ds.button>
            </div>
        @endif
    </form>
</x-layouts.app>
