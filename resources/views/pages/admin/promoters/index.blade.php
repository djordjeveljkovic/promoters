<x-layouts.app :title="__('promoters.page_title')">

    <x-ds.page-header
        :title="$festival?->displayName() ? $festival->displayName() . ' — ' . __('promoters.main_heading') : __('promoters.main_heading')"
        :subtitle="__('Promoters selling for this festival.')"
    >
        <x-slot:actions>
            <x-ds.button variant="primary" :href="route('admin.promoters.create', $festival)" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('promoters.add_promoter_button') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.table>
        <x-slot:head>
            <tr>
                <th>{{ __('promoters.table.header_name') }}</th>
                <th class="hidden md:table-cell">{{ __('promoters.table.header_joined_date') }}</th>
                <th class="text-right">{{ __('promoters.table.header_tickets_sold') }}</th>
                <th class="text-right">{{ __('promoters.table.header_made_for_organizers') }}</th>
                <th class="text-right">{{ __('promoters.table.header_commission_earned') }}</th>
                <th class="text-right">{{ __('promoters.table.header_paid_to_organizers') }}</th>
                <th class="text-right">{{ __('promoters.table.header_owed_to_organizers') }}</th>
                <th class="text-right">{{ __('promoters.table.header_actions') }}</th>
            </tr>
        </x-slot:head>
        @forelse ($promoters as $promoter)
            <tr wire:key="promoter-{{ $promoter->id }}">
                <td>
                    <div class="flex items-center gap-2.5">
                        <x-ds.avatar :name="$promoter->name" size="sm" />
                        <div>
                            <div class="row-title">{{ $promoter->name }}</div>
                            <div class="row-meta">{{ $promoter->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="hidden md:table-cell num text-sm text-[color:var(--ds-text-muted)]">{{ $promoter->created_at->format('Y-m-d') }}</td>
                <td class="text-right num">{{ number_format($promoter->ticketsSoldCount ?? 0) }}</td>
                <td class="text-right num">{{ number_format($promoter->madeForOrganizers ?? 0, 2) }}</td>
                <td class="text-right num">{{ number_format($promoter->totalCommissionEarned ?? 0, 2) }}</td>
                <td class="text-right num">{{ number_format($promoter->amountPaidToOrganizers ?? 0, 2) }}</td>
                <td class="text-right num">
                    <span @class([
                        'font-semibold',
                        'text-rose-600 dark:text-rose-400' => ($promoter->amountOwedToOrganizers ?? 0) > 0,
                        'text-emerald-600 dark:text-emerald-400' => ($promoter->amountOwedToOrganizers ?? 0) <= 0,
                    ])>
                        {{ number_format($promoter->amountOwedToOrganizers ?? 0, 2) }}
                    </span>
                </td>
                <td>
                    <div class="row-actions">
                        <x-ds.button variant="ghost" size="sm" :href="route('admin.promoters.edit', ['festival' => $festival->slug, 'id' => $promoter->id])" wire:navigate>
                            {{ __('promoters.table.action_edit') }}
                        </x-ds.button>
                        <form action="{{ route('admin.promoters.destroy', ['festival' => $festival->slug, 'id' => $promoter->id]) }}" method="POST" onsubmit="return confirm('{{ __('promoters.table.delete_confirm_message') }}')">
                            @csrf @method('DELETE')
                            <x-ds.button variant="danger-ghost" size="sm" type="submit">
                                {{ __('promoters.table.action_delete') }}
                            </x-ds.button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">
                    <x-ds.empty-state
                        :title="__('promoters.table.no_promoters_header')"
                        :message="__('promoters.table.no_promoters_message')"
                    />
                </td>
            </tr>
        @endforelse
    </x-ds.table>

    @if (method_exists($promoters, 'hasPages') && $promoters->hasPages())
        <div class="mt-4">{{ $promoters->links() }}</div>
    @endif
</x-layouts.app>
