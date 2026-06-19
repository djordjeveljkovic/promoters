    <x-layouts.app :title="__('Create New Ticket Order')">
        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="max-w-3xl mx-auto w-full rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create New Ticket Order</h1>
                    <a href="{{ route('admin.orders.index', $festival) }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">&larr; Back to Orders</a>
                </div>

                <form method="POST" action="{{ route('promoter.orders.store', $festival) }}" id="createOrderForm" class="space-y-6">
                    @csrf

                    {{-- Customer Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Email <span class="text-red-500">*</span></label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                               placeholder="customer@example.com"
                               required />
                        @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Add Ticket Items Section --}}
                    <div class="space-y-4 rounded-md border border-gray-300 p-4 dark:border-gray-600">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Order Items</h2>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div class="md:col-span-6">
                                <label for="ticket_type_selector" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ticket Type</label>
                                <select id="ticket_type_selector" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5">
                                    <option value="">Select a ticket type...</option>
                                    @foreach ($ticketTypes as $type)
                                        <option value="{{ $type->id }}" data-price="{{ $type->price }}" data-name="{{ $type->name }}">
                                            {{ $type->name }} (${{ number_format($type->price, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label for="quantity_selector" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                                <input type="number" id="quantity_selector" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5" />
                            </div>
                            <div class="md:col-span-3">
                                <button type="button" id="addItemButton" class="w-full inline-flex justify-center rounded-md border border-transparent bg-green-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Add Item
                                </button>
                            </div>
                        </div>

                        {{-- Table for Added Items --}}
                        <div class="mt-4 flow-root">
                            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    <table id="orderItemsTable" class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-0">Ticket</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Quantity</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Unit Price</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Subtotal</th>
                                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0"><span class="sr-only">Remove</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700" id="orderItemsTbody">
                                            {{-- Items will be added here by JavaScript --}}
                                            <tr id="noItemsRow">
                                                <td colspan="5" class="whitespace-nowrap py-4 text-center text-sm text-gray-500 dark:text-gray-400">No items added yet.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                         @error('items') {{-- For validation error on the items array --}}
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Hidden inputs for items will be populated by JS --}}
                    <div id="hiddenOrderItems"></div>

                    {{-- Order Total --}}
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <dl class="space-y-1 text-sm font-medium text-gray-900 dark:text-white">
                            <div class="flex justify-between">
                                <dt>Total</dt>
                                <dd id="orderTotalDisplay" class="text-lg font-semibold">$0.00</dd>
                            </div>
                        </dl>
                    </div>


                    {{-- Submit Button --}}
                    <div class="flex items-center justify-end space-x-3 pt-6">
                         <a href="{{ route('admin.orders.index', $festival) }}"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                            Cancel
                        </a>
                        <button type="submit"
                                id="submitOrderButton"
                                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            Place Order & Send Tickets
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ticketTypeSelector = document.getElementById('ticket_type_selector');
            const quantitySelector = document.getElementById('quantity_selector');
            const addItemButton = document.getElementById('addItemButton');
            const orderItemsTbody = document.getElementById('orderItemsTbody');
            const orderTotalDisplay = document.getElementById('orderTotalDisplay');
            const hiddenOrderItemsContainer = document.getElementById('hiddenOrderItems');
            const noItemsRow = document.getElementById('noItemsRow');
            const form = document.getElementById('createOrderForm');

            let orderItems = []; // Array to store { ticketTypeId, name, quantity, unitPrice, subtotal }

            addItemButton.addEventListener('click', function () {
                const selectedOption = ticketTypeSelector.options[ticketTypeSelector.selectedIndex];
                const ticketTypeId = selectedOption.value;
                const ticketName = selectedOption.dataset.name;
                const unitPrice = parseFloat(selectedOption.dataset.price);
                const quantity = parseInt(quantitySelector.value);

                if (!ticketTypeId || quantity < 1) {
                    alert('Please select a ticket type and enter a valid quantity.');
                    return;
                }

                // Check if item already exists, if so, update quantity (optional)
                const existingItemIndex = orderItems.findIndex(item => item.ticketTypeId === ticketTypeId);
                if (existingItemIndex > -1) {
                     // For simplicity, we'll just add as a new line.
                     // Or you could update: orderItems[existingItemIndex].quantity += quantity;
                     // orderItems[existingItemIndex].subtotal = orderItems[existingItemIndex].quantity * unitPrice;
                     // For now, let's allow adding the same ticket type multiple times as separate line items.
                }

                const subtotal = quantity * unitPrice;
                orderItems.push({ ticketTypeId, name: ticketName, quantity, unitPrice, subtotal });

                renderOrderItems();
                updateOrderTotal();
                updateHiddenInputs();

                // Reset selectors
                ticketTypeSelector.value = "";
                quantitySelector.value = "1";
            });

            function renderOrderItems() {
                orderItemsTbody.innerHTML = ''; // Clear existing rows
                if (orderItems.length === 0) {
                    orderItemsTbody.appendChild(noItemsRow);
                    return;
                }

                orderItems.forEach((item, index) => {
                    const row = orderItemsTbody.insertRow();
                    row.innerHTML = `
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-0">${item.name}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">${item.quantity}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">$${item.unitPrice.toFixed(2)}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">$${item.subtotal.toFixed(2)}</td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                            <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 removeItemButton" data-index="${index}">Remove</button>
                        </td>
                    `;
                });

                document.querySelectorAll('.removeItemButton').forEach(button => {
                    button.addEventListener('click', function () {
                        const itemIndex = parseInt(this.dataset.index);
                        orderItems.splice(itemIndex, 1);
                        renderOrderItems();
                        updateOrderTotal();
                        updateHiddenInputs();
                    });
                });
            }

            function updateOrderTotal() {
                const total = orderItems.reduce((sum, item) => sum + item.subtotal, 0);
                orderTotalDisplay.textContent = `$${total.toFixed(2)}`;
            }

            function updateHiddenInputs() {
                hiddenOrderItemsContainer.innerHTML = ''; // Clear previous hidden inputs
                orderItems.forEach((item, index) => {
                    const ticketIdInput = document.createElement('input');
                    ticketIdInput.type = 'hidden';
                    ticketIdInput.name = `items[${index}][ticket_type_id]`;
                    ticketIdInput.value = item.ticketTypeId;
                    hiddenOrderItemsContainer.appendChild(ticketIdInput);

                    const quantityInput = document.createElement('input');
                    quantityInput.type = 'hidden';
                    quantityInput.name = `items[${index}][quantity]`;
                    quantityInput.value = item.quantity;
                    hiddenOrderItemsContainer.appendChild(quantityInput);
                });
            }

            // Initial render in case of validation errors repopulating the form (more advanced)
            // For now, it starts empty.
             if (orderItems.length > 0) {
                renderOrderItems();
                updateOrderTotal();
                updateHiddenInputs();
            } else {
                 orderItemsTbody.appendChild(noItemsRow);
            }
        });
    </script>
    </x-layouts.app>

