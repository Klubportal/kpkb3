<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\News;

echo "=== NEWS MEDIA CHECK ===\n\n";

$news = News::find(8);

if ($news) {
    echo "News ID: " . $news->id . "\n";
    echo "Title: " . $news->title . "\n\n";

    echo "Featured Image:\n";
    $featuredUrl = $news->getFirstMediaUrl('featured');
    if ($featuredUrl) {
        echo "  URL: " . $featuredUrl . "\n";
    } else {
        echo "  KEINE Featured Image hochgeladen!\n";
    }

    echo "\nAlle Media:\n";
    $allMedia = $news->getMedia();
    if ($allMedia->count() > 0) {
        foreach ($allMedia as $media) {
            echo "  - Collection: " . $media->collection_name . "\n";
            echo "    File: " . $media->file_name . "\n";
            echo "    URL: " . $media->getUrl() . "\n";
        }
    } else {
        echo "  Keine Media Dateien gefunden\n";
    }
} else {
    echo "News ID 8 nicht gefunden!\n";
}
