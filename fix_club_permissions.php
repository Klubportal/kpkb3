<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "=== Creating Club/Tenant Permissions ===\n";

// Create permissions for Tenant (Club) resource
$permissions = [
    'ViewAny:Tenant',
    'View:Tenant',
    'Create:Tenant',
    'Update:Tenant',
    'Delete:Tenant',
    'Restore:Tenant',
    'ForceDelete:Tenant',
    'ForceDeleteAny:Tenant',
    'RestoreAny:Tenant',
    'Replicate:Tenant',
    'Reorder:Tenant',
];

foreach ($permissions as $permName) {
    $perm = Permission::firstOrCreate(
        ['name' => $permName, 'guard_name' => 'web']
    );
    echo "✓ Created/Found: {$permName}\n";
}

echo "\n=== Creating/Updating Super Admin Role ===\n";
$superAdminRole = Role::firstOrCreate(
    ['name' => 'super_admin', 'guard_name' => 'web']
);
echo "✓ Super Admin Role: {$superAdminRole->name}\n";

// Give super admin ALL permissions
$superAdminRole->syncPermissions(Permission::all());
echo "✓ Assigned " . Permission::count() . " permissions to super_admin\n";

echo "\n=== Assigning Super Admin to User ===\n";
$user = User::where('email', 'info@klubportal.com')->first();
if ($user) {
    $user->assignRole('super_admin');
    echo "✓ User {$user->email} is now super_admin\n";
    echo "✓ User has " . $user->getAllPermissions()->count() . " permissions\n";
    echo "✓ Can ViewAny:Tenant? " . ($user->can('ViewAny:Tenant') ? 'YES' : 'NO') . "\n";
    echo "✓ Can Create:Tenant? " . ($user->can('Create:Tenant') ? 'YES' : 'NO') . "\n";
} else {
    echo "✗ User not found!\n";
}

echo "\n=== DONE ===\n";
