{{-- Main container with added styling --}}
<div>

    {{-- Order Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start mb-6 pb-4 border-b border-gray-300 dark:border-slate-700">
        <div>
            <h1 class="text-3xl font-semibold text-gray-800 dark:text-slate-100">{{ __('order_details.header.order_prefix') }}{{ $order->id }}</h1>
        </div>
        <div class="text-left sm:text-right mt-4 sm:mt-0 w-full sm:w-auto">
            <p class="text-xl font-medium text-gray-700 dark:text-slate-300"><strong>{{ __('order_details.header.total_label') }}</strong> {{ number_format($totalPrice, 2) }} RSD</p>

            @if($showPaidInput)
                {{-- Payment Update Form --}}
                <form wire:submit.prevent="updatePayment" class="mt-4">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2">
                        <label for="paidAmount" class="text-sm font-medium text-gray-700 dark:text-slate-300 sr-only sm:not-sr-only sm:mb-0 mb-1">{{ __('order_details.payment.paid_amount_label') }}</label>
                        <input type="text" id="paidAmount" wire:model.lazy="paid" onfocus="this.select()"
                               class="border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-md px-3 py-1.5 text-sm w-full sm:w-24 focus:ring-yellow-500 focus:border-yellow-500 dark:focus:border-yellow-400" />
                        <button type="submit"
                                class="bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700 text-white text-sm px-4 py-1.5 rounded-md shadow-sm transition-colors">
                            {{ __('order_details.payment.update_button') }}
                        </button>
                        <button type="button" wire:click="togglePaidInput"
                                class="bg-gray-200 hover:bg-gray-300 dark:bg-slate-600 dark:hover:bg-slate-500 text-gray-700 dark:text-slate-300 text-sm px-4 py-1.5 rounded-md border border-gray-300 dark:border-slate-500 shadow-sm transition-colors">
                            {{ __('order_details.payment.cancel_button') }}
                        </button>
                    </div>
                    @error('paid') <span class="text-red-500 dark:text-red-400 text-xs mt-1 block text-right">{{ $message }}</span> @enderror {{-- Validation messages usually come from validation.php --}}
                </form>
            @else
                {{-- Display Paid Amount and Edit Trigger --}}
                <div class="mt-4 flex items-center justify-end gap-3">
                    <p class="font-medium text-gray-700 dark:text-slate-300"><strong>{{ __('order_details.payment.paid_label') }}</strong> {{ number_format($this->order->paid, 2) }} RSD</p>
                    <button wire:click="togglePaidInput"
                            class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium py-1 px-3 rounded-md hover:bg-indigo-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:ring-opacity-50 transition-colors">
                        {{ __('order_details.payment.edit_paid_button') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Dropdown Filter --}}
    <div class="mb-6">
        <label for="ticketTypeFilter" class="mr-2 text-sm font-medium text-gray-700 dark:text-slate-300">{{ __('order_details.filter.label') }}</label>
        <select wire:model.live="ticketTypeFilter" id="ticketTypeFilter" class="border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-md px-3 py-2 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400">
            <option value="all">{{ __('order_details.filter.all_types_option') }}</option>
            @foreach($groupedTickets as $typeName => $tickets)
                <option value="{{ Str::slug($typeName) }}">{{ $typeName }}</option> {{-- $typeName is dynamic, so it's not from lang file here --}}
            @endforeach
        </select>
    </div>

    {{-- Ticket Cards --}}
    @if($groupedTickets->isEmpty())
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-slate-200">{{ __('order_details.tickets.none_found_header') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ __('order_details.tickets.none_found_message') }}</p>
        </div>
    @else
        <div class="flex gap-6 h-fit flex-wrap">
            @php $itemsDisplayedInFilter = false; @endphp
            @foreach($groupedTickets as $typeName => $tickets)
                @foreach($tickets as $ticket)
                    @php $slug = Str::slug($typeName); @endphp
                    @if($ticketTypeFilter === 'all' || $ticketTypeFilter === $slug)
                        @php
                            $itemsDisplayedInFilter = true;
                            $qrImageExistsAndPathIsValid = false;
                            if (!empty($ticket->qr_code_path)) {
                                if (Illuminate\Support\Facades\Storage::disk('public')->exists($ticket->qr_code_path)) {
                                    $qrImageExistsAndPathIsValid = true;
                                }
                            }
                        @endphp
                        <div class="flex flex-col">

                            @if($qrImageExistsAndPathIsValid)
                                <div class="w-fit h-fit p-2 ">
                                    <img src="{{ asset('storage/' . $ticket->qr_code_path) }}"
                                         alt="{{ __('order_details.tickets.image_alt_prefix') }} {{ $ticket->id }}"
                                         class="w-30 h-30 object-contain">
                                </div>
                            @else
                                <div class="flex items-center justify-center bg-zinc-100 dark:bg-zinc-700 text-gray-500 dark:text-slate-400 p-2">
                                    <div class="w-30 h-30 justify-center text-center flex items-center">
                                        {!! nl2br(e(__('order_details.tickets.qr_not_available'))) !!}
                                    </div>
                                </div>
                            @endif
                            <div class="p-2">
                                <h5 class="text-lg text-gray-800 dark:text-slate-100 font-bold mb-1 truncate" title="{{ __('order_details.tickets.card_title_prefix') }}{{ $ticket->id }}">{{ __('order_details.tickets.card_title_prefix') }}{{ $ticket->id }}</h5>
                                <p class="text-sm text-gray-600 dark:text-slate-400 mb-1 truncate" title="{{ $ticket->ticketType->name ?? __('order_details.tickets.unknown_type') }}">{{ $ticket->ticketType->name ?? __('order_details.tickets.unknown_type') }}</p>

                                <p class="text-sm text-gray-600 dark:text-slate-400 mb-3">
                                    {{ __('order_details.tickets.status_label') }}<br>
                                    @if($ticket->is_active)
                                        <span class="font-semibold text-green-600 dark:text-green-400">{{ __('order_details.tickets.status_active') }}</span>
                                    @else
                                        <span class="font-semibold text-red-600 dark:text-red-400">{{ __('order_details.tickets.status_inactive') }}</span>
                                    @endif
                                </p>

                                <label class="inline-flex items-center cursor-pointer p-1 -ml-1 gap-2 rounded-md transition-colors duration-150 ease-in-out hover:bg-gray-100 dark:hover:bg-slate-700 group">
                                    <input type="checkbox"
                                           wire:model.live="selectedCodes"
                                           {{in_array($ticket->code, $selectedCodes) ? 'checked' : ''}}
                                           value="{{ $ticket->code }}"
                                           class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 appearance-auto"
                                           />
                                    <span> {{-- Added span around the text for styling if needed --}}
                                        @if(in_array($ticket->code, $selectedCodes))
                                            {{ __('order_details.tickets.select_checkbox_checked') }}
                                        @else
                                            {{ __('order_details.tickets.select_checkbox_unchecked') }}
                                        @endif
                                    </span>
                                </label>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endforeach
            @if(!$itemsDisplayedInFilter && $ticketTypeFilter !== 'all')
                <div class="col-span-full text-center py-10">
                    <svg class="mx-auto h-10 w-10 text-gray-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="mt-2 text-gray-600 dark:text-slate-400 text-lg">{{ __('order_details.tickets.none_match_filter') }}</p>
                </div>
            @elseif(!$itemsDisplayedInFilter && $groupedTickets->isNotEmpty() && $ticketTypeFilter === 'all' && $groupedTickets->flatten()->isEmpty())
                 {{-- This case implies $groupedTickets has typeName keys but empty ticket arrays within, which means no tickets at all. Should be caught by $groupedTickets->isEmpty() or $groupedTickets->flatten()->isEmpty() if $groupedTickets itself is not empty but contains no actual tickets. --}}
            @elseif(!$itemsDisplayedInFilter && $groupedTickets->isNotEmpty() && $groupedTickets->flatten()->count() > 0)
                 <div class="col-span-full text-center py-10">
                    <p class="text-gray-600 dark:text-slate-400 text-lg">{{ __('order_details.tickets.all_hidden_by_filter') }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Buttons - Wrapped in a form for POST actions as previously established --}}
    @if(!$groupedTickets->isEmpty() && $groupedTickets->flatten()->isNotEmpty())
    <div class="mt-8 pt-6 border-t border-gray-300 dark:border-slate-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-slate-200 mb-3">{{ __('order_details.actions.group_title') }}</h3>
        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('admin.orders.downloadQRCodes', ['order' => $order->id]) }}" class="inline">
                @csrf
                @foreach($selectedCodes as $code)
                    <input type="hidden" name="selected_codes[]" value="{{ $code }}">
                @endforeach
                <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-900 transition-colors disabled:opacity-50"
                        @if(empty($selectedCodes)) disabled @endif>
                    {{ __('order_details.actions.download_selected_button') }}
                </button>
            </form>

            <form method="POST" action="{{ route('admin.orders.downloadQRCodes', ['order' => $order->id]) }}" class="inline">
                @csrf
                {{-- No selected_codes for "Download All" --}}
                <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-slate-600 dark:bg-slate-500 border border-transparent rounded-md shadow-sm hover:bg-slate-700 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 dark:focus:ring-offset-slate-900 transition-colors">
                    {{ __('order_details.actions.download_all_button') }}
                </button>
            </form>

            <button type="button" wire:click="updateSelectedTicketsActiveStatus(true)"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 dark:bg-green-500 border border-transparent rounded-md shadow-sm hover:bg-green-700 dark:hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-slate-900 transition-colors disabled:opacity-50"
                    @if(empty($selectedCodes)) disabled @endif>
                {{ __('order_details.actions.activate_selected_button') }}
            </button>
            <button type="button" wire:click="updateSelectedTicketsActiveStatus(false)"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 dark:bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-slate-900 transition-colors disabled:opacity-50"
                    @if(empty($selectedCodes)) disabled @endif>
                {{ __('order_details.actions.deactivate_selected_button') }}
            </button>
        </div>
    </div>
    @endif
</div>
