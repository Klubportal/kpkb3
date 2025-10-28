<?php

// Debug: Test ein Match und seine Events

$pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');

$apiBase = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Get first played match
$result = $pdo->query("
    SELECT match_fifa_id FROM matches
    WHERE match_status = 'PLAYED'
    ORDER BY id LIMIT 1
");

$match = $result->fetch(PDO::FETCH_ASSOC);
$matchId = $match['match_fifa_id'];

echo "🧪 Debug: Abrufen Events für Match " . $matchId . "\n";
echo str_repeat("─", 80) . "\n\n";

$url = $apiBase . '/api/export/comet/match/' . $matchId . '/events';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "✅ API Response: HTTP " . $httpCode . "\n\n";

$data = json_decode($response, true);

echo "📌 Raw Response (erste 2000 chars):\n";
echo substr(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 0, 2000) . "...\n\n";

if (is_array($data) && count($data) > 0) {
    $firstEvent = $data[0];
    echo "🔍 Erste Event Struktur:\n";
    echo json_encode($firstEvent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

?>
