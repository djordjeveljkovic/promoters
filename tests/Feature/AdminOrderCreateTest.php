<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\TicketOrder;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for B-001:
 *   Admin must be able to create an order via
 *   POST /admin/festivals/{festival}/orders and have it persist with
 *   `requested_by = admin` and `festival_id = current festival`.
 *
 *   Before the fix, `AdminOrderController::store` was empty and the
 *   admin create form posted to the promoter route instead.
 */
class AdminOrderCreateTest extends TestCase
{
    use RefreshDatabase;

    private Festival $festival;
    private User $admin;
    private TicketType $tt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->festival = Festival::create([
            'name'           => 'REFEST',
            'year'           => 2026,
            'slug'           => 'refest-2026',
            'status'         => 'active',
            'primary_color'  => '#dc2626',
            'secondary_color'=> '#fbbf24',
        ]);

        $this->admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.local',
            'password' => bcrypt('x'),
            'role'     => 'admin',
        ]);
        $this->admin->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'admin',
            'assigned_by'      => null,
            'assigned_at'      => now(),
        ]);

        $this->tt = TicketType::create([
            'festival_id'    => $this->festival->id,
            'name'           => 'General',
            'price'          => 1000,
            'qr_coordinates' => ['x' => 0, 'y' => 0, 'size' => 100],
        ]);

        $this->actingAs($this->admin);
    }

    public function test_admin_can_create_order_via_dedicated_route(): void
    {
        $r = $this->post("/admin/festivals/{$this->festival->slug}/orders", [
            'email' => 'customer@test.local',
            'items' => [['ticket_type_id' => $this->tt->id, 'quantity' => 2]],
        ]);

        // Redirect (302) on success
        $r->assertRedirect();

        $order = TicketOrder::where('email', 'customer@test.local')->first();
        $this->assertNotNull($order, 'order should be persisted');
        $this->assertSame($this->festival->id, $order->festival_id);
        $this->assertSame($this->admin->id, $order->requested_by, 'admin (not a promoter) requested the order');
        $this->assertSame(2000.0, (float) $order->total);
        $this->assertSame(2, $order->items()->sum('quantity'));
        $this->assertSame(2, $order->tickets()->count(), 'one Ticket row per quantity');
    }

    public function test_admin_create_form_posts_to_admin_route(): void
    {
        $r = $this->get("/admin/festivals/{$this->festival->slug}/order/create");
        $r->assertOk();

        $adminStore = route('admin.orders.store', ['festival' => $this->festival->slug]);
        $promoterStore = route('promoter.orders.store', ['festival' => $this->festival->slug]);

        $this->assertStringContainsString($adminStore, $r->getContent(),
            'admin create form must post to admin.orders.store');
        $this->assertStringNotContainsString($promoterStore, $r->getContent(),
            'admin create form must NOT post to promoter.orders.store (regression: B-001)');
    }

    public function test_admin_order_create_rejects_unknown_ticket_type(): void
    {
        // Create a ticket type belonging to a DIFFERENT festival and try
        // to use it from this festival's admin order create.
        $otherFestival = Festival::create([
            'name' => 'Other', 'year' => 2025, 'slug' => 'other-2025', 'status' => 'active',
        ]);
        $foreignTt = TicketType::create([
            'festival_id'    => $otherFestival->id,
            'name'           => 'Foreign',
            'price'          => 50,
            'qr_coordinates' => ['x' => 0, 'y' => 0, 'size' => 100],
        ]);

        $r = $this->from("/admin/festivals/{$this->festival->slug}/order/create")
            ->post("/admin/festivals/{$this->festival->slug}/orders", [
                'email' => 'cross@test.local',
                'items' => [['ticket_type_id' => $foreignTt->id, 'quantity' => 1]],
            ]);

        $r->assertSessionHasErrors('items');
        $this->assertSame(0, TicketOrder::where('email', 'cross@test.local')->count());
    }

    public function test_admin_create_order_requires_at_least_one_item(): void
    {
        $r = $this->post("/admin/festivals/{$this->festival->slug}/orders", [
            'email' => 'empty@test.local',
            'items' => [],
        ]);
        $r->assertSessionHasErrors('items');
    }

    public function test_admin_create_order_requires_valid_email(): void
    {
        $r = $this->post("/admin/festivals/{$this->festival->slug}/orders", [
            'email' => 'not-an-email',
            'items' => [['ticket_type_id' => $this->tt->id, 'quantity' => 1]],
        ]);
        $r->assertSessionHasErrors('email');
    }
}