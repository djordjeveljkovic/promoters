<x-layouts.app :title="__('Create New Ticket Order')">
    <x-ds.page-header
        :title="__('Create New Ticket Order')"
        :subtitle="$festival?->displayName() ? __('Festival') . ' · ' . $festival->displayName() : null"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('admin.orders.index', $festival)" wire:navigate>← {{ __('Back to Orders') }}</x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.orders.store', $festival) }}" id="createOrderForm" class="space-y-5">
            @csrf

            <x-ds.field :label="__('Customer Email')" name="email" :required="true" :error="$errors->first('email')">
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="ds-input" placeholder="customer@example.com" required>
            </x-ds.field>

            <div x-data="ticketOrder()" class="space-y-4 rounded-lg border border-[color:var(--ds-border)] p-4">
                <h2 class="text-base font-semibold text-[color:var(--ds-text)]">Order Items</h2>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <div class="md:col-span-6">
                        <x-ds.field label="Ticket Type" name="ticket_type_selector">
                            <select id="ticket_type_selector" class="ds-select">
                                <option value="">Select a ticket type...</option>
                                @foreach ($ticketTypes as $type)
                                    <option value="{{ $type->id }}" data-price="{{ $type->price }}" data-name="{{ $type->name }}">
                                        {{ $type->name }} (${{ number_format($type->price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </x-ds.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-ds.field label="Quantity" name="quantity_selector">
                            <input type="number" id="quantity_selector" value="1" min="1" class="ds-input">
                        </x-ds.field>
                    </div>
                    <div class="md:col-span-3">
                        <button type="button" id="addItemButton" class="ds-btn ds-btn-primary w-full">Add Item</button>
                    </div>
                </div>

                <x-ds.table :padded="false">
                    <x-slot:head>
                        <tr>
                            <th>Ticket</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                            <th class="text-right">Remove</th>
                        </tr>
                    </x-slot:head>
                    <tbody id="orderItemsTbody">
                        <tr id="noItemsRow">
                            <td colspan="5" class="text-center py-6 text-sm text-[color:var(--ds-text-muted)]">No items added yet.</td>
                        </tr>
                    </tbody>
                </x-ds.table>

                <div id="hiddenOrderItems"></div>

                <div class="pt-3 border-t border-[color:var(--ds-divider)] flex items-center justify-between">
                    <span class="text-sm font-medium text-[color:var(--ds-text-muted)]">Total</span>
                    <span id="orderTotalDisplay" class="text-xl font-semibold num text-[color:var(--ds-text)]">$0.00</span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <x-ds.button variant="secondary" :href="route('admin.orders.index', $festival)" wire:navigate>Cancel</x-ds.button>
                <x-ds.button variant="primary" type="submit" id="submitOrderButton">Place Order & Send Tickets</x-ds.button>
            </div>
        </form>
    </x-ds.card>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ticketTypeSelector = document.getElementById('ticket_type_selector');
            const quantitySelector = document.getElementById('quantity_selector');
            const addItemButton = document.getElementById('addItemButton');
            const orderItemsTbody = document.getElementById('orderItemsTbody');
            const orderTotalDisplay = document.getElementById('orderTotalDisplay');
            const hiddenOrderItemsContainer = document.getElementById('hiddenOrderItems');
            const noItemsRow = document.getElementById('noItemsRow');

            let orderItems = [];

            addItemButton.addEventListener('click', function () {
                const selectedOption = ticketTypeSelector.options[ticketTypeSelector.selectedIndex];
                const ticketTypeId = selectedOption.value;
                const ticketName = selectedOption.dataset.name;
                const unitPrice = parseFloat(selectedOption.dataset.price);
                const quantity = parseInt(quantitySelector.value);

                if (!ticketTypeId || quantity < 1) { alert('Please select a ticket type and enter a valid quantity.'); return; }

                const subtotal = quantity * unitPrice;
                orderItems.push({ ticketTypeId, name: ticketName, quantity, unitPrice, subtotal });
                renderOrderItems(); updateOrderTotal(); updateHiddenInputs();
                ticketTypeSelector.value = ''; quantitySelector.value = '1';
            });

            function renderOrderItems() {
                orderItemsTbody.innerHTML = '';
                if (orderItems.length === 0) { orderItemsTbody.appendChild(noItemsRow); return; }
                orderItems.forEach((item, index) => {
                    const row = orderItemsTbody.insertRow();
                    row.innerHTML = `
                        <td class="px-4 py-3 text-sm font-medium text-[color:var(--ds-text)]">${item.name}</td>
                        <td class="px-4 py-3 text-sm num">${item.quantity}</td>
                        <td class="px-4 py-3 text-sm num">$${item.unitPrice.toFixed(2)}</td>
                        <td class="px-4 py-3 text-sm num">$${item.subtotal.toFixed(2)}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" class="ds-btn ds-btn-ghost ds-btn-sm removeItemButton" data-index="${index}">Remove</button>
                        </td>`;
                });
                document.querySelectorAll('.removeItemButton').forEach(button => {
                    button.addEventListener('click', function () {
                        orderItems.splice(parseInt(this.dataset.index), 1);
                        renderOrderItems(); updateOrderTotal(); updateHiddenInputs();
                    });
                });
            }

            function updateOrderTotal() {
                const total = orderItems.reduce((s, i) => s + i.subtotal, 0);
                orderTotalDisplay.textContent = `$${total.toFixed(2)}`;
            }

            function updateHiddenInputs() {
                hiddenOrderItemsContainer.innerHTML = '';
                orderItems.forEach((item, index) => {
                    hiddenOrderItemsContainer.insertAdjacentHTML('beforeend', `
                        <input type="hidden" name="items[${index}][ticket_type_id]" value="${item.ticketTypeId}">
                        <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">`);
                });
            }

            if (orderItems.length === 0) orderItemsTbody.appendChild(noItemsRow);
        });
    </script>
</x-layouts.app>
