<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

config(['database.default' => 'mysql']);

echo "=== FILAMENT PANEL DEBUG ===\n\n";

// Get Filament Panel
try {
    $panel = \Filament\Facades\Filament::getPanel('central');
    echo "✓ Central Panel gefunden\n";
    echo "  ID: " . $panel->getId() . "\n";
    echo "  Path: " . $panel->getPath() . "\n";
    echo "  Auth Guard: " . $panel->getAuthGuard() . "\n";

    // Get User
    $user = \App\Models\Central\User::first();
    echo "\n✓ User gefunden: {$user->email}\n";

    // Test canAccessPanel
    echo "\n=== canAccessPanel Test ===\n";
    $canAccess = $user->canAccessPanel($panel);
    echo ($canAccess ? "✓" : "✗") . " canAccessPanel() = " . ($canAccess ? 'TRUE' : 'FALSE') . "\n";

    if (!$canAccess) {
        echo "\n❌ PROBLEM: canAccessPanel() gibt FALSE zurück!\n";
        echo "   User kann Panel nicht betreten.\n";
    }

    // Check Auth Middleware
    echo "\n=== Panel Middleware ===\n";
    $middleware = $panel->getMiddleware();
    foreach ($middleware as $mw) {
        echo "  - " . (is_string($mw) ? $mw : get_class($mw)) . "\n";
    }

    echo "\n=== Auth Middleware ===\n";
    $authMiddleware = $panel->getAuthMiddleware();
    foreach ($authMiddleware as $mw) {
        echo "  - " . (is_string($mw) ? $mw : get_class($mw)) . "\n";
    }

} catch (\Exception $e) {
    echo "✗ Fehler: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== LÖSUNG ===\n";
echo "Wenn canAccessPanel() FALSE zurückgibt, muss die Methode angepasst werden.\n";
echo "Aktuell in app/Models/Central/User.php Zeile 78\n";
