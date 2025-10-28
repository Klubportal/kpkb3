<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "⚽ SYNCHRONISIERE MATCH EVENTS AUS COMET API\n";
echo "   (Alle Matches von allen Wettbewerben - nicht nur Club 598)\n";
echo str_repeat("═", 100) . "\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=kpkb3', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "【1】 Lade alle Matches\n";
    echo str_repeat("─", 100) . "\n\n";

    $result = $pdo->query("
        SELECT id, match_fifa_id, competition_fifa_id
        FROM comet_matches
        ORDER BY id
    ");

    $matches = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ " . count($matches) . " Matches geladen\n\n";

    // API Configuration
    $apiBase = 'https://api-hns.analyticom.de';
    $username = 'nkprigorje';
    $password = '3c6nR$dS';

    echo "【2】 Abrufen von Match Events pro Spiel\n";
    echo str_repeat("─", 100) . "\n\n";

    $totalEventsInserted = 0;
    $totalEventsFetched = 0;
    $skippedMatches = 0;
    $errors = [];

    // Prepare statement
    $insertStmt = $pdo->prepare("
        INSERT INTO comet_match_events (
            match_event_fifa_id,
            match_fifa_id,
            competition_fifa_id,
            player_fifa_id,
            player_name,
            shirt_number,
            player_fifa_id_2,
            player_name_2,
            team_fifa_id,
            match_team,
            event_type,
            event_minute,
            description,
            created_at,
            updated_at
        ) VALUES (
            :match_event_fifa_id,
            :match_fifa_id,
            :competition_fifa_id,
            :player_fifa_id,
            :player_name,
            :shirt_number,
            :player_fifa_id_2,
            :player_name_2,
            :team_fifa_id,
            :match_team,
            :event_type,
            :event_minute,
            :description,
            NOW(),
            NOW()
        )
    ");

    foreach ($matches as $idx => $match) {
        $matchId = $match['match_fifa_id'];
        $compId = $match['competition_fifa_id'];

        // Show progress every 100 matches
        if (($idx + 1) % 100 === 0) {
            echo "  📊 Verarbeitet: " . ($idx + 1) . "/" . count($matches) . " Matches\n";
        }

        try {
            // Fetch events from API
            $url = $apiBase . '/api/export/comet/match/' . $matchId . '/events';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 404) {
                // No events for this match
                $skippedMatches++;
                continue;
            }

            if ($httpCode !== 200) {
                continue;
            }

            $data = json_decode($response, true);

            if (!is_array($data)) {
                continue;
            }

            // Handle response format
            $eventsArray = [];
            if (isset($data['events']) && is_array($data['events'])) {
                $eventsArray = $data['events'];
            } elseif (is_array($data)) {
                $eventsArray = $data;
            }

            $totalEventsFetched += count($eventsArray);

            // Insert events
            foreach ($eventsArray as $event) {
                try {
                    // Map event types
                    $eventType = null;
                    $eventTypeRaw = isset($event['eventType']) ? strtoupper($event['eventType']) : '';

                    // Valid event types: Goal, Substitution, Yellow, Red, Penalty, Own Goal, Var, Offside Pass
                    if ($eventTypeRaw === 'GOAL') {
                        $eventType = 'Goal';
                    } elseif ($eventTypeRaw === 'YELLOW') {
                        $eventType = 'Yellow';
                    } elseif ($eventTypeRaw === 'RED') {
                        $eventType = 'Red';
                    } elseif ($eventTypeRaw === 'SUBSTITUTION') {
                        $eventType = 'Substitution';
                    } elseif ($eventTypeRaw === 'PENALTY') {
                        $eventType = 'Penalty';
                    } elseif ($eventTypeRaw === 'OWN GOAL') {
                        $eventType = 'Own Goal';
                    } elseif ($eventTypeRaw === 'VAR') {
                        $eventType = 'Var';
                    } elseif ($eventTypeRaw === 'OFFSIDE') {
                        $eventType = 'Offside Pass';
                    }

                    if (!$eventType) {
                        continue;
                    }

                    // Extract player info from flat structure
                    $playerName = isset($event['personName']) ? $event['personName'] : null;
                    $playerFifaId = isset($event['playerFifaId']) ? (int)$event['playerFifaId'] : null;
                    $shirtNumber = isset($event['shirtNumber']) ? (int)$event['shirtNumber'] : null;

                    // Second player (for substitutions)
                    $playerName2 = isset($event['personName2']) ? $event['personName2'] : null;
                    $playerFifaId2 = isset($event['playerFifaId2']) ? (int)$event['playerFifaId2'] : null;

                    $teamFifaId = isset($event['teamOfficialFifaId']) ? (int)$event['teamOfficialFifaId'] : null;
                    $matchTeam = isset($event['matchTeam']) ? $event['matchTeam'] : null;
                    $eventMinute = isset($event['minute']) ? (int)$event['minute'] : null;
                    $eventFifaId = isset($event['id']) ? (int)$event['id'] : null;
                    $description = null;

                    if (!$eventFifaId) {
                        continue;
                    }

                    $insertStmt->execute([
                        ':match_event_fifa_id' => $eventFifaId,
                        ':match_fifa_id' => $matchId,
                        ':competition_fifa_id' => $compId,
                        ':player_fifa_id' => $playerFifaId,
                        ':player_name' => $playerName,
                        ':shirt_number' => $shirtNumber,
                        ':player_fifa_id_2' => $playerFifaId2,
                        ':player_name_2' => $playerName2,
                        ':team_fifa_id' => $teamFifaId,
                        ':match_team' => $matchTeam,
                        ':event_type' => $eventType,
                        ':event_minute' => $eventMinute,
                        ':description' => $description,
                    ]);

                    $totalEventsInserted++;

                } catch (Exception $e) {
                    // Skip individual events on error
                }
            }

        } catch (Exception $e) {
            // Continue with next match instead of stopping
            $errors[] = "Match " . $matchId . ": " . $e->getMessage();
        }
    }

    echo "\n" . str_repeat("─", 100) . "\n";
    echo "【3】 Verifikation\n";
    echo str_repeat("─", 100) . "\n\n";

    $result = $pdo->query("SELECT COUNT(*) as total FROM comet_match_events");
    $totalInDb = $result->fetch(PDO::FETCH_ASSOC)['total'];

    echo "✅ Gesamt abgerufen: " . $totalEventsFetched . " Events\n";
    echo "✅ Erfolgreich eingefügt: " . $totalEventsInserted . " Events\n";
    echo "⏭️  Übersprungen (keine Events): " . $skippedMatches . " Matches\n";
    echo "✅ Insgesamt in Datenbank: " . $totalInDb . " Events\n\n";

    // Show stats by event type
    $result = $pdo->query("
        SELECT event_type, COUNT(*) as count
    FROM comet_match_events
        GROUP BY event_type
        ORDER BY count DESC
    ");

    echo "📊 Events nach Typ:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("   %20s: %5d\n", $row['event_type'], $row['count']);
    }

    echo "\n" . str_repeat("═", 100) . "\n";
    echo "✅ FERTIG! Match Events synchronisiert\n";
    echo str_repeat("═", 100) . "\n\n";

} catch (PDOException $e) {
    echo "❌ Datenbankfehler: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n";
    exit(1);
}

?>
