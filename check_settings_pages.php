<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING SETTINGS PAGES ===\n\n";

$settingsPages = [
    'App\Filament\Central\Pages\ManageGeneralSettings',
    'App\Filament\Central\Pages\ManageEmailSettings',
    'App\Filament\Central\Pages\ManageContactSettings',
    'App\Filament\Central\Pages\ManageThemeSettings',
    'App\Filament\Central\Pages\ManageSocialMediaSettings',
];

foreach ($settingsPages as $page) {
    echo "\n" . str_replace('App\\Filament\\Central\\Pages\\', '', $page) . ":\n";

    if (!class_exists($page)) {
        echo "  ✗ Class not found\n";
        continue;
    }

    try {
        $reflection = new ReflectionClass($page);
        $file = $reflection->getFileName();
        $content = file_get_contents($file);

        // Check imports
        $hasFormsImport = strpos($content, 'use Filament\Forms') !== false;
        $hasSchemasImport = strpos($content, 'use Filament\Schemas') !== false;
        $hasSectionImport = strpos($content, 'use Filament\Schemas\Components\Section') !== false;

        echo "  Has 'use Filament\Forms': " . ($hasFormsImport ? "✓" : "✗") . "\n";
        echo "  Has 'use Filament\Schemas': " . ($hasSchemasImport ? "✓" : "✗") . "\n";
        echo "  Has Section import: " . ($hasSectionImport ? "✓" : "✗") . "\n";

        // Check methods
        $hasForm = $reflection->hasMethod('form');
        echo "  Has form() method: " . ($hasForm ? "✓" : "✗") . "\n";

        // Try to instantiate (without actually rendering)
        echo "  Class can be loaded: ✓\n";

    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== DONE ===\n";
