<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Initialize tenant
tenancy()->initialize(tenancy()->find('nknapijed'));

echo "=== TESTING TEMPLATE SETTING RESOURCE ACCESS ===\n\n";

// Test 1: Can we access the resource class?
echo "1. Resource Class Access:\n";
try {
    $resourceClass = \App\Filament\Club\Resources\TemplateSettingResource::class;
    echo "   ✓ Resource class accessible: $resourceClass\n";

    // Test if we can get the URL
    $url = $resourceClass::getUrl('index');
    echo "   ✓ Index URL: $url\n";

    $editUrl = $resourceClass::getUrl('edit', ['record' => 1]);
    echo "   ✓ Edit URL: $editUrl\n";

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check if pages are registered
echo "\n2. Registered Pages:\n";
try {
    $pages = \App\Filament\Club\Resources\TemplateSettingResource::getPages();
    foreach ($pages as $name => $page) {
        echo "   - $name: " . $page->getPage() . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 3: Check Filament Navigation
echo "\n3. Filament Navigation Items:\n";
try {
    $panel = \Filament\Facades\Filament::getPanel('club');
    echo "   Panel ID: " . $panel->getId() . "\n";
    echo "   Panel Path: " . $panel->getPath() . "\n";

    // Get all resources in the panel
    $resources = $panel->getResources();
    echo "   Total Resources: " . count($resources) . "\n";

    foreach ($resources as $resource) {
        $reflection = new ReflectionClass($resource);
        $navLabel = $reflection->getProperty('navigationLabel');
        $navLabel->setAccessible(true);
        $label = $navLabel->getValue();

        echo "   - $resource";
        if ($label) {
            echo " (Label: $label)";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Stack: " . $e->getTraceAsString() . "\n";
}

// Test 4: Direct database query
echo "\n4. Database Check:\n";
$settings = DB::table('template_settings')->first();
if ($settings) {
    echo "   ✓ Found template_settings record with ID: " . $settings->id . "\n";
    echo "   Website Name: " . $settings->website_name . "\n";
} else {
    echo "   ✗ No template_settings record found\n";
}

echo "\n=== TEST COMPLETE ===\n";
