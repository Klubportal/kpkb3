<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Tenant;
use App\Models\Central\News;

echo "=== WHY canViewAny() RETURNS FALSE ===\n\n";

$user = User::where('email', 'info@klubportal.com')->first();
if (!$user) {
    echo "✗ User not found!\n";
    exit(1);
}

// Authenticate the user for this session
auth()->login($user);

echo "User: {$user->email}\n";
echo "Auth Check: " . (auth()->check() ? '✓ Logged in' : '✗ Not logged in') . "\n";
echo "Auth ID: " . auth()->id() . "\n\n";

// Test 1: Direct Permission Check
echo "=== DIRECT PERMISSION CHECKS ===\n";
$perms = ['ViewAny:Tenant', 'ViewAny:News', 'ViewAny:NewsCategory', 'ViewAny:Page'];
foreach ($perms as $perm) {
    $has = $user->can($perm) ? '✓' : '✗';
    echo "{$has} \$user->can('{$perm}')\n";
}

echo "\n=== GATE CHECKS (with Model Instance) ===\n";

// Test 2: Gate check with Tenant model
try {
    $tenant = new Tenant();
    $canViewTenant = auth()->user()->can('viewAny', $tenant);
    echo ($canViewTenant ? '✓' : '✗') . " auth()->user()->can('viewAny', Tenant::class)\n";
} catch (\Exception $e) {
    echo "✗ Tenant Error: " . $e->getMessage() . "\n";
}

// Test 3: Gate check with News model
try {
    $news = new News();
    $canViewNews = auth()->user()->can('viewAny', $news);
    echo ($canViewNews ? '✓' : '✗') . " auth()->user()->can('viewAny', News::class)\n";
} catch (\Exception $e) {
    echo "✗ News Error: " . $e->getMessage() . "\n";
}

echo "\n=== POLICY CHECKS ===\n";

// Check if policies are registered
$models = [
    'Tenant' => Tenant::class,
    'News' => News::class,
];

foreach ($models as $name => $modelClass) {
    $policy = \Illuminate\Support\Facades\Gate::getPolicyFor($modelClass);
    if ($policy) {
        echo "✓ {$name} Policy: " . get_class($policy) . "\n";

        // Try to call viewAny on policy directly
        try {
            $result = $policy->viewAny($user);
            echo "  Policy->viewAny(\$user): " . ($result ? '✓ TRUE' : '✗ FALSE') . "\n";
        } catch (\Exception $e) {
            echo "  Policy->viewAny() Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ {$name} Policy: NOT REGISTERED\n";
    }
}

echo "\n=== AuthServiceProvider Check ===\n";
$authProvider = app(\App\Providers\AuthServiceProvider::class);
echo "AuthServiceProvider exists: ✓\n";

// Read policies
try {
    $reflection = new \ReflectionClass($authProvider);
    if ($reflection->hasProperty('policies')) {
        $property = $reflection->getProperty('policies');
        $property->setAccessible(true);
        $policies = $property->getValue($authProvider);

        echo "\nRegistered Policies in AuthServiceProvider:\n";
        foreach ($policies as $model => $policy) {
            echo "  - {$model} => {$policy}\n";
        }
    }
} catch (\Exception $e) {
    echo "Could not read policies: " . $e->getMessage() . "\n";
}

echo "\n=== SOLUTION ===\n";
echo "Wenn Policies NICHT registriert sind:\n";
echo "→ Policies in app/Providers/AuthServiceProvider.php registrieren\n";
echo "→ php artisan optimize:clear ausführen\n";
