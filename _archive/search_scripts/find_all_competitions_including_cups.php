<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// Setup database connection
$db = new DB;
$db->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'kp_server',
    'username' => 'root',
    'password' => ''
]);
$db->setAsGlobal();
$db->bootEloquent();

echo "\n" . str_repeat("═", 80) . "\n";
echo "🏆 ALLE WETTBEWERBE FÜR NK PRIGORJE (FIFA 598) - INKLUSIVE CUPS/POKALE\n";
echo str_repeat("═", 80) . "\n\n";

// 1. Search in matches table - all competitions
echo "【METHODE 1】 Aus MATCHES Tabelle (team_fifa_id_home/away = 598):\n";
echo str_repeat("─", 80) . "\n";

$fromMatches = DB::table('kp_server.matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', '598')
          ->orWhere('team_fifa_id_away', '598');
    })
    ->selectRaw('
        DISTINCT
        competition_fifa_id,
        international_competition_name,
        season,
        competition_status,
        age_category
    ')
    ->orderBy('international_competition_name')
    ->get();

$matchCount = count($fromMatches);
echo "Gefunden: " . $matchCount . " Wettbewerbe\n\n";

foreach ($fromMatches as $idx => $comp) {
    $name = $comp->international_competition_name;
    $isOrIsCup = (stripos($name, 'cup') !== false || stripos($name, 'kup') !== false) ? '🏆' : '⚽';
    echo sprintf(
        "%s %2d) %-60s\n     FIFA: %s | Season: %s | Status: %s | Category: %s\n\n",
        $isOrIsCup,
        $idx + 1,
        $name,
        $comp->competition_fifa_id,
        $comp->season,
        $comp->competition_status,
        $comp->age_category ?? 'N/A'
    );
}

// 2. Search for CUP/KUP in names
echo "\n" . str_repeat("═", 80) . "\n";
echo "【METHODE 2】 Filterung nach CUP/KUP im Namen:\n";
echo str_repeat("─", 80) . "\n";

$cups = collect($fromMatches)->filter(function($comp) {
    return stripos($comp->international_competition_name, 'cup') !== false ||
           stripos($comp->international_competition_name, 'kup') !== false;
});

$leagues = collect($fromMatches)->filter(function($comp) {
    return stripos($comp->international_competition_name, 'cup') === false &&
           stripos($comp->international_competition_name, 'kup') === false;
});

echo "🏆 CUPS/POKALE: " . count($cups) . "\n";
foreach ($cups as $idx => $comp) {
    echo sprintf(
        "  %2d) %s (FIFA: %s, Season: %s)\n",
        $idx + 1,
        $comp->international_competition_name,
        $comp->competition_fifa_id,
        $comp->season
    );
}

echo "\n⚽ LIGEN: " . count($leagues) . "\n";
foreach ($leagues as $idx => $comp) {
    echo sprintf(
        "  %2d) %s (FIFA: %s, Season: %s)\n",
        $idx + 1,
        $comp->international_competition_name,
        $comp->competition_fifa_id,
        $comp->season
    );
}

// 3. Check all statuses in competitions table
echo "\n" . str_repeat("═", 80) . "\n";
echo "【METHODE 3】 Aus COMPETITIONS Tabelle (club_fifa_id = 598):\n";
echo str_repeat("─", 80) . "\n";

$fromCompTable = DB::table('kp_server.competitions')
    ->where('club_fifa_id', '598')
    ->selectRaw('
        DISTINCT
        competition_fifa_id,
        international_competition_name,
        season,
        status,
        age_category_name
    ')
    ->orderBy('international_competition_name')
    ->get();

echo "Gefunden: " . count($fromCompTable) . " Wettbewerbe\n\n";

foreach ($fromCompTable as $idx => $comp) {
    $name = $comp->international_competition_name;
    $isOrIsCup = (stripos($name, 'cup') !== false || stripos($name, 'kup') !== false) ? '🏆' : '⚽';
    echo sprintf(
        "%s %2d) %-60s\n     FIFA: %s | Season: %s | Status: %s | Category: %s\n\n",
        $isOrIsCup,
        $idx + 1,
        $name,
        $comp->competition_fifa_id,
        $comp->season,
        $comp->status,
        $comp->age_category_name ?? 'N/A'
    );
}

// 4. Search for other statuses (not just ACTIVE)
echo "\n" . str_repeat("═", 80) . "\n";
echo "【METHODE 4】 Alle Statuses (nicht nur ACTIVE):\n";
echo str_repeat("─", 80) . "\n";

$allStatuses = DB::table('kp_server.matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', '598')
          ->orWhere('team_fifa_id_away', '598');
    })
    ->selectRaw('DISTINCT competition_status')
    ->orderBy('competition_status')
    ->get();

