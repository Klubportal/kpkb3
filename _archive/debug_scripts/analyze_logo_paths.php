<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 ANALYZING LOGO PATHS IN DATABASE\n";
echo "===================================\n\n";

// Check settings table for logo paths
echo "1️⃣ CHECKING SETTINGS TABLE\n";
echo "===========================\n";
try {
    $settings = DB::connection('central')->table('settings')
        ->where('name', 'like', '%logo%')
        ->orWhere('name', 'like', '%brand%')
        ->orWhere('payload', 'like', '%klubportal_landlord%')
        ->orWhere('payload', 'like', '%storage%')
        ->get();

    foreach ($settings as $setting) {
        echo "Setting: {$setting->group}.{$setting->name}\n";
        echo "Payload: {$setting->payload}\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "❌ Error reading settings: " . $e->getMessage() . "\n";
}

echo "\n2️⃣ CHECKING FOR KLUBPORTAL_LANDLORD REFERENCES\n";
echo "==============================================\n";
try {
    $oldRefs = DB::connection('central')->table('settings')
        ->where('payload', 'like', '%klubportal_landlord%')
        ->get();

    if ($oldRefs->count() > 0) {
        echo "❌ Found old references:\n";
        foreach ($oldRefs as $ref) {
            echo "- {$ref->group}.{$ref->name}: {$ref->payload}\n";
        }
    } else {
        echo "✅ No old klubportal_landlord references found\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n3️⃣ CHECKING MEDIA/FILES TABLE\n";
echo "=============================\n";
try {
    // Check if media table exists
    $tables = DB::connection('central')->select("SHOW TABLES LIKE 'media'");
    if (count($tables) > 0) {
        $media = DB::connection('central')->table('media')
            ->where('disk', 'like', '%klubportal%')
            ->orWhere('disk', 'like', '%storage%')
            ->limit(10)
            ->get();

        foreach ($media as $item) {
            echo "Media ID: {$item->id}, Disk: {$item->disk}, Path: " . ($item->path ?? 'N/A') . "\n";
        }
    } else {
        echo "ℹ️ No media table found\n";
    }
} catch (Exception $e) {
    echo "❌ Error reading media: " . $e->getMessage() . "\n";
}

echo "\n4️⃣ CHECKING CURRENT STORAGE STRUCTURE\n";
echo "=====================================\n";
$storagePaths = [
    'storage/app/public' => 'App Storage Public',
    'public/storage' => 'Public Storage Link',
    'storage/branding' => 'Branding Storage'
];

foreach ($storagePaths as $path => $desc) {
    $fullPath = getcwd() . DIRECTORY_SEPARATOR . $path;
    if (is_dir($fullPath)) {
        $files = scandir($fullPath);
        $fileCount = count(array_diff($files, ['.', '..']));
        echo "✅ {$desc}: {$path} ({$fileCount} files)\n";
    } else {
        echo "❌ {$desc}: {$path} (not found)\n";
    }
}

echo "\n💡 RECOMMENDATIONS:\n";
echo "===================\n";
echo "1. Update settings table to replace klubportal_landlord with kpkb3\n";
echo "2. Check storage link: php artisan storage:link\n";
echo "3. Verify logo files exist in correct directories\n";
echo "4. Clear cache: php artisan cache:clear\n";
