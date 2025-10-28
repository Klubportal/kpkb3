<?php

// Direktes MySQL-Script ohne Laravel Bootstrap
echo "🏟️  SYNC COMET MATCH EVENTS - STANDALONE\n";
echo str_repeat("=", 60) . "\n\n";

// MySQL Verbindung
$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// API Settings
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Hole Competition IDs aus DB
$compResult = $mysqli->query("SELECT comet_id FROM comet_competitions");
$competitionIds = [];
while ($row = $compResult->fetch_assoc()) {
    $competitionIds[] = $row['comet_id'];
}

echo "📋 Competitions in DB: " . count($competitionIds) . "\n";

// Datum Filter bis morgen
$tomorrow = date('Y-m-d 23:59:59', strtotime('tomorrow'));
echo "📅 Syncen bis: $tomorrow\n\n";

// Hole alle played Matches
$matchesQuery = "SELECT match_fifa_id, competition_fifa_id, team_fifa_id_home, team_fifa_id_away
                 FROM comet_matches
                 WHERE match_status = 'played'
                 AND competition_fifa_id IN (" . implode(',', $competitionIds) . ")
                 AND date_time_local <= '$tomorrow'
                 ORDER BY match_fifa_id";

$matchesResult = $mysqli->query($matchesQuery);
$totalMatches = $matchesResult->num_rows;

echo "📊 Gefundene Matches: $totalMatches\n\n";

$totalEvents = 0;
$newEvents = 0;
$errorCount = 0;

while ($match = $matchesResult->fetch_assoc()) {
    $matchId = $match['match_fifa_id'];
    echo "⚽ Match {$matchId}\n";

    // API Call
    $ch = curl_init("{$apiUrl}/match/{$matchId}/events");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200 || !$response) {
        echo "   ❌ API Fehler: HTTP $httpCode\n";
        $errorCount++;
        continue;
    }

    $events = json_decode($response, true);

    if (empty($events)) {
        echo "   ℹ️  Keine Events\n";
        continue;
    }

    echo "   📥 API Events: " . count($events) . "\n";

    foreach ($events as $event) {
        $totalEvents++;

        // Team FIFA ID bestimmen
        $matchTeam = $event['matchTeam'] ?? null;
        $teamFifaId = null;

        if ($matchTeam === 'HOME') {
            $teamFifaId = $match['team_fifa_id_home'];
        } elseif ($matchTeam === 'AWAY') {
            $teamFifaId = $match['team_fifa_id_away'];
        }

        // Event Type mapping
        $eventTypeMap = [
            'GOAL' => 'goal',
            'PENALTY' => 'penalty_goal',
            'OWN_GOAL' => 'own_goal',
            'YELLOW' => 'yellow_card',
            'RED' => 'red_card',
            'YELLOW_RED' => 'yellow_red_card',
            'SUBSTITUTION' => 'substitution',
            'PENALTY_MISSED' => 'penalty_missed',
        ];

        $apiEventType = strtoupper($event['eventType'] ?? 'GOAL');
        $eventType = $eventTypeMap[$apiEventType] ?? 'goal';

        // Daten vorbereiten
        $eventData = [
            'match_event_fifa_id' => $event['id'] ?? null,
            'match_fifa_id' => $matchId,
            'competition_fifa_id' => $match['competition_fifa_id'],
            'player_fifa_id' => $event['playerFifaId'] ?? null,
            'player_name' => $mysqli->real_escape_string($event['personName'] ?? $event['localPersonName'] ?? ''),
            'shirt_number' => $event['shirtNumber'] ?? null,
            'player_fifa_id_2' => $event['playerFifaId2'] ?? null,
            'player_name_2' => $mysqli->real_escape_string($event['personName2'] ?? $event['localPersonName2'] ?? ''),
            'team_fifa_id' => $teamFifaId,
            'match_team' => $matchTeam,
            'event_type' => $eventType,
            'event_minute' => $event['minute'] ?? 0,
            'description' => $mysqli->real_escape_string($event['eventDetailType'] ?? ''),
        ];

        if (!$eventData['match_event_fifa_id']) {
            continue;
        }

        // Insert or Update
        $sql = "INSERT INTO comet_match_events (
            match_event_fifa_id, match_fifa_id, competition_fifa_id,
            player_fifa_id, player_name, shirt_number,
            player_fifa_id_2, player_name_2, team_fifa_id,
            match_team, event_type, event_minute, description,
            created_at, updated_at
        ) VALUES (
            {$eventData['match_event_fifa_id']},
            {$eventData['match_fifa_id']},
            {$eventData['competition_fifa_id']},
            " . ($eventData['player_fifa_id'] ? $eventData['player_fifa_id'] : 'NULL') . ",
            " . ($eventData['player_name'] ? "'{$eventData['player_name']}'" : 'NULL') . ",
            " . ($eventData['shirt_number'] ? $eventData['shirt_number'] : 'NULL') . ",
            " . ($eventData['player_fifa_id_2'] ? $eventData['player_fifa_id_2'] : 'NULL') . ",
            " . ($eventData['player_name_2'] ? "'{$eventData['player_name_2']}'" : 'NULL') . ",
            " . ($eventData['team_fifa_id'] ? $eventData['team_fifa_id'] : 'NULL') . ",
            " . ($eventData['match_team'] ? "'{$eventData['match_team']}'" : 'NULL') . ",
            '{$eventData['event_type']}',
            {$eventData['event_minute']},
            " . ($eventData['description'] ? "'{$eventData['description']}'" : 'NULL') . ",
            NOW(), NOW()
        ) ON DUPLICATE KEY UPDATE
            player_fifa_id = VALUES(player_fifa_id),
            player_name = VALUES(player_name),
            team_fifa_id = VALUES(team_fifa_id),
            updated_at = NOW()";

        if ($mysqli->query($sql)) {
            $newEvents++;
        } else {
            $errorCount++;
        }
    }

    echo "   ✅ Events verarbeitet\n";
    usleep(100000); // 100ms Pause
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches geprüft: {$totalMatches}\n";
echo "   - Total API Events: {$totalEvents}\n";
echo "   - Events gespeichert: {$newEvents}\n";
echo "   - Fehler: {$errorCount}\n";

$mysqli->close();
