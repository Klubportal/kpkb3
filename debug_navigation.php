<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Filament\Facades\Filament;

echo "=== FILAMENT NAVIGATION DEBUG ===\n\n";

// Set up Central Panel
$panel = Filament::getPanel('central');
echo "Panel: {$panel->getId()}\n";
echo "Path: {$panel->getPath()}\n\n";

// Get User
$user = User::where('email', 'info@klubportal.com')->first();
if (!$user) {
    echo "✗ User not found!\n";
    exit(1);
}

echo "User: {$user->email}\n";
echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
echo "Permissions: " . $user->getAllPermissions()->count() . "\n\n";

// Test Resources
$resources = [
    'App\Filament\Central\Resources\ClubResource',
    'App\Filament\Central\Resources\NewsResource',
    'App\Filament\Central\Resources\NewsCategoryResource',
    'App\Filament\Central\Resources\PageResource',
];

echo "=== RESOURCE VISIBILITY CHECK ===\n";
foreach ($resources as $resourceClass) {
    $shortName = class_basename($resourceClass);
    echo "\n--- {$shortName} ---\n";

    if (!class_exists($resourceClass)) {
        echo "✗ Class does not exist!\n";
        continue;
    }

    // Check if resource can view any
    $canViewAny = false;
    try {
        $canViewAny = $resourceClass::canViewAny();
        echo "canViewAny(): " . ($canViewAny ? '✓ YES' : '✗ NO') . "\n";
    } catch (\Exception $e) {
        echo "canViewAny() Error: " . $e->getMessage() . "\n";
    }

    // Check navigation
    try {
        $navIcon = $resourceClass::getNavigationIcon();
        $navLabel = $resourceClass::getNavigationLabel();
        $navGroup = method_exists($resourceClass, 'getNavigationGroup') ? $resourceClass::getNavigationGroup() : null;
        $navSort = $resourceClass::getNavigationSort();

        echo "Navigation Icon: {$navIcon}\n";
        echo "Navigation Label: {$navLabel}\n";
        echo "Navigation Group: " . ($navGroup ?? 'None') . "\n";
        echo "Navigation Sort: " . ($navSort ?? 'None') . "\n";

        // Check if should register
        $shouldRegister = true;
        if (method_exists($resourceClass, 'shouldRegisterNavigation')) {
            $shouldRegister = $resourceClass::shouldRegisterNavigation();
        }
        echo "Should Register: " . ($shouldRegister ? '✓ YES' : '✗ NO') . "\n";

    } catch (\Exception $e) {
        echo "Navigation Error: " . $e->getMessage() . "\n";
    }

    // Check model & policy
    try {
        $model = $resourceClass::getModel();
        echo "Model: {$model}\n";

        // Check if policy exists
        $policy = \Illuminate\Support\Facades\Gate::getPolicyFor($model);
        if ($policy) {
            echo "Policy: " . get_class($policy) . "\n";

            // Test viewAny permission
            $modelInstance = new $model;
            $canView = $user->can('viewAny', $modelInstance);
            echo "User can('viewAny', model): " . ($canView ? '✓ YES' : '✗ NO') . "\n";
        } else {
            echo "Policy: ✗ No policy found\n";
        }
    } catch (\Exception $e) {
        echo "Model/Policy Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== CRITICAL PERMISSIONS ===\n";
$criticalPerms = [
    'ViewAny:Tenant',
    'ViewAny:News',
    'ViewAny:NewsCategory',
    'ViewAny:Page',
];

foreach ($criticalPerms as $perm) {
    $has = $user->can($perm) ? '✓' : '✗';
    echo "{$has} {$perm}\n";
}

echo "\n=== RECOMMENDATION ===\n";
echo "1. Browser komplett neu laden (Ctrl+Shift+R)\n";
echo "2. Ggf. ausloggen und neu einloggen\n";
echo "3. Browser DevTools öffnen und Netzwerk-Tab prüfen\n";
echo "4. Laravel Logs prüfen: storage/logs/laravel.log\n";
