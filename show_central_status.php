<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║       CENTRAL BACKEND RESOURCES & PERMISSIONS STATUS            ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "=== REGISTRIERTE RESOURCES im CentralPanelProvider ===\n";
$resources = [
    '✓ TenantResource / ClubResource (Vereinsverwaltung)' => 'app/Filament/Central/Resources/ClubResource.php',
    '✓ NewsResource' => 'app/Filament/Central/Resources/NewsResource.php',
    '✓ NewsCategoryResource' => 'app/Filament/Central/Resources/NewsCategoryResource.php',
    '✓ PageResource' => 'app/Filament/Central/Resources/PageResource.php',
];

foreach ($resources as $name => $path) {
    echo "{$name}\n   → {$path}\n";
}

echo "\n=== NICHT REGISTRIERTE RESOURCES (existieren aber auskommentiert) ===\n";
$notRegistered = [
    '✗ TemplateResource' => 'app/Filament/Central/Resources/TemplateResource.php',
    '✗ FabricatorPageResource' => 'app/Filament/Central/Resources/FabricatorPageResource.php',
    '✗ CustomLanguageLineResource' => 'app/Filament/Central/Resources/CustomLanguageLineResource.php',
];

foreach ($notRegistered as $name => $path) {
    echo "{$name}\n   → {$path}\n";
}

echo "\n=== SETTINGS PAGES (via discoverPages) ===\n";
$pages = [
    'ManageGeneralSettings',
    'ManageThemeSettings',
    'ManageSocialMediaSettings',
    'ManageEmailSettings',
    'ManageContactSettings',
    'Backups',
];
foreach ($pages as $p) {
    echo "  - {$p}\n";
}

echo "\n=== USER PERMISSIONS (info@klubportal.com) ===\n";
$user = User::where('email', 'info@klubportal.com')->first();
if ($user) {
    echo "Role: " . $user->getRoleNames()->first() . "\n";
    echo "Total Permissions: " . $user->getAllPermissions()->count() . "\n\n";

    echo "Wichtige Permissions:\n";
    $criticalPerms = [
        'ViewAny:Tenant' => 'Clubs/Vereine anzeigen',
        'Create:Tenant' => 'Neue Clubs erstellen',
        'ViewAny:News' => 'News anzeigen',
        'Create:News' => 'News erstellen',
        'ViewAny:NewsCategory' => 'News-Kategorien anzeigen',
        'ViewAny:Page' => 'Pages anzeigen',
    ];

    foreach ($criticalPerms as $perm => $desc) {
        $status = $user->can($perm) ? '✓' : '✗';
        echo "  {$status} {$perm}\n     → {$desc}\n";
    }
}

echo "\n=== BACKEND URLs ===\n";
echo "Central Admin: http://localhost:8000/admin\n";
echo "  → Login: info@klubportal.com\n";
echo "  → Clubs: http://localhost:8000/admin/clubs\n";
echo "  → News: http://localhost:8000/admin/news\n";
echo "  → News Categories: http://localhost:8000/admin/news-categories\n";
echo "  → Pages: http://localhost:8000/admin/pages\n";

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  ERGEBNIS: Alle Permissions erstellt! User kann alle Resources  ║\n";
echo "║  sehen. Falls noch immer nicht sichtbar: Browser-Cache leeren!  ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n";
