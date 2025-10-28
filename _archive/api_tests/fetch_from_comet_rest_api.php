<?php

echo "\n" . str_repeat("═", 80) . "\n";
echo "🔐 COMET REST API - DIREKTER DATENABRUF\n";
echo str_repeat("═", 80) . "\n\n";

// Load configuration - use constants directly
$baseUrl = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

echo "【Konfiguration geladen】\n";
echo "  Base URL: " . $baseUrl . "\n";
echo "  Username: " . $username . "\n";
echo "  Password: " . substr($password, 0, 3) . "***\n\n";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Step 1: Login
echo "【Schritt 1: LOGIN】\n";
echo str_repeat("─", 80) . "\n";

$loginUrl = $baseUrl . '/api/login';
$loginPayload = json_encode([
    'username' => $username,
    'password' => $password
]);

echo "POST: $loginUrl\n";
echo "Payload: " . $loginPayload . "\n\n";

curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpCode\n";
echo "Response: " . substr($response, 0, 300) . "...\n\n";

if ($httpCode !== 200) {
    echo "❌ Login fehlgeschlagen!\n";
    echo "Full Response: " . $response . "\n\n";

    // Try different authentication method
    echo "【Versuche alternative Auth-Methode: Basic Auth】\n";
    echo str_repeat("─", 80) . "\n";

    $basicAuth = base64_encode($username . ':' . $password);
    echo "Authorization: Basic " . substr($basicAuth, 0, 20) . "...\n\n";

    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/organisations/598');
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . $basicAuth,
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "HTTP Code: $httpCode\n";
    echo "Response: " . substr($response, 0, 500) . "\n\n";

    curl_close($ch);
    exit(1);
}

$loginResult = json_decode($response, true);

if (!isset($loginResult['token']) && !isset($loginResult['data']['token'])) {
    echo "❌ Kein Token in Response\n";
    echo "Response: " . json_encode($loginResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    curl_close($ch);
    exit(1);
}

// Extract token - try different response formats
$token = $loginResult['token'] ?? $loginResult['data']['token'] ?? null;

if (!$token) {
    echo "❌ Token konnte nicht extrahiert werden\n";
    print_r($loginResult);
    curl_close($ch);
    exit(1);
}

echo "✅ LOGIN ERFOLGREICH!\n";
echo "Token: " . substr($token, 0, 30) . "...\n\n";

// Step 2: Get Competitions
echo "【Schritt 2: WETTBEWERBE ABRUFEN】\n";
echo str_repeat("─", 80) . "\n";

// Try different endpoints for competitions
$competitionEndpoints = [
    '/api/organisations/598/competitions',
    '/api/v1/organisations/598/competitions',
    '/api/competitions?organisation_id=598',
    '/api/organisations/598',
];

$competitionsData = null;

foreach ($competitionEndpoints as $endpoint) {
    $url = $baseUrl . $endpoint;
    echo "GET: $url\n";

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "HTTP Code: $httpCode\n";

    if ($httpCode === 200) {
        $competitionsData = json_decode($response, true);
        echo "✅ Erfolgreich!\n\n";
        break;
    } else {
        echo "Response: " . substr($response, 0, 100) . "...\n\n";
    }
}

if (!$competitionsData) {
    echo "❌ Wettbewerbe konnten nicht abgerufen werden\n";
    curl_close($ch);
    exit(1);
}

// Display competitions
echo "【Gefundene Wettbewerbe】\n";
echo str_repeat("─", 80) . "\n";

$competitions = $competitionsData['data'] ?? $competitionsData['competitions'] ?? $competitionsData;

if (is_array($competitions)) {
    echo "Anzahl: " . count($competitions) . "\n\n";

    foreach ($competitions as $idx => $comp) {
        $name = $comp['name'] ?? $comp['international_competition_name'] ?? 'Unknown';
        $id = $comp['id'] ?? $comp['competition_fifa_id'] ?? 'N/A';
        $season = $comp['season'] ?? 'N/A';
        $status = $comp['status'] ?? 'N/A';

        echo sprintf(
            "%2d) %-60s (ID: %s, Season: %s, Status: %s)\n",
            $idx + 1,
            substr($name, 0, 60),
            $id,
            $season,
            $status
        );
    }
} else {
    echo "Response format nicht erkannt:\n";
    echo json_encode($competitions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

curl_close($ch);

echo "\n" . str_repeat("═", 80) . "\n";
echo "✅ API-Abfrage abgeschlossen!\n";
echo str_repeat("═", 80) . "\n\n";

?>
