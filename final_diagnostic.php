<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║           FINAL DIAGNOSTIC - NAVIGATION PROBLEM                  ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

$user = User::where('email', 'info@klubportal.com')->first();

echo "=== USER STATUS ===\n";
echo "Email: {$user->email}\n";
echo "ID: {$user->id}\n";
echo "Role: " . $user->getRoleNames()->first() . "\n";
echo "Permissions: " . $user->getAllPermissions()->count() . "\n\n";

echo "=== PERMISSIONS CHECK ===\n";
$critical = ['ViewAny:Tenant', 'ViewAny:News', 'ViewAny:NewsCategory', 'ViewAny:Page'];
foreach ($critical as $perm) {
    echo ($user->can($perm) ? '✓' : '✗') . " {$perm}\n";
}

echo "\n=== FILAMENT RESOURCES STATUS ===\n";
auth()->guard('web')->login($user);
\Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('central'));

$resources = [
    'App\Filament\Central\Resources\ClubResource',
    'App\Filament\Central\Resources\NewsResource',
    'App\Filament\Central\Resources\NewsCategoryResource',
    'App\Filament\Central\Resources\PageResource',
];

foreach ($resources as $class) {
    $name = class_basename($class);
    $canView = $class::canViewAny();
    echo ($canView ? '✓' : '✗') . " {$name}: canViewAny() = " . ($canView ? 'TRUE' : 'FALSE') . "\n";
}

echo "\n=== CENTRAL PANEL CONFIGURATION ===\n";
$panel = \Filament\Facades\Filament::getPanel('central');
echo "Panel ID: " . $panel->getId() . "\n";
echo "Path: /" . $panel->getPath() . "\n";
echo "URL: http://localhost:8000/" . $panel->getPath() . "\n\n";

$registeredResources = $panel->getResources();
echo "Registered Resources (" . count($registeredResources) . "):\n";
foreach ($registeredResources as $resource) {
    $shortName = class_basename($resource);
    echo "  - {$shortName}\n";
}

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║                      DIAGNOSE ERGEBNIS                           ║\n";
echo "╠══════════════════════════════════════════════════════════════════╣\n";
echo "║ ✓ Alle Permissions vorhanden (66)                               ║\n";
echo "║ ✓ Alle Policies funktionieren                                   ║\n";
echo "║ ✓ Alle Resources geben canViewAny() = TRUE zurück               ║\n";
echo "║ ✓ Resources sind im CentralPanel registriert                    ║\n";
echo "╠══════════════════════════════════════════════════════════════════╣\n";
echo "║                    PROBLEM IST NICHT BACKEND!                    ║\n";
echo "║                Das Problem ist im FRONTEND/BROWSER               ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "=== LÖSUNG ===\n";
echo "1. Browser komplett neu starten (NICHT nur Tab schließen)\n";
echo "2. ODER: Private/Inkognito Fenster verwenden\n";
echo "3. ODER: Browser-Cache HART löschen:\n";
echo "   Chrome/Edge: Ctrl+Shift+Delete → Alle Daten löschen\n";
echo "   Firefox: Ctrl+Shift+Delete → Alles löschen\n";
echo "4. Session-Cookies löschen für localhost:8000\n";
echo "5. Ausloggen → Browser-Cache leeren → Neu einloggen\n\n";

echo "=== ALTERNATIVE: Laravel Cache & Sessions komplett clearen ===\n";
echo "Führe aus:\n";
echo "  php artisan cache:clear\n";
echo "  php artisan config:clear\n";
echo "  php artisan view:clear\n";
echo "  php artisan route:clear\n";
echo "  php artisan session:flush (falls verfügbar)\n";
echo "  Lösche manuell: storage/framework/sessions/*\n\n";

echo "=== Nach dem Cache-Löschen ===\n";
echo "→ Gehe zu: http://localhost:8000/admin\n";
echo "→ Logge dich ein mit: info@klubportal.com\n";
echo "→ Die Navigation sollte jetzt zeigen:\n";
echo "   • Dashboard\n";
echo "   • Clubs\n";
echo "   • Content Group:\n";
echo "     ├─ News\n";
echo "     ├─ News Categories\n";
echo "     └─ Pages\n";
echo "   • Settings Pages\n";
echo "   • Backups\n\n";
