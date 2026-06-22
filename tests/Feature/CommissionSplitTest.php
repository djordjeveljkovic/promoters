<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\ManagerCommission;
use App\Models\SubPromoterCommission;
use App\Models\TicketCommission;
use App\Models\TicketType;
use App\Models\User;
use App\Services\CommissionDistributor;
use App\Services\CommissionSplit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionSplitTest extends TestCase
{
    use RefreshDatabase;

    private Festival $festival;
    private TicketType $ticketType;
    private User $manager;
    private User $subPromoter;
    private User $plainPromoter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->festival = Festival::create([
            'name' => 'REFEST', 'year' => 2026, 'slug' => 'refest-2026',
            'status' => 'active', 'primary_color' => '#ff2d92', 'secondary_color' => '#5ce1ff',
        ]);

        $this->ticketType = new TicketType([
            'name' => 'Standard',
            'price' => 5000,
            'qr_coordinates' => ['x' => 0, 'y' => 0, 'size' => 100],
        ]);
        $this->ticketType->festival_id = $this->festival->id;
        $this->ticketType->save();

        // Default tier — everyone earns 100 RSD per ticket sold.
        TicketCommission::create([
            'ticket_type_id'    => $this->ticketType->id,
            'min_sold'          => 0,
            'max_sold'          => null,
            'commission_amount' => 100.0,
            'valid_from'        => now()->subDay(),
            'valid_to'          => null,
        ]);

        $this->manager = User::create([
            'name' => 'Manager One', 'email' => 'manager@test.rs',
            'password' => bcrypt('secret'), 'role' => 'promoter',
        ]);
        $this->manager->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter_manager',
            'assigned_by'      => null,
            'assigned_at'      => now(),
        ]);

        $this->plainPromoter = User::create([
            'name' => 'Plain Promoter', 'email' => 'plain@test.rs',
            'password' => bcrypt('secret'), 'role' => 'promoter',
        ]);
        $this->plainPromoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter',
            'assigned_by'      => null,
            'assigned_at'      => now(),
        ]);

        $this->subPromoter = User::create([
            'name' => 'Sub One', 'email' => 'sub@test.rs',
            'password' => bcrypt('secret'), 'role' => 'sub_promoter',
            'parent_id' => $this->manager->id,
        ]);
        $this->subPromoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'sub_promoter',
            'assigned_by'      => null,
            'assigned_at'      => now(),
        ]);
    }

    private function distributor(): CommissionDistributor
    {
        return app(CommissionDistributor::class);
    }

    private function managerPivot(): \App\Models\FestivalUser
    {
        return $this->manager->festivalAssignments()
            ->where('festival_id', $this->festival->id)
            ->where('role_in_festival', 'promoter_manager')
            ->firstOrFail();
    }

    private function subPivot(): \App\Models\FestivalUser
    {
        return $this->subPromoter->festivalAssignments()
            ->where('festival_id', $this->festival->id)
            ->where('role_in_festival', 'sub_promoter')
            ->firstOrFail();
    }

    /* -------------------------------------------------------------- */
    /*  Role helpers                                                    */
    /* -------------------------------------------------------------- */

    public function test_is_promoter_manager_helper(): void
    {
        $this->assertTrue($this->manager->isPromoterManager($this->festival->id));
        $this->assertFalse($this->plainPromoter->isPromoterManager($this->festival->id));
        $this->assertFalse($this->subPromoter->isPromoterManager($this->festival->id));
        $this->assertFalse($this->manager->isRegularPromoter($this->festival->id));
        $this->assertTrue($this->plainPromoter->isRegularPromoter($this->festival->id));
        $this->assertTrue($this->subPromoter->isSubPromoter($this->festival->id));
    }

    public function test_make_manager_promotes_user_to_promoter_manager(): void
    {
        $this->assertFalse($this->plainPromoter->isPromoterManager($this->festival->id));

        // Simulate what the controller does
        $this->plainPromoter->festivals()->updateExistingPivot($this->festival->id, [
            'role_in_festival' => 'promoter_manager',
        ]);

        $this->assertTrue($this->plainPromoter->fresh()->isPromoterManager($this->festival->id));
    }

    public function test_remove_manager_demotes_user_to_regular_promoter(): void
    {
        $this->assertTrue($this->manager->isPromoterManager($this->festival->id));

        $this->manager->festivals()->updateExistingPivot($this->festival->id, [
            'role_in_festival' => 'promoter',
        ]);

        $this->assertFalse($this->manager->fresh()->isPromoterManager($this->festival->id));
        $this->assertTrue($this->manager->fresh()->isRegularPromoter($this->festival->id));
    }

    /* -------------------------------------------------------------- */
    /*  Default (no overrides)                                         */
    /* -------------------------------------------------------------- */

    public function test_plain_promoter_gets_the_default_commission(): void
    {
        $split = $this->distributor()->splitForSingleTicket(
            $this->plainPromoter,
            $this->plainPromoter,
            $this->ticketType->id,
        );

        $this->assertInstanceOf(CommissionSplit::class, $split);
        $this->assertSame(100.0, $split->sellerAmount);
        $this->assertSame(0.0, $split->managerAmount);
        $this->assertFalse($split->overrodeDefault);
    }

    public function test_manager_selling_for_themselves_keeps_the_whole_commission(): void
    {
        $split = $this->distributor()->splitForSingleTicket(
            $this->manager,
            $this->manager,
            $this->ticketType->id,
        );

        $this->assertSame(100.0, $split->sellerAmount);
        $this->assertSame(0.0, $split->managerAmount);
    }

    /* -------------------------------------------------------------- */
    /*  Manager override                                                */
    /* -------------------------------------------------------------- */

    public function test_manager_override_beats_the_default(): void
    {
        ManagerCommission::create([
            'festival_user_id'   => $this->managerPivot()->id,
            'ticket_type_id'     => $this->ticketType->id,
            'commission_amount'  => 200.0,
            'valid_from'         => now()->subDay(),
            'valid_to'           => null,
        ]);

        $split = $this->distributor()->splitForSingleTicket(
            $this->manager,
            $this->manager,
            $this->ticketType->id,
        );

        $this->assertSame(200.0, $split->sellerAmount);
        $this->assertTrue($split->overrodeDefault);
    }

    public function test_expired_manager_override_falls_back_to_default(): void
    {
        ManagerCommission::create([
            'festival_user_id'   => $this->managerPivot()->id,
            'ticket_type_id'     => $this->ticketType->id,
            'commission_amount'  => 200.0,
            'valid_from'         => now()->subDays(10),
            'valid_to'           => now()->subDays(5),
        ]);

        $split = $this->distributor()->splitForSingleTicket(
            $this->manager,
            $this->manager,
            $this->ticketType->id,
        );

        $this->assertSame(100.0, $split->sellerAmount);
        $this->assertFalse($split->overrodeDefault);
    }

    /* -------------------------------------------------------------- */
    /*  Sub-promoter split                                              */
    /* -------------------------------------------------------------- */

    public function test_sub_promoter_gets_their_commission_and_manager_gets_the_rest(): void
    {
        SubPromoterCommission::create([
            'festival_user_id'   => $this->subPivot()->id,
            'ticket_type_id'     => $this->ticketType->id,
            'commission_amount'  => 30.0,
            'valid_from'         => now()->subDay(),
            'valid_to'           => null,
        ]);

        $split = $this->distributor()->splitForSingleTicket(
            $this->subPromoter,
            $this->manager,
            $this->ticketType->id,
        );

        $this->assertSame(30.0, $split->sellerAmount);
        $this->assertSame(70.0, $split->managerAmount);
        $this->assertTrue($split->overrodeManager);
    }

    public function test_sub_promoter_with_no_override_uses_the_default(): void
    {
        $split = $this->distributor()->splitForSingleTicket(
            $this->subPromoter,
            $this->manager,
            $this->ticketType->id,
        );

        $this->assertSame(100.0, $split->sellerAmount);
        $this->assertSame(0.0, $split->managerAmount);
    }

    public function test_sub_promoter_commission_capped_at_manager_commission(): void
    {
        ManagerCommission::create([
            'festival_user_id'   => $this->managerPivot()->id,
            'ticket_type_id'     => $this->ticketType->id,
            'commission_amount'  => 50.0,
            'valid_from'         => now()->subDay(),
            'valid_to'           => null,
        ]);
        SubPromoterCommission::create([
            'festival_user_id'   => $this->subPivot()->id,
            'ticket_type_id'     => $this->ticketType->id,
            'commission_amount'  => 80.0,
            'valid_from'         => now()->subDay(),
            'valid_to'           => null,
        ]);

        $split = $this->distributor()->splitForSingleTicket(
            $this->subPromoter,
            $this->manager,
            $this->ticketType->id,
        );

        // Sub-promoter is capped at the manager's commission (50 RSD).
        $this->assertSame(50.0, $split->sellerAmount);
        $this->assertSame(0.0, $split->managerAmount);
    }

    public function test_orphan_sub_promoter_falls_back_to_default(): void
    {
        $orphan = User::create([
            'name' => 'Orphan', 'email' => 'orphan@test.rs',
            'password' => bcrypt('secret'), 'role' => 'sub_promoter',
        ]);
        $orphan->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'sub_promoter',
            'assigned_by'      => null,
            'assigned_at'      => now(),
        ]);

        $split = $this->distributor()->splitForSingleTicket(
            $orphan,
            null,
            $this->ticketType->id,
        );

        $this->assertSame(100.0, $split->sellerAmount);
        $this->assertSame(0.0, $split->managerAmount);
    }

    public function test_totals_aggregate_across_quantities(): void
    {
        $totals = $this->distributor()->totalsForOrder(
            new \App\Models\TicketOrder([
                'requested_by' => $this->manager->id,
                'festival_id' => $this->festival->id,
                'job_status' => 'completed',
                'email' => 'test@test.rs',
                'created_at' => now(),
            ])
        );

        $this->assertSame(0.0, $totals['manager']);
        $this->assertSame([], $totals['sellers']);
    }

    /* -------------------------------------------------------------- */
    /*  Validation: sub-promoter commission cannot exceed manager's      */
    /* -------------------------------------------------------------- */

    public function test_validation_caps_sub_promoter_at_manager_commission(): void
    {
        ManagerCommission::create([
            'festival_user_id'   => $this->managerPivot()->id,
            'ticket_type_id'     => $this->ticketType->id,
            'commission_amount'  => 50.0,
            'valid_from'         => now()->subDay(),
            'valid_to'           => null,
        ]);

        // Try to set 80 — should be capped to 50
        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'commissions' => [
                $this->ticketType->id => 80.0,
            ],
        ]);

        $controller = new \App\Http\Controllers\Promoter\SubPromoterCommissionController();
        $manager = $this->manager;
        $request->setUserResolver(fn() => $manager);

        try {
            $controller->update($request, $this->festival, $this->subPromoter);
            $this->fail('Expected validation error to be thrown');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey("commissions.{$this->ticketType->id}", $e->errors());
        }
    }

    public function test_validation_accepts_valid_sub_promoter_commission(): void
    {
        ManagerCommission::create([
            'festival_user_id'   => $this->managerPivot()->id,
            'ticket_type_id'     => $this->ticketType->id,
            'commission_amount'  => 50.0,
            'valid_from'         => now()->subDay(),
            'valid_to'           => null,
        ]);

        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'commissions' => [
                $this->ticketType->id => 30.0,
            ],
        ]);

        $controller = new \App\Http\Controllers\Promoter\SubPromoterCommissionController();
        $request->setUserResolver(fn() => $this->manager);

        $response = $controller->update($request, $this->festival, $this->subPromoter);
        $this->assertSame(302, $response->getStatusCode());

        $this->assertDatabaseHas('sub_promoter_commissions', [
            'festival_user_id' => $this->subPivot()->id,
            'ticket_type_id'   => $this->ticketType->id,
            'commission_amount' => 30.0,
        ]);
    }
}
