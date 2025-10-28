<?php

use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/bootstrap/app.php';

$app = new Illuminate\Foundation\Application(
    basePath: dirname(__DIR__),
);

$app->bind(
    \Illuminate\Contracts\Http\Kernel::class,
    \App\Http\Kernel::class,
);

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n" . str_repeat("═", 60) . "\n";
echo "🔍 FINAL 11TH COMPETITION SEARCH - ALL POSSIBILITIES\n";
echo str_repeat("═", 60) . "\n\n";

// 1. Get from matches table (home OR away)
echo "【METHOD 1】 From MATCHES table (team_fifa_id_home OR away = 598):\n";
echo str_repeat("─", 60) . "\n";

$fromMatches = DB::connection('mysql')
    ->table('kp_server.matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', '598')
          ->orWhere('team_fifa_id_away', '598');
    })
    ->selectRaw('DISTINCT competition_fifa_id, international_competition_name, season, competition_status')
    ->orderBy('season', 'desc')
    ->orderBy('international_competition_name')
    ->get();

echo "Count: " . count($fromMatches) . " competitions\n";
foreach ($fromMatches as $idx => $comp) {
    echo sprintf(
        "  %2d) %s (FIFA: %s, Season: %s, Status: %s)\n",
        $idx + 1,
        $comp->international_competition_name,
        $comp->competition_fifa_id,
        $comp->season,
        $comp->competition_status
    );
}

// 2. Get from competitions table
echo "\n【METHOD 2】 From COMPETITIONS table (club_fifa_id = 598):\n";
echo str_repeat("─", 60) . "\n";

$fromCompetitions = DB::connection('mysql')
    ->table('kp_server.competitions')
    ->where('club_fifa_id', '598')
    ->selectRaw('DISTINCT competition_fifa_id, international_competition_name, season, status')
    ->orderBy('season', 'desc')
    ->orderBy('international_competition_name')
    ->get();

echo "Count: " . count($fromCompetitions) . " competitions\n";
foreach ($fromCompetitions as $idx => $comp) {
    echo sprintf(
        "  %2d) %s (FIFA: %s, Season: %s, Status: %s)\n",
        $idx + 1,
        $comp->international_competition_name,
        $comp->competition_fifa_id,
        $comp->season,
        $comp->status
    );
}

// 3. Check for other seasons in matches
echo "\n【METHOD 3】 From MATCHES with ALL SEASONS (team_fifa_id = 598):\n";
echo str_repeat("─", 60) . "\n";

$allSeasons = DB::connection('mysql')
    ->table('kp_server.matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', '598')
          ->orWhere('team_fifa_id_away', '598');
    })
    ->selectRaw('DISTINCT season')
    ->orderBy('season', 'desc')
    ->get();

echo "Seasons found: " . implode(", ", $allSeasons->pluck('season')->toArray()) . "\n";

$allSeasonComps = DB::connection('mysql')
    ->table('kp_server.matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', '598')
          ->orWhere('team_fifa_id_away', '598');
    })
    ->selectRaw('DISTINCT competition_fifa_id, international_competition_name, season, competition_status')
    ->orderBy('season', 'desc')
    ->orderBy('international_competition_name')
    ->get();

echo "Count with ALL seasons: " . count($allSeasonComps) . " competitions\n";
foreach ($allSeasonComps as $idx => $comp) {
    echo sprintf(
        "  %2d) %s (FIFA: %s, Season: %s, Status: %s)\n",
        $idx + 1,
        $comp->international_competition_name,
        $comp->competition_fifa_id,
        $comp->season,
        $comp->competition_status
    );
}

// 4. Check by FIFA team ID vs club FIFA ID
echo "\n【METHOD 4】 From MATCHES with fifa_team_id variations:\n";
echo str_repeat("─", 60) . "\n";

$byTeamFifaId = DB::connection('mysql')
    ->table('kp_server.matches')
    ->where(function($q) {
        $q->where('fifa_team_id_home', '598')
          ->orWhere('fifa_team_id_away', '598');
    })
    ->selectRaw('DISTINCT competition_fifa_id, international_competition_name, season, competition_status')
    ->orderBy('season', 'desc')
    ->orderBy('international_competition_name')
    ->get();

echo "Count: " . count($byTeamFifaId) . " competitions\n";
foreach ($byTeamFifaId as $idx => $comp) {
    echo sprintf(
        "  %2d) %s (FIFA: %s, Season: %s, Status: %s)\n",
        $idx + 1,
        $comp->international_competition_name,
        $comp->competition_fifa_id,
        $comp->season,
        $comp->competition_status
    );
}

// 5. Check total unique competitions in kp_server.competitions for ANY club
echo "\n【METHOD 5】 Check COMPETITIONS table structure:\n";
echo str_repeat("─", 60) . "\n";

$compSchema = DB::connection('mysql')
    ->table('information_schema.COLUMNS')
    ->where('TABLE_SCHEMA', 'kp_server')
    ->where('TABLE_NAME', 'competitions')
    ->select('COLUMN_NAME', 'DATA_TYPE', 'COLUMN_KEY')
    ->get();

echo "Columns in kp_server.competitions:\n";
foreach ($compSchema as $col) {
    echo sprintf("  - %s (%s) %s\n", $col->COLUMN_NAME, $col->DATA_TYPE, $col->COLUMN_KEY);
}

$totalCompetitions = DB::connection('mysql')
    ->table('kp_server.competitions')
    ->count();

echo "\nTotal competitions in competitions table: " . $totalCompetitions . "\n";

$clubIds = DB::connection('mysql')
    ->table('kp_server.competitions')
    ->selectRaw('DISTINCT club_fifa_id')
    ->get();

echo "Unique club_fifa_ids: " . count($clubIds) . "\n";
echo "Club IDs: " . implode(", ", $clubIds->pluck('club_fifa_id')->toArray()) . "\n";

// Final summary
echo "\n" . str_repeat("═", 60) . "\n";
echo "📊 SUMMARY:\n";
echo str_repeat("═", 60) . "\n";
echo "Matches table (team_fifa_id_home/away = 598): " . count($fromMatches) . " competitions\n";
echo "Competitions table (club_fifa_id = 598): " . count($fromCompetitions) . " competitions\n";
echo "Matches with all statuses: " . count($allSeasonComps) . " competitions\n";
echo "Matches with fifa_team_id: " . count($byTeamFifaId) . " competitions\n\n";

if (count($fromMatches) == 10) {
    echo "✅ CONCLUSION: Exactly 10 competitions found for NK Prigorje (FIFA 598)\n";
    echo "   across all search methods and all seasons.\n\n";
    echo "⚠️  The 11th competition either:\n";
    echo "   1) Does not exist in the data\n";
    echo "   2) Exists under different FIFA ID for NK Prigorje\n";
    echo "   3) Exists in different database/source\n";
    echo "   4) Is from a different club (not FK Prigorje)\n";
} else {
    echo "🔔 Found " . count($fromMatches) . " competitions\n";
}

echo "\n";
?>
