<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use App\Models\Tenant\TemplateSetting;
use Illuminate\Support\Facades\Storage;

echo "🧪 TESTING LOGO UPLOAD FUNCTIONALITY\n";
echo "====================================\n\n";

$tenant = Tenant::find('nknapijed');

if (!$tenant) {
    echo "❌ Tenant 'nknapijed' not found!\n";
    exit(1);
}

echo "Tenant: {$tenant->name}\n\n";

// Initialize tenancy
tenancy()->initialize($tenant);

echo "📋 TEMPLATE SETTINGS CHECK\n";
echo "==========================\n";

$settings = TemplateSetting::first();

if (!$settings) {
    echo "❌ No template settings found. Creating default...\n";
    $settings = TemplateSetting::create([
        'website_name' => $tenant->name,
        'primary_color' => '#dc2626',
        'secondary_color' => '#1e40af',
        'accent_color' => '#f59e0b',
    ]);
    echo "✅ Created default template settings\n\n";
} else {
    echo "✅ Template settings found (ID: {$settings->id})\n";
    echo "   Website Name: {$settings->website_name}\n";
    echo "   Logo: " . ($settings->logo ?? 'NULL') . "\n\n";
}

echo "📁 STORAGE DISK CHECK\n";
echo "=====================\n";

$disk = Storage::disk('public');
echo "Disk: public\n";
echo "Root: " . $disk->path('') . "\n";

// Check logos directory
if (!$disk->exists('logos')) {
    echo "Creating logos directory...\n";
    $disk->makeDirectory('logos');
    echo "✅ Created\n";
} else {
    echo "✅ Logos directory exists\n";
}

// List files in logos
$files = $disk->files('logos');
echo "Files in logos: " . count($files) . "\n";
foreach ($files as $file) {
    echo "  - {$file}\n";
}

echo "\n";

echo "🔗 URL GENERATION TEST\n";
echo "======================\n";

if ($settings->logo) {
    $url = Storage::disk('public')->url($settings->logo);
    echo "Storage URL: {$url}\n";

    // Test tenant route
    $tenantUrl = "http://nknapijed.localhost:8000/storage/" . $settings->logo;
    echo "Tenant Route URL: {$tenantUrl}\n";

    // Check if file exists
    if ($disk->exists($settings->logo)) {
        echo "✅ Logo file exists in storage\n";
        $size = $disk->size($settings->logo);
        echo "   Size: " . number_format($size / 1024, 2) . " KB\n";
    } else {
        echo "❌ Logo file does NOT exist in storage\n";
    }
} else {
    echo "⚠️ No logo set in template settings\n";
}

tenancy()->end();

echo "\n✅ Test complete!\n";
echo "\nℹ️ To upload a logo:\n";
echo "1. Go to http://nknapijed.localhost:8000/club/template-settings/1/edit\n";
echo "2. Upload an image in the 'Logo' field\n";
echo "3. The image should be stored in: storage/tenantnknapijed/app/public/logos/\n";
echo "4. The image should be accessible via: http://nknapijed.localhost:8000/storage/logos/filename.jpg\n";
