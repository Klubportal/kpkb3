<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Wie wurde comet_club_competitions befüllt? ===\n\n";

$comps = DB::connection('central')->table('comet_club_competitions')->get();
echo "Competitions in DB: " . $comps->count() . "\n\n";

echo "Erste 5 Einträge:\n";
foreach ($comps->take(5) as $c) {
    echo "ID: {$c->competitionFifaId} - {$c->internationalName} (Season: {$c->season}, Status: {$c->status})\n";
}

echo "\n=== Wo kommen diese Daten her? ===\n";
echo "Ich suche nach einem Command der diese Tabelle befüllt...\n";
