<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== RAW DATABASE CHECK FOR NEWS ID 8 ===\n\n";

$news = DB::connection('central')->table('news')->where('id', 8)->first();

if ($news) {
    echo "Title (raw from DB): " . $news->title . "\n";
    echo "Excerpt (raw): " . ($news->excerpt ?? 'NULL') . "\n";
    echo "Content (raw, first 100 chars): " . substr($news->content ?? 'NULL', 0, 100) . "\n\n";

    // Check if it's JSON
    $titleDecoded = json_decode($news->title, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Title IS JSON!\n";
        print_r($titleDecoded);
    } else {
        echo "Title is NOT JSON, it's a plain string\n";
    }
} else {
    echo "News ID 8 not found!\n";
}
