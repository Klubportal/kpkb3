<?php

namespace App\Console\Commands;

use App\Models\Central\User;
use Illuminate\Console\Command;

class AssignSuperAdmin extends Command
{
    protected $signature = 'user:super-admin {email}';
    protected $description = 'Assign super_admin role to a central admin user';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        // Get or create super_admin role with 'web' guard
        $role = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web']
        );

        $user->assignRole($role);

        $this->info("Admin role assigned to {$user->email}");

        return 0;
    }
}
