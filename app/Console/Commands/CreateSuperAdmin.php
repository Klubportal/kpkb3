<?php

namespace App\Console\Commands;

use App\Models\Central\User as CentralUser;
use App\Models\Tenant\User as TenantUser;
use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:super-admin
                            {--type=central : Type of admin (central or tenant)}
                            {--tenant= : Tenant ID (required for tenant type)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Super Admin user for Central or Tenant database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');

        $this->info("🔐 Creating {$type} Super Admin User");
        $this->newLine();

        // Get user details
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password (min. 8 characters)');
        $passwordConfirmation = $this->secret('Confirm Password');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            $this->error('❌ Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("   • {$error}");
            }
            return Command::FAILURE;
        }

        if ($type === 'tenant') {
            return $this->createTenantAdmin($name, $email, $password);
        }

        return $this->createCentralAdmin($name, $email, $password);
    }

    /**
     * Create Central Super Admin
     */
    protected function createCentralAdmin(string $name, string $email, string $password)
    {
        // Check if user exists
        if (CentralUser::where('email', $email)->exists()) {
            $this->error("❌ User with email {$email} already exists in Central database!");
            return Command::FAILURE;
        }

        // Create user
        $user = CentralUser::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Assign super_admin role (if using Spatie Permissions)
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('super_admin');
                $this->info('   ✅ Super Admin role assigned');
            } catch (\Exception $e) {
                $this->warn('   ⚠️  Role assignment skipped (role may not exist yet)');
            }
        }

        $this->newLine();
        $this->info('✅ Central Super Admin created successfully!');
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Database', 'kpkb3 (Central)'],
                ['Login URL', 'http://127.0.0.1:8000/super-admin/login'],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Create Tenant Super Admin
     */
    protected function createTenantAdmin(string $name, string $email, string $password)
    {
        $tenantId = $this->option('tenant');

        if (!$tenantId) {
            // Show available tenants
            $tenants = Tenant::all();

            if ($tenants->isEmpty()) {
                $this->error('❌ No tenants found! Create a tenant first.');
                return Command::FAILURE;
            }

            $this->info('Available tenants:');
            foreach ($tenants as $tenant) {
                $this->info("   • {$tenant->id}");
            }

            $tenantId = $this->ask('Enter Tenant ID');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("❌ Tenant {$tenantId} not found!");
            return Command::FAILURE;
        }

        // Initialize tenancy
        tenancy()->initialize($tenant);

        // Check if user exists
        if (TenantUser::where('email', $email)->exists()) {
            $this->error("❌ User with email {$email} already exists in tenant {$tenantId}!");
            tenancy()->end();
            return Command::FAILURE;
        }

        // Create user
        $user = TenantUser::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'manager',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign admin role (if using Spatie Permissions)
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('admin');
                $this->info('   ✅ Admin role assigned');
            } catch (\Exception $e) {
                $this->warn('   ⚠️  Role assignment skipped (role may not exist yet)');
            }
        }

        tenancy()->end();

        $this->newLine();
        $this->info('✅ Tenant Admin created successfully!');
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', $user->role],
                ['Tenant', $tenantId],
                ['Database', "tenant_{$tenantId}"],
                ['Login URL', "http://127.0.0.1:8000/admin/{$tenantId}/login"],
            ]
        );

        return Command::SUCCESS;
    }
}
