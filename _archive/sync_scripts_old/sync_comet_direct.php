<?php

/**
 * Direct COMET API Sync
 *
 * Synchronisiert COMET-Daten direkt via API ohne Laravel Bootstrap
 * Speichert in zentrale kpkb3 Datenbank
 */

// Datenbank-Verbindung
$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("❌ Datenbankverbindung fehlgeschlagen: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

echo "\n=== DIRECT COMET API SYNC ===\n\n";

// API Konfiguration
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Vereine
$clubs = [
    [
        'name' => 'NK Prigorje Markuševec',
        'fifa_id' => 598,
    ],
    [
        'name' => 'Club 396',
        'fifa_id' => 396,
    ],
    [
        'name' => 'Club 601',
        'fifa_id' => 601,
    ],
];

foreach ($clubs as $club) {
    echo "🔄 Synchronisiere: {$club['name']} (FIFA ID: {$club['fifa_id']})\n";
    echo str_repeat('-', 70) . "\n";

    $clubFifaId = $club['fifa_id'];
    $currentYear = date('Y');
    $nextYear = $currentYear + 1;

    // 1. Competitions laden
    echo "\n1️⃣  Lade Competitions...\n";
    $competitions = apiCall($apiUrl . "/competitions?teamFifaId={$clubFifaId}&active=true", $username, $password);

    if (!$competitions) {
        echo "   ❌ Keine Competitions gefunden\n";
        continue;
    }

    // Filtere nach aktuellem/nächstem Jahr
    $relevantComps = array_filter($competitions, function($comp) use ($currentYear, $nextYear) {
        $season = $comp['season'] ?? 0;
        return $season == $currentYear || $season == $nextYear;
    });

    echo "   ✓ Gefunden: " . count($relevantComps) . " Competitions für {$currentYear}/{$nextYear}\n";

    // Speichere Competitions
    $compCount = 0;
    foreach ($relevantComps as $comp) {
        $compId = (int)$comp['competitionFifaId'];
        $season = (int)($comp['season'] ?? $currentYear);
        $ageCategory = $mysqli->real_escape_string($comp['ageCategory'] ?? 'SENIORS');
        $ageCategoryName = $mysqli->real_escape_string($comp['ageCategoryName'] ?? 'label.seniors');
        $internationalName = $mysqli->real_escape_string($comp['internationalName'] ?? 'Unknown');
        $status = $mysqli->real_escape_string($comp['status'] ?? 'ACTIVE');

        $sql = "INSERT INTO comet_club_competitions
                (competitionFifaId, ageCategory, ageCategoryName, internationalName, season, status, created_at, updated_at)
                VALUES
                ({$compId}, '{$ageCategory}', '{$ageCategoryName}', '{$internationalName}', {$season}, '{$status}', NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                ageCategory = VALUES(ageCategory),
                ageCategoryName = VALUES(ageCategoryName),
                internationalName = VALUES(internationalName),
                season = VALUES(season),
                status = VALUES(status),
                updated_at = NOW()";

        if ($mysqli->query($sql)) {
            $compCount++;
        }
    }
    echo "   ✓ Gespeichert: {$compCount} Competitions\n";    $competitionIds = array_column($relevantComps, 'competitionFifaId');

    // 2. Matches laden
    echo "\n2️⃣  Lade Matches...\n";
    $totalMatches = 0;
    foreach ($competitionIds as $compId) {
        $matches = apiCall($apiUrl . "/matches?competitionFifaId={$compId}", $username, $password);
        if ($matches) {
            foreach ($matches as $match) {
                saveMatch($mysqli, $match);
                $totalMatches++;
            }
        }
    }
    echo "   ✓ Gespeichert: {$totalMatches} Matches\n";

    // 3. Rankings laden
    echo "\n3️⃣  Lade Rankings...\n";
    $totalRankings = 0;
    foreach ($competitionIds as $compId) {
        $rankings = apiCall($apiUrl . "/rankings?competitionFifaId={$compId}", $username, $password);
        if ($rankings) {
            foreach ($rankings as $ranking) {
                saveRanking($mysqli, $ranking);
                $totalRankings++;
            }
        }
    }
    echo "   ✓ Gespeichert: {$totalRankings} Rankings\n";

    // 4. Top Scorers laden
    echo "\n4️⃣  Lade Top Scorers...\n";
    $totalScorers = 0;
    foreach ($competitionIds as $compId) {
        $scorers = apiCall($apiUrl . "/top-scorers?competitionFifaId={$compId}", $username, $password);
        if ($scorers) {
            foreach ($scorers as $scorer) {
                saveTopScorer($mysqli, $scorer);
                $totalScorers++;
            }
        }
    }
    echo "   ✓ Gespeichert: {$totalScorers} Top Scorers\n";

    echo "\n✅ {$club['name']} erfolgreich synchronisiert!\n";
    echo str_repeat('=', 70) . "\n\n";
}

$mysqli->close();
echo "✅ COMET Sync abgeschlossen!\n\n";

/**
 * API Call Helper
 */
function apiCall($url, $username, $password) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return null;
    }

    return json_decode($response, true);
}

