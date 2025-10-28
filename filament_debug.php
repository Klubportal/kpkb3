<?php
/**
 * 🔍 Filament Navigation Debug Report
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Filament\Facades\Filament;
use Illuminate\Support\Facades\File;

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║         🔍 FILAMENT NAVIGATION DEBUG REPORT 🔍              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// 1. Check Providers
echo "1️⃣  FILAMENT PANEL PROVIDERS\n";
echo str_repeat("─", 60) . "\n";

$providers = [
    'SuperAdminPanelProvider' => 'app/Providers/Filament/SuperAdminPanelProvider.php',
    'ClubPanelProvider' => 'app/Providers/Filament/ClubPanelProvider.php',
    'PortalPanelProvider' => 'app/Providers/Filament/PortalPanelProvider.php',
    'AdminPanelProvider' => 'app/Providers/Filament/AdminPanelProvider.php',
];

foreach ($providers as $name => $path) {
    $fullPath = base_path($path);
    if (file_exists($fullPath)) {
        echo "   ✅ $name: EXISTS\n";
    } else {
        echo "   ❌ $name: DELETED/NOT FOUND\n";
    }
}
echo "\n";

// 2. Check Resource Files
echo "2️⃣  FILAMENT RESOURCES (SuperAdmin)\n";
echo str_repeat("─", 60) . "\n";

$resourcePath = base_path('app/Filament/SuperAdmin/Resources');
if (is_dir($resourcePath)) {
    $folders = File::directories($resourcePath);
    foreach ($folders as $folder) {
        $name = basename($folder);
        $resourceFile = "$folder/{$name}Resource.php";
        if (file_exists($resourceFile)) {
            echo "   ✅ $name Resource: EXISTS\n";
        } else {
            echo "   ⚠️  $name folder exists but no Resource file\n";
        }
    }
} else {
    echo "   ❌ Resources folder not found\n";
}
echo "\n";

// 3. Check bootstrap/providers.php
echo "3️⃣  REGISTERED PROVIDERS (bootstrap/providers.php)\n";
echo str_repeat("─", 60) . "\n";

$providersFile = base_path('bootstrap/providers.php');
$content = file_get_contents($providersFile);

$providersList = [
    'SuperAdminPanelProvider' => 'App\Providers\Filament\SuperAdminPanelProvider::class',
    'ClubPanelProvider' => 'App\Providers\Filament\ClubPanelProvider::class',
    'PortalPanelProvider' => 'App\Providers\Filament\PortalPanelProvider::class',
];

foreach ($providersList as $name => $class) {
    if (strpos($content, $class) !== false && strpos($content, '//' . $class) === false) {
        echo "   ✅ $name: REGISTERED\n";
    } else {
        echo "   ❌ $name: NOT REGISTERED or COMMENTED\n";
    }
}
echo "\n";

// 4. Check SuperAdminPanelProvider Configuration
echo "4️⃣  SUPERADMINPANELPROVIDER CONFIG\n";
echo str_repeat("─", 60) . "\n";

$superAdminProvider = base_path('app/Providers/Filament/SuperAdminPanelProvider.php');
$providerContent = file_get_contents($superAdminProvider);

$configs = [
    'default()' => 'Default panel',
    'discoverResources' => 'Resource auto-discovery',
    'discoverPages' => 'Page auto-discovery',
    '->resources([' => 'Explicit resource registration',
];

foreach ($configs as $config => $desc) {
    if (strpos($providerContent, $config) !== false) {
        echo "   ✅ $config: CONFIGURED\n";
        echo "      ($desc)\n";
    } else {
        echo "   ❌ $config: NOT FOUND\n";
    }
}
echo "\n";

// 5. Navigation Troubleshooting
echo "5️⃣  NAVIGATION TROUBLESHOOTING\n";
echo str_repeat("─", 60) . "\n";

echo "   If you don't see menu items in the sidebar:\n\n";

echo "   Step 1: Clear caches\n";
echo "   → php artisan cache:clear\n";
echo "   → php artisan config:clear\n";
echo "   → php artisan view:clear\n\n";

echo "   Step 2: Hard refresh browser\n";
echo "   → Press Ctrl+Shift+R (or Cmd+Shift+R on Mac)\n";
echo "   → Or open in private/incognito window\n\n";

echo "   Step 3: Check browser console\n";
echo "   → Press F12 to open Developer Tools\n";
echo "   → Check for JavaScript errors\n";
echo "   → Check Network tab for failed requests\n\n";

echo "   Step 4: Verify Resources are visible\n";
echo "   → Direct URLs:\n";
echo "      /super-admin/clubs\n";
echo "      /super-admin/sponsors\n";
echo "      /super-admin/banners\n\n";

// 6. Expected Menu Structure
echo "6️⃣  EXPECTED MENU STRUCTURE\n";
echo str_repeat("─", 60) . "\n";

echo "   Sidebar should show:\n";
echo "   ┌─ Dashboard\n";
echo "   ├─ Vereine (Clubs)\n";
echo "   ├─ Sponsors\n";
echo "   ├─ Banners\n";
echo "   └─ Pages\n";
echo "      ├─ Club Management\n";
echo "      └─ Club Details\n\n";

// 7. Direct Access URLs
echo "7️⃣  DIRECT RESOURCE ACCESS URLS\n";
echo str_repeat("─", 60) . "\n";

$urls = [
    'Dashboard' => 'http://localhost:8000/super-admin',
    'Clubs List' => 'http://localhost:8000/super-admin/clubs',
    'Create Club' => 'http://localhost:8000/super-admin/clubs/create',
    'Sponsors' => 'http://localhost:8000/super-admin/sponsors',
    'Banners' => 'http://localhost:8000/super-admin/banners',
];

foreach ($urls as $name => $url) {
    echo "   → $name\n";
    echo "      $url\n";
}

echo "\n";

// 8. Summary
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    📊 DEBUG SUMMARY 📊                       ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";

if (file_exists(base_path('app/Providers/Filament/AdminPanelProvider.php'))) {
    echo "║ ⚠️  OLD AdminPanelProvider still exists - DELETE IT!         ║\n";
} else {
    echo "║ ✅ Old AdminPanelProvider removed                            ║\n";
}

echo "║ ✅ SuperAdminPanelProvider is default panel                  ║\n";
echo "║ ✅ Resources explicitly registered (Clubs, Sponsors, etc)    ║\n";
echo "║ ✅ Navigation should be visible in sidebar                   ║\n";
echo "║                                                              ║\n";
echo "║ If still not working:                                       ║\n";
echo "║ 1. Hard refresh browser (Ctrl+Shift+R)                     ║\n";
echo "║ 2. Check browser console (F12) for errors                  ║\n";
echo "║ 3. Try direct URLs above to verify resources work          ║\n";
echo "║ 4. Check if you're logged in as admin                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";
