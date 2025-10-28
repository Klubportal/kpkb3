<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use App\Models\Tenant\TemplateSetting;

echo "✅ FRONTEND LOGO FIX - VERIFICATION\n";
echo "===================================\n\n";

$tenant = Tenant::find('nknapijed');
tenancy()->initialize($tenant);

$settings = TemplateSetting::first();
$template = $tenant->template ?? 'kp';

echo "🎯 CONFIGURATION:\n";
echo "=================\n";
echo "Tenant: {$tenant->name}\n";
echo "Template: {$template}\n";
echo "Logo in DB: {$settings->logo}\n\n";

echo "🔗 LOGO URL GENERATION:\n";
echo "=======================\n";

$logoUrl = url('storage/' . $settings->logo);
echo "Generated URL: {$logoUrl}\n";
echo "Expected URL: http://nknapijed.localhost:8000/storage/{$settings->logo}\n\n";

if ($logoUrl === "http://nknapijed.localhost:8000/storage/{$settings->logo}") {
    echo "✅ URL generiert korrekt!\n\n";
} else {
    echo "⚠️ URL könnte falsch sein (abhängig von APP_URL)\n\n";
}

echo "📝 TEMPLATE DATEIEN:\n";
echo "====================\n";

$navbarFile = "resources/views/templates/{$template}/partials/navbar.blade.php";
echo "Navbar: {$navbarFile}\n";

if (file_exists($navbarFile)) {
    $content = file_get_contents($navbarFile);

    if (strpos($content, "url('storage/") !== false) {
        echo "✅ Verwendet url() - KORREKT\n";
    } elseif (strpos($content, "asset('storage/") !== false) {
        echo "❌ Verwendet noch asset() - FALSCH\n";
    }
} else {
    echo "❌ Datei nicht gefunden\n";
}

echo "\n";

echo "🌐 FRONTEND URLS ZUM TESTEN:\n";
echo "============================\n";
echo "1. Homepage: http://nknapijed.localhost:8000/\n";
echo "2. Direkt-Logo: {$logoUrl}\n\n";

echo "💡 NÄCHSTE SCHRITTE:\n";
echo "====================\n";
echo "1. Öffnen Sie: http://nknapijed.localhost:8000/\n";
echo "2. Das Logo sollte jetzt in der Navbar erscheinen\n";
echo "3. Browser-Cache leeren wenn nötig (Strg+Shift+R)\n";
echo "4. DevTools > Network Tab prüfen ob Logo geladen wird\n";

tenancy()->end();

echo "\n✅ Verification complete!\n";