/**
 * Speichere Match
 */
function saveMatch($mysqli, $match) {
    $matchId = (int)$match['matchFifaId'];
    $compId = (int)($match['competitionFifaId'] ?? 0);
    $homeId = (int)($match['homeClubFifaId'] ?? 0);
    $awayId = (int)($match['awayClubFifaId'] ?? 0);
    $homeGoals = (int)($match['homeGoals'] ?? 0);
    $awayGoals = (int)($match['awayGoals'] ?? 0);
    $matchDate = $mysqli->real_escape_string($match['matchDate'] ?? date('Y-m-d'));
    $status = $mysqli->real_escape_string($match['status'] ?? 'SCHEDULED');
    $homeName = $mysqli->real_escape_string($match['homeClubName'] ?? '');
    $awayName = $mysqli->real_escape_string($match['awayClubName'] ?? '');

    $sql = "INSERT INTO comet_matches
            (match_fifa_id, competition_fifa_id, home_club_fifa_id, away_club_fifa_id,
             home_club_name, away_club_name, home_goals, away_goals, match_date, status, created_at, updated_at)
            VALUES
            ({$matchId}, {$compId}, {$homeId}, {$awayId}, '{$homeName}', '{$awayName}',
             {$homeGoals}, {$awayGoals}, '{$matchDate}', '{$status}', NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            home_goals = VALUES(home_goals),
            away_goals = VALUES(away_goals),
            status = VALUES(status),
            updated_at = NOW()";

    $mysqli->query($sql);
}

/**
 * Speichere Ranking
 */
function saveRanking($mysqli, $ranking) {
    $compId = (int)$ranking['competitionFifaId'];
    $clubId = (int)$ranking['clubFifaId'];
    $position = (int)($ranking['position'] ?? 0);
    $played = (int)($ranking['played'] ?? 0);
    $won = (int)($ranking['won'] ?? 0);
    $drawn = (int)($ranking['drawn'] ?? 0);
    $lost = (int)($ranking['lost'] ?? 0);
    $goalsFor = (int)($ranking['goalsFor'] ?? 0);
    $goalsAgainst = (int)($ranking['goalsAgainst'] ?? 0);
    $goalDiff = (int)($ranking['goalDifference'] ?? 0);
    $points = (int)($ranking['points'] ?? 0);
    $clubName = $mysqli->real_escape_string($ranking['clubName'] ?? '');

    $sql = "INSERT INTO comet_rankings
            (competition_fifa_id, club_fifa_id, club_name, position, played, won, drawn, lost,
             goals_for, goals_against, goal_difference, points, created_at, updated_at)
            VALUES
            ({$compId}, {$clubId}, '{$clubName}', {$position}, {$played}, {$won}, {$drawn}, {$lost},
             {$goalsFor}, {$goalsAgainst}, {$goalDiff}, {$points}, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            position = VALUES(position),
            played = VALUES(played),
            won = VALUES(won),
            drawn = VALUES(drawn),
            lost = VALUES(lost),
            goals_for = VALUES(goals_for),
            goals_against = VALUES(goals_against),
            goal_difference = VALUES(goal_difference),
            points = VALUES(points),
            updated_at = NOW()";

    $mysqli->query($sql);
}

/**
 * Speichere Top Scorer
 */
function saveTopScorer($mysqli, $scorer) {
    $compId = (int)$scorer['competitionFifaId'];
    $clubId = (int)($scorer['clubFifaId'] ?? 0);
    $playerName = $mysqli->real_escape_string($scorer['playerName'] ?? '');
    $goals = (int)($scorer['goals'] ?? 0);
    $position = (int)($scorer['position'] ?? 0);

    $sql = "INSERT INTO comet_top_scorers
            (competition_fifa_id, club_fifa_id, player_name, goals, position, created_at, updated_at)
            VALUES
            ({$compId}, {$clubId}, '{$playerName}', {$goals}, {$position}, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            goals = VALUES(goals),
            position = VALUES(position),
            updated_at = NOW()";

    $mysqli->query($sql);
}
