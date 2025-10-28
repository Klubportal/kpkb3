<?php

// SYNC MATCH PLAYERS - ALLE TEAMS, ALLE MATCHES
// Lädt für jedes Match die Aufstellungen beider Teams und speichert sie in comet_match_players

echo "👥 SYNC MATCH PLAYERS - ALLE MATCHES\n";
echo str_repeat("=", 60) . "\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Optional: zuerst Tabelle bereinigen? Wir nutzen UPSERT, daher nicht nötig.

$matchesQuery = "
    SELECT match_fifa_id
    FROM comet_matches
    WHERE match_status = 'played'
    ORDER BY match_fifa_id DESC
";
$matchesResult = $mysqli->query($matchesQuery);
if (!$matchesResult) {
    die("Query Error: " . $mysqli->error . "\n");
}

$matches = [];
while ($row = $matchesResult->fetch_assoc()) {
    $matches[] = (int)$row['match_fifa_id'];
}

echo "📊 Gespielte Matches: " . count($matches) . "\n\n";

$totalProcessed = 0;
$totalInserted = 0;
$totalUpdated = 0;
$totalPlayers = 0;
$errors = 0;

foreach ($matches as $i => $matchFifaId) {
    if ($i > 0 && $i % 50 === 0) {
        echo "⏳ Progress: {$i}/" . count($matches) . " matches, inserted={$totalInserted}, updated={$totalUpdated}\n";
    }

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
        $totalProcessed++;
        continue;
    }

    $teamsData = json_decode($response, true);
    if (empty($teamsData) || !is_array($teamsData)) {
        echo " ⚠️  Keine Daten\n";
        $totalProcessed++;
        continue;
    }

    $playersInMatch = 0;

    foreach ($teamsData as $teamData) {
        $teamFifaId = $teamData['teamFifaId'] ?? null;
        $teamNature = $teamData['teamNature'] ?? null; // HOME/AWAY
        $players = $teamData['players'] ?? [];
        if (!$teamFifaId || empty($players)) {
            continue;
        }

        foreach ($players as $playerData) {
            $personFifaId = $playerData['personFifaId'] ?? null;
            if (!$personFifaId) continue;

            $shirtNumber = $playerData['shirtNumber'] ?? null;
            $captain = !empty($playerData['captain']) ? 1 : 0;
            $goalkeeper = !empty($playerData['goalkeeper']) ? 1 : 0;
            $startingLineup = !empty($playerData['startingLineup']) ? 1 : 0;
            $played = !empty($playerData['played']) ? 1 : 0;

            // Person names
            $person = $playerData['person'] ?? [];
            $firstName = $mysqli->real_escape_string($person['internationalFirstName'] ?? '');
            $lastName  = $mysqli->real_escape_string($person['internationalLastName'] ?? '');
            $personName = trim($firstName . ' ' . $lastName);
            $localPersonName = '';
            if (isset($person['localPersonNames'][0])) {
                $localFirst = $mysqli->real_escape_string($person['localPersonNames'][0]['firstName'] ?? '');
                $localLast  = $mysqli->real_escape_string($person['localPersonNames'][0]['lastName'] ?? '');
                $localPersonName = trim($localFirst . ' ' . $localLast);
            }

            // Count simple event stats from nested matchEvents if present
            $goals = 0; $yellowCards = 0; $redCards = 0;
            if (!empty($playerData['matchEvents'])) {
                foreach ($playerData['matchEvents'] as $ev) {
                    $t = strtoupper($ev['eventType'] ?? '');
                    if ($t === 'GOAL' || $t === 'PENALTY_GOAL') $goals++;
                    elseif ($t === 'YELLOW_CARD') $yellowCards++;
                    elseif ($t === 'RED_CARD' || $t === 'YELLOW_RED_CARD') $redCards++;
                }
            }

            $stmt = $mysqli->prepare("\n                INSERT INTO comet_match_players (\n                    match_fifa_id, team_fifa_id, person_fifa_id, person_name, local_person_name,\n                    shirt_number, team_nature, captain, goalkeeper, starting_lineup, played,\n                    goals, yellow_cards, red_cards, last_synced_at, created_at, updated_at\n                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())\n                ON DUPLICATE KEY UPDATE\n                    person_name = VALUES(person_name),\n                    local_person_name = VALUES(local_person_name),\n                    shirt_number = VALUES(shirt_number),\n                    team_nature = VALUES(team_nature),\n                    captain = VALUES(captain),\n                    goalkeeper = VALUES(goalkeeper),\n                    starting_lineup = VALUES(starting_lineup),\n                    played = VALUES(played),\n                    goals = VALUES(goals),\n                    yellow_cards = VALUES(yellow_cards),\n                    red_cards = VALUES(red_cards),\n                    last_synced_at = NOW(),\n                    updated_at = NOW()\n            ");

            if (!$stmt) {
                $errors++;
                continue;
            }

            $stmt->bind_param(
                "iiisssissiiiii",
                $matchFifaId,
                $teamFifaId,
                $personFifaId,
                $personName,
                $localPersonName,
                $shirtNumber,
                $teamNature,
                $captain,
                $goalkeeper,
                $startingLineup,
                $played,
                $goals,
                $yellowCards,
                $redCards
            );

            if ($stmt->execute()) {
                // affected_rows: 1 insert, 2 update in MySQL's odd semantics for ON DUPLICATE
                if ($stmt->affected_rows == 1) $totalInserted++;
                if ($stmt->affected_rows == 2) $totalUpdated++;
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

    usleep(100000); // 100ms
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ MATCH PLAYERS SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Spieler-Einträge: {$totalPlayers}\n";
echo "   - Neu eingefügt: {$totalInserted}\n";
echo "   - Aktualisiert: {$totalUpdated}\n";
echo "   - Fehler: {$errors}\n\n";

$mysqli->close();
