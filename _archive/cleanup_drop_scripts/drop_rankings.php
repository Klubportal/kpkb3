<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Lösche alte comet_rankings Tabelle...\n";
DB::connection('central')->statement('DROP TABLE IF EXISTS comet_rankings');
echo "✅ Tabelle gelöscht!\n";
