<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "⚽ ALLE MATCHES NACH SPIELTAGEN - ALLE WETTBEWERBE\n";
echo str_repeat("═", 100) . "\n\n";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=club_manager', 'root', '');

// Get all competitions
$compsResult = $pdo->query("
    SELECT DISTINCT competition_fifa_id, international_competition_name
    FROM matches
    ORDER BY international_competition_name
");

$competitions = $compsResult->fetchAll(PDO::FETCH_ASSOC);

echo "📊 " . count($competitions) . " Wettbewerbe gefunden\n\n";

$totalMatches = 0;

foreach ($competitions as $comp) {
    $compId = $comp['competition_fifa_id'];
    $compName = $comp['international_competition_name'];

    echo str_repeat("═", 100) . "\n";
    echo "⚽ " . $compName . " (FIFA: $compId)\n";
    echo str_repeat("═", 100) . "\n";

    // Get all matchdays for this competition
    $matchdaysResult = $pdo->query("
        SELECT DISTINCT match_day
        FROM matches
        WHERE competition_fifa_id = '$compId'
        ORDER BY match_day ASC
    ");

    $matchdays = $matchdaysResult->fetchAll(PDO::FETCH_COLUMN);

    echo "Spieltage: " . implode(", ", $matchdays) . "\n";
    echo "Insgesamt: " . count($matchdays) . " Spieltage\n\n";

    // For each matchday, show all matches
    foreach ($matchdays as $matchday) {
        echo "\n" . str_repeat("─", 100) . "\n";
        echo "  SPIELTAG " . ($matchday ?? "N/A") . "\n";
        echo str_repeat("─", 100) . "\n";

        $matchesResult = $pdo->query("
            SELECT
                match_fifa_id,
                team_name_home,
                team_score_home,
                team_score_away,
                team_name_away,
                match_status,
                date_time_local,
                match_place
            FROM matches
            WHERE competition_fifa_id = '$compId' AND match_day " . ($matchday ? "= $matchday" : "IS NULL") . "
            ORDER BY date_time_local ASC
        ");

        $matches = $matchesResult->fetchAll(PDO::FETCH_ASSOC);

        foreach ($matches as $idx => $match) {
            $totalMatches++;

            // Format the match result
            $homeTeam = substr($match['team_name_home'], 0, 25);
            $awayTeam = substr($match['team_name_away'], 0, 25);

            // Show result if match is played
            if ($match['match_status'] === 'PLAYED') {
                $score = sprintf("%d:%d", $match['team_score_home'], $match['team_score_away']);
                echo sprintf("  ✓ %-27s %s %-27s  | %s | %s\n",
                    $homeTeam,
                    $score,
                    $awayTeam,
                    $match['match_status'],
                    $match['date_time_local']
                );
            } elseif ($match['match_status'] === 'SCHEDULED') {
                echo sprintf("  ○ %-27s vs  %-27s  | %s | %s\n",
                    $homeTeam,
                    $awayTeam,
                    $match['match_status'],
                    $match['date_time_local']
                );
            } elseif ($match['match_status'] === 'POSTPONED') {
                echo sprintf("  ⏸ %-27s -- %-27s  | %s | %s\n",
                    $homeTeam,
                    $awayTeam,
                    $match['match_status'],
                    $match['date_time_local']
                );
            } else {
                echo sprintf("  ? %-27s %d:%d %-27s  | %s\n",
                    $homeTeam,
                    $match['team_score_home'] ?? '-',
                    $match['team_score_away'] ?? '-',
                    $awayTeam,
                    $match['match_status']
                );
            }
        }

        echo "\n  Matches in diesem Spieltag: " . count($matches) . "\n";
    }

    // Summary for this competition
    $statsResult = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN match_status = 'PLAYED' THEN 1 ELSE 0 END) as played,
            SUM(CASE WHEN match_status = 'SCHEDULED' THEN 1 ELSE 0 END) as scheduled,
            SUM(CASE WHEN match_status = 'POSTPONED' THEN 1 ELSE 0 END) as postponed
        FROM matches
        WHERE competition_fifa_id = '$compId'
    ");

    $stats = $statsResult->fetch(PDO::FETCH_ASSOC);

    echo "\n📈 Zusammenfassung für " . $compName . ":\n";
    echo "   Gesamt: " . $stats['total'] . " Matches\n";
    echo "   Gespielt: " . ($stats['played'] ?? 0) . " ✓\n";
    echo "   Geplant: " . ($stats['scheduled'] ?? 0) . " ○\n";
    echo "   Verschoben: " . ($stats['postponed'] ?? 0) . " ⏸\n";
    echo "\n";
}

echo "\n" . str_repeat("═", 100) . "\n";
echo "📊 GESAMT STATISTIK\n";
echo str_repeat("═", 100) . "\n";

$finalStats = $pdo->query("
    SELECT
        COUNT(*) as total,
        COUNT(DISTINCT competition_fifa_id) as competitions,
        SUM(CASE WHEN match_status = 'PLAYED' THEN 1 ELSE 0 END) as played,
        SUM(CASE WHEN match_status = 'SCHEDULED' THEN 1 ELSE 0 END) as scheduled,
        SUM(CASE WHEN match_status = 'POSTPONED' THEN 1 ELSE 0 END) as postponed,
        MIN(date_time_local) as earliest,
        MAX(date_time_local) as latest
    FROM matches
")->fetch(PDO::FETCH_ASSOC);

echo "Wettbewerbe: " . $finalStats['competitions'] . "\n";
echo "Insgesamt Matches: " . $finalStats['total'] . "\n";
echo "  ✓ Gespielt: " . $finalStats['played'] . "\n";
echo "  ○ Geplant: " . $finalStats['scheduled'] . "\n";
echo "  ⏸ Verschoben: " . $finalStats['postponed'] . "\n";
echo "\nZeitraum: " . $finalStats['earliest'] . " bis " . $finalStats['latest'] . "\n";
echo "\n";

?>
