<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 CENTRAL USERS:\n";
echo str_repeat("=", 60) . "\n\n";

$users = \App\Models\Central\User::all(['id', 'name', 'email']);

if ($users->isEmpty()) {
    echo "❌ KEINE CENTRAL USERS GEFUNDEN!\n";
} else {
    foreach ($users as $user) {
        echo "✅ {$user->email} - {$user->name}\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📋 Login mit einer dieser Email-Adressen!\n";
