<?php

echo "📊 NK PRIGORJE MATCHES - SORTED BY COMPETITION & MATCH_DAY\n";
echo str_repeat("=", 80) . "\n\n";

$tenant = new mysqli('localhost', 'root', '', 'tenant_nkprigorjem');
if ($tenant->connect_error) {
    die("❌ Connection failed: " . $tenant->connect_error);
}

$prigorjeFifaId = 598;

// Get all competitions where NK Prigorje plays
$compsResult = $tenant->query("
    SELECT DISTINCT
        international_competition_name,
        competition_fifa_id,
        age_category_name
    FROM comet_matches
    WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId
    ORDER BY
        CASE
            WHEN age_category_name LIKE '%senior%' THEN 1
            WHEN age_category_name LIKE '%junior%' THEN 2
            WHEN age_category_name LIKE '%kadet%' THEN 3
            WHEN age_category_name LIKE '%pionir%' THEN 4
            ELSE 5
        END,
        international_competition_name
");

while ($comp = $compsResult->fetch_assoc()) {
    echo "🏆 {$comp['international_competition_name']}\n";
    echo "   ({$comp['age_category_name']})\n";
    echo str_repeat("-", 80) . "\n";

    // Get matches for this competition, sorted by match_day
    $matchesResult = $tenant->query("
        SELECT
            match_day,
            date_time_local,
            team_name_home,
            team_score_home,
            team_name_away,
            team_score_away,
            match_status,
            team_logo_home,
            team_logo_away
        FROM comet_matches
        WHERE competition_fifa_id = {$comp['competition_fifa_id']}
        AND (team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId)
        ORDER BY
            CASE WHEN match_day IS NULL THEN 999 ELSE match_day END,
            date_time_local
    ");

    $currentMatchDay = null;
    $matchCount = 0;

    while ($match = $matchesResult->fetch_assoc()) {
        $matchDay = $match['match_day'] ?? 'N/A';

        // Show matchday header if changed
        if ($matchDay !== $currentMatchDay) {
            if ($currentMatchDay !== null) {
                echo "\n";
            }
            echo "   📅 Matchday {$matchDay}:\n";
            $currentMatchDay = $matchDay;
        }

        $date = $match['date_time_local'] ? date('d.m.Y H:i', strtotime($match['date_time_local'])) : 'Noch kein Termin';
        $score = ($match['team_score_home'] !== null && $match['team_score_away'] !== null)
            ? "{$match['team_score_home']}:{$match['team_score_away']}"
            : "-:-";

        $status = match($match['match_status']) {
            'played' => '✅',
            'scheduled' => '📅',
            'cancelled' => '❌',
            default => '❓'
        };

        $homeLogoPre = $match['team_logo_home'] ? '🖼️' : '  ';
        $awayLogoPre = $match['team_logo_away'] ? '🖼️' : '  ';

        // Highlight NK Prigorje
        $homeName = ($match['team_name_home'] === 'NK Prigorje (M)')
            ? "**{$match['team_name_home']}**"
            : $match['team_name_home'];
        $awayName = ($match['team_name_away'] === 'NK Prigorje (M)')
            ? "**{$match['team_name_away']}**"
            : $match['team_name_away'];

        echo "      {$status} {$date} | {$homeName} {$score} {$awayName}\n";
        $matchCount++;
    }

    echo "\n   Total: {$matchCount} matches\n\n";
}

// Summary statistics
echo str_repeat("=", 80) . "\n";
echo "📈 SUMMARY:\n\n";

$result = $tenant->query("
    SELECT
        match_status,
        COUNT(*) as count
    FROM comet_matches
    WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId
    GROUP BY match_status
");

echo "   Match Status:\n";
while ($row = $result->fetch_assoc()) {
    $statusIcon = match($row['match_status']) {
        'played' => '✅ Played',
        'scheduled' => '📅 Scheduled',
        'cancelled' => '❌ Cancelled',
        default => "❓ {$row['match_status']}"
    };
    echo "      {$statusIcon}: {$row['count']}\n";
}

echo "\n";

$tenant->close();
