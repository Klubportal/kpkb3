<?php

use Illuminate\Support\Facades\File;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

$app = require __DIR__ . '/bootstrap/app.php';

echo "=== FILAMENT PANEL AUDIT ===\n\n";

// Check SuperAdminPanelProvider
$providerPath = 'app/Providers/Filament/SuperAdminPanelProvider.php';
$providerContent = File::get($providerPath);

echo "✅ SuperAdminPanelProvider.php:\n";

// Count resources
preg_match_all('/ClubResource|SponsorResource|BannerResource/', $providerContent, $resources);
echo "  - Resources registered: " . count($resources[0]) . "\n";

// Count pages
preg_match_all('/Dashboard|ClubManagement|ClubDetails|ClubPortalDashboard|SponsorManagementPage|SocialLinksPage|NotificationCenterPage|EmailWidgetsPage|SmsWidgetsPage|ContactFormAdminPage|ClubSettingsBrandingPage|PwaNotificationsPage|SponsorsBannersPage|SubscriptionPackagesPage|MultilingualSettingsPage|SmsEmailMessagingPage|AnalyticsStatisticsPage|ClubMembersManagementPage/', $providerContent, $pages);
echo "  - Pages registered: " . count(array_unique($pages[0])) . "\n";

echo "\n✅ Portal Pages with navigationLabel:\n";
$portalDir = glob(__DIR__ . '/app/Filament/Pages/Portal/*.php');
foreach ($portalDir as $file) {
    $name = basename($file, '.php');
    $content = File::get($file);

    if (preg_match('/navigationLabel\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $match)) {
        echo "  ✓ $name: {$match[1]}\n";
    } else {
        echo "  ✗ $name: NO navigationLabel\n";
    }
}

echo "\n✅ SuperAdmin Pages with navigationLabel:\n";
$superAdminDir = glob(__DIR__ . '/app/Filament/SuperAdmin/Pages/*.php');
foreach ($superAdminDir as $file) {
    $name = basename($file, '.php');
    $content = File::get($file);

    if (preg_match('/navigationLabel\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $match)) {
        echo "  ✓ $name: {$match[1]}\n";
    } else {
        echo "  ✗ $name: NO navigationLabel\n";
    }
}

echo "\n✅ Check if pages are discoverable:\n";
echo "  - discoverPages(in: app_path('Filament/SuperAdmin/Pages')): YES\n";
echo "  - discoverPages(in: app_path('Filament/Pages')): NO (not configured)\n";
echo "\n⚠️  ISSUE: Portal Pages are in app/Filament/Pages/Portal but only SuperAdmin/Pages is configured!\n";

?>
