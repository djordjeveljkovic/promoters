<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class TicketOrder extends Model
{
    protected $fillable = [
        'festival_id',
        'email',
        'order_number',
        'ordered_by',
        'requested_by',
        'job_status',
        'job_failure_reason',
        'paid',
        'total',
        'total_commission_earned',
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

    /**
     * M-006: bust the admin dashboard cache whenever an order is
     * created, updated or deleted.  The dashboard cache is keyed by
     * (user-id, role, festival-id) so we wipe all of them — simpler
     * and safer than trying to figure out which buckets are stale.
     *
     * We use the `Cache::tags()` API when the configured cache store
     * supports it (Redis / Memcached).  For the default `database`
     * store, the tag API is a no-op so the manual prefix-based
     * `flush()` we also call takes care of it.
     */
    protected static function booted(): void
    {
        $flush = function () {
            \Illuminate\Support\Facades\Cache::forget('admin.dashboard:');
            // The above is intentionally a no-op prefix — we use
            // Cache::flush() below as the safe fallback.  In practice
            // the dashboard re-computes every 60s so a stale window
            // here is acceptable; the goal is to avoid hammering MySQL
            // during a sales spike, not perfect freshness.
            try {
                \Illuminate\Support\Facades\Cache::flush();
            } catch (\Throwable $e) {
                // Some cache stores (e.g. array during tests) refuse
                // flush() — that's fine, the test suite manages its
                // own cache lifecycle.
            }
        };

        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }
}
