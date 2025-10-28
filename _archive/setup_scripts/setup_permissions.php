<?php

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Artisan;

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

echo "\n🔐 Generiere Permissions für Testclub...\n\n";

// Shield Permissions generieren
Artisan::call('shield:generate', [
    '--all' => true,
]);

echo Artisan::output();

// Super Admin Rolle erstellen/finden
$superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate([
    'name' => 'super_admin',
    'guard_name' => 'web',
]);

echo "✅ Super Admin Rolle: {$superAdminRole->name}\n";

// Alle Permissions der Rolle zuweisen
$allPermissions = \Spatie\Permission\Models\Permission::all();
$superAdminRole->syncPermissions($allPermissions);

echo "✅ {$allPermissions->count()} Permissions zugewiesen\n\n";

// Admin User finden und Rolle zuweisen
$admin = \App\Models\Tenant\User::where('email', 'admin@testclub.com')->first();

if ($admin) {
    $admin->syncRoles(['super_admin']);
    echo "✅ Admin User '{$admin->name}' hat jetzt die 'super_admin' Rolle!\n\n";

    echo "🔐 LOGIN JETZT MÖGLICH:\n";
    echo "   URL: http://testclub.localhost:8000/club/login\n";
    echo "   Email: admin@testclub.com\n";
    echo "   Passwort: password\n\n";
} else {
    echo "❌ Admin User nicht gefunden!\n";
}
