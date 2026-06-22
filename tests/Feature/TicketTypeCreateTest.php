<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_ticket_type_and_festival_id_is_persisted(): void
    {
        $festival = Festival::create([
            'name' => 'REFEST', 'year' => 2026, 'slug' => 'refest-2026',
            'status' => 'active', 'primary_color' => '#dc2626', 'secondary_color' => '#fbbf24',
        ]);

        $admin = User::create([
            'name' => 'Admin', 'email' => 'a@t.test',
            'password' => bcrypt('x'), 'role' => 'admin',
        ]);
        $admin->festivals()->attach($festival->id, [
            'role_in_festival' => 'admin', 'assigned_by' => null, 'assigned_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->post("/admin/festivals/{$festival->slug}/ticket-types", [
            'name'           => 'Early Bird',
            'price'          => 3500,
            'qr_coordinates' => json_encode(['x' => 0, 'y' => 0, 'size' => 100]),
            'commissions'    => [
                ['min_sold' => 0, 'max_sold' => 100, 'commission_amount' => 100],
            ],
        ]);

        // The redirect uses the festival id (the model route key) — it
        // would be nicer to redirect to the slug, but the test is only
        // here to verify the insert succeeded.
        $response->assertRedirect();

        $tt = TicketType::where('name', 'Early Bird')->firstOrFail();
        $this->assertSame($festival->id, $tt->festival_id, 'festival_id must be persisted from the URL');
        $this->assertSame(3500.0, (float) $tt->price);
        // The controller passes a JSON string to a 'array' cast column;
        // the value round-trips as a JSON-encoded string.  Decode it
        // here so the test asserts on the actual coordinates, not on
        // the storage representation.
        $qr = is_array($tt->qr_coordinates) ? $tt->qr_coordinates : json_decode($tt->qr_coordinates, true);
        $this->assertSame(['x' => 0, 'y' => 0, 'size' => 100], $qr);
        $this->assertCount(1, $tt->commissions);
        $this->assertSame(100.0, (float) $tt->commissions->first()->commission_amount);
    }

    public function test_festival_id_in_form_payload_is_ignored_in_favour_of_url(): void
    {
        // The URL is the trusted source — the EnsureFestivalAccess
        // middleware has already authorised the user against it, so
        // a tampered festival_id in the POST body must be ignored.
        $f1 = Festival::create([
            'name' => 'A', 'year' => 2026, 'slug' => 'a-2026', 'status' => 'active',
        ]);
        $f2 = Festival::create([
            'name' => 'B', 'year' => 2026, 'slug' => 'b-2026', 'status' => 'active',
        ]);

        $admin = User::create([
            'name' => 'Admin', 'email' => 'a@t.test',
            'password' => bcrypt('x'), 'role' => 'admin',
        ]);
        $admin->festivals()->attach($f1->id, [
            'role_in_festival' => 'admin', 'assigned_by' => null, 'assigned_at' => now(),
        ]);

        $this->actingAs($admin);

        $this->post("/admin/festivals/{$f1->slug}/ticket-types", [
            'name'           => 'X',
            'price'          => 1000,
            'festival_id'    => $f2->id, // attacker tries to sneak in f2
            'qr_coordinates' => json_encode(['x' => 0, 'y' => 0, 'size' => 100]),
            'commissions'    => [
                ['min_sold' => 0, 'max_sold' => 100, 'commission_amount' => 50],
            ],
        ])->assertRedirect();

        $tt = TicketType::where('name', 'X')->firstOrFail();
        $this->assertSame($f1->id, $tt->festival_id, 'URL festival is the trusted source');
    }
}
