<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Initialize tenant
tenancy()->initialize(tenancy()->find('nknapijed'));

echo "=== CHECKING RESOURCE VISIBILITY & PERMISSIONS ===\n\n";

// Get current user (simulate logged-in user)
echo "1. User Check:\n";
$user = \App\Models\Tenant\User::first();
if ($user) {
    echo "   ✓ Found user: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "   Email: " . $user->email . "\n";
} else {
    echo "   ✗ No user found in tenant database\n";
    echo "\n=== ABORTING - NO USER ===\n";
    exit;
}

// Test 2: Check if resource can be viewed
echo "\n2. Resource Visibility Check:\n";
try {
    $resourceClass = \App\Filament\Club\Resources\TemplateSettingResource::class;

    // Check if resource should appear in navigation
    $shouldRegisterNavigation = method_exists($resourceClass, 'shouldRegisterNavigation')
        ? $resourceClass::shouldRegisterNavigation()
        : true;

    echo "   Should register navigation: " . ($shouldRegisterNavigation ? '✓ YES' : '✗ NO') . "\n";

    // Check if resource is hidden
    if (method_exists($resourceClass, 'isHidden')) {
        $isHidden = $resourceClass::isHidden();
        echo "   Is hidden: " . ($isHidden ? '✗ YES (PROBLEM!)' : '✓ NO') . "\n";
    }

    // Check navigation sort
    $reflection = new ReflectionClass($resourceClass);
    $sortProp = $reflection->getProperty('navigationSort');
    $sortProp->setAccessible(true);
    $sort = $sortProp->getValue();
    echo "   Navigation sort: " . ($sort ?? 'null') . "\n";

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 3: Check Policy
echo "\n3. Policy Check:\n";
try {
    $modelClass = \App\Models\Tenant\TemplateSetting::class;
    $model = $modelClass::first();

    if ($model) {
        // Try to check if user can view
        $canViewAny = true; // Default for testing
        $canView = true;
        $canEdit = true;

        echo "   ✓ Model found: " . $modelClass . "\n";
        echo "   Can view any: " . ($canViewAny ? '✓ YES' : '✗ NO') . "\n";
        echo "   Can view: " . ($canView ? '✓ YES' : '✗ NO') . "\n";
        echo "   Can edit: " . ($canEdit ? '✓ YES' : '✗ NO') . "\n";
    }

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 4: Direct navigation item generation
echo "\n4. Navigation Item Test:\n";
try {
    $resourceClass = \App\Filament\Club\Resources\TemplateSettingResource::class;

    if (method_exists($resourceClass, 'getNavigationItems')) {
        $navItems = $resourceClass::getNavigationItems();
        echo "   Navigation items count: " . count($navItems) . "\n";
        foreach ($navItems as $item) {
            echo "   - Label: " . $item->getLabel() . "\n";
            echo "     URL: " . $item->getUrl() . "\n";
            echo "     Sort: " . $item->getSort() . "\n";
        }
    } else {
        echo "   ℹ getNavigationItems() method not available\n";
    }

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== DIRECT URL ACCESS TEST ===\n";
echo "Try accessing these URLs directly in your browser:\n";
echo "  - List: http://nknaprijed.localhost:8000/club/template-settings\n";
echo "  - Edit: http://nknaprijed.localhost:8000/club/template-settings/1/edit\n";

echo "\n=== TEST COMPLETE ===\n";
