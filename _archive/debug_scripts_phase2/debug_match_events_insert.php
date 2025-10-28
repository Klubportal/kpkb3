<?php

// Debug: Test Einfügung mit Fehlerausgabe

$pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$apiBase = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Get first played match
$result = $pdo->query("
    SELECT match_fifa_id, competition_fifa_id FROM matches
    WHERE match_status = 'PLAYED'
    ORDER BY id LIMIT 1
");

$match = $result->fetch(PDO::FETCH_ASSOC);
$matchId = $match['match_fifa_id'];
$compId = $match['competition_fifa_id'];

echo "🧪 Debug: Teste Einfügung für Match " . $matchId . "\n";
echo str_repeat("─", 80) . "\n\n";

$url = $apiBase . '/api/export/comet/match/' . $matchId . '/events';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

echo "✅ " . count($data) . " Events abgerufen\n\n";

$insertStmt = $pdo->prepare("
    INSERT INTO match_events (
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

$inserted = 0;
$skipped = 0;

foreach ($data as $idx => $event) {
    echo "📝 Event " . ($idx + 1) . ": ";

    try {
        $eventTypeRaw = isset($event['eventType']) ? strtoupper($event['eventType']) : '';

        $eventType = null;
        if ($eventTypeRaw === 'GOAL') {
            $eventType = 'Goal';
        } elseif ($eventTypeRaw === 'YELLOW') {
            $eventType = 'Yellow';
        } elseif ($eventTypeRaw === 'RED') {
            $eventType = 'Red';
        } elseif ($eventTypeRaw === 'SUBSTITUTION') {
            $eventType = 'Substitution';
        }

        if (!$eventType) {
            echo "❌ Skipped (Type: " . $eventTypeRaw . ")\n";
            $skipped++;
            continue;
        }

        $eventFifaId = isset($event['id']) ? (int)$event['id'] : null;

        if (!$eventFifaId) {
            echo "❌ Skipped (no ID)\n";
            $skipped++;
            continue;
        }

        $insertStmt->execute([
            ':match_event_fifa_id' => $eventFifaId,
            ':match_fifa_id' => $matchId,
            ':competition_fifa_id' => $compId,
            ':player_fifa_id' => isset($event['playerFifaId']) ? (int)$event['playerFifaId'] : null,
            ':player_name' => isset($event['personName']) ? $event['personName'] : null,
            ':shirt_number' => isset($event['shirtNumber']) ? (int)$event['shirtNumber'] : null,
            ':player_fifa_id_2' => isset($event['playerFifaId2']) ? (int)$event['playerFifaId2'] : null,
            ':player_name_2' => isset($event['personName2']) ? $event['personName2'] : null,
            ':team_fifa_id' => isset($event['teamOfficialFifaId']) ? (int)$event['teamOfficialFifaId'] : null,
            ':match_team' => isset($event['matchTeam']) ? $event['matchTeam'] : null,
            ':event_type' => $eventType,
            ':event_minute' => isset($event['minute']) ? (int)$event['minute'] : null,
            ':description' => null,
        ]);

        echo "✅ Eingefügt (" . $eventType . " @ " . (isset($event['minute']) ? $event['minute'] : '?') . "')\n";
        $inserted++;

    } catch (Exception $e) {
        echo "❌ Fehler: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("─", 80) . "\n";
echo "✅ Erfolgreich: " . $inserted . "\n";
echo "⏭️  Übersprungen: " . $skipped . "\n";

?>
