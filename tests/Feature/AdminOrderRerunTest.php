<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Covers BUG-AUDIT-003 + BUG-AUDIT-005 fixes.
 *
 * Verifies that:
 *   - the Livewire order details page renders (BUG-AUDIT-003)
 *   - the admin-side "rerun image generation" endpoint exists and
 *     re-dispatches the GenerateTicketImagesJob for a failed order
 *     (BUG-AUDIT-005)
 *   - the admin-side "rerun email sending" endpoint exists and
 *     re-dispatches the SendCustomerTicketsEmailJob
 */
class AdminOrderRerunTest extends TestCase
{
    use RefreshDatabase;

    private Festival $festival;
    private User $admin;
    private TicketOrder $failedOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // Skip CSRF — these POST endpoints are still gated by the
        // role + festival-access middleware.
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $this->festival = Festival::create([
            'name' => 'REFEST', 'year' => 2026, 'slug' => 'refest-2026',
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'a@test.rs',
            'password' => bcrypt('x'), 'role' => 'admin',
        ]);
        $this->admin->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'admin', 'assigned_at' => now(),
        ]);

        $tt = TicketType::create([
            'festival_id' => $this->festival->id,
            'name' => 'GA',
            'price' => 1000,
            'qr_coordinates' => ['x' => 0, 'y' => 0, 'size' => 100],
        ]);

        $buyer = User::create([
            'name' => 'B', 'email' => 'b@test.rs',
            'password' => bcrypt('x'), 'role' => 'buyer',
        ]);
        $promoter = User::create([
            'name' => 'P', 'email' => 'p@test.rs',
            'password' => bcrypt('x'), 'role' => 'promoter',
        ]);
        $promoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter', 'assigned_at' => now(),
        ]);

        $this->failedOrder = TicketOrder::create([
            'festival_id' => $this->festival->id,
            'order_number' => 'TEST01',
            'email' => 'b@test.rs',
            'ordered_by' => $buyer->id,
            'requested_by' => $promoter->id,
            'job_status' => 'failed',
            'job_failure_reason' => 'Original GD error',
            'paid' => 1000,
            'total' => 1000,
        ]);
        Ticket::create([
            'festival_id' => $this->festival->id,
            'ticket_type_id' => $tt->id,
            'ticket_order_id' => $this->failedOrder->id,
            'code' => 'TKT-1', 'is_active' => true,
        ]);
    }

    public function test_admin_can_view_order_detail_livewire(): void
    {
        // BUG-AUDIT-003: Livewire mount() must accept $order (route param name).
        $this->actingAs($this->admin);
        $response = $this->get("/admin/festivals/{$this->festival->slug}/orders/{$this->failedOrder->id}");
        $response->assertOk();
        $response->assertSee('#TEST01');
    }

    public function test_admin_rerun_image_generation_re_dispatches_job(): void
    {
        Queue::fake();

        $this->actingAs($this->admin);
        $response = $this->post(
            "/admin/festivals/{$this->festival->slug}/orders/{$this->failedOrder->id}/rerun-image-generation"
        );

        $response->assertRedirect();
        $this->assertSame('pending', $this->failedOrder->fresh()->job_status);
        $this->assertNull($this->failedOrder->fresh()->job_failure_reason);
        Queue::assertPushed(\App\Jobs\GenerateTicketImagesJob::class, fn ($job) => $job->ticketOrderId === $this->failedOrder->id);
    }

    public function test_admin_rerun_email_sending_re_dispatches_job(): void
    {
        Queue::fake();

        $this->actingAs($this->admin);
        $response = $this->post(
            "/admin/festivals/{$this->festival->slug}/orders/{$this->failedOrder->id}/rerun-email-sending"
        );

        $response->assertRedirect();
        Queue::assertPushed(\App\Jobs\SendCustomerTicketsEmailJob::class);
    }

    public function test_admin_rerun_image_generation_rejects_wrong_festival(): void
    {
        Queue::fake();

        $other = Festival::create([
            'name' => 'Other', 'year' => 2026, 'slug' => 'other-2026',
            'status' => 'active',
        ]);
        $this->admin->festivals()->attach($other->id, [
            'role_in_festival' => 'admin', 'assigned_at' => now(),
        ]);

        $this->actingAs($this->admin);
        $response = $this->post(
            "/admin/festivals/{$other->slug}/orders/{$this->failedOrder->id}/rerun-image-generation"
        );
        // The order belongs to a different festival; admin in the new
        // festival scope has no business re-running it.
        $response->assertStatus(403);
    }
}
