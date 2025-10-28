<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔧 FIXING LOGO PATH IN DATABASE\n";
echo "===============================\n\n";

// Current logo path in DB
$currentSetting = DB::connection('central')->table('settings')
    ->where('group', 'general')
    ->where('name', 'logo')
    ->first();

if ($currentSetting) {
    $currentPath = json_decode($currentSetting->payload, true);
    echo "Current logo path: {$currentPath}\n";

    // Check if file exists at current path
    $currentFullPath = public_path($currentPath);
    echo "Checking: {$currentFullPath}\n";

    if (file_exists($currentFullPath)) {
        echo "✅ File exists at current path - no change needed\n";
    } else {
        echo "❌ File not found at current path\n";

        // Check if file exists in storage
        $storagePath = "storage/branding/01K8EXGGYK76K5NP820N82J4MR.png";
        $storageFullPath = public_path($storagePath);
        echo "Checking storage path: {$storageFullPath}\n";

        if (file_exists($storageFullPath)) {
            echo "✅ File found in storage path!\n";
            echo "Updating database path...\n";

            $updated = DB::connection('central')->table('settings')
                ->where('group', 'general')
                ->where('name', 'logo')
                ->update(['payload' => json_encode($storagePath)]);

            if ($updated) {
                echo "✅ Database updated successfully!\n";
                echo "New path: {$storagePath}\n";
            } else {
                echo "❌ Failed to update database\n";
            }
        } else {
            echo "❌ File not found in storage either\n";
            echo "Available files in storage/branding:\n";

            $brandingDir = storage_path('app/public/branding');
            if (is_dir($brandingDir)) {
                $files = scandir($brandingDir);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        echo "- {$file}\n";
                    }
                }
            } else {
                echo "❌ Branding directory not found\n";
            }
        }
    }
} else {
    echo "❌ Logo setting not found in database\n";
}

echo "\n🧹 CLEARING CACHE\n";
echo "=================\n";
try {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo "✅ Cache cleared\n";
} catch (Exception $e) {
    echo "❌ Cache clear failed: " . $e->getMessage() . "\n";
}

echo "\n🎯 RECOMMENDATIONS:\n";
echo "===================\n";
echo "1. Check http://localhost:8000/landing now\n";
echo "2. If still not working, check browser console for errors\n";
echo "3. Verify public/storage symlink exists\n";
echo "4. Check file permissions\n";
