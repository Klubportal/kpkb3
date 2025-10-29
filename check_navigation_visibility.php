<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Initialize tenant
tenancy()->initialize(tenancy()->find('nknapijed'));

echo "=== CHECKING TEMPLATE_SETTING_RESOURCE VISIBILITY ===\n\n";

// Check if TemplateSetting model exists
echo "1. Model Check:\n";
$modelExists = class_exists('App\Models\Tenant\TemplateSetting');
echo "   TemplateSetting Model exists: " . ($modelExists ? '✓ YES' : '✗ NO') . "\n";

// Check if Resource exists
echo "\n2. Resource Check:\n";
$resourceExists = class_exists('App\Filament\Club\Resources\TemplateSettingResource');
echo "   TemplateSettingResource exists: " . ($resourceExists ? '✓ YES' : '✗ NO') . "\n";

if ($resourceExists) {
    $reflection = new ReflectionClass('App\Filament\Club\Resources\TemplateSettingResource');

    // Get navigation properties
    echo "\n3. Navigation Properties:\n";

    $properties = [
        'navigationIcon' => null,
        'navigationLabel' => null,
        'navigationGroup' => null,
        'navigationSort' => null,
        'model' => null,
    ];

    foreach ($properties as $propName => $value) {
        try {
            $prop = $reflection->getProperty($propName);
            $prop->setAccessible(true);
            $value = $prop->getValue();
            echo sprintf("   %-20s = %s\n", '$'.$propName, var_export($value, true));
        } catch (ReflectionException $e) {
            echo sprintf("   %-20s = NOT DEFINED\n", '$'.$propName);
        }
    }

    // Check methods
    echo "\n4. Available Methods:\n";
    $methods = ['form', 'table', 'getPages'];
    foreach ($methods as $method) {
        $hasMethod = $reflection->hasMethod($method);
        echo sprintf("   %-20s : %s\n", $method.'()', $hasMethod ? '✓ EXISTS' : '✗ MISSING');
    }

    // Get pages
    echo "\n5. Resource Pages:\n";
    if ($reflection->hasMethod('getPages')) {
        try {
            $pages = call_user_func(['App\Filament\Club\Resources\TemplateSettingResource', 'getPages']);
            foreach ($pages as $route => $page) {
                echo sprintf("   %-15s => %s\n", $route, $page);
            }
        } catch (Exception $e) {
            echo "   Error: " . $e->getMessage() . "\n";
        }
    }
}

// Check database records
echo "\n6. Database Records:\n";
$count = DB::table('template_settings')->count();
echo "   template_settings records: $count\n";

// Check if Filament panel is registered
echo "\n7. Filament Panel Check:\n";
try {
    $panels = \Filament\Facades\Filament::getPanels();
    foreach ($panels as $id => $panel) {
        echo sprintf("   Panel ID: %-10s | Path: %s\n", $id, $panel->getPath());
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
