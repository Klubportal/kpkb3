<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'user:create-admin
                            {name? : The name of the admin user}
                            {email? : The email of the admin user}
                            {--password= : The password for the admin user}';

    protected $description = 'Create a new admin/super admin user';

    public function handle()
    {
        $this->info('🔧 Creating Admin User');
        $this->line(str_repeat('=', 50));
        $this->newLine();

        // Get name
        $name = $this->argument('name') ?? $this->ask('Name');

        // Get email
        $email = $this->argument('email') ?? $this->ask('Email address');

        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email'
        ]);

        if ($validator->fails()) {
            $this->error('Invalid or already existing email address!');
            return 1;
        }

        // Get password
        $password = $this->option('password');

        if (!$password) {
            $password = $this->secret('Password (min. 8 characters)');
            $passwordConfirm = $this->secret('Confirm password');

            if ($password !== $passwordConfirm) {
                $this->error('Passwords do not match!');
                return 1;
            }
        }

        // Validate password
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters!');
            return 1;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Assign admin role if using Spatie Permission
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            try {
                $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin']);
                $user->assignRole($role);
                $this->info('✓ Assigned role: super-admin');
            } catch (\Exception $e) {
                $this->warn('Could not assign role (Spatie Permission might not be configured)');
            }
        }

        $this->newLine();
        $this->info('✅ Admin user created successfully!');
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Created', $user->created_at->format('Y-m-d H:i:s')],
            ]
        );

        return 0;
    }
}
