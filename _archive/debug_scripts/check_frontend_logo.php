<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use App\Models\Tenant\TemplateSetting;

echo "🔍 CHECKING FRONTEND LOGO DISPLAY\n";
echo "==================================\n\n";

$tenant = Tenant::find('nknapijed');
tenancy()->initialize($tenant);

$settings = TemplateSetting::first();

echo "Template: " . ($tenant->template ?? 'kp') . "\n";
echo "Logo in DB: " . ($settings->logo ?? 'NULL') . "\n\n";

echo "🔗 URL GENERATION:\n";
echo "==================\n";

// What asset() generates (WRONG for tenants)
$assetUrl = "http://localhost:8000/storage/" . $settings->logo;
echo "❌ asset() URL (falsch): {$assetUrl}\n";

// What it SHOULD be for tenants
$correctUrl = "http://nknapijed.localhost:8000/storage/" . $settings->logo;
echo "✅ Korrekte URL: {$correctUrl}\n\n";

echo "📝 TEMPLATE PFADE:\n";
echo "==================\n";

$template = $tenant->template ?? 'kp';
$navbarPath = "resources/views/templates/{$template}/partials/navbar.blade.php";

echo "Navbar Template: {$navbarPath}\n";

if (file_exists($navbarPath)) {
    echo "✅ Template existiert\n\n";

    // Read the navbar file
    $content = file_get_contents($navbarPath);

    // Check what's used for logo
    if (strpos($content, "asset('storage/") !== false) {
        echo "⚠️ PROBLEM GEFUNDEN!\n";
        echo "Template verwendet: asset('storage/' . \$settings->logo)\n";
        echo "Das generiert eine zentrale URL statt Tenant-URL\n\n";

        echo "🔧 LÖSUNG:\n";
        echo "Ersetzen mit: url('storage/' . \$settings->logo)\n";
        echo "Oder: '/storage/' . \$settings->logo\n";
    }
} else {
    echo "❌ Template nicht gefunden\n";
}

tenancy()->end();

echo "\n✅ Analyse abgeschlossen\n";
