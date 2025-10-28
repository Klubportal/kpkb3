<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

config(['database.default' => 'mysql']);

echo "=== RESET USER PASSWORD ===\n\n";

$user = \App\Models\Central\User::first();

if ($user) {
    $newPassword = 'Zagreb123!';
    $user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
    $user->save();

    echo "✓ User aktualisiert!\n\n";
    echo "Email: {$user->email}\n";
    echo "Password: {$newPassword}\n";
    echo "\nLogin URL: http://localhost:8000/admin/login\n";

    // Test password
    if (\Illuminate\Support\Facades\Hash::check($newPassword, $user->password)) {
        echo "\n✓ Password Hash Test erfolgreich!\n";
    }
} else {
    echo "✗ Kein User gefunden. Erstelle neuen User...\n\n";

    $user = \App\Models\Central\User::create([
        'name' => 'Admin',
        'email' => 'admin@klubportal.com',
        'password' => \Illuminate\Support\Facades\Hash::make('Zagreb123!'),
    ]);

    echo "✓ Neuer User erstellt!\n\n";
    echo "Email: admin@klubportal.com\n";
    echo "Password: Zagreb123!\n";
    echo "\nLogin URL: http://localhost:8000/admin/login\n";
}
