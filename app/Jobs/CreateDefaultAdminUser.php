<?php

namespace App\Jobs;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class CreateDefaultAdminUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public TenantWithDatabase $tenant
    ) {}

    public function handle(): void
    {
        tenancy()->initialize($this->tenant);

        // Super Admin mit festen Zugangsdaten
        $superAdmin = User::firstOrCreate(
            ['email' => 'info@klubportal.com'],
            [
                'name' => 'klubportal',
                'password' => Hash::make('Zagreb123!'),
                'email_verified_at' => now(),
            ]
        );

        // Super Admin-Rolle zuweisen
        if (method_exists($superAdmin, 'assignRole')) {
            // Rolle erstellen falls nicht vorhanden
            \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => 'super_admin', 'guard_name' => 'tenant']
            );
            $superAdmin->assignRole('super_admin');
        }

        // Zusätzlicher Admin aus dem data Feld (falls vorhanden)
        $adminData = $this->tenant->data ?? [];

        if (!empty($adminData['admin_email']) && !empty($adminData['admin_password'])) {
            $user = User::firstOrCreate(
                ['email' => $adminData['admin_email']],
                [
                    'name' => $adminData['admin_name'] ?? 'Admin',
                    'password' => Hash::make($adminData['admin_password']),
                    'email_verified_at' => now(),
                ]
            );

            // Admin-Rolle zuweisen
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('admin');
            }
        }

        tenancy()->end();
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['tenant:' . $this->tenant->id, 'tenant-setup', 'admin-user'];
    }
}
