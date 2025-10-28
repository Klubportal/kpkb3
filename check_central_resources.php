<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Permission;
use App\Models\User;

echo "=== Registrierte Ressourcen im CentralPanelProvider ===\n";
$registered = [
    'TenantResource',
    'ClubResource',
    'NewsResource',
    'NewsCategoryResource',
    'PageResource',
];
foreach ($registered as $r) {
    echo "✓ {$r}\n";
}

echo "\n=== Existierende aber NICHT registrierte Ressourcen ===\n";
$existing = [
    'TemplateResource',
    'FabricatorPageResource',
    'CustomLanguageLineResource',
];
foreach ($existing as $r) {
    echo "✗ {$r} (existiert aber nicht registriert)\n";
}

echo "\n=== Existierende Pages im Central/Pages Verzeichnis ===\n";
$pages = [
    'Backups',
    'ManageGeneralSettings',
    'ManageThemeSettings',
    'ManageSocialMediaSettings',
    'ManageEmailSettings',
    'ManageContactSettings',
    'RestoreBackup',
];
foreach ($pages as $p) {
    echo "- {$p}\n";
}

echo "\n=== Permissions für Central Resources ===\n";
$resourceModels = ['Tenant', 'News', 'NewsCategory', 'Page', 'Template', 'CustomLanguageLine'];
foreach ($resourceModels as $model) {
    $count = Permission::where('name', 'like', "%:{$model}")->count();
    echo "{$model}: {$count} permissions\n";
}

echo "\n=== User info@klubportal.com Details ===\n";
$user = User::where('email', 'info@klubportal.com')->first();
if ($user) {
    echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
    echo "Total Permissions: " . $user->getAllPermissions()->count() . "\n";

    // Check specific permissions
    $checkPerms = ['ViewAny:News', 'ViewAny:NewsCategory', 'ViewAny:Page', 'ViewAny:Template'];
    foreach ($checkPerms as $perm) {
        $has = $user->can($perm) ? '✓' : '✗';
        echo "{$has} {$perm}\n";
    }
}
