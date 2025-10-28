<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use App\Models\Central\User;

$email = 'info@klubportal.com';
$password = 'Zagreb12#!';

echo "=== FILAMENT LOGIN TEST ===\n\n";

// 1. Hole das Central Panel
echo "1. Filament Panels:\n";
$panels = Filament::getPanels();
foreach ($panels as $panelId => $panel) {
    echo "   - Panel ID: {$panelId}, Path: /{$panel->getPath()}\n";
    echo "     Auth Guard: " . ($panel->getAuthGuard() ?? 'default') . "\n";
}

// 2. Setze das Central Panel als aktiv
echo "\n2. Setze Central Panel:\n";
$centralPanel = Filament::getPanel('central');
Filament::setCurrentPanel($centralPanel);
echo "   ✓ Current Panel: " . Filament::getCurrentPanel()->getId() . "\n";
echo "   Auth Guard: " . ($centralPanel->getAuthGuard() ?? 'default') . "\n";

// 3. Teste Login mit Filament's Guard
echo "\n3. Login Test mit Panel Guard:\n";
$guardName = $centralPanel->getAuthGuard() ?? config('auth.defaults.guard');
echo "   Guard Name: {$guardName}\n";

$credentials = ['email' => $email, 'password' => $password];

if (Auth::guard($guardName)->attempt($credentials)) {
    echo "   ✓✓✓ LOGIN ERFOLGREICH!\n";

    $user = Auth::guard($guardName)->user();
    echo "   User: {$user->name} ({$user->email})\n";

    // 4. Teste canAccessPanel
    echo "\n4. canAccessPanel Test:\n";
    if ($user->canAccessPanel($centralPanel)) {
        echo "   ✓ User KANN auf Central Panel zugreifen\n";
    } else {
        echo "   ✗ User KANN NICHT auf Central Panel zugreifen!\n";
    }

    Auth::guard($guardName)->logout();
} else {
    echo "   ✗✗✗ LOGIN FEHLGESCHLAGEN!\n";

    // Debug
    echo "\n   DEBUG INFO:\n";

    // User direkt suchen
    $user = User::where('email', $email)->first();
    if ($user) {
        echo "   - User existiert (ID: {$user->id})\n";
        echo "   - Connection: {$user->getConnectionName()}\n";
        echo "   - Table: {$user->getTable()}\n";

        // Passwort prüfen
        if (Hash::check($password, $user->password)) {
            echo "   - Passwort STIMMT!\n";
        } else {
            echo "   - Passwort STIMMT NICHT!\n";
        }
    } else {
        echo "   - User NICHT gefunden!\n";
    }

    // Guard-Provider prüfen
    $guard = Auth::guard($guardName);
    echo "   - Guard Driver: " . get_class($guard) . "\n";
}
