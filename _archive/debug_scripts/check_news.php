<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\News;

echo "=== NEWS CHECK ===\n\n";

$allNews = News::latest()->get();

echo "Total news count: " . $allNews->count() . "\n\n";

foreach ($allNews as $news) {
    echo "ID: " . $news->id . "\n";
    echo "Status: " . $news->status . "\n";
    echo "Published at: " . ($news->published_at ?? 'NULL') . "\n";
    echo "Title (raw): " . json_encode($news->title) . "\n";
    echo "---\n";
}

echo "\n=== QUERY TEST (like LandingPage.php) ===\n\n";

$latestNews = News::where('status', 'published')
    ->where('published_at', '<=', now())
    ->orderBy('published_at', 'desc')
    ->take(3)
    ->get();

echo "Published news count: " . $latestNews->count() . "\n";
foreach ($latestNews as $news) {
    echo "- " . ($news->title['hr'] ?? 'No Croatian title') . " (" . $news->published_at . ")\n";
}
