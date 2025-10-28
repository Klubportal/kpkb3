<?php

use App\Models\Central\Tenant;
use App\Models\Tenant\User;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Testclub Tenant initialisieren
$tenant = Tenant::find('testclub');

if (!$tenant) {
    echo "❌ Tenant 'testclub' nicht gefunden!\n";
    exit(1);
}

tenancy()->initialize($tenant);

echo "\n✅ Tenant initialisiert: {$tenant->name}\n";
echo "📊 Datenbank: " . config('database.connections.tenant.database') . "\n\n";

// Admin User prüfen
$admin = User::where('email', 'admin@testclub.com')->first();

if ($admin) {
    echo "✅ Admin User existiert:\n";
    echo "   Name: {$admin->name}\n";
    echo "   Email: {$admin->email}\n";

    if ($admin->roles()->count() > 0) {
        echo "   Rollen: " . $admin->roles->pluck('name')->join(', ') . "\n";
    } else {
        echo "   ⚠️  Keine Rollen zugewiesen!\n";
    }

    echo "\n🔐 LOGIN-DATEN:\n";
    echo "   URL: http://testclub.localhost:8000/club/login\n";
    echo "   Email: admin@testclub.com\n";
    echo "   Passwort: password\n\n";

} else {
    echo "❌ Admin User nicht gefunden!\n";
    echo "   Erstelle User...\n\n";

    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@testclub.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Admin-Rolle zuweisen (wenn vorhanden)
    $adminRole = \Spatie\Permission\Models\Role::where('name', 'super_admin')
        ->orWhere('name', 'admin')
        ->first();

    if ($adminRole) {
        $admin->assignRole($adminRole);
        echo "✅ Admin erstellt und Rolle '{$adminRole->name}' zugewiesen!\n";
    } else {
        echo "✅ Admin erstellt (keine Rollen verfügbar)\n";
    }

    echo "\n🔐 LOGIN-DATEN:\n";
    echo "   URL: http://testclub.localhost:8000/club/login\n";
    echo "   Email: admin@testclub.com\n";
    echo "   Passwort: password\n\n";
}

// Verfügbare Resources anzeigen
echo "📦 VERFÜGBARE RESOURCES:\n";
echo "   - News (Nachrichten)\n";
echo "   - Events (Termine)\n";
echo "   - Members (Mitglieder)\n";
echo "   - Pages (Seiten)\n";
echo "   - Roles & Permissions (Shield)\n\n";

// Daten-Status
$newsCount = \App\Models\Tenant\News::count();
$eventsCount = \App\Models\Tenant\Event::count();
$membersCount = \App\Models\Tenant\Member::count();

echo "📊 DATEN-STATUS:\n";
echo "   News: {$newsCount}\n";
echo "   Events: {$eventsCount}\n";
echo "   Mitglieder: {$membersCount}\n\n";
