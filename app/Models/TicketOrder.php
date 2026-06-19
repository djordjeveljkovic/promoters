<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class TicketOrder extends Model
{
    protected $fillable = [
        'email',
        'order_number',
        'ordered_by',
        'requested_by',
        'job_status',
        'job_failure_reason',
        'paid',
        'total',
    ];

    // Existing Relationships //

    /**
     * Get the items associated with the ticket order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TicketOrderItem::class);
    }

    /**
     * Get the user who placed the order (the customer).
     */
    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }
    // Alias if you prefer 'customer' relationship name
    public function customer(): BelongsTo
    {
        return $this->orderedBy();
    }


    /**
     * Get the user who requested/created the order (e.g., promoter, admin).
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get all individual tickets associated with the ticket order.
     */
    public function tickets(): HasMany
    {
        // Assumes the foreign key on the 'tickets' table is 'ticket_order_id'
        return $this->hasMany(Ticket::class);
    }

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    public function userCommission()
    {
        // PRIMARY: Return already calculated and stored commission.
        if (isset($this->total_commission_earned) && $this->total_commission_earned !== null) {
            return $this->total_commission_earned;
        }

        // FALLBACK/CALCULATION (should be called by the storing mechanism, not typically for display)
        Log::warning("Dynamically calculating commission for Order ID: {$this->id} as total_commission_earned is not set. This should occur during order finalization.");
        $commission = 0.0;
        $promoter = $this->requestedBy; // Ensure 'requestedBy' relationship is eager loaded if called frequently

        if (!$promoter) {
            Log::error("Promoter not loaded for TicketOrder ID: {$this->id}. Cannot calculate commission.");
            return 0.0;
        }
        $promoterModelClass = get_class($promoter);

        foreach ($this->items as $item) { // Ensure 'items' relationship is eager loaded
            if (method_exists($promoterModelClass, 'calculateCommission')) {
                $commission += $promoterModelClass::calculateCommission(
                    $item->ticket_type_id,
                    $this->id,
                    $item->quantity,
                    $promoter,
                    $this->created_at // CRITICAL: Pass the order's creation date
                );
            } else {
                Log::warning("Static method calculateCommission not found on class {$promoterModelClass}.");
            }
        }
        return $commission;
    }
}
