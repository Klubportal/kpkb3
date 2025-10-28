<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

config(['database.default' => 'mysql']);

echo "=== FIX USER GUARD & PERMISSIONS ===\n\n";

$user = \App\Models\Central\User::first();

echo "1. User Guard fixen:\n";
echo "   Aktuell: '{$user->guard_name}' (leer!)\n";
$user->guard_name = 'web';
$user->save();
echo "   ✓ Gesetzt auf: 'web'\n\n";

echo "2. Roles prüfen:\n";
$roles = \Spatie\Permission\Models\Role::where('guard_name', 'web')->get();
echo "   Gefundene Roles:\n";
foreach ($roles as $role) {
    echo "     - {$role->name} (guard: {$role->guard_name})\n";
}

echo "\n3. User Roles:\n";
$userRoles = $user->roles()->get();
foreach ($userRoles as $role) {
    echo "     - {$role->name} (guard: {$role->guard_name})\n";

    if ($role->guard_name !== 'web') {
        echo "       ✗ Falscher Guard! Entferne...\n";
        $user->removeRole($role);
    }
}

echo "\n4. Super-Admin Role erstellen/zuweisen:\n";
$adminRole = \Spatie\Permission\Models\Role::firstOrCreate(
    ['name' => 'super-admin', 'guard_name' => 'web']
);
echo "   ✓ Role 'super-admin' bereit\n";

if (!$user->hasRole('super-admin')) {
    $user->assignRole('super-admin');
    echo "   ✓ Role 'super-admin' zugewiesen\n";
} else {
    echo "   ✓ User hat bereits 'super-admin'\n";
}

// Refresh
$user = $user->fresh();

echo "\n=== ERGEBNIS ===\n";
echo "User: {$user->email}\n";
echo "Guard: {$user->guard_name}\n";
echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
echo "hasRole('super-admin'): " . ($user->hasRole('super-admin') ? 'JA ✓' : 'NEIN ✗') . "\n";

echo "\n✓ Jetzt einloggen:\n";
echo "URL: http://localhost:8000/admin/login\n";
echo "Email: {$user->email}\n";
echo "Password: Zagreb123!\n";
