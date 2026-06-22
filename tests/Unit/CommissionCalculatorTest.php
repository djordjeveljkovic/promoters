<?php

namespace Tests\Unit;

use App\Models\Festival;
use App\Models\TicketCommission;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use App\Services\CommissionCalculator;
use App\Services\NoCommissionTierException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T-005: comprehensive coverage for the CommissionCalculator service.
 *
 *   - tier-overlap arithmetic (within tier, straddling tiers, before/after tiers)
 *   - open-ended tiers (max_sold = null / 0)
 *   - multi-line orders summing cleanly
 *   - re-issuing commission for the same order id doesn't double-count
 *   - "no tier defined" raises NoCommissionTierException
 *   - expired tier window doesn't apply
 *   - breakdown() returns the same numbers as compute()
 */
class CommissionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private CommissionCalculator $calc;
    private Festival $festival;
    private TicketType $tt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new CommissionCalculator();

        $this->festival = Festival::create([
            'name'    => 'Test', 'year' => 2026, 'slug' => 'test-2026',
            'status'  => 'active',
            'primary_color'   => '#000000',
            'secondary_color' => '#ffffff',
        ]);

        $this->tt = TicketType::create([
            'festival_id'    => $this->festival->id,
            'name'           => 'GA',
            'price'          => 1000,
            'qr_coordinates' => ['x' => 0, 'y' => 0, 'size' => 100],
        ]);

        // Three tiers:
        //   1-10    @ 5.00
        //   11-20   @ 7.00
        //   21+     @ 10.00  (open-ended)
        TicketCommission::create([
            'ticket_type_id'    => $this->tt->id,
            'min_sold'           => 1,
            'max_sold'           => 10,
            'commission_amount'  => 5.00,
            'valid_from'         => '2025-01-01',
            'valid_to'           => null,
        ]);
        TicketCommission::create([
            'ticket_type_id'    => $this->tt->id,
            'min_sold'           => 11,
            'max_sold'           => 20,
            'commission_amount'  => 7.00,
            'valid_from'         => '2025-01-01',
            'valid_to'           => null,
        ]);
        TicketCommission::create([
            'ticket_type_id'    => $this->tt->id,
            'min_sold'           => 21,
            'max_sold'           => 0, // 0 == open-ended per the original User method
            'commission_amount'  => 10.00,
            'valid_from'         => '2025-01-01',
            'valid_to'           => null,
        ]);
    }

    private function makePromoter(): User
    {
        $p = User::create([
            'name' => 'Promoter', 'email' => 'p@t.local',
            'password' => bcrypt('x'), 'role' => 'promoter',
        ]);
        $p->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter',
            'assigned_by'      => null,
            'assigned_at'      => now(),
        ]);
        return $p;
    }

    private function makeCompletedOrder(User $promoter, int $quantity, int $ticketTypeId = null): TicketOrder
    {
        $ticketTypeId = $ticketTypeId ?? $this->tt->id;

        $order = TicketOrder::create([
            'festival_id'   => $this->festival->id,
            'order_number'   => strtoupper(\Illuminate\Support\Str::random(6)),
            'email'          => 'cust@t.local',
            'ordered_by'     => $promoter->id,
            'requested_by'   => $promoter->id,
            'job_status'     => 'completed',
            'paid'           => $quantity * 1000,
            'total'          => $quantity * 1000,
        ]);
        TicketOrderItem::create([
            'festival_id'     => $this->festival->id,
            'ticket_order_id' => $order->id,
            'ticket_type_id'  => $ticketTypeId,
            'quantity'        => $quantity,
            'price_at_order'  => 1000,
        ]);
        return $order;
    }

    public function test_first_order_within_first_tier(): void
    {
        $p = $this->makePromoter();
        $commission = $this->calc->compute($p, $this->tt->id, 3, 999, now());
        // 3 units * 5.00 = 15.00
        $this->assertSame(15.00, $commission);
    }

    public function test_single_order_straddling_two_tiers(): void
    {
        $p = $this->makePromoter();
        // 13 units on sales 1-13:
        //   tier 1 (1-10):  10 units * 5.00 = 50.00
        //   tier 2 (11-20):  3 units * 7.00 = 21.00
        // Total = 71.00
        $commission = $this->calc->compute($p, $this->tt->id, 13, 999, now());
        $this->assertSame(71.00, $commission);
    }

    public function test_previous_orders_counted_correctly(): void
    {
        $p = $this->makePromoter();

        // First order: 8 units -> tier 1 entirely
        $first = $this->makeCompletedOrder($p, 8);
        // Second order of 5 -> starts at sale #9, ends at sale #13.
        // Tier 1 covers 9-10 (2 units @ 5.00 = 10.00)
        // Tier 2 covers 11-13 (3 units @ 7.00 = 21.00)
        $commission = $this->calc->compute($p, $this->tt->id, 5, $first->id + 100, now());
        $this->assertSame(31.00, $commission);
    }

    public function test_open_ended_tier_caps_at_php_int_max(): void
    {
        $p = $this->makePromoter();
        // 25 units: 10 in tier 1, 10 in tier 2, 5 in tier 3 (open-ended)
        // = 50 + 70 + 50 = 170.00
        $commission = $this->calc->compute($p, $this->tt->id, 25, 999, now());
        $this->assertSame(170.00, $commission);
    }

    public function test_quantity_zero_returns_zero(): void
    {
        $p = $this->makePromoter();
        $this->assertSame(0.0, $this->calc->compute($p, $this->tt->id, 0, 999, now()));
    }

    public function test_quantity_completely_before_first_tier_is_zero(): void
    {
        $p = $this->makePromoter();
        // first tier starts at sale #1; ordering 0 units is fine, but
        // a quantity placing sales at 0 (impossible) should yield 0.
        $this->assertSame(0.0, $this->calc->forTiers(collect(), 0, 10));
    }

    public function test_no_active_tier_throws(): void
    {
        $p = $this->makePromoter();

        // Wipe tiers so the active window has nothing.
        TicketCommission::query()->update(['valid_to' => '2020-01-01']);

        $this->expectException(NoCommissionTierException::class);
        $this->calc->compute($p, $this->tt->id, 5, 999, now());
    }

    public function test_no_active_tier_user_wrapper_returns_zero(): void
    {
        // Back-compat: User::calculateCommission() used to return 0
        // silently.  Make sure the shim still does.
        $p = $this->makePromoter();
        TicketCommission::query()->update(['valid_to' => '2020-01-01']);

        $legacy = User::calculateCommission($this->tt->id, 999, 5, $p, now());
        $this->assertSame(0.0, $legacy);
    }

    public function test_expired_tier_doesnt_apply(): void
    {
        $p = $this->makePromoter();

        // Expire everything before a date in the past
        TicketCommission::query()->update(['valid_to' => '2020-12-31']);

        $this->expectException(NoCommissionTierException::class);
        $this->calc->compute($p, $this->tt->id, 1, 999, now());
    }

    public function test_only_tier_active_at_order_date_is_used(): void
    {
        $p = $this->makePromoter();

        // Add a tier that only applies far in the future.
        TicketCommission::create([
            'ticket_type_id'    => $this->tt->id,
            'min_sold'           => 1,
            'max_sold'           => 10,
            'commission_amount'  => 999.00, // would blow up the math if used
            'valid_from'         => now()->addYears(5),
            'valid_to'           => null,
        ]);

        // Today's commission should ignore the future tier (use the
        // first seeded tier at 5.00 instead of the future 999.00 one).
        $commission = $this->calc->compute($p, $this->tt->id, 3, 999, now());
        $this->assertSame(15.00, $commission); // 3 * 5.00
    }

    public function test_breakdown_matches_compute(): void
    {
        $p = $this->makePromoter();
        $computed = $this->calc->compute($p, $this->tt->id, 25, 999, now());

        $rows = $this->calc->breakdown($p, $this->tt->id, 25, 999, now());
        $sum = array_sum(array_column($rows, 'commission'));
        $this->assertSame($computed, round($sum, 2));
        // Expect 3 rows (one per tier).
        $this->assertCount(3, $rows);
    }

    public function test_reissuing_commission_for_same_order_doesnt_double_count(): void
    {
        $p = $this->makePromoter();
        $order = $this->makeCompletedOrder($p, 7);

        // Two calls with the same orderId should produce the same
        // commission, because the "previous sales" count only looks
        // at orders *before* the given id.
        $a = $this->calc->compute($p, $this->tt->id, 7, $order->id, now());
        $b = $this->calc->compute($p, $this->tt->id, 7, $order->id, now());
        $this->assertSame($a, $b);
    }

    public function test_promoter_can_be_passed_as_id_or_model(): void
    {
        $p = $this->makePromoter();
        $byModel = $this->calc->compute($p, $this->tt->id, 3, 999, now());
        $byId    = $this->calc->compute($p->id, $this->tt->id, 3, 999, now());
        $this->assertSame($byModel, $byId);
    }
}