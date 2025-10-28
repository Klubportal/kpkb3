<?php

// CHECK MATCHES BY MATCHDAY
echo "📊 COMET MATCHES - Grouped by Competition & Match Day\n";
echo str_repeat("=", 80) . "\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get competitions
$competitionsQuery = "SELECT DISTINCT
                        competition_fifa_id,
                        international_competition_name,
                        age_category_name
                      FROM comet_matches
                      ORDER BY international_competition_name";

$competitions = $mysqli->query($competitionsQuery);

while ($comp = $competitions->fetch_assoc()) {
    echo "\n📋 {$comp['international_competition_name']} ({$comp['age_category_name']})\n";
    echo str_repeat("-", 80) . "\n";

    // Get matches grouped by match_day
    $matchesQuery = "SELECT
                        match_day,
                        match_fifa_id,
                        date_time_local,
                        team_name_home,
                        team_score_home,
                        team_name_away,
                        team_score_away,
                        match_status
                     FROM comet_matches
                     WHERE competition_fifa_id = {$comp['competition_fifa_id']}
                     ORDER BY match_day ASC, date_time_local ASC
                     LIMIT 50";

    $matches = $mysqli->query($matchesQuery);

    $currentMatchDay = null;
    $matchCount = 0;

    while ($match = $matches->fetch_assoc()) {
        if ($currentMatchDay !== $match['match_day']) {
            if ($currentMatchDay !== null) {
                echo "\n";
            }
            $currentMatchDay = $match['match_day'];
            $matchDay = $match['match_day'] ?? 'N/A';
            echo "\n  Spieltag {$matchDay}:\n";
            echo "  " . str_repeat("─", 76) . "\n";
        }

        $homeScore = $match['team_score_home'] ?? '-';
        $awayScore = $match['team_score_away'] ?? '-';
        $status = $match['match_status'];
        $date = $match['date_time_local'] ? date('d.m.Y H:i', strtotime($match['date_time_local'])) : 'TBD';

        $statusIcon = $status === 'played' ? '✅' : '📅';

        echo sprintf("  %s %s | %-25s %2s : %-2s %-25s\n",
            $statusIcon,
            $date,
            substr($match['team_name_home'], 0, 25),
            $homeScore,
            $awayScore,
            substr($match['team_name_away'], 0, 25)
        );

        $matchCount++;
    }

    echo "\n  Total matches shown: {$matchCount}\n";
}

$mysqli->close();

?>
