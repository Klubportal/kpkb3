<?php

// SYNC MATCH PLAYERS - NK Prigorje (Team 598)
echo "⚽ SYNC MATCH PLAYERS - NK PRIGORJE (Team 598)\n";
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
$teamFifaId = 598;

// Hole alle Matches von NK Prigorje
$matchesQuery = "SELECT match_fifa_id FROM comet_matches
                 WHERE team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId}
                 ORDER BY match_fifa_id DESC";
$matchesResult = $mysqli->query($matchesQuery);

if (!$matchesResult) {
    die("Query Error: " . $mysqli->error . "\n");
}

$matches = [];
while ($row = $matchesResult->fetch_assoc()) {
    $matches[] = $row['match_fifa_id'];
}

echo "📊 Gefundene Matches: " . count($matches) . "\n\n";

$totalProcessed = 0;
$totalPlayers = 0;
$totalInserted = 0;
$totalUpdated = 0;
$errors = 0;

// Lösche alte Match Players von Team 598
echo "🗑️  Lösche alte Match Players...\n";
$mysqli->query("DELETE FROM comet_match_players WHERE team_fifa_id = {$teamFifaId}");
echo "✅ Alte Daten gelöscht\n\n";

// Sync Match Players für jedes Match
foreach ($matches as $matchFifaId) {
    echo "📥 Match {$matchFifaId}...";

    // API Call
    $ch = curl_init("{$apiUrl}/match/{$matchFifaId}/players");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        echo " ❌ HTTP {$httpCode}\n";
        $errors++;
        continue;
    }

    $data = json_decode($response, true);
    if (empty($data)) {
        echo " ⚠️  Keine Daten\n";
        continue;
    }

    $playersInMatch = 0;

    // Beide Teams durchgehen
    foreach ($data as $teamData) {
        $teamFifaIdFromApi = $teamData['teamFifaId'] ?? null;
        $teamNature = $teamData['teamNature'] ?? null;
        $players = $teamData['players'] ?? [];

        // Nur Spieler vom Team 598 syncen
        if ($teamFifaIdFromApi != $teamFifaId) {
            continue;
        }

        foreach ($players as $playerData) {
            $personFifaId = $playerData['personFifaId'] ?? null;
            $shirtNumber = $playerData['shirtNumber'] ?? null;
            $captain = $playerData['captain'] ?? 0;
            $goalkeeper = $playerData['goalkeeper'] ?? 0;
            $startingLineup = $playerData['startingLineup'] ?? 0;
            $played = $playerData['played'] ?? 0;

            // Person Daten
            $person = $playerData['person'] ?? [];
            $firstName = $person['internationalFirstName'] ?? '';
            $lastName = $person['internationalLastName'] ?? '';
            $personName = trim($firstName . ' ' . $lastName);

            // Local Name
            $localPersonName = '';
            if (isset($person['localPersonNames'][0])) {
                $localFirst = $person['localPersonNames'][0]['firstName'] ?? '';
                $localLast = $person['localPersonNames'][0]['lastName'] ?? '';
                $localPersonName = trim($localFirst . ' ' . $localLast);
            }

            // Match Events zählen
            $matchEvents = $playerData['matchEvents'] ?? [];
            $goals = 0;
            $yellowCards = 0;
            $redCards = 0;

            foreach ($matchEvents as $event) {
                $eventType = strtoupper($event['eventType'] ?? '');
                if ($eventType == 'GOAL' || $eventType == 'PENALTY_GOAL') {
                    $goals++;
                } elseif ($eventType == 'YELLOW_CARD') {
                    $yellowCards++;
                } elseif ($eventType == 'RED_CARD' || $eventType == 'YELLOW_RED_CARD') {
                    $redCards++;
                }
            }

            if (!$personFifaId) {
                continue;
            }

            // Insert or Update
            $stmt = $mysqli->prepare("
                INSERT INTO comet_match_players (
                    match_fifa_id, team_fifa_id, person_fifa_id, person_name, local_person_name,
                    shirt_number, team_nature, captain, goalkeeper, starting_lineup, played,
                    goals, yellow_cards, red_cards, last_synced_at, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    person_name = VALUES(person_name),
                    local_person_name = VALUES(local_person_name),
                    shirt_number = VALUES(shirt_number),
                    team_nature = VALUES(team_nature),
                    captain = VALUES(captain),
                    goalkeeper = VALUES(goalkeeper),
                    starting_lineup = VALUES(starting_lineup),
                    played = VALUES(played),
                    goals = VALUES(goals),
                    yellow_cards = VALUES(yellow_cards),
                    red_cards = VALUES(red_cards),
                    last_synced_at = NOW(),
                    updated_at = NOW()
            ");

            $stmt->bind_param(
                "iiisssissiiiii",
                $matchFifaId, $teamFifaId, $personFifaId, $personName, $localPersonName,
                $shirtNumber, $teamNature, $captain, $goalkeeper, $startingLineup, $played,
                $goals, $yellowCards, $redCards
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows == 1) {
                    $totalInserted++;
                } elseif ($stmt->affected_rows == 2) {
                    $totalUpdated++;
                }
                $playersInMatch++;
            } else {
                $errors++;
            }

            $stmt->close();
        }
    }

    echo " ✅ {$playersInMatch} Spieler\n";
    $totalPlayers += $playersInMatch;
    $totalProcessed++;

    // Rate limiting
    usleep(100000); // 100ms pause
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ MATCH PLAYERS SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Spieler-Einträge: {$totalPlayers}\n";
echo "   - Neu eingefügt: {$totalInserted}\n";
echo "   - Aktualisiert: {$totalUpdated}\n";
echo "   - Fehler: {$errors}\n\n";

// Aktualisiere Shirt Numbers in comet_players
echo "🔄 Aktualisiere Shirt Numbers in comet_players...\n";

$updateQuery = "
    UPDATE comet_players cp
    INNER JOIN (
        SELECT person_fifa_id, shirt_number
        FROM comet_match_players
        WHERE team_fifa_id = {$teamFifaId}
          AND shirt_number IS NOT NULL
        GROUP BY person_fifa_id
        ORDER BY match_fifa_id DESC
    ) mp ON cp.person_fifa_id = mp.person_fifa_id
    SET cp.shirt_number = mp.shirt_number
    WHERE cp.club_fifa_id = {$teamFifaId}
";

$mysqli->query($updateQuery);
$updated = $mysqli->affected_rows;
echo "✅ {$updated} Spieler Shirt Numbers aktualisiert\n\n";

// Zeige Top Spieler mit Shirt Numbers
echo "⭐ Spieler mit Shirt Numbers:\n";
echo str_repeat("-", 60) . "\n";

$topQuery = "SELECT name, shirt_number, total_matches, total_goals
             FROM comet_players
             WHERE club_fifa_id = {$teamFifaId} AND shirt_number IS NOT NULL
             ORDER BY shirt_number ASC
             LIMIT 20";
$result = $mysqli->query($topQuery);

while ($row = $result->fetch_assoc()) {
    echo sprintf("#%-3d | %-30s | %2d Spiele | %2d Tore\n",
        $row['shirt_number'], $row['name'], $row['total_matches'], $row['total_goals']
    );
}

$mysqli->close();
