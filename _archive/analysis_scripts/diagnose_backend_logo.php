<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use App\Models\Tenant\TemplateSetting;
use Illuminate\Support\Facades\Storage;

echo "🔍 CHECKING BACKEND LOGO DISPLAY\n";
echo "=================================\n\n";

$tenant = Tenant::find('nknapijed');
tenancy()->initialize($tenant);

$settings = TemplateSetting::first();

echo "📊 DATENBANK:\n";
echo "=============\n";
echo "Logo in DB: " . ($settings->logo ?? 'NULL') . "\n\n";

echo "📁 STORAGE:\n";
echo "===========\n";
$disk = Storage::disk('public');
echo "Disk: public\n";
echo "Root: " . $disk->path('') . "\n";

if ($settings->logo && $disk->exists($settings->logo)) {
    echo "✅ Datei existiert: {$settings->logo}\n";
    $size = $disk->size($settings->logo);
    echo "   Größe: " . number_format($size / 1024, 2) . " KB\n\n";
} else {
    echo "❌ Datei NICHT gefunden\n\n";
}

echo "🔗 URL TESTS:\n";
echo "=============\n";

// Test verschiedene URL-Generierungsmethoden
$logoPath = $settings->logo;

echo "1. asset() - verwendet in Landing:\n";
echo "   " . asset('storage/' . $logoPath) . "\n\n";

echo "2. Storage::disk('public')->url() - Filament Standard:\n";
try {
    // Filament ImageColumn verwendet intern temporaryUrl oder url
    $url = '/storage/' . $logoPath;
    echo "   {$url}\n\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n\n";
}

echo "3. Relative URL (korrekt für Tenants):\n";
echo "   /storage/{$logoPath}\n\n";

echo "4. Volle Tenant URL:\n";
echo "   http://nknapijed.localhost:8000/storage/{$logoPath}\n\n";

echo "📋 FILAMENT CONFIGURATION:\n";
echo "==========================\n";

echo "Default filesystem disk: " . config('filament.default_filesystem_disk') . "\n";
echo "Filesystems default: " . config('filesystems.default') . "\n\n";

echo "🔍 TESTING FILAMENT ImageColumn Logic:\n";
echo "=======================================\n";

// Simuliere was Filament macht
$diskName = 'public';
$visibility = 'public';

echo "FileUpload verwendet:\n";
echo "  - disk('public')\n";
echo "  - visibility('public')\n";
echo "  - directory('logos')\n\n";

echo "ImageColumn sollte verwenden:\n";
echo "  - disk('public')\n";
echo "  - visibility('public')\n\n";

// Check wie Filament die URL generiert
echo "Filament URL-Generierung:\n";

// Für public disk mit visibility public sollte Filament die URL direkt generieren
$filamentUrl = "/storage/{$logoPath}";
echo "  Erwartete URL: {$filamentUrl}\n";

// Test ob Route funktioniert
echo "\n🧪 ROUTE TEST:\n";
echo "==============\n";
echo "Storage Route registriert: ";
$routes = app()->router->getRoutes();
$storageRoute = null;
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'storage/{path}')) {
        $storageRoute = $route;
        break;
    }
}
echo ($storageRoute ? "✅ JA" : "❌ NEIN") . "\n";

if ($storageRoute) {
    echo "Route URI: " . $storageRoute->uri() . "\n";
    echo "Route Name: " . ($storageRoute->getName() ?? 'unnamed') . "\n";
}

tenancy()->end();

echo "\n✅ Diagnose abgeschlossen\n\n";

echo "💡 MÖGLICHE PROBLEME:\n";
echo "====================\n";
echo "1. Filament ImageColumn generiert falsche URL (asset statt relative)\n";
echo "2. Browser cached alte Version\n";
echo "3. FileUpload speichert Pfad falsch in DB\n";
echo "4. Storage Route wird nicht getroffen\n";
