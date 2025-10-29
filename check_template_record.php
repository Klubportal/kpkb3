<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant\TemplateSetting;
use Stancl\Tenancy\Facades\Tenancy;

echo "\n=== Template Settings Record Check ===\n\n";

// Initialize tenant
try {
    $tenant = \App\Models\Tenant::find('nknapijed');
    if (!$tenant) {
        echo "❌ Tenant 'nknapijed' not found!\n";
        exit(1);
    }

    echo "✓ Tenant found: {$tenant->id}\n";

    // Initialize tenancy
    Tenancy::initialize($tenant);
    echo "✓ Tenancy initialized\n\n";

    // Check if TemplateSetting record exists
    $count = TemplateSetting::count();
    echo "Total TemplateSetting records: $count\n";

    if ($count === 0) {
        echo "\n❌ No TemplateSetting records found!\n";
        echo "Creating default record...\n";

        // Get central template
        $centralTemplate = \App\Models\Template::first();

        $templateSetting = TemplateSetting::create([
            'template_id' => $centralTemplate ? $centralTemplate->id : 1,
            'website_name' => 'NK Naprijed',
            'slogan' => 'Unser Verein',
            'primary_color' => '#1e40af',
            'secondary_color' => '#dc2626',
            'accent_color' => '#f59e0b',
            'background_color' => '#ffffff',
            'text_color' => '#1f2937',
            'link_color' => '#2563eb',
            'header_style' => 'default',
            'enable_search' => true,
            'show_language_switcher' => true,
            'logo_path' => null,
            'logo_height' => 50,
            'logo_position' => 'left',
            'show_club_name' => true,
            'footer_text' => '© 2024 NK Naprijed. Alle Rechte vorbehalten.',
            'show_social_links' => true,
            'show_newsletter' => false,
            'facebook_url' => null,
            'twitter_url' => null,
            'instagram_url' => null,
            'youtube_url' => null,
            'linkedin_url' => null,
            'enable_news_widget' => true,
            'enable_matches_widget' => true,
            'enable_standings_widget' => true,
            'enable_gallery_widget' => false,
            'custom_css' => null,
            'custom_js' => null,
            'enable_dark_mode' => false,
            'enable_animations' => true,
            'meta_description' => null,
            'meta_keywords' => null,
        ]);

        echo "\n✓ Created TemplateSetting with ID: {$templateSetting->id}\n";
    } else {
        // Show all records
        $settings = TemplateSetting::all();
        echo "\nExisting records:\n";
        foreach ($settings as $setting) {
            echo "  ID {$setting->id}:\n";
            echo "    - template_id: {$setting->template_id}\n";
            echo "    - website_name: {$setting->website_name}\n";
            echo "    - slogan: {$setting->slogan}\n";
            echo "    - primary_color: {$setting->primary_color}\n";
            echo "    - logo_path: {$setting->logo_path}\n";
            echo "\n";
        }

        // Check specific record ID 1
        $setting = TemplateSetting::find(1);
        if ($setting) {
            echo "✓ Record ID 1 exists\n";
        } else {
            echo "❌ Record ID 1 NOT found!\n";
            echo "Available IDs: " . $settings->pluck('id')->implode(', ') . "\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
