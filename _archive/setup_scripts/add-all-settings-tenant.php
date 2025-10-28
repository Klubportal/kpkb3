<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Tenant ID
$tenantId = 'testclub';

// Tenant initialisieren
$tenant = \App\Models\Central\Tenant::find($tenantId);
if (!$tenant) {
    echo "❌ Tenant '{$tenantId}' nicht gefunden!\n";
    exit(1);
}

tenancy()->initialize($tenant);

echo "✅ Tenant '{$tenantId}' initialisiert\n";
echo "📊 Füge fehlende Settings hinzu...\n\n";

// Theme Settings
$themeSettings = [
    'active_theme' => 'default',
    'header_bg_color' => '#dc2626',
    'footer_bg_color' => '#1f2937',
    'text_color' => '#1f2937',
    'link_color' => '#2563eb',
    'button_style' => 'rounded',
    'dark_mode_enabled' => false,
    'layout_style' => 'full-width',
    'font_family' => 'inter',
    'border_radius' => 'md',
    'sidebar_width' => 'normal',
];

echo "🎨 Theme Settings:\n";
$added = 0;
foreach ($themeSettings as $name => $defaultValue) {
    $exists = DB::table('settings')
        ->where('group', 'theme')
        ->where('name', $name)
        ->exists();

    if (!$exists) {
        DB::table('settings')->insert([
            'group' => 'theme',
            'name' => $name,
            'locked' => false,
            'payload' => json_encode($defaultValue),
        ]);
        echo "  ✓ {$name} hinzugefügt\n";
        $added++;
    } else {
        echo "  - {$name} existiert bereits\n";
    }
}

// Social Media Settings
$socialSettings = [
    'facebook_url' => null,
    'instagram_url' => null,
    'twitter_url' => null,
    'youtube_url' => null,
    'linkedin_url' => null,
    'tiktok_url' => null,
];

echo "\n📱 Social Media Settings:\n";
foreach ($socialSettings as $name => $defaultValue) {
    $exists = DB::table('settings')
        ->where('group', 'social_media')
        ->where('name', $name)
        ->exists();

    if (!$exists) {
        DB::table('settings')->insert([
            'group' => 'social_media',
            'name' => $name,
            'locked' => false,
            'payload' => json_encode($defaultValue),
        ]);
        echo "  ✓ {$name} hinzugefügt\n";
        $added++;
    } else {
        echo "  - {$name} existiert bereits\n";
    }
}

// Contact Settings
$contactSettings = [
    'company_name' => 'Test Fußballverein e.V.',
    'street' => 'Sportplatzstraße 1',
    'postal_code' => '12345',
    'city' => 'Musterstadt',
    'country' => 'Deutschland',
    'phone' => '0123 / 456789',
    'fax' => null,
    'mobile' => null,
    'email' => 'info@testclub.de',
    'google_maps_url' => null,
    'google_maps_embed' => null,
];

echo "\n📧 Contact Settings:\n";
foreach ($contactSettings as $name => $defaultValue) {
    $exists = DB::table('settings')
        ->where('group', 'contact')
        ->where('name', $name)
        ->exists();

    if (!$exists) {
        DB::table('settings')->insert([
            'group' => 'contact',
            'name' => $name,
            'locked' => false,
            'payload' => json_encode($defaultValue),
        ]);
        echo "  ✓ {$name} hinzugefügt\n";
        $added++;
    } else {
        echo "  - {$name} existiert bereits\n";
    }
}

echo "\n✅ Fertig! {$added} Settings hinzugefügt\n";
