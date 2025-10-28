<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Foundation\Console\Kernel::class);

echo "========================================\n";
echo "   ALLE BENUTZER IN DER DATENBANK\n";
echo "========================================\n\n";

$users = \App\Models\User::all();
if ($users->isEmpty()) {
    echo "❌ Keine User gefunden!\n";
} else {
    foreach ($users as $user) {
        echo "✓ {$user->name}\n";
        echo "  Email: {$user->email}\n";
        echo "  ID: {$user->id}\n\n";
    }
}

echo "========================================\n";
echo "GESAMT: " . \App\Models\User::count() . " User\n";
echo "========================================\n";
