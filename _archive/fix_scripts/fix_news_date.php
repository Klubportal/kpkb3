<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Updating news ID 8 published_at to current time...\n";

DB::connection('central')->table('news')
    ->where('id', 8)
    ->update(['published_at' => now()->subHour()]);

echo "Done! New time: " . DB::connection('central')->table('news')->where('id', 8)->value('published_at') . "\n";
