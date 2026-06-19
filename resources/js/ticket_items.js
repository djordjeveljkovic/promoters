export default function ticketOrder() {
    return {
        items: [],
        selectedTicketId: '',
        quantity: 1, // Default quantity for new items, bound to x-model.number="quantity"

        get total() {
            return this.items.reduce((sum, item) => sum + item.subtotal, 0);
        },

        addItem() {
            console.log('[addItem] Called. Input quantity (this.quantity):', this.quantity, 'Type:', typeof this.quantity);

            // Explicitly parse the quantity from the input model.
            // x-model.number should make this.quantity a number (if valid input) or null (if empty).
            const currentInputQuantity = parseInt(this.quantity, 10);
            console.log('[addItem] Parsed currentInputQuantity:', currentInputQuantity);

            // More robust guard:
            if (!this.selectedTicketId) {
                alert('Please select a ticket type.');
                return;
            }
            if (isNaN(currentInputQuantity) || currentInputQuantity < 1) {
                alert('Please enter a valid quantity (at least 1).');
                this.quantity = 1; // Reset input model to a valid state
                return;
            }

            const selector = document.getElementById('ticket_type_selector');
            const selectedOption = selector.options[selector.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                // This case should ideally be covered by !this.selectedTicketId check if x-model is on selectedTicketId
                alert('Error: Could not retrieve ticket type details (no selected option).');
                return;
            }

            const ticketTypeId = selectedOption.value;
            const name = selectedOption.dataset.name;
            const unitPrice = parseFloat(selectedOption.dataset.price);

            if (isNaN(unitPrice)) {
                alert('Error: Ticket price is not a valid number. Please check ticket type data.');
                return;
            }

            const existing = this.items.find(item => item.ticketTypeId === ticketTypeId);

            if (existing) {
                const existingQuantityInt = parseInt(existing.quantity, 10) || 0;
                existing.quantity = existingQuantityInt + currentInputQuantity;
                existing.subtotal = existing.quantity * unitPrice;
                console.log('[addItem] Updated existing item. ID:', ticketTypeId, 'New Quantity:', existing.quantity);
            } else {
                const newItem = {
                    ticketTypeId,
                    name,
                    quantity: currentInputQuantity, // Use the validated and parsed quantity
                    unitPrice,
                    subtotal: unitPrice * currentInputQuantity
                };
                this.items.push(newItem);
                console.log('[addItem] Added new item:', JSON.parse(JSON.stringify(newItem)));
            }

            console.log('[addItem] Current items array:', JSON.parse(JSON.stringify(this.items)));

            // Reset for next item
            this.selectedTicketId = '';
            this.quantity = 1;
        },

        removeItem(index) {
            this.items.splice(index, 1);
            console.log('[removeItem] Current items array after removal:', JSON.parse(JSON.stringify(this.items)));
        },

        // Helper for debugging right before form submission
        logItemsBeforeSubmit() {
            console.log('FINAL ALPINE ITEMS BEFORE SUBMIT (from logItemsBeforeSubmit):', JSON.parse(JSON.stringify(this.items)));
            // You can also inspect 'this.items' in the Sources tab debugger if you set a breakpoint here
        }
    }
}