$statusList = implode(", ", $allStatuses->pluck('competition_status')->toArray());
echo "Verfügbare Statuses: " . $statusList . "\n\n";

// 5. Check all seasons
echo "\n" . str_repeat("═", 80) . "\n";
echo "【METHODE 5】 Alle Saisons (nicht nur 2026):\n";
echo str_repeat("─", 80) . "\n";

$allSeasons = DB::table('kp_server.matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', '598')
          ->orWhere('team_fifa_id_away', '598');
    })
    ->selectRaw('DISTINCT season')
    ->orderBy('season', 'desc')
    ->get();

$seasonList = implode(", ", $allSeasons->pluck('season')->toArray());
echo "Verfügbare Saisons: " . $seasonList . "\n";

$allSeasonComps = DB::table('kp_server.matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', '598')
          ->orWhere('team_fifa_id_away', '598');
    })
    ->selectRaw('
        DISTINCT
        competition_fifa_id,
        international_competition_name,
        season,
        competition_status
    ')
    ->orderBy('season', 'desc')
    ->orderBy('international_competition_name')
    ->get();

echo "\nGesamtanzahl mit ALLEN Saisons: " . count($allSeasonComps) . " Wettbewerbe\n\n";

foreach ($allSeasonComps as $idx => $comp) {
    $name = $comp->international_competition_name;
    $isOrIsCup = (stripos($name, 'cup') !== false || stripos($name, 'kup') !== false) ? '🏆' : '⚽';
    echo sprintf(
        "%s %2d) %-55s Season %s (%s)\n",
        $isOrIsCup,
        $idx + 1,
        substr($name, 0, 55),
        $comp->season,
        $comp->competition_status
    );
}

// Summary
echo "\n" . str_repeat("═", 80) . "\n";
echo "📊 ZUSAMMENFASSUNG:\n";
echo str_repeat("═", 80) . "\n";

$totalMatches = DB::table('kp_server.matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', '598')
          ->orWhere('team_fifa_id_away', '598');
    })
    ->count();

echo "Matches Tabelle (Heim/Auswärts = 598): " . $matchCount . " Wettbewerbe\n";
echo "Competitions Tabelle (club_fifa_id = 598): " . count($fromCompTable) . " Wettbewerbe\n";
echo "Mit ALLEN Saisons: " . count($allSeasonComps) . " Wettbewerbe\n";
echo "Insgesamt Matches: " . $totalMatches . "\n";

$cupCounts = $allSeasonComps->filter(function($c) {
    return stripos($c->international_competition_name, 'cup') !== false ||
           stripos($c->international_competition_name, 'kup') !== false;
});

$leagueCounts = $allSeasonComps->filter(function($c) {
    return stripos($c->international_competition_name, 'cup') === false &&
           stripos($c->international_competition_name, 'kup') === false;
});

echo "\n🏆 Cups/Pokale: " . count($cupCounts) . "\n";
echo "⚽ Ligen: " . count($leagueCounts) . "\n";
echo "📈 TOTAL: " . count($allSeasonComps) . " Wettbewerbe\n";

echo "\n";
?>
