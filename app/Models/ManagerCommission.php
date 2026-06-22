<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-manager commission override.
 *
 * The default commission a promoter manager earns for a given ticket type
 * is the matching `TicketCommission` row.  When an admin wants to give a
 * specific manager a different rate (e.g. a VIP partner gets 20% instead
 * of 10%), they create a `ManagerCommission` here.
 *
 * Historical versions are tracked through `valid_from` / `valid_to` so we
 * can recompute historical orders correctly.
 */
class ManagerCommission extends Model
{
    protected $fillable = [
        'festival_user_id',
        'ticket_type_id',
        'commission_amount',
        'valid_from',
        'valid_to',
        'set_by',
    ];

    protected $casts = [
        'commission_amount' => 'decimal:2',
        'valid_from'        => 'datetime',
        'valid_to'          => 'datetime',
    ];

    public function festivalUser(): BelongsTo
    {
        return $this->belongsTo(FestivalUser::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }

    /**
     * Find the active (non-expired) override for this manager + ticket type.
     */
    public static function activeFor(int $festivalUserId, int $ticketTypeId, ?\DateTimeInterface $at = null): ?self
    {
        $at ??= now();
        return static::query()
            ->where('festival_user_id', $festivalUserId)
            ->where('ticket_type_id', $ticketTypeId)
            ->where('valid_from', '<=', $at)
            ->where(function ($q) use ($at) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $at);
            })
            ->latest('valid_from')
            ->first();
    }
}
