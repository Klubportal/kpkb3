<?php

/**
 * Erstelle einen Admin-User für den Tenant "testclub"
 * Ausführen mit: php create_tenant_user.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Central\Tenant;
use App\Models\Tenant\User;

echo "\n========================================\n";
echo "   TENANT USER ERSTELLEN\n";
echo "========================================\n\n";

// Tenant initialisieren
$tenant = Tenant::where('id', 'testclub')->first();

if (!$tenant) {
    echo "❌ Tenant 'testclub' nicht gefunden!\n\n";
    exit(1);
}

echo "✅ Tenant gefunden: {$tenant->id}\n";
echo "   Domains: " . $tenant->domains->pluck('domain')->join(', ') . "\n\n";

// Tenancy initialisieren
tenancy()->initialize($tenant);

echo "🔄 Tenancy initialisiert für: {$tenant->id}\n";
echo "   Aktuelle DB: tenant_testclub\n\n";

// Prüfen ob User bereits existiert
$existingUser = User::where('email', 'admin@testclub.com')->first();

if ($existingUser) {
    echo "ℹ️  User existiert bereits!\n";
    echo "   Email: {$existingUser->email}\n";
    echo "   Name: {$existingUser->name}\n\n";
} else {
    // User erstellen
    $user = User::create([
        'name' => 'Test Club Admin',
        'email' => 'admin@testclub.com',
        'password' => bcrypt('password'),
        'phone' => '+49 123 456789',
        'role' => 'admin',
        'is_active' => true,
    ]);

    echo "✅ User erfolgreich erstellt!\n";
    echo "   Email: {$user->email}\n";
    echo "   Name: {$user->name}\n";
    echo "   Passwort: password\n\n";

    // Admin-Rolle zuweisen (falls Shield installiert)
    try {
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'tenant']
        );
        $user->assignRole($adminRole);
        echo "✅ Admin-Rolle zugewiesen\n\n";
    } catch (\Exception $e) {
        echo "⚠️  Rolle konnte nicht zugewiesen werden: {$e->getMessage()}\n\n";
    }
}

// Statistiken anzeigen
echo "========================================\n";
echo "   DATENBANK STATISTIKEN\n";
echo "========================================\n\n";

try {
    echo "Users: " . User::count() . "\n";
    echo "Teams: " . \App\Models\Tenant\Team::count() . "\n";
    echo "Players: " . \App\Models\Tenant\Player::count() . "\n";
    echo "Matches: " . \App\Models\Tenant\Match::count() . "\n";
    echo "News: " . \App\Models\Tenant\News::count() . "\n\n";
} catch (\Exception $e) {
    echo "Fehler beim Abrufen der Statistiken: {$e->getMessage()}\n\n";
}

echo "========================================\n";
echo "   LOGIN INFORMATIONEN\n";
echo "========================================\n\n";

echo "URL: http://testclub.localhost:8000/login\n";
echo "Email: admin@testclub.com\n";
echo "Passwort: password\n\n";

echo "========================================\n";
