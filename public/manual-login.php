<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

config(['database.default' => 'mysql']);

echo "=== MANUAL LOGIN TEST ===\n\n";

// Get User
$user = \App\Models\Central\User::where('email', 'info@klubportal.com')->first();

if (!$user) {
    echo "✗ User nicht gefunden!\n";
    exit(1);
}

echo "✓ User gefunden: {$user->email}\n";

// Check password
$password = 'Zagreb123!';
if (!\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
    echo "✗ Password falsch!\n";
    exit(1);
}

echo "✓ Password korrekt!\n";

// Check canAccessPanel
$panel = \Filament\Facades\Filament::getPanel('central');
$canAccess = $user->canAccessPanel($panel);

echo "✓ canAccessPanel('central'): " . ($canAccess ? 'true' : 'false') . "\n";

// Try to login
\Illuminate\Support\Facades\Auth::guard('web')->login($user);

if (\Illuminate\Support\Facades\Auth::guard('web')->check()) {
    echo "✓ User erfolgreich eingeloggt!\n";
    echo "\nAuthenticated User: " . \Illuminate\Support\Facades\Auth::guard('web')->user()->email . "\n";
} else {
    echo "✗ Login fehlgeschlagen!\n";
}

echo "\n=== SESSION ERSTELLEN ===\n";
echo "Öffne: http://localhost:8000/admin\n";
echo "Du solltest jetzt eingeloggt sein!\n";
