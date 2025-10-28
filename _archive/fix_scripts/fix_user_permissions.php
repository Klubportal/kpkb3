<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

config(['database.default' => 'mysql']);

echo "=== SPATIE PERMISSIONS CHECK ===\n\n";

$user = \App\Models\Central\User::first();

echo "User: {$user->email}\n";
echo "Guard: {$user->guard_name}\n\n";

// Check if roles/permissions tables exist
try {
    $roles = $user->roles()->get();
    echo "Roles (" . $roles->count() . "):\n";
    foreach ($roles as $role) {
        echo "  - {$role->name}\n";
    }

    if ($roles->isEmpty()) {
        echo "  (keine Roles zugewiesen)\n\n";
        echo "=== ERSTELLE SUPER-ADMIN ROLE ===\n";

        // Create role
        $role = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'web']
        );
        echo "✓ Role 'super-admin' erstellt/gefunden\n";

        // Assign to user
        $user->assignRole('super-admin');
        echo "✓ Role an User zugewiesen\n";

        // Refresh user
        $user = \App\Models\Central\User::first();
        echo "\n✓ User hat jetzt Role: " . $user->roles->pluck('name')->implode(', ') . "\n";
    }

} catch (\Exception $e) {
    echo "✗ Fehler: " . $e->getMessage() . "\n";
}

echo "\n=== ADMIN CHECK ===\n";
echo "hasRole('super-admin'): " . ($user->hasRole('super-admin') ? 'JA' : 'NEIN') . "\n";

echo "\n✓ Jetzt sollte der Login funktionieren!\n";
echo "URL: http://localhost:8000/admin/login\n";
echo "Email: {$user->email}\n";
echo "Password: Zagreb123!\n";
