<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\Central\User;

$email = 'info@klubportal.com';
$password = 'Zagreb12#!';

echo "=== DETAILLIERTER LOGIN TEST ===\n\n";

// 1. Teste User Model direkt
echo "1. User Model Test:\n";
$user = User::where('email', $email)->first();
if ($user) {
    echo "   ✓ User gefunden via Model (ID: {$user->id})\n";
    echo "   Connection: {$user->getConnectionName()}\n";
    echo "   Guard Name: {$user->guard_name}\n";
} else {
    echo "   ✗ User NICHT gefunden via Model!\n";
}

// 2. Teste Auth mit 'central' Guard
echo "\n2. Auth::guard('central') Test:\n";
try {
    $credentials = ['email' => $email, 'password' => $password];

    if (Auth::guard('central')->attempt($credentials)) {
        echo "   ✓✓✓ LOGIN MIT 'central' GUARD ERFOLGREICH!\n";
        $authUser = Auth::guard('central')->user();
        echo "   Eingeloggter User: {$authUser->name} ({$authUser->email})\n";
        Auth::guard('central')->logout();
    } else {
        echo "   ✗✗✗ LOGIN MIT 'central' GUARD FEHLGESCHLAGEN!\n";

        // Debug: Provider prüfen
        $provider = Auth::guard('central')->getProvider();
        echo "   Provider: " . get_class($provider) . "\n";
        echo "   Model: " . $provider->getModel() . "\n";

        // User direkt vom Provider holen
        $userFromProvider = $provider->retrieveByCredentials($credentials);
        if ($userFromProvider) {
            echo "   ✓ User vom Provider gefunden\n";

            // Passwort-Validierung
            if ($provider->validateCredentials($userFromProvider, $credentials)) {
                echo "   ✓ Passwort vom Provider validiert\n";
            } else {
                echo "   ✗ Passwort-Validierung fehlgeschlagen!\n";
            }
        } else {
            echo "   ✗ User vom Provider NICHT gefunden!\n";
        }
    }
} catch (\Exception $e) {
    echo "   FEHLER: {$e->getMessage()}\n";
    echo "   Trace: {$e->getTraceAsString()}\n";
}

// 3. Teste Auth mit 'web' Guard
echo "\n3. Auth::guard('web') Test:\n";
try {
    $credentials = ['email' => $email, 'password' => $password];

    if (Auth::guard('web')->attempt($credentials)) {
        echo "   ✓✓✓ LOGIN MIT 'web' GUARD ERFOLGREICH!\n";
        $authUser = Auth::guard('web')->user();
        echo "   Eingeloggter User: {$authUser->name} ({$authUser->email})\n";
        Auth::guard('web')->logout();
    } else {
        echo "   ✗✗✗ LOGIN MIT 'web' GUARD FEHLGESCHLAGEN!\n";
    }
} catch (\Exception $e) {
    echo "   FEHLER: {$e->getMessage()}\n";
}

// 4. Zeige Config
echo "\n4. Auth Konfiguration:\n";
echo "   Default Guard: " . config('auth.defaults.guard') . "\n";
echo "   Central Guard Provider: " . config('auth.guards.central.provider') . "\n";
echo "   Central Provider Model: " . config('auth.providers.central_users.model') . "\n";
