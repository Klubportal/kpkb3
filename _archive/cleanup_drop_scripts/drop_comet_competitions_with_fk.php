<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

try {
    echo "Dropping FK comet_player_competition_stats_competition_id_foreign if exists...\n";
    DB::connection('central')->statement("ALTER TABLE comet_player_competition_stats DROP FOREIGN KEY comet_player_competition_stats_competition_id_foreign");
    echo "Dropped FK.\n";
} catch (\Exception $e) {
    echo "FK drop error (maybe doesn't exist): " . $e->getMessage() . "\n";
}

try {
    echo "Dropping table comet_competitions...\n";
    DB::connection('central')->statement('DROP TABLE IF EXISTS comet_competitions');
    echo "Dropped comet_competitions.\n";
} catch (\Exception $e) {
    echo "Table drop error: " . $e->getMessage() . "\n";
}
