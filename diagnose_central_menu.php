<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Filament\Facades\Filament;

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║         CENTRAL BACKEND - NAVIGATION PROBLEM LÖSUNG             ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Login User
$user = User::where('email', 'info@klubportal.com')->first();
auth()->guard('web')->login($user);
Filament::setCurrentPanel(Filament::getPanel('central'));

echo "=== USER INFO ===\n";
echo "Email: {$user->email}\n";
echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
echo "Permissions: " . $user->getAllPermissions()->count() . "\n\n";

echo "=== CRITICAL PERMISSIONS ===\n";
$perms = ['ViewAny:Tenant', 'ViewAny:News', 'ViewAny:NewsCategory', 'ViewAny:Page'];
foreach ($perms as $p) {
    echo ($user->can($p) ? '✓' : '✗') . " {$p}\n";
}

echo "\n=== RESOURCES canViewAny() CHECK ===\n";
$resources = [
    'App\Filament\Central\Resources\ClubResource' => 'ClubResource',
    'App\Filament\Central\Resources\NewsResource' => 'NewsResource',
    'App\Filament\Central\Resources\NewsCategoryResource' => 'NewsCategoryResource',
    'App\Filament\Central\Resources\PageResource' => 'PageResource',
];

foreach ($resources as $class => $name) {
    $canView = $class::canViewAny();
    echo ($canView ? '✓' : '✗') . " {$name}: " . ($canView ? 'TRUE' : 'FALSE') . "\n";
}

echo "\n=== PANEL CONFIGURATION ===\n";
$panel = Filament::getPanel('central');
$registeredResources = $panel->getResources();
echo "Registered Resources: " . count($registeredResources) . "\n";
foreach ($registeredResources as $r) {
    echo "  • " . class_basename($r) . "\n";
}

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║                         LÖSUNG                                   ║\n";
echo "╠══════════════════════════════════════════════════════════════════╣\n";

if (count(array_filter($resources, fn($c) => $c::canViewAny())) === count($resources)) {
    echo "║ ✓ Backend funktioniert perfekt!                                 ║\n";
    echo "║ ✓ Alle Resources geben canViewAny() = TRUE                      ║\n";
    echo "║                                                                  ║\n";
    echo "║ Das Problem IST im Browser:                                     ║\n";
    echo "║                                                                  ║\n";
    echo "║ SOFORT-LÖSUNG:                                                  ║\n";
    echo "║ 1. Öffne INKOGNITO/PRIVAT-Fenster                               ║\n";
    echo "║ 2. Gehe zu: http://localhost:8000/admin                         ║\n";
    echo "║ 3. Login: info@klubportal.com                                   ║\n";
    echo "║ 4. Alle Menüs sollten SOFORT sichtbar sein!                     ║\n";
    echo "║                                                                  ║\n";
    echo "║ PERMANENTE LÖSUNG:                                              ║\n";
    echo "║ 1. Im normalen Browser: Ausloggen                               ║\n";
    echo "║ 2. Browser komplett schließen                                   ║\n";
    echo "║ 3. Browser-Daten löschen:                                       ║\n";
    echo "║    Chrome/Edge: Ctrl+Shift+Del                                  ║\n";
    echo "║    → Cookies und Websitedaten löschen                           ║\n";
    echo "║    → Bilder und Dateien im Cache löschen                        ║\n";
    echo "║ 4. Browser neu öffnen                                           ║\n";
    echo "║ 5. Zu http://localhost:8000/admin                               ║\n";
    echo "║ 6. Neu einloggen                                                ║\n";
} else {
    echo "║ ✗ Einige Resources geben canViewAny() = FALSE                   ║\n";
    echo "║ → Prüfe Policies und Permissions                                ║\n";
}

echo "╚══════════════════════════════════════════════════════════════════╝\n";
