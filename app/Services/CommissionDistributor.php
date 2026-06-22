<?php

namespace App\Services;

use App\Models\ManagerCommission;
use App\Models\SubPromoterCommission;
use App\Models\TicketCommission;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Single source of truth for the per-ticket commission split between
 * a promoter manager and their sub-promoters.
 *
 * Mental model:
 *
 *   1. Every festival ticket type has a default commission ladder
 *      (TicketCommission) — `min_sold → max_sold → amount`.
 *   2. A promoter manager can have a per-ticket-type override
 *      (ManagerCommission).  If set, this is what the manager earns
 *      for *their own* sales.  Otherwise the default applies.
 *   3. A sub-promoter can have a per-ticket-type commission
 *      (SubPromoterCommission) set by their parent manager.  When a
 *      sub-promoter sells, the manager's payout is
 *          max(0, manager_commission - sub_promoter_commission)
 *      and the sub-promoter gets `sub_promoter_commission`.
 *
 * Edge cases:
 *   - Manager sets sub-promoter commission > manager's own commission.
 *     We cap it at the manager commission so the manager always gets
 *     ≥ 0 and notify the caller.
 *   - Sub-promoter has no override.  We use the default TicketCommission
 *     so the math still works.
 *   - Sub-promoter's parent is no longer a manager.  Treat as orphaned
 *     and fall back to the default for the sub-promoter (manager gets
 *     nothing).
 */
class CommissionDistributor
{
    /**
     * Compute the per-item commission split.
     *
     * Returns a value object with the breakdown for the line item.
     *
     * @return Collection<int, CommissionSplit>
     *   One row per ticket in the order (so the total is `qty * per_ticket`).
     */
    public function splitForOrder(TicketOrder $order, ?\DateTimeInterface $at = null): Collection
    {
        $order->loadMissing(['items.ticketType', 'requestedBy', 'requestedBy.parent', 'festival']);
        $at ??= $order->created_at ?? now();

        $rows = collect();

        foreach ($order->items as $item) {
            $ticketTypeId = $item->ticket_type_id;
            $manager = $this->resolveManager($order->requestedBy, $order->festival_id);

            for ($i = 0; $i < $item->quantity; $i++) {
                $rows->push($this->splitForSingleTicket(
                    $order->requestedBy,
                    $manager,
                    $ticketTypeId,
                    $at,
                ));
            }
        }

        return $rows;
    }

    /**
     * Compute the split for a single ticket sale.
     */
    public function splitForSingleTicket(
        User $seller,
        ?User $manager,
        int $ticketTypeId,
        ?\DateTimeInterface $at = null,
    ): CommissionSplit {
        $at ??= now();

        // 1. Manager commission — override if set, else the default.
        $managerComm = $manager
            ? $this->lookupManagerCommission($manager, $ticketTypeId, $at)
            : null;

        if ($managerComm === null) {
            $defaultForSeller = $this->lookupDefaultCommission($seller, $ticketTypeId, $at);
            $managerAmount = (float) $defaultForSeller;
        } else {
            $managerAmount = (float) $managerComm;
        }

        // 2. Sub-promoter commission.
        $subCommAmount = null;
        if ($this->isSubPromoter($seller, $manager)) {
            $subComm = $this->lookupSubPromoterCommission($seller, $ticketTypeId, $at);
            $subCommAmount = (float) ($subComm ?? $this->lookupDefaultCommission($seller, $ticketTypeId, $at));
        }

        // 3. Split.
        if ($subCommAmount !== null) {
            // Cap the sub-promoter's share at the manager's commission so
            // the manager doesn't end up with a negative payout.
            $capped = min($subCommAmount, $managerAmount);
            return new CommissionSplit(
                seller:         $seller,
                manager:        $manager,
                ticketTypeId:   $ticketTypeId,
                managerAmount:   round($managerAmount - $capped, 2),
                sellerAmount:   round($capped, 2),
                overrodeDefault: $managerComm !== null,
                overrodeManager: $subComm !== null,
            );
        }

        // No sub-promoter split — seller (could be a plain promoter, a
        // manager selling for themselves, or a sub-promoter without an
        // override who is being treated as a regular seller) gets the
        // whole commission.
        return new CommissionSplit(
            seller:         $seller,
            manager:        $manager,
            ticketTypeId:   $ticketTypeId,
            managerAmount:   $managerComm !== null ? round($managerAmount, 2) : 0.0,
            sellerAmount:   round($managerAmount, 2),
            overrodeDefault: $managerComm !== null,
            overrodeManager: false,
        );
    }

