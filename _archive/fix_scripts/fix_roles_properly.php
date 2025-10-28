<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

config(['database.default' => 'mysql']);

echo "=== FIX ROLES ===\n\n";

$user = \App\Models\Central\User::first();

echo "User: {$user->email}\n";
echo "Model Guard: {$user->guard_name}\n\n";

echo "Aktuelle Roles:\n";
$currentRoles = $user->roles()->get();
foreach ($currentRoles as $role) {
    echo "  - {$role->name} (guard: {$role->guard_name})\n";
}

// Remove all roles
echo "\nEntferne alle Roles...\n";
foreach ($currentRoles as $role) {
    $user->removeRole($role);
    echo "  ✓ {$role->name} entfernt\n";
}

// Create/get super-admin role
echo "\nErstelle 'super-admin' Role...\n";
$adminRole = \Spatie\Permission\Models\Role::firstOrCreate(
    ['name' => 'super-admin'],
    ['guard_name' => 'web']
);
echo "  ✓ Role erstellt: {$adminRole->name} (guard: {$adminRole->guard_name})\n";

// Assign role
echo "\nWeise Role zu...\n";
$user->assignRole($adminRole);
echo "  ✓ Role zugewiesen\n";

// Verify
$user = $user->fresh();
echo "\n=== VERIFIZIERUNG ===\n";
echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
echo "hasRole('super-admin'): " . ($user->hasRole('super-admin') ? 'JA ✓' : 'NEIN ✗') . "\n";

// Check AppServiceProvider Gate
echo "\n=== GATE CHECK ===\n";
echo "AppServiceProvider hat Gate::before für admin check\n";
echo "User sollte ALLE Permissions haben wenn hasRole('super-admin') true ist\n";

echo "\n✓ JETZT EINLOGGEN:\n";
echo "URL: http://localhost:8000/admin/login\n";
echo "Email: {$user->email}\n";
echo "Password: Zagreb123!\n";
