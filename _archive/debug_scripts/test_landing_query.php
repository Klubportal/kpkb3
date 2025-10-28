<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\News;

echo "=== LANDING PAGE NEWS QUERY SIMULATION ===\n\n";

// Set locale
app()->setLocale('hr');
echo "Current locale: " . app()->getLocale() . "\n";
echo "Current time: " . now() . "\n\n";

// Exactly the same query as LandingPage.php
$latestNews = News::where('status', 'published')
    ->where('published_at', '<=', now())
    ->orderBy('published_at', 'desc')
    ->take(3)
    ->get();

echo "Query result count: " . $latestNews->count() . "\n\n";

if ($latestNews->count() > 0) {
    foreach ($latestNews as $news) {
        echo "ID: " . $news->id . "\n";
        echo "Title: " . $news->title . "\n";
        echo "Excerpt: " . ($news->excerpt ?? 'N/A') . "\n";
        echo "Published: " . $news->published_at . "\n";
        echo "---\n";
    }
} else {
    echo "No news found!\n";
}

// Try without filters
echo "\n=== ALL PUBLISHED NEWS (no date filter) ===\n\n";
$all = News::where('status', 'published')->get();
echo "Total published: " . $all->count() . "\n";
foreach ($all as $n) {
    echo "- ID " . $n->id . ": " . $n->title . " (" . $n->published_at . ")\n";
}
