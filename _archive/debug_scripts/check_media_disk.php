<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MEDIA DISK CHECK ===\n\n";

$media = DB::connection('central')->table('media')->where('id', 9)->first();

if ($media) {
    echo "Media ID: " . $media->id . "\n";
    echo "Disk: " . $media->disk . "\n";
    echo "File: " . $media->file_name . "\n\n";

    // Check both possible paths
    $localPath = storage_path('app/local/' . $media->id . '/' . $media->file_name);
    $publicPath = storage_path('app/public/' . $media->id . '/' . $media->file_name);

    echo "Checking local disk: " . $localPath . "\n";
    if (file_exists($localPath)) {
        echo "  ✓ EXISTS!\n";
    } else {
        echo "  ✗ NOT FOUND\n";
    }

    echo "\nChecking public disk: " . $publicPath . "\n";
    if (file_exists($publicPath)) {
        echo "  ✓ EXISTS!\n";
    } else {
        echo "  ✗ NOT FOUND\n";
    }

    // Check config
    echo "\nMedia Library Config Disk: " . config('media-library.disk_name') . "\n";

} else {
    echo "Media ID 9 nicht gefunden\n";
}
