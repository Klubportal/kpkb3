<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking comet_club_competitions structure...\n";
echo "==============================================\n\n";

$cols = DB::connection('mysql')->select('DESCRIBE kpkb3.comet_club_competitions');

foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type})\n";
}

echo "\n\nSample data:\n";
echo "============\n";

$sample = DB::connection('mysql')->table('kpkb3.comet_club_competitions')
    ->limit(3)
    ->get();

foreach ($sample as $row) {
    print_r($row);
}
