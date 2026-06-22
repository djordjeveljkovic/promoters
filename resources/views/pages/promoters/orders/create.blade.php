<x-layouts.app :title="__('orders.create_page_title')">

    <x-ds.page-header
        :title="__('orders.create_main_heading')"
        :subtitle="$festival?->displayName() ? __('Festival') . ' · ' . $festival->displayName() : null"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="Auth::user()->role === 'promoter' ? route('promoter.orders.index', $festival) : route('admin.orders.index', $festival)" wire:navigate>
                ← {{ __('orders.create_back_to_orders_link') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    @if (empty($ticketTypes) || $ticketTypes->isEmpty())
        <x-ds.empty-state
            :title="__('No ticket types')"
            :message="__('Ask your admin to create ticket types for this festival first.')"
        />
    @else
        <x-ds.card class="max-w-3xl">
            <form method="POST" action="{{ route('promoter.orders.store', $festival) }}" id="createOrderForm" class="space-y-5">
                @csrf

                <x-ds.field :label="__('orders.create_customer_email_label')" name="email" :required="true" :error="$errors->first('email')">
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="ds-input" placeholder="customer@example.com" required>
                </x-ds.field>

                <div x-data="ticketOrder()" class="space-y-4 rounded-lg border border-[color:var(--ds-border)] p-4">
                    <h2 class="text-base font-semibold text-[color:var(--ds-text)]">{{ __('orders.create_order_items_heading') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-6">
                            <x-ds.field :label="__('orders.create_ticket_type_label')" name="ticket_type_selector">
                                <select id="ticket_type_selector" x-model="selectedTicketId" class="ds-select">
                                    <option value="">{{ __('orders.create_select_ticket_type_option') }}</option>
                                    @foreach ($ticketTypes as $type)
                                        <option value="{{ $type->id }}" data-name="{{ $type->name }}" data-price="{{ $type->price }}">
                                            {{ $type->name }} ({{ number_format($type->price, 2) }} RSD)
                                        </option>
                                    @endforeach
                                </select>
                            </x-ds.field>
                        </div>

                        <div class="md:col-span-3">
                            <x-ds.field :label="__('orders.create_quantity_label')" name="quantity_selector">
                                <input type="number" id="quantity_selector" min="1" x-model.number="quantity" class="ds-input">
                            </x-ds.field>
                        </div>

                        <div class="md:col-span-3">
                            <button type="button" @click="addItem" class="ds-btn ds-btn-primary w-full">
                                {{ __('orders.create_add_item_button') }}
                            </button>
                        </div>
                    </div>

                    <x-ds.table :padded="false">
                        <x-slot:head>
                            <tr>
                                <th>{{ __('orders.create_items_table_header_ticket') }}</th>
                                <th>{{ __('orders.create_items_table_header_quantity') }}</th>
                                <th>{{ __('orders.create_items_table_header_unit_price') }}</th>
                                <th>{{ __('orders.create_items_table_header_subtotal') }}</th>
                                <th class="text-right">{{ __('orders.create_items_table_header_remove') }}</th>
                            </tr>
                        </x-slot:head>
                        <template x-if="items.length === 0">
                            <tr>
                                <td colspan="5">
                                    <div class="py-6 text-center text-sm text-[color:var(--ds-text-muted)]">{{ __('orders.create_no_items_message') }}</div>
                                </td>
                            </tr>
                        </template>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td x-text="item.name"></td>
                                <td class="num" x-text="item.quantity"></td>
                                <td class="num" x-text="`${item.unitPrice.toFixed(2)} RSD`"></td>
                                <td class="num font-medium" x-text="`${item.subtotal.toFixed(2)} RSD`"></td>
                                <td class="text-right">
                                    <button type="button" class="ds-btn ds-btn-ghost ds-btn-sm" @click="removeItem(index)">{{ __('orders.create_items_table_header_remove') }}</button>
                                </td>
                            </tr>
                        </template>
                    </x-ds.table>

                    <div id="hiddenOrderItems">
                        <template x-for="(item, index) in items" :key="'hidden-' + index">
                            <div>
                                <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity" />
                                <input type="hidden" :name="`items[${index}][ticket_type_id]`" :value="item.ticketTypeId" />
                            </div>
                        </template>
                    </div>

                    <div class="pt-3 border-t border-[color:var(--ds-divider)] flex items-center justify-between">
                        <span class="text-sm font-medium text-[color:var(--ds-text-muted)]">{{ __('orders.create_total_label') }}</span>
                        <span class="text-xl font-semibold num text-[color:var(--ds-text)]" x-text="`${total.toFixed(2)} RSD`"></span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <x-ds.button variant="secondary" :href="Auth::user()->role === 'promoter' ? route('promoter.orders.index', $festival) : route('admin.orders.index', $festival)" wire:navigate>
                        {{ __('orders.create_cancel_button') }}
                    </x-ds.button>
                    <x-ds.button variant="primary" type="submit" id="submitOrderButton">
                        {{ __('orders.create_submit_button') }}
                    </x-ds.button>
                </div>
            </form>
        </x-ds.card>
    @endif
</x-layouts.app>