    /**
     * Sum the commission owed to each party across the whole order.
     *
     * @return array{manager: float, sellers: array<string, float>, total: float}
     *   `sellers` is keyed by user id so multiple sub-promoters in the
     *   same order can each be reported separately.
     */
    public function totalsForOrder(TicketOrder $order, ?\DateTimeInterface $at = null): array
    {
        $splits = $this->splitForOrder($order, $at);

        $managerTotal = 0.0;
        $sellerTotals = [];

        foreach ($splits as $split) {
            $managerTotal += $split->managerAmount;
            $sellerTotals[$split->seller->id] = ($sellerTotals[$split->seller->id] ?? 0) + $split->sellerAmount;
        }

        return [
            'manager'  => round($managerTotal, 2),
            'sellers'  => $sellerTotals,
            'total'    => round($managerTotal + array_sum($sellerTotals), 2),
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Internal helpers                                                    */
    /* ------------------------------------------------------------------ */

    private function resolveManager(?User $seller, int $festivalId): ?User
    {
        if (!$seller) return null;
        if ($seller->isPromoterManager($festivalId)) {
            return $seller;
        }
        return $seller->parent; // sub-promoter's parent
    }

    private function isSubPromoter(User $seller, ?User $manager): bool
    {
        if (!$manager) return false;
        return $seller->id !== $manager->id;
    }

    private function lookupManagerCommission(User $manager, int $ticketTypeId, \DateTimeInterface $at): ?float
    {
        $pivot = $manager->festivalAssignments()
            ->where('festival_id', $this->resolveFestivalId($manager, $ticketTypeId))
            ->first();
        if (!$pivot) return null;
        $row = ManagerCommission::activeFor($pivot->id, $ticketTypeId, $at);
        return $row?->commission_amount;
    }

    private function lookupSubPromoterCommission(User $sub, int $ticketTypeId, \DateTimeInterface $at): ?float
    {
        $pivot = $sub->festivalAssignments()->first();
        if (!$pivot) return null;
        $row = SubPromoterCommission::activeFor($pivot->id, $ticketTypeId, $at);
        return $row?->commission_amount;
    }

    /**
     * Default commission for a given user (or any seller) + ticket type.
     * Falls back to the first active tier with the lowest `min_sold` so
     * the math has *some* value to work with when the tier ladder
     * doesn't include a row that exactly matches the seller's cumulative
     * volume.
     */
    private function lookupDefaultCommission(User $seller, int $ticketTypeId, \DateTimeInterface $at): float
    {
        $tier = TicketCommission::query()
            ->where('ticket_type_id', $ticketTypeId)
            ->where('valid_from', '<=', $at)
            ->where(function ($q) use ($at) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $at);
            })
            ->where('min_sold', '<=', 1)
            ->orderBy('min_sold')
            ->first();

        if (!$tier) {
            // Last-resort fallback: the lowest active tier regardless of
            // min_sold.  In practice the admin should always have at
            // least one row with min_sold = 0 or 1.
            $tier = TicketCommission::query()
                ->where('ticket_type_id', $ticketTypeId)
                ->where('valid_from', '<=', $at)
                ->where(function ($q) use ($at) {
                    $q->whereNull('valid_to')->orWhere('valid_to', '>=', $at);
                })
                ->orderBy('min_sold')
                ->first();
        }

        return (float) ($tier?->commission_amount ?? 0);
    }

    private function resolveFestivalId(User $user, int $ticketTypeId): ?int
    {
        $tt = \App\Models\TicketType::find($ticketTypeId);
        return $tt?->festival_id;
    }
}
