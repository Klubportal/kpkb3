<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LÖSUNG: Import der vorhandenen 598-Daten für Tenant nknapijed ===\n\n";

echo "Da FIFA ID 396 keine Daten im COMET-System hat, können wir:\n\n";
echo "OPTION 1: Die vorhandenen Daten von FIFA 598 importieren (als Platzhalter)\n";
echo "OPTION 2: Warten bis FIFA 396 im COMET-System registriert ist\n";
echo "OPTION 3: Demo-Daten erstellen\n\n";

echo "Aktuelle Datenlage:\n";
echo "- Matches für 598: " . DB::connection('central')->table('comet_matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', 598)->orWhere('team_fifa_id_away', 598);
    })->count() . "\n";
echo "- Players für 598: " . DB::connection('central')->table('comet_players')->where('club_fifa_id', 598)->count() . "\n";
echo "- Coaches für 598: " . DB::connection('central')->table('comet_coaches')->where('club_fifa_id', 598)->count() . "\n";
echo "- Rankings: " . DB::connection('central')->table('comet_rankings')->where('team_fifa_id', 598)->count() . "\n";
echo "- Top Scorers: " . DB::connection('central')->table('comet_top_scorers')->where('team_fifa_id', 598)->count() . "\n";

echo "\n=== Empfehlung ===\n";
echo "Temporär die 598-Daten für den Tenant importieren, damit die Website funktioniert.\n";
echo "Später können wir die Daten aktualisieren, sobald FIFA 396 im COMET-System verfügbar ist.\n";
