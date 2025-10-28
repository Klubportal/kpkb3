<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Central\Tenant;

echo "\n========================================\n";
echo "  TESTCLUB ERSTELLEN\n";
echo "========================================\n\n";

// 1. Tenant erstellen
echo "1. Erstelle Tenant 'testclub'...\n";

$tenant = Tenant::create([
    'id' => 'testclub',
    'name' => 'Test Fußballverein',
    'email' => 'info@testclub.com',
    'phone' => '+49 123 456789',
    'plan' => 'premium',
    'trial_ends_at' => now()->addDays(30),
]);

echo "   ✓ Tenant erstellt: {$tenant->id}\n";

// 2. Domain erstellen
echo "\n2. Erstelle Domain 'testclub.localhost'...\n";

$tenant->domains()->create([
    'domain' => 'testclub.localhost',
]);

echo "   ✓ Domain erstellt: testclub.localhost\n";

// 3. Warte auf Tenant-Datenbank (wird durch Event automatisch erstellt)
echo "\n3. Warte auf Datenbank-Erstellung...\n";
sleep(2);

// 4. Initialisiere Tenant und erstelle Admin
echo "\n4. Erstelle Admin-User...\n";

$tenant->run(function () {
    // Prüfe ob User-Tabelle existiert
    if (!DB::getSchemaBuilder()->hasTable('users')) {
        echo "   ⚠ Tabelle 'users' existiert noch nicht. Bitte warten...\n";
        return;
    }

    // Admin User erstellen
    $admin = DB::table('users')->insert([
        'name' => 'Admin Test',
        'email' => 'admin@testclub.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "   ✓ Admin User erstellt\n";
    echo "   Email: admin@testclub.com\n";
    echo "   Passwort: password\n";

    // Prüfe ob Rollen existieren
    if (DB::getSchemaBuilder()->hasTable('roles')) {
        $adminRole = DB::table('roles')->where('name', 'admin')->first();

        if ($adminRole) {
            $userId = DB::table('users')->where('email', 'admin@testclub.com')->value('id');

            DB::table('model_has_roles')->insert([
                'role_id' => $adminRole->id,
                'model_type' => 'App\\Models\\User',
                'model_id' => $userId,
            ]);

            echo "   ✓ Admin-Rolle zugewiesen\n";
        }
    }
});

echo "\n========================================\n";
echo "  TESTCLUB ERFOLGREICH ERSTELLT!\n";
echo "========================================\n\n";

echo "Backend (Filament Panel):\n";
echo "  URL: http://testclub.localhost:8000/club/login\n";
echo "  Email: admin@testclub.com\n";
echo "  Passwort: password\n\n";

echo "Frontend (Öffentliche Webseite):\n";
echo "  URL: http://testclub.localhost:8000/\n\n";

echo "========================================\n\n";
