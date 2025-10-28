<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Aktualisiere Vereinsnamen in comet_rankings...\n\n";

// Update Central DB
echo "Central DB:\n";

// Get all rankings with team IDs
$rankings = DB::connection('central')
    ->table('comet_rankings')
    ->select('id', 'team_fifa_id', 'international_team_name')
    ->get();

$updated = 0;

foreach ($rankings as $ranking) {
    // Try to find team name from matches (as home team)
    $match = DB::connection('central')
        ->table('comet_matches')
        ->where('team_fifa_id_home', $ranking->team_fifa_id)
        ->whereNotNull('team_name_home')
        ->first();
    
    $teamName = null;
    
    if ($match && $match->team_name_home) {
        $teamName = $match->team_name_home;
    } else {
        // Try as away team
        $match = DB::connection('central')
            ->table('comet_matches')
            ->where('team_fifa_id_away', $ranking->team_fifa_id)
            ->whereNotNull('team_name_away')
            ->first();
        
        if ($match && $match->team_name_away) {
            $teamName = $match->team_name_away;
        }
    }
    
    if ($teamName && $teamName !== 'Unknown') {
        DB::connection('central')
            ->table('comet_rankings')
            ->where('id', $ranking->id)
            ->update(['international_team_name' => $teamName]);
        $updated++;
        
        if ($updated % 10 == 0) {
            echo "  Progress: {$updated} rankings updated...\n";
        }
    }
}

echo "  ✅ {$updated} Rankings aktualisiert\n\n";// Show sample
$sample = DB::connection('central')->table('comet_rankings')
    ->select('team_fifa_id', 'international_team_name', 'position', 'points')
    ->where('international_team_name', '!=', 'Unknown')
    ->limit(10)
    ->get();

echo "Beispiel:\n";
foreach ($sample as $row) {
    echo "  - FIFA ID {$row->team_fifa_id}: {$row->international_team_name} (Platz {$row->position}, {$row->points} Punkte)\n";
}

echo "\n✅ Fertig!\n";
