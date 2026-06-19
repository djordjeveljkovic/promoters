<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Superadmin (global, can manage every festival) ----
        User::updateOrCreate(
            ['email' => 'superadmin@refest.rs'],
            [
                'name'     => 'Root Operator',
                'password' => Hash::make('supremeAdminXOXO'),
                'role'     => 'superadmin',
            ]
        );

        // ---- Festival admin (REFEST admin) ----
        User::updateOrCreate(
            ['email' => 'refestrs@gmail.com'],
            [
                'name'     => 'REFEST Festival',
                'password' => Hash::make('Refestcar123***'),
                'role'     => 'admin',
            ]
        );

        // ---- Sample promoter (assigned to REFEST 2026 by FestivalSeeder) ----
        User::updateOrCreate(
            ['email' => 'promoter@example.com'],
            [
                'name'     => 'Sample Promoter',
                'password' => Hash::make('promoter123'),
                'role'     => 'promoter',
            ]
        );

        // ---- Sample sub-promoter (assigned to REFEST 2026 by FestivalSeeder) ----
        User::updateOrCreate(
            ['email' => 'sub@example.com'],
            [
                'name'     => 'Sample Sub-Promoter',
                'password' => Hash::make('sub12345'),
                'role'     => 'sub_promoter',
            ]
        );
    }
}