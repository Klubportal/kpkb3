<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Lösche comet_matches Tabelle...\n";

if (Schema::connection('central')->hasTable('comet_matches')) {
    Schema::connection('central')->drop('comet_matches');
    echo "✓ Tabelle comet_matches gelöscht\n";
} else {
    echo "⚠ Tabelle comet_matches existiert nicht\n";
}
