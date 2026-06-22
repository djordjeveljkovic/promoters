<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_scanner(): void
    {
        $f = Festival::create(['name' => 'F', 'year' => 2026, 'slug' => 'f-2026', 'status' => 'active']);
        $a = User::create(['name' => 'A', 'email' => 'a@t.test', 'password' => bcrypt('x'), 'role' => 'admin']);
        $a->festivals()->attach($f->id, ['role_in_festival' => 'admin', 'assigned_by' => null, 'assigned_at' => now()]);
        $this->actingAs($a);

        $r = $this->get("/admin/festivals/{$f->slug}/scan");
        $r->assertOk();
        $r->assertSee('scanner');
    }

    public function test_scanner_marks_ticket_as_scanned(): void
    {
        $f = Festival::create(['name' => 'F', 'year' => 2026, 'slug' => 'f-2026', 'status' => 'active']);
        $tt = TicketType::create(['festival_id' => $f->id, 'name' => 'GA', 'price' => 1000, 'qr_coordinates' => ['x'=>0,'y'=>0,'size'=>100]]);
        $buyer = User::create(['name' => 'B', 'email' => 'b@t.test', 'password' => bcrypt('x'), 'role' => 'buyer']);
        $promoter = User::create(['name' => 'P', 'email' => 'p@t.test', 'password' => bcrypt('x'), 'role' => 'promoter']);
        $promoter->festivals()->attach($f->id, ['role_in_festival' => 'promoter', 'assigned_by' => null, 'assigned_at' => now()]);
        $order = TicketOrder::create([
            'festival_id' => $f->id, 'order_number' => 'X1', 'email' => 'b@t.test',
            'ordered_by' => $buyer->id, 'requested_by' => $promoter->id,
            'job_status' => 'completed', 'paid' => 1000, 'total' => 1000,
        ]);
        $ticket = Ticket::create([
            'festival_id' => $f->id, 'ticket_type_id' => $tt->id, 'ticket_order_id' => $order->id,
            'code' => 'TKT-TEST-1', 'is_active' => true,
        ]);

        $a = User::create(['name' => 'A', 'email' => 'a2@t.test', 'password' => bcrypt('x'), 'role' => 'admin']);
        $a->festivals()->attach($f->id, ['role_in_festival' => 'admin', 'assigned_by' => null, 'assigned_at' => now()]);
        $this->actingAs($a);

        $r = $this->postJson("/admin/festivals/{$f->slug}/scan", ['code' => 'TKT-TEST-1']);
        $r->assertOk();
        $r->assertJsonPath('ok', true);
        $r->assertJsonPath('ticket.code', 'TKT-TEST-1');
        $r->assertJsonPath('ticket.is_active', false);

        $ticket->refresh();
        $this->assertNotNull($ticket->scanned_at);
        $this->assertFalse($ticket->is_active);
    }

    public function test_double_scan_returns_409(): void
    {
        $f = Festival::create(['name' => 'F', 'year' => 2026, 'slug' => 'f-2026', 'status' => 'active']);
        $tt = TicketType::create(['festival_id' => $f->id, 'name' => 'GA', 'price' => 1000, 'qr_coordinates' => ['x'=>0,'y'=>0,'size'=>100]]);
        $buyer = User::create(['name' => 'B', 'email' => 'b@t.test', 'password' => bcrypt('x'), 'role' => 'buyer']);
        $promoter = User::create(['name' => 'P', 'email' => 'p@t.test', 'password' => bcrypt('x'), 'role' => 'promoter']);
        $promoter->festivals()->attach($f->id, ['role_in_festival' => 'promoter', 'assigned_by' => null, 'assigned_at' => now()]);
        $order = TicketOrder::create([
            'festival_id' => $f->id, 'order_number' => 'X1', 'email' => 'b@t.test',
            'ordered_by' => $buyer->id, 'requested_by' => $promoter->id,
            'job_status' => 'completed', 'paid' => 1000, 'total' => 1000,
        ]);
        $ticket = Ticket::create([
            'festival_id' => $f->id, 'ticket_type_id' => $tt->id, 'ticket_order_id' => $order->id,
            'code' => 'TKT-TEST-2', 'is_active' => true, 'scanned_at' => now()->subMinute(),
        ]);

        $a = User::create(['name' => 'A', 'email' => 'a@t.test', 'password' => bcrypt('x'), 'role' => 'admin']);
        $a->festivals()->attach($f->id, ['role_in_festival' => 'admin', 'assigned_by' => null, 'assigned_at' => now()]);
        $this->actingAs($a);

        $r = $this->postJson("/admin/festivals/{$f->slug}/scan", ['code' => 'TKT-TEST-2']);
        $r->assertStatus(409);
        $r->assertJsonPath('ok', false);
    }
}
