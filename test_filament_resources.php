<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Filament\Facades\Filament;

echo "=== FILAMENT RESOURCE canViewAny() TEST ===\n\n";

// Get and login user
$user = User::where('email', 'info@klubportal.com')->first();
if (!$user) {
    echo "✗ User not found!\n";
    exit(1);
}

// Set current panel
Filament::setCurrentPanel(Filament::getPanel('central'));
echo "Current Panel: " . Filament::getCurrentPanel()->getId() . "\n";

// Login user
auth()->guard('web')->login($user);
echo "Auth User: " . auth()->user()->email . "\n";
echo "Auth ID: " . auth()->id() . "\n\n";

// Test Resources
$resources = [
    'App\Filament\Central\Resources\ClubResource' => 'ClubResource',
    'App\Filament\Central\Resources\NewsResource' => 'NewsResource',
    'App\Filament\Central\Resources\NewsCategoryResource' => 'NewsCategoryResource',
    'App\Filament\Central\Resources\PageResource' => 'PageResource',
];

echo "=== RESOURCE canViewAny() Results ===\n";
foreach ($resources as $class => $name) {
    if (!class_exists($class)) {
        echo "✗ {$name}: Class does not exist\n";
        continue;
    }

    try {
        $canViewAny = $class::canViewAny();
        $icon = $canViewAny ? '✓' : '✗';
        echo "{$icon} {$name}: " . ($canViewAny ? 'TRUE' : 'FALSE') . "\n";

        // If false, try to understand why
        if (!$canViewAny) {
            $model = $class::getModel();
            echo "   Model: {$model}\n";

            // Test direct gate
            $testModel = new $model;
            $gateCan = auth()->user()->can('viewAny', $testModel);
            echo "   Gate->can('viewAny'): " . ($gateCan ? 'TRUE' : 'FALSE') . "\n";

            // Test permission
            $modelName = class_basename($model);
            $permCheck = auth()->user()->can("ViewAny:{$modelName}");
            echo "   Permission ViewAny:{$modelName}: " . ($permCheck ? 'TRUE' : 'FALSE') . "\n";
        }
    } catch (\Exception $e) {
        echo "✗ {$name}: ERROR - " . $e->getMessage() . "\n";
        echo "   " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\n=== Testing Static Methods ===\n";
try {
    echo "ClubResource::getModel(): " . \App\Filament\Central\Resources\ClubResource::getModel() . "\n";
    echo "ClubResource::getNavigationLabel(): " . \App\Filament\Central\Resources\ClubResource::getNavigationLabel() . "\n";
    echo "ClubResource::getNavigationSort(): " . \App\Filament\Central\Resources\ClubResource::getNavigationSort() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
