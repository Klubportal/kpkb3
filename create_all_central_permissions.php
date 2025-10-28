<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "=== Creating ALL Missing Permissions for Central Resources ===\n\n";

// Define all resource models that need permissions
$resourceModels = [
    'Tenant',           // Already exists (11 permissions)
    'News',             // Missing
    'NewsCategory',     // Missing
    'Page',             // Missing
    'Template',         // Missing (not registered but exists)
    'CustomLanguageLine', // Missing (not registered but exists)
];

// Standard Filament permissions for each resource
$permissionTypes = [
    'ViewAny',
    'View',
    'Create',
    'Update',
    'Delete',
    'Restore',
    'ForceDelete',
    'ForceDeleteAny',
    'RestoreAny',
    'Replicate',
    'Reorder',
];

$createdCount = 0;
$existingCount = 0;

foreach ($resourceModels as $model) {
    echo "--- {$model} Permissions ---\n";

    foreach ($permissionTypes as $type) {
        $permName = "{$type}:{$model}";

        $perm = Permission::firstOrCreate(
            ['name' => $permName, 'guard_name' => 'web']
        );

        if ($perm->wasRecentlyCreated) {
            echo "✓ Created: {$permName}\n";
            $createdCount++;
        } else {
            echo "  Exists: {$permName}\n";
            $existingCount++;
        }
    }
    echo "\n";
}

echo "=== Summary ===\n";
echo "Created: {$createdCount} new permissions\n";
echo "Existing: {$existingCount} permissions\n";
echo "Total: " . Permission::count() . " permissions in database\n\n";

echo "=== Updating Super Admin Role ===\n";
$superAdmin = Role::where('name', 'super_admin')->where('guard_name', 'web')->first();

if ($superAdmin) {
    // Give super admin ALL permissions
    $allPermissions = Permission::where('guard_name', 'web')->get();
    $superAdmin->syncPermissions($allPermissions);
    echo "✓ Super Admin now has " . $superAdmin->permissions()->count() . " permissions\n\n";
} else {
    echo "✗ Super Admin role not found!\n\n";
}

echo "=== Verifying User info@klubportal.com ===\n";
$user = User::where('email', 'info@klubportal.com')->first();
if ($user) {
    // Refresh permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
    echo "Total Permissions: " . $user->getAllPermissions()->count() . "\n\n";

    echo "Sample Permission Checks:\n";
    $checkPerms = [
        'ViewAny:Tenant',
        'Create:Tenant',
        'ViewAny:News',
        'Create:News',
        'ViewAny:NewsCategory',
        'ViewAny:Page',
        'ViewAny:Template',
    ];

    foreach ($checkPerms as $perm) {
        $has = $user->can($perm) ? '✓' : '✗';
        echo "{$has} {$perm}\n";
    }
} else {
    echo "✗ User not found!\n";
}

echo "\n=== Done! ===\n";
