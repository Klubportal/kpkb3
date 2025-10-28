<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== RICHTIGE ZUORDNUNG: Club 598 zu Competitions ===\n\n";

echo "1. Checking comet_club_competitions table:\n";
try {
    $clubComps = DB::connection('central')
        ->table('comet_club_competitions')
        ->where('club_fifa_id', 598)
        ->get();

    echo "   Anzahl Einträge für club_fifa_id = 598: " . $clubComps->count() . "\n\n";

    if ($clubComps->count() > 0) {
        $competitionIds = $clubComps->pluck('competition_fifa_id')->unique();

        echo "   Unique Competition IDs: " . $competitionIds->count() . "\n";
        echo "   Competition FIFA IDs: " . $competitionIds->implode(', ') . "\n\n";

        // Get competition details
        $competitions = DB::connection('central')
            ->table('comet_competitions')
            ->whereIn('comet_id', $competitionIds)
            ->where('active', true)
            ->whereIn('season', [2025, 2026])
            ->orderBy('season', 'desc')
            ->orderBy('name')
            ->get();

        echo "   AKTIVE Competitions (Season 2025/2026):\n";
        echo "   Anzahl: " . $competitions->count() . "\n\n";

        foreach ($competitions as $comp) {
            echo "   - [{$comp->comet_id}] {$comp->name}\n";
            echo "     Season: {$comp->season} | Type: {$comp->type} | Gender: {$comp->gender}\n\n";
        }
    }
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n\n";
}

echo "2. Alternative: Check if we synced with teamFifaId = 618:\n";
echo "   The sync script used teamFifaId = 618, NOT club 598\n";
echo "   We need to check comet_club_competitions for the relationship\n";
