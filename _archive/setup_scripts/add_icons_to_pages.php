<?php

// Script to add icons to all Portal Pages without icons
// Icons sourced from Heroicons (Filament's default icon library)

$iconMapping = [
    'AnalyticsStatisticsPage' => 'Heroicon::OutlinedChartBarSquare',
    'ClubMembersManagementPage' => 'Heroicon::OutlinedUsers',
    'ClubPortalDashboard' => 'Heroicon::OutlinedHome',
    'ClubSettingsBrandingPage' => 'Heroicon::OutlinedPaintBrush',
    'ContactFormAdminPage' => 'Heroicon::OutlinedEnvelope',
    'EmailWidgetsPage' => 'Heroicon::OutlinedEnvelopeOpen',
    'MultilingualSettingsPage' => 'Heroicon::OutlinedLanguage',
    'NotificationCenterPage' => 'Heroicon::OutlinedBell',
    'PwaNotificationsPage' => 'Heroicon::OutlinedDevicePhoneMobile',
    'SmsEmailMessagingPage' => 'Heroicon::OutlinedChatBubbleLeftRight',
    'SmsWidgetsPage' => 'Heroicon::OutlinedChatBubbleBottomCenterText',
    'SocialLinksPage' => 'Heroicon::OutlinedShareAlt',
    'SponsorManagementPage' => 'Heroicon::OutlinedHandThumbsUp',
    'SponsorsBannersPage' => 'Heroicon::OutlinedImage',
    'SubscriptionPackagesPage' => 'Heroicon::OutlinedCreditCard',
];

$pagesDir = __DIR__ . '/../app/Filament/SuperAdmin/Pages';

foreach ($iconMapping as $filename => $icon) {
    $filepath = $pagesDir . '/' . $filename . '.php';

    if (!file_exists($filepath)) {
        echo "⚠️ File not found: $filename\n";
        continue;
    }

    $content = file_get_contents($filepath);

    // Check if navigationIcon already exists
    if (strpos($content, 'navigationIcon') !== false) {
        echo "✓ $filename already has icon\n";
        continue;
    }

    // Find the navigationLabel line and add navigationIcon after it
    if (preg_match('/protected static \?string \$navigationLabel.*?;/s', $content)) {
        $pattern = '/(protected static \?string \$navigationLabel[^;]*;)/';
        $replacement = "$1\n    protected static ?string|\Filament\Support\Enums\Heroicon|null \$navigationIcon = " . $icon . ";";

        $newContent = preg_replace($pattern, $replacement, $content, 1);

        if (file_put_contents($filepath, $newContent)) {
            echo "✅ Added icon to: $filename\n";
        } else {
            echo "❌ Failed to update: $filename\n";
        }
    }
}

echo "\n✨ Icon mapping complete!\n";
?>
