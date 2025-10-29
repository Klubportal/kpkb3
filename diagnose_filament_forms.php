<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FILAMENT FORM DIAGNOSE ===\n\n";

// Check Filament version
echo "Filament Version: ";
$composer = json_decode(file_get_contents('composer.lock'), true);
foreach ($composer['packages'] as $package) {
    if ($package['name'] === 'filament/filament') {
        echo $package['version'] . "\n";
        break;
    }
}

echo "\n=== Checking Schema API ===\n";
echo "Schema class exists: " . (class_exists('Filament\Schemas\Schema') ? "✓ YES" : "✗ NO") . "\n";
echo "Form class exists: " . (class_exists('Filament\Forms\Form') ? "✓ YES" : "✗ NO") . "\n";

echo "\n=== Checking Resource Pages ===\n";

$editPages = [
    'App\Filament\Club\Resources\TemplateSettingResource\Pages\EditTemplateSetting',
    'App\Filament\Club\Resources\NewsResource\Pages\EditNews',
    'App\Filament\Central\Resources\TenantResource\Pages\EditTenant',
];

foreach ($editPages as $page) {
    echo "\n$page:\n";
    if (class_exists($page)) {
        $reflection = new ReflectionClass($page);
        $hasFormActions = $reflection->hasMethod('getFormActions');
        $hasHeaderActions = $reflection->hasMethod('getHeaderActions');

        echo "  - Has getFormActions(): " . ($hasFormActions ? "✓ YES" : "✗ NO") . "\n";
        echo "  - Has getHeaderActions(): " . ($hasHeaderActions ? "✓ YES" : "✗ NO") . "\n";

        // Check parent
        $parent = $reflection->getParentClass();
        echo "  - Parent: " . $parent->getName() . "\n";
        echo "  - Parent has getFormActions(): " . ($parent->hasMethod('getFormActions') ? "✓ YES" : "✗ NO") . "\n";
    } else {
        echo "  ✗ Class not found\n";
    }
}

echo "\n=== Checking Settings Pages ===\n";

$settingsPages = [
    'App\Filament\Central\Pages\ManageGeneralSettings',
    'App\Filament\Central\Pages\ManageEmailSettings',
];

foreach ($settingsPages as $page) {
    echo "\n$page:\n";
    if (class_exists($page)) {
        $reflection = new ReflectionClass($page);
        $hasForm = $reflection->hasMethod('form');
        $hasFormActions = $reflection->hasMethod('getFormActions');

        echo "  - Has form(): " . ($hasForm ? "✓ YES" : "✗ NO") . "\n";
        echo "  - Has getFormActions(): " . ($hasFormActions ? "✓ YES" : "✗ NO") . "\n";

        // Check parent
        $parent = $reflection->getParentClass();
        echo "  - Parent: " . $parent->getName() . "\n";
        echo "  - Parent has getFormActions(): " . ($parent->hasMethod('getFormActions') ? "✓ YES" : "✗ NO") . "\n";
    } else {
        echo "  ✗ Class not found\n";
    }
}

echo "\n=== DONE ===\n";
