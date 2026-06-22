<div class="ds-stack">

    {{-- Order Header --}}
    <x-ds.card>
        <x-slot:body>
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="text-xs uppercase tracking-wider text-[color:var(--ds-text-muted)] font-medium">{{ __('order_details.header.order_prefix') }}</div>
                        {{-- P-053: print button for the gate staff --}}
                        <button type="button" onclick="window.print()" class="ds-btn ds-btn-ghost ds-btn-sm" title="{{ __('Print') }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            {{ __('Print') }}
                        </button>
                    </div>
                    <div class="text-2xl font-semibold text-[color:var(--ds-text)] mt-1">#{{ $order->order_number ?? $order->id }}</div>
                </div>
                <div class="text-left sm:text-right w-full sm:w-auto">
                    <div class="text-sm text-[color:var(--ds-text-muted)]">{{ __('order_details.header.total_label') }}</div>
                    <div class="text-2xl font-semibold text-[color:var(--ds-text)] num">{{ number_format($totalPrice, 2) }} RSD</div>

                    @if($showPaidInput)
                        <form wire:submit.prevent="updatePayment" class="mt-3">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2">
                                <input type="text" id="paidAmount" wire:model.lazy="paid" onfocus="this.select()"
                                       class="ds-input num" style="width: 120px;">
                                <button type="submit" class="ds-btn ds-btn-primary ds-btn-sm">{{ __('order_details.payment.update_button') }}</button>
                                <button type="button" wire:click="togglePaidInput" class="ds-btn ds-btn-secondary ds-btn-sm">{{ __('order_details.payment.cancel_button') }}</button>
                            </div>
                            @error('paid') <span class="ds-error text-right block">{{ $message }}</span> @enderror
                        </form>
                    @else
                        <div class="mt-3 flex items-center justify-end gap-3">
                            <div class="text-sm">
                                <span class="text-[color:var(--ds-text-muted)]">{{ __('order_details.payment.paid_label') }}</span>
                                <span class="font-medium num text-[color:var(--ds-text)]">{{ number_format($this->order->paid, 2) }} RSD</span>
                            </div>
                            <button wire:click="togglePaidInput" class="ds-btn ds-btn-ghost ds-btn-sm">
                                {{ __('order_details.payment.edit_paid_button') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </x-slot:body>
    </x-ds.card>

    {{-- U-008: stats summary card mirroring the promoter show page. --}}
<x-ds.card>
    <x-slot:body>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <dt class="text-[10px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">{{ __('Total') }}</dt>
                <dd class="text-base font-semibold num mt-0.5">{{ number_format((float) ($order->total ?: $totalPrice), 0, ',', '.') }} RSD</dd>
            </div>
            <div>
                <dt class="text-[10px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">{{ __('Paid') }}</dt>
                <dd class="text-base font-semibold num mt-0.5">{{ number_format((float) $order->paid, 0, ',', '.') }} RSD</dd>
            </div>
            <div>
                <dt class="text-[10px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">{{ __('Commission') }}</dt>
                <dd class="text-base font-semibold num mt-0.5">
                    @if (in_array($order->job_status, ['completed', 'sent']) && isset($order->total_commission_earned))
                        {{ number_format((float) $order->total_commission_earned, 0, ',', '.') }} RSD
                    @else
                        <span class="text-xs text-[color:var(--ds-text-subtle)]">—</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-[10px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">{{ __('Tickets') }}</dt>
                <dd class="text-base font-semibold num mt-0.5">{{ $order->tickets->count() }}</dd>
            </div>
        </dl>
    </x-slot:body>
</x-ds.card>

{{-- B-002: missing rerun/email actions the promoter show has. Inline the same
     buttons here so the admin can recover from image-generation or email
     failures without leaving the page. --}}
@php
    $statusKey = $order->job_status;
    $canRerunImages = in_array($statusKey, ['failed', 'pending', 'processing', 'blocked'], true);
    $canRerunEmail  = in_array($statusKey, ['failed', 'completed', 'sent', 'processing'], true);
    $festivalSlugForActions = $order->festival?->slug ?? $order->festival_id;
@endphp
<div class="flex flex-wrap items-center gap-2">
    @if ($canRerunImages)
        <form method="POST" action="{{ route('admin.orders.rerun-image-generation', ['festival' => $festivalSlugForActions, 'order' => $order->id]) }}">
            @csrf
            <x-ds.button variant="ghost" size="sm" type="submit" title="{{ __('Re-run image generation') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><polyline points="21 3 21 8 16 8"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><polyline points="3 21 3 16 8 16"/></svg>
                {{ __('Re-run images') }}
            </x-ds.button>
        </form>
    @endif
    @if ($canRerunEmail)
        <form method="POST" action="{{ route('admin.orders.rerun-email-sending', ['festival' => $festivalSlugForActions, 'order' => $order->id]) }}">
            @csrf
            <x-ds.button variant="primary" size="sm" type="submit" title="{{ __('Resend the customer email') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><polyline points="22 6 12 13 2 6"/></svg>
                {{ __('Resend email') }}
            </x-ds.button>
        </form>
    @endif
</div>

{{-- Filter --}}
    <x-ds.field :label="__('order_details.filter.label')" name="ticketTypeFilter">
        <select wire:model.live="ticketTypeFilter" id="ticketTypeFilter" class="ds-select" style="max-width: 280px;">
            <option value="all">{{ __('order_details.filter.all_types_option') }}</option>
            @foreach($groupedTickets as $typeName => $tickets)
                <option value="{{ Str::slug($typeName) }}">{{ $typeName }}</option>
            @endforeach
        </select>
    </x-ds.field>

    {{-- Ticket cards --}}
    @if($groupedTickets->isEmpty())
        <x-ds.empty-state
            :title="__('order_details.tickets.none_found_header')"
            :message="__('order_details.tickets.none_found_message')"
        />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php $itemsDisplayedInFilter = false; @endphp
            @foreach($groupedTickets as $typeName => $tickets)
                @foreach($tickets as $ticket)
                    @php
                        $slug = Str::slug($typeName);
                        $show = $ticketTypeFilter === 'all' || $ticketTypeFilter === $slug;
                        if ($show) $itemsDisplayedInFilter = true;
                        $qrImageExistsAndPathIsValid = !empty($ticket->qr_code_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($ticket->qr_code_path);
                    @endphp
                    @if($show)
                        <x-ds.card :padded="false">
                            <div class="p-3 bg-[color:var(--ds-bg-subtle)] border-b border-[color:var(--ds-divider)] flex items-center justify-center min-h-[140px]">
                                @if($qrImageExistsAndPathIsValid)
                                    <img src="{{ asset('storage/' . $ticket->qr_code_path) }}"
                                         alt="{{ __('order_details.tickets.image_alt_prefix') }} {{ $ticket->id }}"
                                         class="max-h-32 w-auto object-contain">
                                @else
                                    <div class="text-sm text-[color:var(--ds-text-muted)] text-center px-4">
                                        {!! nl2br(e(__('order_details.tickets.qr_not_available'))) !!}
                                    </div>
                                @endif
                            </div>
                            <div class="ds-card-body">
                                <div class="font-semibold text-[color:var(--ds-text)] truncate" title="{{ __('order_details.tickets.card_title_prefix') }}{{ $ticket->id }}">
                                    {{ __('order_details.tickets.card_title_prefix') }}{{ $ticket->id }}
                                </div>
                                <div class="text-sm text-[color:var(--ds-text-muted)] truncate mt-0.5" title="{{ $ticket->ticketType->name ?? __('order_details.tickets.unknown_type') }}">
                                    {{ $ticket->ticketType->name ?? __('order_details.tickets.unknown_type') }}
                                </div>
                                <div class="mt-2 mb-3 text-sm">
                                    {{ __('order_details.tickets.status_label') }}
                                    <x-ds.badge :variant="$ticket->is_active ? 'success' : 'danger'" size="sm" dot>
                                        {{ $ticket->is_active ? __('order_details.tickets.status_active') : __('order_details.tickets.status_inactive') }}
                                    </x-ds.badge>
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" wire:model.live="selectedCodes" {{ in_array($ticket->code, $selectedCodes) ? 'checked' : '' }} value="{{ $ticket->code }}" class="ds-checkbox">
                                    <span class="text-[color:var(--ds-text-muted)] text-xs">
                                        @if(in_array($ticket->code, $selectedCodes))
                                            {{ __('order_details.tickets.select_checkbox_checked') }}
                                        @else
                                            {{ __('order_details.tickets.select_checkbox_unchecked') }}
                                        @endif
                                    </span>
                                </label>
                            </div>
                        </x-ds.card>
                    @endif
                @endforeach
            @endforeach

            @if(!$itemsDisplayedInFilter && $ticketTypeFilter !== 'all')
                <div class="col-span-full">
                    <x-ds.empty-state
                        :title="__('order_details.tickets.none_match_filter')"
                    />
                </div>
            @elseif($itemsDisplayedInFilter && false)
                {{-- placeholder for future "all hidden" state --}}
            @endif
        </div>
    @endif

    {{-- Bulk actions --}}
    @if(!$groupedTickets->isEmpty() && $groupedTickets->flatten()->isNotEmpty())
        <x-ds.card :title="__('order_details.actions.group_title')">
            <x-slot:body>
                <div class="flex flex-wrap items-center gap-2">
                    @php $dlSlug = $order->festival?->slug ?? $order->festival_id; @endphp
                    <form method="POST" action="{{ route('admin.orders.downloadQRCodes', ['festival' => $dlSlug, 'order' => $order->id]) }}">
                        @csrf
                        @foreach($selectedCodes as $code)
                            <input type="hidden" name="selected_codes[]" value="{{ $code }}">
                        @endforeach
                        <x-ds.button variant="primary" size="sm" type="submit" :disabled="empty($selectedCodes)">
                            {{ __('order_details.actions.download_selected_button') }}
                        </x-ds.button>
                    </form>

                    <form method="POST" action="{{ route('admin.orders.downloadQRCodes', ['festival' => $dlSlug, 'order' => $order->id]) }}">
                        @csrf
                        <x-ds.button variant="secondary" size="sm" type="submit">
                            {{ __('order_details.actions.download_all_button') }}
                        </x-ds.button>
                    </form>

                    <x-ds.button variant="success" size="sm" wire:click="updateSelectedTicketsActiveStatus(true)" :disabled="empty($selectedCodes)">
                        {{ __('order_details.actions.activate_selected_button') }}
                    </x-ds.button>
                    <x-ds.button variant="danger" size="sm" wire:click="updateSelectedTicketsActiveStatus(false)" :disabled="empty($selectedCodes)">
                        {{ __('order_details.actions.deactivate_selected_button') }}
                    </x-ds.button>
                </div>
            </x-slot:body>
        </x-ds.card>
    @endif
</div>
