<?php

namespace Database\Seeders;

use App\Models\Central\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MichaelSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Michael',
            'email' => 'michael@klubportal.com',
            'password' => Hash::make('Zagreb123!'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('super_admin');

        $this->command->info('✅ Super Admin Michael erstellt: michael@klubportal.com');
    }
}
