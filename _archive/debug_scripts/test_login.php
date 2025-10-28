<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$email = 'info@klubportal.com';
$password = 'Zagreb12#!';

echo "=== LOGIN TEST ===\n\n";

// Hole User aus der Datenbank
$user = DB::table('users')->where('email', $email)->first();

if (!$user) {
    echo "✗ Benutzer nicht gefunden in Default-Connection!\n";

    // Prüfe in central connection
    $user = DB::connection('central')->table('users')->where('email', $email)->first();
    if ($user) {
        echo "✓ Aber gefunden in 'central' connection!\n";
    }
} else {
    echo "✓ Benutzer gefunden (ID: {$user->id})\n";
}

if ($user) {
    echo "Email: {$user->email}\n";
    echo "Name: {$user->name}\n";
    echo "Hash (first 30 chars): " . substr($user->password, 0, 30) . "...\n\n";

    // Teste Passwort
    if (Hash::check($password, $user->password)) {
        echo "✓✓✓ PASSWORT IST KORREKT! ✓✓✓\n";
    } else {
        echo "✗✗✗ PASSWORT IST FALSCH! ✗✗✗\n";
    }

    // Zeige auch den Default Connection Namen
    echo "\nDefault DB Connection: " . config('database.default') . "\n";
    echo "Default DB Database: " . config('database.connections.' . config('database.default') . '.database') . "\n";
}
