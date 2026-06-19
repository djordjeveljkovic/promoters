<?php

namespace Database\Seeders;

use App\Models\Festival;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the canonical festivals used to bootstrap a fresh install.
 *
 * Three festivals are created so the admin UI has something to show:
 *  - REFEST 2025 (archived — the past edition)
 *  - REFEST 2026 (active — the current edition)
 *  - Lovefest 2027 (draft — a future festival)
 */
class FestivalSeeder extends Seeder
{
    public function run(): void
    {
        $refest2025 = Festival::updateOrCreate(
            ['name' => 'REFEST', 'year' => 2025],
            [
                'tagline'        => 'Where the wild things dance.',
                'description'    => 'Three days of music, lights and friendship on the banks of the Danube.',
                'location'       => 'Novi Sad, Serbia',
                'start_date'     => '2025-07-18',
                'end_date'       => '2025-07-20',
                'primary_color'  => '#ff2d92',
                'secondary_color'=> '#5ce1ff',
                'status'         => 'archived',
                'is_public'      => true,
            ]
        );

        $refest2026 = Festival::updateOrCreate(
            ['name' => 'REFEST', 'year' => 2026],
            [
                'tagline'        => 'Bigger. Louder. Wilder.',
                'description'    => 'REFEST returns for its 2026 edition with an even bigger lineup.',
                'location'       => 'Novi Sad, Serbia',
                'start_date'     => '2026-07-17',
                'end_date'       => '2026-07-19',
                'primary_color'  => '#ff2d92',
                'secondary_color'=> '#5ce1ff',
                'status'         => 'active',
                'is_public'      => true,
            ]
        );

        $lovefest = Festival::updateOrCreate(
            ['name' => 'Lovefest', 'year' => 2027],
            [
                'tagline'        => 'Love brings us together.',
                'description'    => 'A brand-new festival focused on love, community and electronic music.',
                'location'       => 'Belgrade, Serbia',
                'start_date'     => '2027-08-05',
                'end_date'       => '2027-08-07',
                'primary_color'  => '#ff5fb1',
                'secondary_color'=> '#ffd166',
                'status'         => 'draft',
                'is_public'      => false,
            ]
        );

        // Assign the existing admins to every seeded festival so the demo
        // data is usable out of the box.
        foreach (User::whereIn('role', ['admin', 'superadmin'])->get() as $user) {
            foreach ([$refest2025, $refest2026, $lovefest] as $festival) {
                $user->festivals()->syncWithoutDetaching([
                    $festival->id => [
                        'role_in_festival' => 'admin',
                        'assigned_at'      => now(),
                    ],
                ]);
            }
        }

        // Sample promoter → REFEST 2026 only
        $promoter = User::where('email', 'promoter@example.com')->first();
        if ($promoter) {
            $promoter->festivals()->syncWithoutDetaching([
                $refest2026->id => [
                    'role_in_festival' => 'promoter',
                    'assigned_at'      => now(),
                ],
            ]);
        }

        // Sample sub-promoter → REFEST 2026 only
        $sub = User::where('email', 'sub@example.com')->first();
        if ($sub) {
            $sub->festivals()->syncWithoutDetaching([
                $refest2026->id => [
                    'role_in_festival' => 'sub_promoter',
                    'assigned_at'      => now(),
                ],
            ]);
        }
    }
}