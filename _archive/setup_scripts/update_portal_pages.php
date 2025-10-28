<?php

$pages = [
    'ClubPortalDashboard' => ['icon' => 'heroicon-o-home', 'label' => 'Portal Dashboard', 'sort' => 1],
    'SponsorManagementPage' => ['icon' => 'heroicon-o-briefcase', 'label' => 'Sponsor Management', 'sort' => 2],
    'SocialLinksPage' => ['icon' => 'heroicon-o-link', 'label' => 'Social Links', 'sort' => 3],
    'NotificationCenterPage' => ['icon' => 'heroicon-o-bell', 'label' => 'Notifications', 'sort' => 4],
    'EmailWidgetsPage' => ['icon' => 'heroicon-o-envelope', 'label' => 'Email Settings', 'sort' => 5],
    'SmsWidgetsPage' => ['icon' => 'heroicon-o-chat-bubble-left', 'label' => 'SMS Settings', 'sort' => 6],
    'ContactFormAdminPage' => ['icon' => 'heroicon-o-document-text', 'label' => 'Contact Forms', 'sort' => 7],
    'ClubSettingsBrandingPage' => ['icon' => 'heroicon-o-palette', 'label' => 'Club Branding', 'sort' => 8],
    'PwaNotificationsPage' => ['icon' => 'heroicon-o-bell-alert', 'label' => 'PWA Notifications', 'sort' => 9],
    'SponsorsBannersPage' => ['icon' => 'heroicon-o-photo', 'label' => 'Sponsors & Banners', 'sort' => 10],
    'SubscriptionPackagesPage' => ['icon' => 'heroicon-o-credit-card', 'label' => 'Subscriptions', 'sort' => 11],
    'MultilingualSettingsPage' => ['icon' => 'heroicon-o-language', 'label' => 'Languages', 'sort' => 12],
    'SmsEmailMessagingPage' => ['icon' => 'heroicon-o-megaphone', 'label' => 'Messaging', 'sort' => 13],
    'AnalyticsStatisticsPage' => ['icon' => 'heroicon-o-chart-bar', 'label' => 'Analytics', 'sort' => 14],
    'ClubMembersManagementPage' => ['icon' => 'heroicon-o-users', 'label' => 'Members', 'sort' => 15],
];

$basePath = __DIR__ . '/app/Filament/Pages/Portal/';

foreach ($pages as $class => $config) {
    $file = $basePath . $class . '.php';

    if (!file_exists($file)) {
        echo "❌ File not found: $file\n";
        continue;
    }

    $content = file_get_contents($file);

    // Check if navigationIcon already exists
    if (strpos($content, 'protected static ?string $navigationIcon') !== false) {
        echo "⏭️  Skip (already has navigationIcon): $class\n";
        continue;
    }

    // Find the class definition line
    $classLinePattern = '/class\s+' . $class . '\s+extends\s+Page/';

    if (!preg_match($classLinePattern, $content)) {
        echo "⚠️  Could not find class definition: $class\n";
        continue;
    }

    // Add the properties after "class XyzPage extends Page {"
    $replacement = "class $class extends Page
{
    protected static ?string \$navigationIcon = '{$config['icon']}';
    protected static ?string \$navigationLabel = '{$config['label']}';
    protected static ?int \$navigationSort = {$config['sort']};";

    $updated = preg_replace(
        '/(class\s+' . $class . '\s+extends\s+Page)\s*\{/',
        $replacement,
        $content
    );

    if ($updated === $content) {
        echo "⚠️  No changes made to: $class\n";
        continue;
    }

    file_put_contents($file, $updated);
    echo "✅ Updated: $class\n";
}

echo "\n✅ All Portal Pages updated!\n";
?>
