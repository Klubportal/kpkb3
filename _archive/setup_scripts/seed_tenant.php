<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "\n========================================\n";
echo "   TENANT DATENBANK INITIALISIEREN\n";
echo "========================================\n\n";

$tenantId = $argv[1] ?? 'testclub';

// Tenant finden
$tenant = Tenant::where('id', $tenantId)->first();

if (!$tenant) {
    echo "❌ Tenant '{$tenantId}' nicht gefunden!\n\n";
    exit(1);
}

echo "✅ Tenant gefunden: {$tenant->id}\n";
echo "   Domains: " . $tenant->domains->pluck('domain')->join(', ') . "\n\n";

// Tenancy initialisieren
tenancy()->initialize($tenant);

// Manuell DB Bootstrapper ausführen
$bootstrapper = app(\Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class);
$bootstrapper->bootstrap($tenant);

$currentDB = DB::connection()->getDatabaseName();
echo "🔄 Tenancy initialisiert - Datenbank: {$currentDB}\n\n";

// ========================================
// 1. SHIELD: Rollen & Permissions
// ========================================
echo "📋 Erstelle Rollen & Permissions...\n";

// Erstmal alte Daten löschen
DB::table('model_has_roles')->delete();
DB::table('model_has_permissions')->delete();
DB::table('role_has_permissions')->delete();
DB::table('permissions')->delete();
DB::table('roles')->delete();

// Rollen erstellen via direktem INSERT
$roles = [
    ['name' => 'super_admin', 'guard_name' => 'tenant', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'admin', 'guard_name' => 'tenant', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'trainer', 'guard_name' => 'tenant', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'player', 'guard_name' => 'tenant', 'created_at' => now(), 'updated_at' => now()],
];

foreach ($roles as $role) {
    DB::table('roles')->insert($role);
}

echo "   ✅ 4 Rollen erstellt\n";

// Permissions erstellen
$resources = ['Team', 'Player', 'Match', 'Training', 'News', 'Event', 'Member', 'Standing'];
$actions = ['view', 'view_any', 'create', 'update', 'delete', 'restore', 'force_delete'];

$permissionsData = [];
foreach ($resources as $resource) {
    foreach ($actions as $action) {
        $permissionName = $action . '_' . strtolower($resource);
        $permissionsData[] = [
            'name' => $permissionName,
            'guard_name' => 'tenant',
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}

foreach ($permissionsData as $perm) {
    DB::table('permissions')->insert($perm);
}

echo "   ✅ " . count($permissionsData) . " Permissions erstellt\n";

// Super Admin bekommt ALLE Permissions
$superAdminId = DB::table('roles')->where('name', 'super_admin')->value('id');
$allPermissionIds = DB::table('permissions')->pluck('id');

foreach ($allPermissionIds as $permId) {
    DB::table('role_has_permissions')->insert([
        'permission_id' => $permId,
        'role_id' => $superAdminId,
    ]);
}

// Admin bekommt fast alles (außer force_delete)
$adminId = DB::table('roles')->where('name', 'admin')->value('id');
$adminPermissionIds = DB::table('permissions')
    ->where('name', 'not like', '%force_delete%')
    ->pluck('id');

foreach ($adminPermissionIds as $permId) {
    DB::table('role_has_permissions')->insert([
        'permission_id' => $permId,
        'role_id' => $adminId,
    ]);
}

echo "   ✅ Permissions zugewiesen\n\n";

// ========================================
// 2. Admin User erstellen/aktualisieren
// ========================================
echo "👤 Erstelle Admin User...\n";

$user = DB::table('users')->where('email', 'admin@testclub.com')->first();

if (!$user) {
    DB::table('users')->insert([
        'name' => 'Test Admin',
        'email' => 'admin@testclub.com',
        'password' => Hash::make('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $userId = DB::getPdo()->lastInsertId();
    echo "   ✅ User erstellt\n";
} else {
    $userId = $user->id;
    echo "   ℹ️  User existiert bereits\n";
}

// Super Admin Rolle zuweisen
$superAdminRoleId = DB::table('roles')->where('name', 'super_admin')->value('id');

// Zuerst alle alten Rollen-Zuordnungen löschen
DB::table('model_has_roles')->where('model_id', $userId)->delete();

DB::table('model_has_roles')->insert([
    'model_type' => 'App\\Models\\Tenant\\User',
    'model_id' => $userId,
    'role_id' => $superAdminRoleId,
]);

echo "   ✅ Super Admin Rolle zugewiesen\n\n";

// ========================================
// 3. Beispiel-Daten erstellen
// ========================================
echo "🏟️  Erstelle Beispiel-Daten...\n";

// Saison erstellen (falls Tabelle existiert)
try {
    if (DB::table('seasons')->count() == 0) {
        DB::table('seasons')->insert([
            'name' => '2025/2026',
            'start_date' => '2025-08-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "   ✅ Saison erstellt\n";
    }
} catch (\Exception $e) {
    echo "   ⚠️  Seasons-Tabelle nicht verfügbar\n";
}

// Team erstellen (falls Tabelle existiert)
try {
    if (DB::table('teams')->count() == 0) {
        DB::table('teams')->insert([
            'name' => 'Erste Mannschaft',
            'short_name' => '1st Team',
            'founded_year' => 2000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "   ✅ Team erstellt\n";
    }
} catch (\Exception $e) {
    echo "   ⚠️  Teams-Tabelle nicht verfügbar\n";
}

echo "\n========================================\n";
echo "   ✅ ERFOLGREICH ABGESCHLOSSEN!\n";
echo "========================================\n\n";

echo "LOGIN DATEN:\n";
echo "------------\n";
echo "URL:      http://{$tenantId}.localhost:8000/login\n";
echo "Email:    admin@testclub.com\n";
echo "Passwort: password\n";
echo "Rolle:    Super Admin\n\n";

echo "STATISTIKEN:\n";
echo "------------\n";
echo "Rollen:      " . DB::table('roles')->count() . "\n";
echo "Permissions: " . DB::table('permissions')->count() . "\n";
echo "Users:       " . DB::table('users')->count() . "\n";

try {
    echo "Teams:       " . DB::table('teams')->count() . "\n";
    echo "Seasons:     " . DB::table('seasons')->count() . "\n";
} catch (\Exception $e) {
    // Ignore if tables don't exist
}

echo "\n========================================\n\n";
