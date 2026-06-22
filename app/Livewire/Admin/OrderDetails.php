<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\TicketOrder;
use App\Models\Ticket; // Make sure to import your Ticket model

class OrderDetails extends Component
{
    public TicketOrder $order;
    public $totalPrice;
    public $groupedTickets = [];
    public $paid;
    public $ticketTypeFilter = 'all';
    public $selectedCodes = [];

    public $showPaidInput = false;

    protected $rules = [
        'paid' => 'required|numeric|min:0',
    ];

    protected $successs = [
        'paid.required' => 'The paid amount is required.',
        'paid.numeric' => 'The paid amount must be a number.',
        'paid.min' => 'The paid amount cannot be negative.',
    ];

    /**
     * BUG-AUDIT-003 fix: Livewire passes route parameters to mount()
     * by NAME match. The route is `/orders/{order}` so we accept
     * `$order` (not `$id`). Accepting either keeps backwards
     * compatibility with the previous signature for any inline
     * usage from other Livewire components.
     *
     * Livewire 3 also auto-resolves route-model-binding for typed
     * parameters, so when the route uses `{order}` the framework
     * may inject an actual `TicketOrder` model rather than the raw
     * id.  We accept either form.
     */
    public function mount($order = null, $id = null)
    {
        $key = $order ?? $id;
        abort_if($key === null, 404);

        // Livewire 3 may auto-resolve the route parameter as a TicketOrder
        // model via implicit route model binding. Handle either case.
        if ($key instanceof TicketOrder) {
            $orderModel = $key->loadMissing('tickets.ticketType');
        } else {
            $orderModel = TicketOrder::with('tickets.ticketType')->findOrFail($key);
        }

        $this->order = $orderModel;
        $this->paid = $orderModel->paid;
        $this->totalPrice = $orderModel->total;
        $this->groupedTickets = $orderModel->tickets->mapToGroups(function ($ticket) {
            $typeName = optional($ticket->ticketType)->name ?? 'Unknown Type';
            return [$typeName => $ticket];
        });
    }

    public function togglePaidInput()
    {
        $this->showPaidInput = !$this->showPaidInput;
        if (!$this->showPaidInput) {
            $this->paid = $this->order->paid;
            $this->clearValidation('paid');
        }
    }

    public function updatePayment()
    {
        $this->validate();
        $this->order->paid = $this->paid;
        $this->order->save();
        $this->showPaidInput = false;
        session()->flash('success', 'Payment amount updated successfully.');
    }

    public function downloadSelected()
    {
        if (empty($this->selectedCodes)) {
            session()->flash('error', 'Please select at least one ticket to download.');
            return;
        }
        return redirect()->route('admin.orders.downloadQRCodes', [
            'order' => $this->order->id,
            'selected_codes' => $this->selectedCodes,
        ]);
    }

    /**
     * Updates the 'is_active' status for the selected tickets.
     */
    public function updateSelectedTicketsActiveStatus(bool $isActive)
    {
        if (empty($this->selectedCodes)) {
            session()->flash('error', 'No tickets selected to update.');
            return;
        }

        // Assuming $this->selectedCodes contains the 'code' of the tickets.
        // Fetch the IDs of the tickets that belong to the current order and match the selected codes.
        $ticketIdsToUpdate = $this->order->tickets()
                                ->whereIn('code', $this->selectedCodes) // Or 'id' if selectedCodes stores IDs
                                ->pluck('id');

        if ($ticketIdsToUpdate->isEmpty()) {
            session()->flash('error', 'None of the selected ticket codes were found for this order.');
            return;
        }

        // Update the 'is_active' status for the identified tickets.
        // Ensure your App\Models\Ticket model has 'is_active' in its $fillable array.
        Ticket::whereIn('id', $ticketIdsToUpdate)->update(['is_active' => $isActive]);

        // Refresh the order and its tickets to reflect the changes in the component's state.
        $this->order = $this->order->fresh(['tickets.ticketType']);

        // Re-populate groupedTickets
        $this->groupedTickets = $this->order->tickets->mapToGroups(function ($ticket) {
            $typeName = optional($ticket->ticketType)->name ?? 'Unknown Type';
            return [$typeName => $ticket];
        });

        $actionVerb = $isActive ? 'activated' : 'deactivated';
        session()->flash('success', count($ticketIdsToUpdate) . ' ticket(s) have been ' . $actionVerb . '.');

        $this->selectedCodes = []; // Clear selection after action
    }

    public function render()
    {
        return view('livewire.admin.order-details');
    }
}
