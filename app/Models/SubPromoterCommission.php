<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Commission the parent promoter-manager pays to a sub-promoter.
 *
 * The math:
 *   manager_payout     = max(0, manager_commission - sub_promoter_commission)
 *   sub_promoter_payout = sub_promoter_commission
 *
 * Both numbers are tracked separately so the promoter dashboard can show
 * the manager both "what you earn" and "what your sub-promoters earn".
 *
 * Historical versions are tracked through `valid_from` / `valid_to`.
 */
class SubPromoterCommission extends Model
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
     * Find the active (non-expired) commission for this sub-promoter +
     * ticket type.
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
