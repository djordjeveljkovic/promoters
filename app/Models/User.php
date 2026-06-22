<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'paid',
        'parent_id',
        'bio',
        'avatar_path',
        'is_public',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function subPromoters(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Festival assignments (through the festival_user pivot).
     * Use `$user->festivalAssignments` to access the FestivalUser rows
     * directly (with role_in_festival, assigned_by, etc).
     */
    public function festivalAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FestivalUser::class);
    }

    /**
     * Festivals this user is assigned to (through the festival_user pivot).
     * Superadmins implicitly have access to every festival — see
     * {@see accessibleFestivals()}.
     */
    public function festivals(): BelongsToMany
    {
        return $this->belongsToMany(Festival::class, 'festival_user')
            ->withPivot(['role_in_festival', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Festivals the user can actually operate on right now.
     * Superadmins get a synthetic "all festivals" — but since they are
     * rare and global, we still return the explicit list and let the
     * middleware short-circuit them.
     */
    public function accessibleFestivals(): \Illuminate\Support\Collection
    {
        if ($this->isSuperAdmin()) {
            return Festival::orderByDesc('year')->orderBy('name')->get();
        }
        return $this->festivals()
            ->orderByDesc('year')
            ->orderBy('name')
            ->get();
    }

    /**
     * Per-festival role lookup, e.g. ['refest-2026' => 'promoter'].
     */
    public function festivalRoles(): array
    {
        return $this->festivals()
            ->get()
            ->mapWithKeys(fn ($f) => [$f->slug => $f->pivot->role_in_festival])
            ->toArray();
    }

    public function hasFestivalAccess(int $festivalId, ?string $role = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $query = $this->festivals()->where('festivals.id', $festivalId);
        if ($role !== null) {
            $query->wherePivot('role_in_festival', $role);
        }
        return $query->exists();
    }

    public function roleInFestival(int $festivalId): ?string
    {
        if ($this->isSuperAdmin()) {
            return 'superadmin';
        }
        $festival = $this->festivals()->where('festivals.id', $festivalId)->first();
        return $festival?->pivot?->role_in_festival;
    }

    public function ticketOrders(): HasMany
    {
        return $this->hasMany(TicketOrder::class, 'ordered_by');
    }

    public function placedOrders(): HasMany
    {
        return $this->hasMany(TicketOrder::class, 'requested_by');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(TicketCommission::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    // In your User or Promoter model (or a dedicated CommissionService)
    public static function calculateCommission(
        $ticketTypeId,
        $ticketOrderId, // Used to determine sales before this order
        $quantity,      // Quantity of the current item being calculated
        $user,          // The promoter User model instance
        \DateTimeInterface $orderCreatedAtDate // Pass the order's creation_at timestamp
    ) {
        // 1. Calculate total quantity of this ticket_type_id sold by this user in *completed* orders
        //    that were created *before* the current ticketOrderId. This establishes the baseline.
        //    Using ticketOrderId assumes IDs are sequential and reflect creation order.
        //    If not, you might need a more complex query based on created_at < $orderCreatedAtDate.
        $promoterId = is_object($user) ? $user->id : $user; // Get ID if $user is object

        $quantityPreviousOrders = TicketOrder::join('ticket_order_items', 'ticket_orders.id', '=', 'ticket_order_items.ticket_order_id')
            ->where('ticket_orders.job_status', 'completed') // Or your array of successfulSaleStatuses
            ->where('ticket_orders.id', '<', $ticketOrderId)
            ->where('ticket_order_items.ticket_type_id', $ticketTypeId)
            ->where('ticket_orders.requested_by', $promoterId)
            ->sum('ticket_order_items.quantity');

        // 2. Get all commission tiers for this ticket type, active at the time the order was created
        $commissionTiers = TicketCommission::where('ticket_type_id', $ticketTypeId)
            ->where('valid_from', '<=', $orderCreatedAtDate)
            ->where(function ($query) use ($orderCreatedAtDate) {
                $query->where('valid_to', '>=', $orderCreatedAtDate)
                    ->orWhereNull('valid_to');
            })
            ->orderBy('min_sold', 'asc')
            ->get();

        if ($commissionTiers->isEmpty()) {
            Log::warning("No active commission tiers found for ticket_type_id: {$ticketTypeId} at order creation date: " . $orderCreatedAtDate->format('Y-m-d H:i:s'));
            return 0.0;
        }

        // 3. Calculate commission based on how the current item's quantity falls into these historical tiers
        $commission = 0.0;
        // $quantityPreviousOrders is the count *before* this order's items.
        // We are calculating for a block of '$quantity' new items for the current order.

        foreach ($commissionTiers as $tier) {
            $minSoldTier = $tier->min_sold;
            // If max_sold is 0 or null, consider it effectively infinite for this tier's upper bound.
            $maxSoldTier = ($tier->max_sold === null || $tier->max_sold == 0) ? PHP_INT_MAX : $tier->max_sold;
            $commissionAmountPerUnitInTier = $tier->commission_amount;

            // Determine the range of sales numbers covered by the current order's items
            $startSaleNumberOfCurrentOrder = $quantityPreviousOrders + 1;
            $endSaleNumberOfCurrentOrder = $quantityPreviousOrders + $quantity;

            // Find the overlap between the current order's sale numbers and the tier's range
            $overlapStart = max($startSaleNumberOfCurrentOrder, $minSoldTier);
            $overlapEnd = min($endSaleNumberOfCurrentOrder, $maxSoldTier);

            if ($overlapStart <= $overlapEnd) {
                $unitsInThisTierFromCurrentOrder = $overlapEnd - $overlapStart + 1;
                $commission += $unitsInThisTierFromCurrentOrder * $commissionAmountPerUnitInTier;
                Log::info("Order {$ticketOrderId}, Item Type {$ticketTypeId}, Qty {$quantity}: Matched Tier (min:{$minSoldTier}, max:" . ($maxSoldTier == PHP_INT_MAX ? 'Inf' : $maxSoldTier) . "). Units in tier: {$unitsInThisTierFromCurrentOrder}, Comm/Unit: {$commissionAmountPerUnitInTier}");
            }
        }

        Log::info("Order {$ticketOrderId}, Item Type {$ticketTypeId}, Qty {$quantity}: Total Item Commission Calculated: {$commission} using rules from " . $orderCreatedAtDate->format('Y-m-d H:i:s'));
        return $commission;
    }

	public function hasRole($roles)
    {
        return in_array($this->role, (array) $roles);
    }

    public function isAdmin(): bool
    {
        // "admin" in the global sense = someone with full powers.
        // Superadmins are global, festival admins are scoped — see
        // {@see isFestivalAdmin()}.
        return $this->hasRole(['superadmin', 'admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isFestivalAdmin(?int $festivalId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($festivalId === null) {
            return $this->role === 'admin';
        }
        return $this->roleInFestival($festivalId) === 'admin';
    }

    public function isPromoter(?int $festivalId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($festivalId === null) {
            return $this->role === 'promoter';
        }
        $r = $this->roleInFestival($festivalId);
        return in_array($r, ['promoter', 'admin'], true);
    }

    public function isSubPromoter(?int $festivalId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($festivalId === null) {
            return $this->role === 'sub_promoter';
        }
        return $this->roleInFestival($festivalId) === 'sub_promoter';
    }

    /**
     * Is this user a "promoter manager" on the given festival (or any
     * festival when null)?  A promoter manager is a regular promoter that
     * is allowed to create their own sub-promoters and split their
     * commission with them.
     */
    public function isPromoterManager(?int $festivalId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($festivalId === null) {
            return $this->festivalAssignments()
                ->where('role_in_festival', 'promoter_manager')
                ->exists();
        }
        return $this->festivalAssignments()
            ->where('festival_id', $festivalId)
            ->where('role_in_festival', 'promoter_manager')
            ->exists();
    }

    /**
     * Convenience: returns true when the user has the plain "promoter"
     * role (not manager, not sub-promoter) on the given festival.
     */
    public function isRegularPromoter(?int $festivalId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($festivalId === null) {
            return $this->festivalAssignments()
                ->where('role_in_festival', 'promoter')
                ->exists();
        }
        return $this->festivalAssignments()
            ->where('festival_id', $festivalId)
            ->where('role_in_festival', 'promoter')
            ->exists();
    }

    /**
     * ManagerCommission overrides the admin set for this user (as a
     * promoter manager) for each ticket type.  Only relevant for users
     * with the `promoter_manager` role; falls back to TicketCommission
     * for everyone else.
     */
    public function managerCommissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasManyThrough(
            ManagerCommission::class,
            FestivalUser::class,
            'user_id',           // FK on festival_user
            'festival_user_id',  // FK on manager_commissions
            'id',                // local key on users
            'id'                 // local key on festival_user
        );
    }

    /**
     * SubPromoterCommission rows set for this user when they act as a
     * sub-promoter.
     */
    public function subPromoterCommissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasManyThrough(
            SubPromoterCommission::class,
            FestivalUser::class,
            'user_id',
            'festival_user_id',
            'id',
            'id'
        );
    }
}
