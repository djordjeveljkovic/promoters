<x-layouts.app :title="__('orders.create_page_title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="max-w-3xl mx-auto w-full rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('orders.create_main_heading') }}</h1>
               @if(Auth::user()->role == 'promoter')
 <a href="{{ route('promoter.orders.index', $festival) }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">{!! __('orders.create_back_to_orders_link') !!}</a> 
@else
 <a href="{{ route('admin.orders.index', $festival) }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">{!! __('orders.create_back_to_orders_link') !!}</a> 
@endif
            </div>

            <form method="POST" action="{{ route('promoter.orders.store', $festival) }}" id="createOrderForm" class="space-y-6">
                @csrf

                {{-- Customer Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('orders.create_customer_email_label') }} <span class="text-red-500">*</span></label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                           placeholder="customer@example.com"
                           required />
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p> {{-- Validation messages are typically in validation.php --}}
                    @enderror
                </div>

                {{-- Add Ticket Items Section with Alpine.js --}}
                <div x-data="ticketOrder()" class="space-y-4 rounded-md border border-gray-300 p-4 dark:border-gray-600">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('orders.create_order_items_heading') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-6">
                            <label for="ticket_type_selector" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('orders.create_ticket_type_label') }}</label>
                            <select
                                id="ticket_type_selector"
                                x-model="selectedTicketId"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                            >
                                <option value="">{{ __('orders.create_select_ticket_type_option') }}</option>
                                @foreach ($ticketTypes as $type)
                                    <option value="{{ $type->id }}"
                                            data-name="{{ $type->name }}"
                                            data-price="{{ $type->price }}">
                                        {{ $type->name }} ({{ number_format($type->price, 2) }} RSD) {{-- Ticket type name and price are dynamic --}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label for="quantity_selector" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('orders.create_quantity_label') }}</label>
                            <input type="number" id="quantity_selector" min="1" x-model.number="quantity"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5" />
                        </div>

                        <div class="md:col-span-3">
                            <button
                                type="button"
                                @click="addItem"
                                class="w-full inline-flex justify-center rounded-md border border-transparent bg-green-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            >
                                {{ __('orders.create_add_item_button') }}
                            </button>
                        </div>
                    </div>

                    {{-- Table for Added Items --}}
                    <div class="mt-4 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                    <thead>
                                        <tr>
                                            <th class="py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('orders.create_items_table_header_ticket') }}</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('orders.create_items_table_header_quantity') }}</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('orders.create_items_table_header_unit_price') }}</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('orders.create_items_table_header_subtotal') }}</th>
                                            <th class="py-3.5 text-right text-sm font-semibold text-gray-900 dark:text-white">{{ __('orders.create_items_table_header_remove') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-if="items.length === 0">
                                            <tr>
                                                <td colspan="5" class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">{{ __('orders.create_no_items_message') }}</td>
                                            </tr>
                                        </template>
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="py-4 text-sm font-medium text-gray-900 dark:text-white" x-text="item.name"></td>
                                                <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-300" x-text="item.quantity"></td>
                                                <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-300" x-text="`${item.unitPrice.toFixed(2)} RSD`"></td> {{-- Currency symbol and formatting is dynamic data --}}
                                                <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-300" x-text="`${item.subtotal.toFixed(2)} RSD`"></td> {{-- Currency symbol and formatting is dynamic data --}}
                                                <td class="py-4 text-right">
                                                    <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                                            @click="removeItem(index)">{{ __('orders.create_items_table_header_remove') }}</button> {{-- Reusing remove as button text --}}
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden Inputs (This section is correctly fixed) --}}
                    <div id="hiddenOrderItems">
                        <template x-for="(item, index) in items" :key="'hidden-' + index">
                            <div>
                                <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity" />
                                <input type="hidden" :name="`items[${index}][ticket_type_id]`" :value="item.ticketTypeId" />
                            </div>
                        </template>
                    </div>

                    {{-- Order Total --}}
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <dl class="space-y-1 text-sm font-medium text-gray-900 dark:text-white">
                            <div class="flex justify-between">
                                <dt>{{ __('orders.create_total_label') }}</dt>
                                <dd x-text="`${total.toFixed(2)} RSD`" class="text-lg font-semibold"></dd> {{-- Currency symbol and formatting is dynamic data --}}
                            </div>
                        </dl>
                    </div>
                </div>


                {{-- Submit Button --}}
                <div class="flex items-center justify-end space-x-3 pt-6">
               @if(Auth::user()->role == 'promoter')
                     <a href="{{ route('promoter.orders.index', $festival) }}"
                           class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                        {{ __('orders.create_cancel_button') }}
                    </a>
@else
                     <a href="{{ route('admin.orders.index', $festival) }}"
                           class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                        {{ __('orders.create_cancel_button') }}
                    </a>
@endif
                    <button type="submit"
                            id="submitOrderButton"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('orders.create_submit_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
