<?php

namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class CreateTenantSuperAdmin
{
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant;

        // Nur wenn Tenant eine Datenbank hat
        if (!$tenant instanceof TenantWithDatabase) {
            return;
        }

        // Tenancy initialisieren
        $tenant->run(function () {
            // Prüfen ob users Tabelle existiert
            if (!DB::getSchemaBuilder()->hasTable('users')) {
                return;
            }

            // Super Admin erstellen (wenn noch nicht vorhanden)
            $existingUser = DB::table('users')
                ->where('email', 'info@klubportal.com')
                ->first();

            if ($existingUser) {
                $userId = $existingUser->id;
            } else {
                $userData = [
                    'name' => 'Klubportal',
                    'email' => 'info@klubportal.com',
                    'password' => Hash::make('Zagreb123!'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // is_active nur hinzufügen wenn Spalte existiert
                if (DB::getSchemaBuilder()->hasColumn('users', 'is_active')) {
                    $userData['is_active'] = true;
                }

                $userId = DB::table('users')->insertGetId($userData);
            }

            // Super Admin Rolle zuweisen (wenn Shield installiert ist)
            if (DB::getSchemaBuilder()->hasTable('roles')) {
                $superAdminRoleId = DB::table('roles')
                    ->where('name', 'super_admin')
                    ->value('id');

                if ($superAdminRoleId) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $superAdminRoleId,
                        'model_type' => 'App\\Models\\User',
                        'model_id' => $userId,
                    ]);
                }
            }

            \Log::info("Super Admin created for tenant: {$userId}");
        });
    }
}
