<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔍 FIND THE 11TH COMPETITION\n";
echo str_repeat("═", 80) . "\n\n";

try {
    // Check for ALL competitions regardless of status
    $allComps = DB::connection('mysql')
        ->table('kp_server.matches')
        ->where(function($q) {
            $q->where('team_fifa_id_home', '598')
              ->orWhere('team_fifa_id_away', '598');
        })
        ->selectRaw('DISTINCT
            competition_fifa_id,
            international_competition_name,
            season,
            age_category,
            age_category_name,
            competition_status')
        ->orderBy('season', 'desc')
        ->orderBy('international_competition_name')
        ->get();

    echo "All competitions (any status): " . count($allComps) . "\n\n";

    foreach ($allComps as $i => $comp) {
        echo "【" . ($i + 1) . "】 " . ($comp->international_competition_name ?? 'Unknown') .
             " | Status: " . ($comp->competition_status ?? 'N/A') . " | Season: " . ($comp->season ?? 'N/A') . "\n";
    }
    echo "\n";

    // Check status breakdown
    echo str_repeat("═", 80) . "\n";
    echo "Status breakdown:\n";
    echo str_repeat("─", 80) . "\n\n";

    $statusBreakdown = DB::connection('mysql')
        ->table('kp_server.matches')
        ->where(function($q) {
            $q->where('team_fifa_id_home', '598')
              ->orWhere('team_fifa_id_away', '598');
        })
        ->selectRaw('competition_status, COUNT(DISTINCT competition_fifa_id) as count')
        ->groupBy('competition_status')
        ->get();

    foreach ($statusBreakdown as $status) {
        echo "  " . $status->competition_status . ": " . $status->count . " competitions\n";
    }
    echo "\n";

    // Check if competitions table has more
    echo str_repeat("═", 80) . "\n";
    echo "Check competitions table for FIFA 598:\n";
    echo str_repeat("─", 80) . "\n\n";

    $tableComps = DB::connection('mysql')
        ->table('kp_server.competitions')
        ->where('club_fifa_id', '598')
        ->get();

    echo "Found " . count($tableComps) . " in competitions table:\n\n";
    foreach ($tableComps as $comp) {
        echo "  - " . ($comp->international_competition_name ?? 'Unknown') .
             " | Status: " . ($comp->status ?? 'N/A') . " | Season: " . ($comp->season ?? 'N/A') . "\n";
    }
    echo "\n";

    // Combine all unique
    echo str_repeat("═", 80) . "\n";
    echo "Trying to combine from both sources:\n";
    echo str_repeat("─", 80) . "\n\n";

    $combined = DB::connection('mysql')
        ->table('kp_server.matches')
        ->where(function($q) {
            $q->where('team_fifa_id_home', '598')
              ->orWhere('team_fifa_id_away', '598');
        })
        ->union(
            DB::connection('mysql')
                ->table('kp_server.competitions')
                ->where('club_fifa_id', '598')
                ->selectRaw('CAST(competition_fifa_id as CHAR) as competition_fifa_id,
                    international_competition_name,
                    season,
                    age_category,
                    age_category_name,
                    status as competition_status')
        )
        ->selectRaw('DISTINCT
            competition_fifa_id,
            international_competition_name,
            season')
        ->orderBy('season', 'desc')
        ->orderBy('international_competition_name')
        ->get();

    echo "Combined from both sources: " . count($combined) . "\n\n";

    foreach ($combined as $i => $comp) {
        echo "【" . ($i + 1) . "】 " . ($comp->international_competition_name ?? 'Unknown') . "\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo str_repeat("═", 80) . "\n";
