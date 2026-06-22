<x-layouts.app :title="__('sub_promoters.page_title')">

    <x-ds.page-header
        :title="$subPromoter->name"
        :subtitle="__('sub_promoters.show.subtitle')"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('promoter.sub-promoters.index', $festival)" wire:navigate>
                ← {{ __('sub_promoters.show.back') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    @if (session('success'))
        <x-ds.alert variant="success" class="mb-4">{{ session('success') }}</x-ds.alert>
    @endif

    <x-ds.alert variant="warning" class="mb-4">
        <div>
            <div class="font-semibold mb-1">{{ __('sub_promoters.show.warning_title') }}</div>
            <p class="text-sm">{{ __('sub_promoters.show.warning_body') }}</p>
        </div>
    </x-ds.alert>

    <form method="POST" action="{{ route('promoter.sub-promoters.update', ['festival' => $festival->slug, 'subPromoter' => $subPromoter->id]) }}">
        @csrf
        @method('PUT')

        <x-ds.card :padded="false">
            <x-ds.table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('sub_promoters.show.header_ticket_type') }}</th>
                        <th class="text-right">{{ __('sub_promoters.show.header_your_commission') }}</th>
                        <th class="text-right">{{ __('sub_promoters.show.header_their_commission') }}</th>
                        <th class="text-right">{{ __('Your payout will be') }}</th>
                    </tr>
                </x-slot:head>
                @forelse ($ticketTypes as $tt)
                    @php
                        $managerComm = (float) ($managerOverrides[$tt->id]->commission_amount
                            ?? $defaults[$tt->id]->commission_amount
                            ?? 0);
                        $current = $overrides[$tt->id]->commission_amount ?? null;
                    @endphp
                    <tr wire:key="tt-{{ $tt->id }}" x-data="{ val: {{ $current !== null ? (float) $current : 'null' }}, manager: {{ $managerComm }} }">
                        <td class="row-title">{{ $tt->name }}</td>
                        <td class="text-right num text-[color:var(--ds-text-muted)]">
                            {{ number_format($managerComm, 2) }}
                        </td>
                        <td class="text-right" style="width: 180px;">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="{{ $managerComm }}"
                                name="commissions[{{ $tt->id }}]"
                                x-model.number="val"
                                placeholder="{{ $current !== null ? number_format((float) $current, 2, '.', '') : '0.00' }}"
                                class="ds-input num"
                                style="max-width: 160px; text-align: right;"
                            />
                            @error("commissions.{$tt->id}")
                                <div class="ds-error text-right">{{ $message }}</div>
                            @enderror
                        </td>
                        <td class="text-right num font-semibold text-[color:var(--ds-text)]">
                            <span x-text="val === null || val === '' ? manager.toFixed(2) : Math.max(0, manager - (val || 0)).toFixed(2)"></span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-ds.empty-state
                                :title="__('sub_promoters.show.no_ticket_types')"
                                :message="__('sub_promoters.show.no_ticket_types_message')"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-ds.table>
        </x-ds.card>

        @if ($ticketTypes->isNotEmpty())
            <div class="mt-4 flex items-center justify-end gap-2">
                <x-ds.button variant="primary" type="submit">
                    {{ __('sub_promoters.show.save_button') }}
                </x-ds.button>
            </div>
        @endif
    </form>
</x-layouts.app>
