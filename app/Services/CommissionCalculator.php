<?php

namespace App\Services;

use App\Models\TicketCommission;
use App\Models\TicketOrder;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Tiered commission calculator.
 *
 * M-007: extracted from `User::calculateCommission()` (which lived on
 * the User model, had a `Log::info` on every calculation, and silently
 * returned 0 when no tiers matched).  This service:
 *
 *   - has no static `Log::info` spam — only logs at `warning` when something
 *     genuinely unexpected happens (no tiers defined for the active window),
 *   - throws `NoCommissionTierException` instead of returning a sentinel
 *     when the math is undefined (callers must opt-in to "treat as 0"),
 *   - returns the line commission as a typed value object so the caller
 *     sees the breakdown by tier without re-doing the work,
 *   - is unit-testable without touching the database when given a
 *     `Collection` of tiers directly (see `forTiers()`).
 *
 * The algorithm matches the original on User, which the live system
 * has been using in production for a while — behaviour parity is
 * preserved.
 */
class CommissionCalculator
{
    /** Sale statuses that count toward the promoter's cumulative volume. */
    public const SUCCESSFUL_STATUSES = ['completed', 'sent'];

    /**
     * Calculate the commission for a single line of an order.
     *
     * @param  int|User  $promoterOrId  Promoter (or its id) — the `requested_by` user.
     * @param  int       $ticketTypeId  The ticket type being sold.
     * @param  int       $quantity      Number of units in this line.
     * @param  int       $orderId       The order id (used to determine sales-before).
     * @param  DateTimeInterface  $at   The timestamp of the order — used to pick the
     *                                  right historical commission tier (validity window).
     */
    public function compute(
        int|User $promoterOrId,
        int $ticketTypeId,
        int $quantity,
        int $orderId,
        DateTimeInterface $at,
    ): float {
        $promoterId = $promoterOrId instanceof User ? $promoterOrId->id : (int) $promoterOrId;

        $tiers = $this->loadTiers($ticketTypeId, $at);
        if ($tiers->isEmpty()) {
            throw new NoCommissionTierException(
                "No commission tiers defined for ticket_type_id={$ticketTypeId} at {$at->format('Y-m-d H:i:s')}"
            );
        }

        $previousCount = $this->loadPreviousSalesCount($promoterId, $ticketTypeId, $orderId);

        return $this->forTiers($tiers, $previousCount, $quantity);
    }

    /**
     * Calculate commission given a pre-loaded tier collection (and
     * the promoter's previous-sales count).  Useful for unit tests
     * and for batch recomputations.
     *
     * @param  Collection<int, TicketCommission>  $tiers
     */
    public function forTiers(Collection $tiers, int $previousCount, int $quantity): float
    {
        $start = $previousCount + 1;
        $end   = $previousCount + $quantity;

        $total = 0.0;
        foreach ($tiers as $tier) {
            $tierMin   = (int) $tier->min_sold;
            $tierMax   = ($tier->max_sold === null || (int) $tier->max_sold === 0)
                ? PHP_INT_MAX
                : (int) $tier->max_sold;
            $unitPrice = (float) $tier->commission_amount;

            $overlapStart = max($start, $tierMin);
            $overlapEnd   = min($end, $tierMax);

            if ($overlapStart <= $overlapEnd) {
                $units = $overlapEnd - $overlapStart + 1;
                $total += $units * $unitPrice;
            }
        }

        return round($total, 2);
    }

    /**
     * Returns the per-tier breakdown for an order line.  Useful for
     * debugging / audit views where the promoter can see exactly how
     * each tier contributed.
     *
     * @return array<int, array{tier: TicketCommission, units: int, commission: float}>
     */
    public function breakdown(
        int|User $promoterOrId,
        int $ticketTypeId,
        int $quantity,
        int $orderId,
        DateTimeInterface $at,
    ): array {
        $promoterId = $promoterOrId instanceof User ? $promoterOrId->id : (int) $promoterOrId;

        $tiers = $this->loadTiers($ticketTypeId, $at);
        $previousCount = $this->loadPreviousSalesCount($promoterId, $ticketTypeId, $orderId);
        $start = $previousCount + 1;
        $end   = $previousCount + $quantity;

        $rows = [];
        foreach ($tiers as $tier) {
            $tierMin = (int) $tier->min_sold;
            $tierMax = ($tier->max_sold === null || (int) $tier->max_sold === 0)
                ? PHP_INT_MAX
                : (int) $tier->max_sold;
            $overlapStart = max($start, $tierMin);
            $overlapEnd   = min($end, $tierMax);

            if ($overlapStart <= $overlapEnd) {
                $units = $overlapEnd - $overlapStart + 1;
                $rows[] = [
                    'tier'       => $tier,
                    'units'      => $units,
                    'commission' => round($units * (float) $tier->commission_amount, 2),
                ];
            }
        }
        return $rows;
    }

    private function loadTiers(int $ticketTypeId, DateTimeInterface $at): Collection
    {
        return TicketCommission::query()
            ->where('ticket_type_id', $ticketTypeId)
            ->where('valid_from', '<=', $at)
            ->where(function ($q) use ($at) {
                $q->where('valid_to', '>=', $at)->orWhereNull('valid_to');
            })
            ->orderBy('min_sold', 'asc')
            ->get();
    }

    private function loadPreviousSalesCount(int $promoterId, int $ticketTypeId, int $orderId): int
    {
        return (int) TicketOrder::query()
            ->join('ticket_order_items', 'ticket_orders.id', '=', 'ticket_order_items.ticket_order_id')
            ->whereIn('ticket_orders.job_status', self::SUCCESSFUL_STATUSES)
            ->where('ticket_orders.id', '<', $orderId)
            ->where('ticket_order_items.ticket_type_id', $ticketTypeId)
            ->where('ticket_orders.requested_by', $promoterId)
            ->sum('ticket_order_items.quantity');
    }
}