<?php

namespace Database\Seeders;

use App\Models\TicketType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TicketType::create([
            'name' => 'VIP Ticket',
            'price' => 100.00,
            'photo_path' => 'tickets/vip.png',
            'qr_coordinates' => json_encode(['x' => 120, 'y' => 210]),
        ]);

        TicketType::create([
            'name' => 'Standard Ticket',
            'price' => 50.00,
            'photo_path' => 'tickets/standard.png',
            'qr_coordinates' => json_encode(['x' => 100, 'y' => 200]),
        ]);
        $ticketType = TicketType::create([
            'name' => 'Regular Ticket',
            'price' => 20.00,
            'photo_path' => 'tickets/regular.png',
            'qr_coordinates' => json_encode(['x' => 120, 'y' => 210]),
        ]);

        $ticketType->commissions()->createMany([
            ['min_sold' => 0, 'max_sold' => 10, 'commission_amount' => 5.00],
            ['min_sold' => 10, 'max_sold' => 21, 'commission_amount' => 7.00],
            ['min_sold' => 21, 'max_sold' => null, 'commission_amount' => 10.00],
        ]);
    }
}
