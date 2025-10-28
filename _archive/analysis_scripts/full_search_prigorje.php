<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔍 CHECK ALL DATA FOR NK PRIGORJE IN KP_SERVER\n";
echo str_repeat("═", 80) . "\n\n";

try {
    // Check the clubs table for NK Prigorje
    echo "Step 1: Find NK Prigorje in kp_server.clubs\n";
    echo str_repeat("─", 80) . "\n\n";

    $club = DB::connection('mysql')
        ->table('kp_server.clubs')
        ->where('id', 1)
        ->first();

    if ($club) {
        echo json_encode($club, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

        $clubId = $club->id;
        $fifaTeamId = $club->fifa_team_id;
        $fifaOrgId = $club->fifa_organisation_id;

        echo "Club IDs:\n";
        echo "  - Local DB ID: $clubId\n";
        echo "  - FIFA Team ID: $fifaTeamId\n";
        echo "  - FIFA Org ID: $fifaOrgId\n\n";

        // Now search for competitions using BOTH IDs
        echo str_repeat("═", 80) . "\n";
        echo "Step 2: Search competitions with both FIFA IDs\n";
        echo str_repeat("─", 80) . "\n\n";

        foreach ([$clubId, $fifaTeamId, $fifaOrgId, 598] as $searchId) {
            if ($searchId) {
                $comps = DB::connection('mysql')
                    ->table('kp_server.matches')
                    ->where('club_fifa_id', (string)$searchId)
                    ->selectRaw('DISTINCT
                        competition_fifa_id,
                        international_competition_name,
                        season')
                    ->count();

                echo "  Club FIFA ID '$searchId': $comps distinct competitions\n";
            }
        }
        echo "\n";

    }

    // Check the competitions table (direct)
    echo str_repeat("═", 80) . "\n";
    echo "Step 3: Check competitions table directly\n";
    echo str_repeat("─", 80) . "\n\n";

    $directComps = DB::connection('mysql')
        ->table('kp_server.competitions')
        ->where('club_fifa_id', '598')
        ->orWhere('club_fifa_id', '1')
        ->selectRaw('DISTINCT
            club_fifa_id,
            competition_fifa_id,
            international_competition_name,
            season,
            status')
        ->orderBy('club_fifa_id')
        ->orderBy('international_competition_name')
        ->get();

    echo "Found " . count($directComps) . " in direct competitions table:\n\n";
    foreach ($directComps as $comp) {
        echo "  Club: " . $comp->club_fifa_id . " | " . $comp->international_competition_name . " | S: " . $comp->season . "\n";
    }
    echo "\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo str_repeat("═", 80) . "\n";
