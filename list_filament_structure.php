<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║          FILAMENT RESOURCES & PAGES INVENTORY                    ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "=== CENTRAL PANEL (app/Filament/Central) ===\n\n";

echo "--- RESOURCES ---\n";
$centralResources = [
    'ClubResource' => 'app/Filament/Central/Resources/ClubResource.php',
    'TenantResource' => 'app/Filament/Central/Resources/TenantResource.php',
    'NewsResource' => 'app/Filament/Central/Resources/NewsResource.php',
    'NewsCategoryResource' => 'app/Filament/Central/Resources/NewsCategoryResource.php',
    'PageResource' => 'app/Filament/Central/Resources/PageResource.php',
    'TemplateResource' => 'app/Filament/Central/Resources/TemplateResource.php',
    'FabricatorPageResource' => 'app/Filament/Central/Resources/FabricatorPageResource.php',
    'CustomLanguageLineResource' => 'app/Filament/Central/Resources/CustomLanguageLineResource.php',
];

foreach ($centralResources as $name => $path) {
    $exists = file_exists(base_path($path)) ? '✓' : '✗';
    $registered = in_array($name, ['ClubResource', 'TenantResource', 'NewsResource', 'NewsCategoryResource', 'PageResource']) ? '[REGISTERED]' : '[NOT REGISTERED]';
    echo "{$exists} {$name} {$registered}\n";
    if ($exists === '✓') {
        // Check if it has syntax errors
        $content = file_get_contents(base_path($path));
        if (strpos($content, 'namespace App\Filament\Central\Resources;') !== false) {
            echo "   ✓ Valid namespace\n";
        }
    }
}

echo "\n--- PAGES ---\n";
$centralPages = [
    'Backups' => 'app/Filament/Central/Pages/Backups.php',
    'ManageGeneralSettings' => 'app/Filament/Central/Pages/ManageGeneralSettings.php',
    'ManageThemeSettings' => 'app/Filament/Central/Pages/ManageThemeSettings.php',
    'ManageSocialMediaSettings' => 'app/Filament/Central/Pages/ManageSocialMediaSettings.php',
    'ManageEmailSettings' => 'app/Filament/Central/Pages/ManageEmailSettings.php',
    'ManageContactSettings' => 'app/Filament/Central/Pages/ManageContactSettings.php',
    'RestoreBackup' => 'app/Filament/Central/Pages/RestoreBackup.php',
];

foreach ($centralPages as $name => $path) {
    $exists = file_exists(base_path($path)) ? '✓' : '✗';
    echo "{$exists} {$name}\n";
}

echo "\n=== CLUB PANEL (app/Filament/Club) ===\n\n";

echo "--- RESOURCES ---\n";
$clubResources = [
    'NewsResource' => 'app/Filament/Club/Resources/NewsResource.php',
    'TemplateSettingResource' => 'app/Filament/Club/Resources/TemplateSettingResource.php',
];

foreach ($clubResources as $name => $path) {
    $exists = file_exists(base_path($path)) ? '✓' : '✗';
    echo "{$exists} {$name}\n";
    if ($exists === '✓') {
        $content = file_get_contents(base_path($path));
        if (strpos($content, 'namespace App\Filament\Club\Resources;') !== false) {
            echo "   ✓ Valid namespace\n";
        } else {
            echo "   ✗ Invalid/missing namespace\n";
        }
    }
}

echo "\n--- PAGES ---\n";
$clubPages = [
    'Dashboard' => 'app/Filament/Club/Pages/Dashboard.php',
    'ManageClubSettings' => 'app/Filament/Club/Pages/ManageClubSettings.php',
];

foreach ($clubPages as $name => $path) {
    $exists = file_exists(base_path($path)) ? '✓' : '✗';
    echo "{$exists} {$name}\n";
}

echo "\n=== REGISTERED IN PROVIDERS ===\n\n";

// Check CentralPanelProvider
$centralProviderPath = 'app/Providers/Filament/CentralPanelProvider.php';
if (file_exists(base_path($centralProviderPath))) {
    $content = file_get_contents(base_path($centralProviderPath));

    echo "CentralPanelProvider - Registered Resources:\n";
    preg_match_all('/(\w+Resource)::class/', $content, $matches);
    foreach (array_unique($matches[1]) as $resource) {
        echo "  • {$resource}\n";
    }

    echo "\nCentralPanelProvider - discoverPages():\n";
    if (strpos($content, '->discoverPages(') !== false) {
        echo "  ✓ Auto-discovery enabled für Pages\n";
    } else {
        echo "  ✗ No auto-discovery\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Central Resources: " . count(array_filter($centralResources, fn($p) => file_exists(base_path($p)))) . "/" . count($centralResources) . "\n";
echo "Central Pages: " . count(array_filter($centralPages, fn($p) => file_exists(base_path($p)))) . "/" . count($centralPages) . "\n";
echo "Club Resources: " . count(array_filter($clubResources, fn($p) => file_exists(base_path($p)))) . "/" . count($clubResources) . "\n";
echo "Club Pages: " . count(array_filter($clubPages, fn($p) => file_exists(base_path($p)))) . "/" . count($clubPages) . "\n";
