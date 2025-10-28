<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Force Central Connection
config(['database.default' => 'mysql']);

echo "=== CENTRAL DATABASE USERS ===\n\n";

try {
    $users = \App\Models\User::all(['id', 'name', 'email']);

    echo "Total Users: " . $users->count() . "\n\n";

    if ($users->isEmpty()) {
        echo "❌ NO USERS FOUND!\n";
        echo "You need to create a user for Central Admin.\n";
    } else {
        echo "Users:\n";
        foreach ($users as $user) {
            echo "  {$user->id} | {$user->name} | {$user->email}\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== AUTH GUARD CONFIG ===\n";
echo "Default Guard: " . config('auth.defaults.guard') . "\n";
echo "Web Guard Driver: " . config('auth.guards.web.driver') . "\n";
echo "Web Guard Provider: " . config('auth.guards.web.provider') . "\n";
echo "Users Provider Model: " . config('auth.providers.users.model') . "\n";
