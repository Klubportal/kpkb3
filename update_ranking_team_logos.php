<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Aktualisiere Team-Logos in comet_rankings...\n\n";

// Update Central DB
echo "Central DB:\n";

// Get all unique team IDs from rankings
$rankings = DB::connection('central')
    ->table('comet_rankings')
    ->select('id', 'team_fifa_id')
    ->get();

$updated = 0;

foreach ($rankings as $ranking) {
    // Try to find team logo from matches (as home team first)
    $match = DB::connection('central')
        ->table('comet_matches')
        ->where('team_fifa_id_home', $ranking->team_fifa_id)
        ->whereNotNull('team_logo_home')
        ->first();

    $teamLogo = null;

    if ($match && $match->team_logo_home) {
        $teamLogo = $match->team_logo_home;
    } else {
        // Try as away team
        $match = DB::connection('central')
            ->table('comet_matches')
            ->where('team_fifa_id_away', $ranking->team_fifa_id)
            ->whereNotNull('team_logo_away')
            ->first();

        if ($match && $match->team_logo_away) {
            $teamLogo = $match->team_logo_away;
        }
    }

    if ($teamLogo) {
        DB::connection('central')
            ->table('comet_rankings')
            ->where('id', $ranking->id)
            ->update(['team_image_logo' => $teamLogo]);
        $updated++;

        if ($updated % 10 == 0) {
            echo "  Progress: {$updated} logos updated...\n";
        }
    }
}

echo "  ✅ {$updated} Team-Logos aktualisiert\n\n";

// Show sample
$sample = DB::connection('central')->table('comet_rankings')
    ->select('team_fifa_id', 'international_team_name', 'team_image_logo')
    ->whereNotNull('team_image_logo')
    ->limit(10)
    ->get();

echo "Beispiel:\n";
foreach ($sample as $row) {
    echo "  - {$row->international_team_name}: {$row->team_image_logo}\n";
}

echo "\n✅ Fertig!\n";
