<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use App\Models\Tenant\TemplateSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "🔍 CHECKING LOGO UPLOAD IN DATABASE\n";
echo "====================================\n\n";

$tenant = Tenant::find('nknapijed');

if (!$tenant) {
    echo "❌ Tenant not found\n";
    exit(1);
}

echo "Tenant: {$tenant->name}\n\n";

// Initialize tenancy
tenancy()->initialize($tenant);

echo "📊 TEMPLATE_SETTINGS TABLE\n";
echo "==========================\n";

// Check if table exists
$tableExists = DB::select("SHOW TABLES LIKE 'template_settings'");
if (empty($tableExists)) {
    echo "❌ Table 'template_settings' does NOT exist!\n";
    tenancy()->end();
    exit(1);
}

echo "✅ Table exists\n\n";

// Get table structure
echo "📋 TABLE STRUCTURE:\n";
$columns = DB::select("DESCRIBE template_settings");
foreach ($columns as $col) {
    echo "  {$col->Field} ({$col->Type}) - Null: {$col->Null} - Default: " . ($col->Default ?? 'NULL') . "\n";
}

echo "\n";

// Get all records
echo "📄 ALL RECORDS:\n";
$records = DB::table('template_settings')->get();

if ($records->count() === 0) {
    echo "⚠️ No records found in template_settings table\n";
    echo "\nCreating default record...\n";

    $id = DB::table('template_settings')->insertGetId([
        'website_name' => $tenant->name,
        'primary_color' => '#dc2626',
        'secondary_color' => '#1e40af',
        'accent_color' => '#f59e0b',
        'header_bg_color' => '#ffffff',
        'header_text_color' => '#1f2937',
        'badge_bg_color' => '#dc2626',
        'badge_text_color' => '#ffffff',
        'footer_bg_color' => '#111827',
        'footer_text_color' => '#ffffff',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "✅ Created record with ID: {$id}\n\n";

    $records = DB::table('template_settings')->get();
}

foreach ($records as $record) {
    echo "\nRecord ID: {$record->id}\n";
    echo "  website_name: {$record->website_name}\n";
    echo "  logo: " . ($record->logo ?? 'NULL') . "\n";
    echo "  slogan: " . ($record->slogan ?? 'NULL') . "\n";
    echo "  primary_color: {$record->primary_color}\n";
    echo "  created_at: {$record->created_at}\n";
    echo "  updated_at: {$record->updated_at}\n";
}

echo "\n";

// Check using Eloquent model
echo "🔧 ELOQUENT MODEL CHECK:\n";
echo "========================\n";

try {
    $setting = TemplateSetting::first();

    if ($setting) {
        echo "✅ Model found (ID: {$setting->id})\n";
        echo "  website_name: {$setting->website_name}\n";
        echo "  logo: " . ($setting->logo ?? 'NULL') . "\n";
        echo "  logo (raw): " . var_export($setting->getRawOriginal('logo'), true) . "\n";

        // Check if logo attribute has accessor
        echo "\n📝 Model Attributes:\n";
        $attributes = $setting->getAttributes();
        foreach ($attributes as $key => $value) {
            if (in_array($key, ['logo', 'website_name', 'slogan'])) {
                echo "  {$key}: " . var_export($value, true) . "\n";
            }
        }

    } else {
        echo "❌ No model found\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Check storage files
echo "📁 STORAGE FILES:\n";
echo "=================\n";

$disk = Storage::disk('public');
echo "Storage path: " . $disk->path('') . "\n";

if ($disk->exists('logos')) {
    $files = $disk->files('logos');
    echo "Files in logos directory: " . count($files) . "\n";

    foreach ($files as $file) {
        $size = $disk->size($file);
        $modified = date('Y-m-d H:i:s', $disk->lastModified($file));
        echo "  - {$file} ({$size} bytes, modified: {$modified})\n";
    }
} else {
    echo "⚠️ 'logos' directory does not exist\n";
}

echo "\n";

// Check Filament upload configuration
echo "⚙️ FILAMENT CONFIGURATION:\n";
echo "==========================\n";

echo "Default filesystem disk: " . config('filament.default_filesystem_disk') . "\n";
echo "Filesystems default: " . config('filesystems.default') . "\n";

echo "\n";

// Watch for changes
echo "👁️ MONITORING MODE:\n";
echo "===================\n";
echo "Watching template_settings table for changes...\n";
echo "Please upload a logo now in the admin panel.\n";
echo "Press Ctrl+C to stop monitoring.\n\n";

$lastCheck = null;
$count = 0;

while ($count < 30) { // Monitor for 30 seconds
    sleep(2);
    $count++;

    $current = DB::table('template_settings')->first();

    if ($current && $current != $lastCheck) {
        echo "\n[" . date('H:i:s') . "] CHANGE DETECTED!\n";
        echo "  logo: " . ($current->logo ?? 'NULL') . "\n";
        echo "  updated_at: {$current->updated_at}\n";

        if ($current->logo) {
            echo "\n✅ LOGO UPLOADED SUCCESSFULLY!\n";
            echo "  Path in DB: {$current->logo}\n";

            if ($disk->exists($current->logo)) {
                echo "  ✅ File exists in storage\n";
                $url = "http://nknapijed.localhost:8000/storage/{$current->logo}";
                echo "  URL: {$url}\n";
            } else {
                echo "  ❌ File does NOT exist in storage\n";
            }
            break;
        }

        $lastCheck = $current;
    }

    echo ".";
}

echo "\n\n";

tenancy()->end();

echo "✅ Check complete!\n";
