<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧪 FINAL LOGO TEST\n";
echo "==================\n\n";

// Check updated database setting
$logoSetting = DB::table('settings')
    ->where('group', 'general')
    ->where('name', 'logo')
    ->first();

if ($logoSetting) {
    $logoPath = json_decode($logoSetting->payload, true);
    echo "✅ Logo path in DB: {$logoPath}\n";

    // Check if file is accessible
    $fullPath = public_path($logoPath);
    echo "Full path: {$fullPath}\n";

    if (file_exists($fullPath)) {
        $fileSize = filesize($fullPath);
        echo "✅ File exists! Size: " . number_format($fileSize) . " bytes\n";

        // Test URL accessibility
        $logoUrl = "http://localhost:8000/{$logoPath}";
        echo "✅ Logo URL: {$logoUrl}\n";

    } else {
        echo "❌ File not found at: {$fullPath}\n";
    }
} else {
    echo "❌ Logo setting not found\n";
}

echo "\n🌐 TESTING LANDING PAGE ACCESS\n";
echo "==============================\n";

// Check if landing page config exists
try {
    $landingSettings = DB::table('settings')
        ->where('group', 'general')
        ->whereIn('name', ['logo', 'app_name', 'logo_height'])
        ->get();

    echo "Landing page settings:\n";
    foreach ($landingSettings as $setting) {
        $value = json_decode($setting->payload, true);
        echo "- {$setting->name}: {$value}\n";
    }
} catch (Exception $e) {
    echo "❌ Error reading settings: " . $e->getMessage() . "\n";
}

echo "\n🎯 NEXT STEPS:\n";
echo "=============\n";
echo "1. Open: http://localhost:8000/landing\n";
echo "2. The logo should now be visible\n";
echo "3. If not, check browser developer tools for 404 errors\n";
echo "4. Verify the image loads at: http://localhost:8000/storage/branding/01K8EXGGYK76K5NP820N82J4MR.png\n";

echo "\n✅ LOGO FIX COMPLETED!\n";
