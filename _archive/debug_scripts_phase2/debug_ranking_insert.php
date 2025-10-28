<?php

// Debug: Abrufen der ersten Competition und Testen der Einfügung

$pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$apiBase = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Get first competition
$result = $pdo->query("SELECT id, comet_id, name FROM competitions LIMIT 1");
$comp = $result->fetch(PDO::FETCH_ASSOC);

echo "🧪 Debug: Teste mit " . $comp['name'] . "\n";
echo "───────────────────────────────────────────────────────\n\n";

$url = $apiBase . '/api/export/comet/competition/' . $comp['comet_id'] . '/ranking';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

echo "✅ API Response (HTTP " . $httpCode . ", " . count($data) . " items)\n\n";

if (count($data) > 0) {
    $firstItem = $data[0];
    echo "📌 Erstes Item:\n";
    echo json_encode($firstItem, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    echo "🔍 Extrahierte Werte:\n";
    $position = isset($firstItem['position']) ? (int)$firstItem['position'] : 0;
    $teamName = '';
    if (isset($firstItem['team']) && is_array($firstItem['team'])) {
        $teamName = isset($firstItem['team']['internationalName']) ? $firstItem['team']['internationalName'] : '';
    }
    $cometId = isset($firstItem['teamFifaId']) ? (string)$firstItem['teamFifaId'] : '';
    $matchesPlayed = isset($firstItem['matchesPlayed']) ? (int)$firstItem['matchesPlayed'] : 0;
    $wins = isset($firstItem['wins']) ? (int)$firstItem['wins'] : 0;
    $draws = isset($firstItem['draws']) ? (int)$firstItem['draws'] : 0;
    $losses = isset($firstItem['losses']) ? (int)$firstItem['losses'] : 0;
    $goalsFor = isset($firstItem['goalsFor']) ? (int)$firstItem['goalsFor'] : 0;
    $goalsAgainst = isset($firstItem['goalsAgainst']) ? (int)$firstItem['goalsAgainst'] : 0;
    $goalDifference = isset($firstItem['goalDifference']) ? (int)$firstItem['goalDifference'] : 0;
    $points = isset($firstItem['points']) ? (int)$firstItem['points'] : 0;

    echo "  Position: " . $position . "\n";
    echo "  Team: " . $teamName . "\n";
    echo "  Comet ID: " . $cometId . "\n";
    echo "  Matches: " . $matchesPlayed . "\n";
    echo "  W-D-L: " . $wins . "-" . $draws . "-" . $losses . "\n";
    echo "  Goals: " . $goalsFor . " - " . $goalsAgainst . " (" . $goalDifference . ")\n";
    echo "  Points: " . $points . "\n\n";

    // Try to insert
    echo "📝 Versuche zu Einfügen...\n";

    try {
        $stmt = $pdo->prepare("
            INSERT INTO rankings (
                competition_id,
                comet_id,
                name,
                position,
                club_id,
                matches_played,
                wins,
                draws,
                losses,
                goals_for,
                goals_against,
                goal_difference,
                points,
                created_at,
                updated_at
            ) VALUES (
                :competition_id,
                :comet_id,
                :name,
                :position,
                :club_id,
                :matches_played,
                :wins,
                :draws,
                :losses,
                :goals_for,
                :goals_against,
                :goal_difference,
                :points,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            ':competition_id' => $comp['id'],
            ':comet_id' => $cometId,
            ':name' => $teamName,
            ':position' => $position,
            ':club_id' => null,
            ':matches_played' => $matchesPlayed,
            ':wins' => $wins,
            ':draws' => $draws,
            ':losses' => $losses,
            ':goals_for' => $goalsFor,
            ':goals_against' => $goalsAgainst,
            ':goal_difference' => $goalDifference,
            ':points' => $points,
        ]);

        echo "✅ Erfolgreich eingefügt!\n";

    } catch (Exception $e) {
        echo "❌ Fehler: " . $e->getMessage() . "\n";
    }
}

?>
