<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTenantAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:tenantadmin {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a tenant admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get tenant ID
        $tenantId = $this->option('tenant');

        if (!$tenantId) {
            // Show available tenants
            $tenants = Tenant::all();

            if ($tenants->isEmpty()) {
                $this->error('No tenants found! Create a tenant first.');
                return Command::FAILURE;
            }

            $this->info('Available tenants:');
            foreach ($tenants as $tenant) {
                $this->info("  • {$tenant->id}");
            }

            $tenantId = $this->ask('Enter Tenant ID');
        }

        // Find tenant
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant {$tenantId} not found!");
            return Command::FAILURE;
        }

        // Initialize tenancy
        tenancy()->initialize($tenant);

        // Get user details
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        // Check if user exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");
            tenancy()->end();
            return Command::FAILURE;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'manager',
            'is_active' => true,
        ]);

        // Assign admin role (if using Spatie Permissions)
        try {
            $user->assignRole('admin');
        } catch (\Exception $e) {
            // Role might not exist yet
        }

        tenancy()->end();

        $this->info('Tenant admin created successfully!');
        $this->info("Tenant: {$tenantId}");
        $this->info("Login URL: http://127.0.0.1:8000/admin/{$tenantId}/login");

        return Command::SUCCESS;
    }
}
