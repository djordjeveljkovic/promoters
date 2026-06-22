<x-layouts.app :title="__('ticket_types.page_title')">

    <x-ds.page-header
        :title="$festival?->displayName() ? $festival->displayName() . ' — ' . __('ticket_types.main_heading') : __('ticket_types.main_heading')"
        :subtitle="__('Ticket types define what promoters can sell.')"
    >
        <x-slot:actions>
            <x-ds.button variant="primary" :href="route('admin.ticket-types.create', $festival)" wire:navigate>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                {{ __('ticket_types.create_button') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.table>
        <x-slot:head>
            <tr>
                <th>{{ __('ticket_types.table.header_name') }}</th>
                <th class="text-right">{{ __('ticket_types.table.header_price') }}</th>
                <th class="hidden md:table-cell">{{ __('ticket_types.table.header_photo') }}</th>
                <th class="text-right">{{ __('ticket_types.table.header_actions') }}</th>
            </tr>
        </x-slot:head>
        @forelse ($ticketTypes as $ticketType)
            <tr wire:key="tt-{{ $ticketType->id }}">
                <td class="row-title">{{ $ticketType->name }}</td>
                <td class="text-right num font-medium">
                    <form method="POST" action="{{ route('admin.ticket-types.setPrice', ['festival' => $festival->slug, 'id' => $ticketType]) }}" class="inline-flex items-center gap-1.5 justify-end">
                        @csrf
                        @method('PUT')
                        <input
                            type="number"
                            name="price"
                            value="{{ $ticketType->price }}"
                            step="0.01" min="0"
                            class="ds-input !w-24 text-right text-sm py-1 px-2"
                            onchange="this.form.submit()"
                            aria-label="{{ __('Price for :name', ['name' => $ticketType->name]) }}"
                        >
                        <span class="text-xs text-[color:var(--ds-text-muted)]">{{ __('ticket_types.currency_symbol') }}</span>
                    </form>
                </td>
                <td class="hidden md:table-cell">
                    @if ($ticketType->photo_path)
                        <img src="{{ asset($ticketType->photo_path) }}" alt="{{ $ticketType->name }}" class="h-10 w-10 rounded-md object-cover border border-[color:var(--ds-border)]">
                    @else
                        <span class="text-xs text-[color:var(--ds-text-subtle)] italic">{{ __('ticket_types.table.no_photo') }}</span>
                    @endif
                </td>
                <td>
                    <div class="row-actions">
                        <x-ds.button variant="ghost" size="sm" :href="route('admin.ticket-types.edit', ['festival' => $festival->slug, 'id' => $ticketType])" wire:navigate>
                            {{ __('ticket_types.table.action_edit') }}
                        </x-ds.button>
                        <form action="{{ route('admin.ticket-types.destroy', ['festival' => $festival->slug, 'id' => $ticketType]) }}" method="POST" onsubmit="return confirm('{{ __('ticket_types.table.delete_confirm_message') }}');">
                            @csrf @method('DELETE')
                            <x-ds.button variant="danger-ghost" size="sm" type="submit">
                                {{ __('ticket_types.table.action_delete') }}
                            </x-ds.button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-ds.empty-state
                        :title="__('No ticket types yet')"
                        :message="__('Create one to start selling tickets for this festival.')"
                    >
                        <x-ds.button variant="primary" :href="route('admin.ticket-types.create', $festival)" wire:navigate>
                            {{ __('ticket_types.create_button') }}
                        </x-ds.button>
                    </x-ds.empty-state>
                </td>
            </tr>
        @endforelse
    </x-ds.table>

    @if ($ticketTypes->hasPages())
        <div class="mt-4">{{ $ticketTypes->links() }}</div>
    @endif
</x-layouts.app>
