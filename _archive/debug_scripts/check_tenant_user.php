<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant\User;

// Initialisiere Tenancy für testclub
$tenant = \App\Models\Central\Tenant::where('id', 'testclub')->first();
if (!$tenant) {
    echo "❌ Tenant 'testclub' nicht gefunden!\n";
    exit(1);
}

tenancy()->initialize($tenant);

echo "✅ Tenancy initialisiert für: {$tenant->id}\n";
echo "📊 Datenbank: " . config('database.connections.tenant.database') . "\n\n";

// Prüfe User
$user = User::where('email', 'admin@testclub.com')->first();

if ($user) {
    echo "✅ User gefunden:\n";
    echo "   Name: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   ID: {$user->id}\n";
    echo "   Hat Password: " . (!empty($user->password) ? 'Ja' : 'Nein') . "\n";
    echo "   Erstellt: {$user->created_at}\n";

    // Teste Password
    if (\Illuminate\Support\Facades\Hash::check('password', $user->password)) {
        echo "   ✅ Password 'password' ist KORREKT\n";
    } else {
        echo "   ❌ Password 'password' ist FALSCH\n";
    }

    // Zähle alle Users
    $totalUsers = User::count();
    echo "\n📊 Gesamt Users in Tenant DB: {$totalUsers}\n";
} else {
    echo "❌ User 'admin@testclub.com' NICHT gefunden!\n\n";
    echo "📋 Verfügbare Users:\n";
    User::all()->each(function($u) {
        echo "   - {$u->email} ({$u->name})\n";
    });
}
