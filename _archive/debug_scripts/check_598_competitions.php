<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING CLUB FIFA ID 598 (NK PRIGORJE) ===\n\n";

// Check comet_competitions table structure
echo "1. Checking comet_competitions table columns:\n";
$columns = DB::connection('central')->select("SHOW COLUMNS FROM comet_competitions");
foreach ($columns as $column) {
    echo "   - {$column->Field} ({$column->Type})\n";
}

echo "\n2. Total competitions in database:\n";
$total = DB::connection('central')->table('comet_competitions')->count();
echo "   Total: $total\n";

echo "\n3. Competitions with team_fifa_id or club_fifa_id = 598:\n";
$withTeamId = DB::connection('central')
    ->table('comet_competitions')
    ->where('team_fifa_id', 598)
    ->count();
echo "   team_fifa_id = 598: $withTeamId\n";

// Check if there's a club_fifa_id column
try {
    $withClubId = DB::connection('central')
        ->table('comet_competitions')
        ->where('club_fifa_id', 598)
        ->count();
    echo "   club_fifa_id = 598: $withClubId\n";
} catch (\Exception $e) {
    echo "   club_fifa_id column doesn't exist\n";
}

echo "\n4. Checking comet_club_competitions table:\n";
try {
    $clubCompetitions = DB::connection('central')
        ->table('comet_club_competitions')
        ->where('club_fifa_id', 598)
        ->count();
    echo "   Entries with club_fifa_id = 598: $clubCompetitions\n";

    if ($clubCompetitions > 0) {
        echo "\n   Competition IDs for club 598:\n";
        $competitionIds = DB::connection('central')
            ->table('comet_club_competitions')
            ->where('club_fifa_id', 598)
            ->pluck('competition_fifa_id');

        echo "   Found " . $competitionIds->count() . " competition IDs\n";

        // Get competition details
        $competitions = DB::connection('central')
            ->table('comet_competitions')
            ->whereIn('comet_id', $competitionIds)
            ->get(['comet_id', 'name', 'season', 'active']);

        echo "\n   Competitions:\n";
        foreach ($competitions as $comp) {
            $active = $comp->active ? 'ACTIVE' : 'inactive';
            echo "   - [{$comp->comet_id}] {$comp->name} (Season {$comp->season}) - $active\n";
        }
    }
} catch (\Exception $e) {
    echo "   Table doesn't exist or error: " . $e->getMessage() . "\n";
}

echo "\n5. How was the sync done? Checking sync script logic:\n";
echo "   The sync used teamFifaId parameter = 618 (not 598!)\n";
echo "   FIFA ID 618 = NK Prigorje TEAM\n";
echo "   FIFA ID 598 = NK Prigorje CLUB\n";
