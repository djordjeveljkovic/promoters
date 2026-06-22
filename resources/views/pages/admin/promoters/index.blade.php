<x-layouts.app :title="__('promoters.page_title')">

    <x-ds.page-header
        :title="$festival?->displayName() ? $festival->displayName() . ' — ' . __('promoters.main_heading') : __('promoters.main_heading')"
        :subtitle="__('Promoters selling for this festival. Promote a promoter to manager to give them sub-promoters and a per-manager commission rate.')"
    >
        <x-slot:actions>
            <x-ds.button variant="secondary" :href="route('admin.promoter-managers.index', $festival)" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                {{ __('Manager rates') }}
            </x-ds.button>
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
                        @if ($promoter->isPromoterManager($festival->id))
                            <form action="{{ route('admin.promoters.remove-manager', ['festival' => $festival->slug, 'id' => $promoter->id]) }}" method="POST" onsubmit="return confirm('{{ __('Demote this promoter manager to a regular promoter?') }}')">
                                @csrf @method('PUT')
                                <x-ds.button variant="ghost" size="sm" type="submit" title="{{ __('Demote to regular promoter') }}">
                                    {{ __('Demote') }}
                                </x-ds.button>
                            </form>
                            <x-ds.button variant="accent" size="sm" :href="route('admin.promoter-managers.show', ['festival' => $festival->slug, 'manager' => $promoter->id])" wire:navigate>
                                {{ __('Rates') }}
                            </x-ds.button>
                        @else
                            <form action="{{ route('admin.promoters.make-manager', ['festival' => $festival->slug, 'id' => $promoter->id]) }}" method="POST" onsubmit="return confirm('{{ __('Promote this promoter to a manager? They will be able to create their own sub-promoters.') }}')">
                                @csrf @method('PUT')
                                <x-ds.button variant="ghost" size="sm" type="submit" title="{{ __('Promote to promoter manager') }}">
                                    {{ __('Make manager') }}
                                </x-ds.button>
                            </form>
                        @endif
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
