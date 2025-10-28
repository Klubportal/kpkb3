<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo Users für Tenant
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@' . tenant()->id . '.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Trainer User
        User::firstOrCreate(
            ['email' => 'trainer@' . tenant()->id . '.com'],
            [
                'name' => 'Trainer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Manager User
        User::firstOrCreate(
            ['email' => 'manager@' . tenant()->id . '.com'],
            [
                'name' => 'Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('   ✅ Demo Users created');
    }
}
