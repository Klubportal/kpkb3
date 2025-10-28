<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MEDIA TABLE CHECK ===\n\n";

$media = DB::connection('central')->table('media')->where('id', 7)->first();

if ($media) {
    echo "Media ID: " . $media->id . "\n";
    echo "Model: " . $media->model_type . " #" . $media->model_id . "\n";
    echo "Collection: " . $media->collection_name . "\n";
    echo "File: " . $media->file_name . "\n";
    echo "Disk: " . $media->disk . "\n";
    echo "\nExpected path: storage/app/" . $media->disk . "/" . $media->id . "/" . $media->file_name . "\n";

    // Check if file exists
    $path = storage_path('app/' . $media->disk . '/' . $media->id . '/' . $media->file_name);
    if (file_exists($path)) {
        echo "File EXISTS: " . $path . "\n";
    } else {
        echo "File NOT FOUND: " . $path . "\n";
    }
} else {
    echo "Media ID 7 nicht gefunden\n";
}
