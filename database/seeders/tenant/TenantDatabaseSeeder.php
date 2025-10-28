<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Hash;

/**
 * 🌱 TENANT DATABASE SEEDER
 *
 * Master Seeder für Tenant-Datenbanken
 * Wird ausgeführt mit: php artisan tenants:seed
 *
 * Läuft automatisch im Tenant Context - keine explizite DB Connection nötig!
 */
class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database.
     */
    public function run(): void
    {
        $this->command->info("🌱 Seeding Tenant: " . tenant()->id);

        // Standard-Rollen erstellen (falls du Spatie Permission nutzt)
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::create(['name' => 'admin']);
            \Spatie\Permission\Models\Role::create(['name' => 'editor']);
            \Spatie\Permission\Models\Role::create(['name' => 'viewer']);

            // Standard-Permissions
            \Spatie\Permission\Models\Permission::create(['name' => 'manage news']);
            \Spatie\Permission\Models\Permission::create(['name' => 'manage players']);
            \Spatie\Permission\Models\Permission::create(['name' => 'manage games']);
        }

        // Call Tenant Seeders
        $this->call([
            \Database\Seeders\Tenant\DemoUserSeeder::class,
            \Database\Seeders\Tenant\TeamSeeder::class,
            \Database\Seeders\Tenant\PlayerSeeder::class,
            \Database\Seeders\Tenant\MatchSeeder::class,
            \Database\Seeders\Tenant\TenantNewsSeeder::class,
            \Database\Seeders\Tenant\EventSeeder::class,
        ]);

        // Demo-Daten (optional)
        if (app()->environment('local')) {
            $this->command->info("🎮 Local environment detected - seeding demo data");
            // Additional demo seeders can be called here if needed
        }

        $this->command->info("✅ Tenant '" . tenant()->id . "' seeded successfully!");
    }
}
