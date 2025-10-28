<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Tenant/Club Permissions ===\n";
$perms = Spatie\Permission\Models\Permission::where('name', 'like', '%Tenant%')
    ->orWhere('name', 'like', '%Club%')
    ->get(['name', 'guard_name']);

foreach($perms as $p) {
    echo "- {$p->name} ({$p->guard_name})\n";
}

echo "\n=== All Users ===\n";
$users = App\Models\User::take(5)->get(['id', 'name', 'email']);
foreach($users as $u) {
    echo "- {$u->email} (ID: {$u->id})\n";
}

echo "\n=== All Roles ===\n";
$roles = Spatie\Permission\Models\Role::all(['name', 'guard_name']);
foreach($roles as $r) {
    echo "- {$r->name} ({$r->guard_name})\n";
}

echo "\n=== First User Permissions ===\n";
$user = App\Models\User::first();
if ($user) {
    echo "User: {$user->email}\n";
    echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
    $perms = $user->getAllPermissions()->take(20)->pluck('name');
    echo "Permissions (first 20): " . $perms->implode(', ') . "\n";
} else {
    echo "No users found\n";
}
