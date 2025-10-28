<?php

echo "\n" . str_repeat("═", 80) . "\n";
echo "🔍 COMET API - ENDPOINT DISCOVERY\n";
echo str_repeat("═", 80) . "\n\n";

$baseUrl = 'https://api-hns.analyticom.de';

// Common API endpoints to try
$endpoints = [
    '/authentication/login',
    '/auth/login',
    '/login',
    '/api/login',
    '/api/v1/login',
    '/api/authentication/login',
    '/rest/login',
    '/authenticate',
    '/api/authenticate',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

echo "Teste mögliche Login-Endpoints:\n\n";

foreach ($endpoints as $endpoint) {
    $url = $baseUrl . $endpoint;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => 'test', 'password' => 'test']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $status = ($httpCode === 404) ? '❌' : '✓';
    echo sprintf("  %s %-50s HTTP %d\n", $status, $endpoint, $httpCode);

    if ($httpCode !== 404 && $httpCode !== 0) {
        echo "     Response: " . substr($response, 0, 100) . "...\n";
    }
}

// Try to access root API
echo "\n\nVersuche Root-Endpoint:\n\n";

$rootEndpoints = [
    '/',
    '/api',
    '/api/',
    '/api/v1',
    '/rest',
];

foreach ($rootEndpoints as $endpoint) {
    $url = $baseUrl . $endpoint;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo sprintf("  GET %-50s HTTP %d\n", $endpoint, $httpCode);

    if ($httpCode !== 404 && $response) {
        echo "     Response: " . substr($response, 0, 150) . "...\n";
    }
}

curl_close($ch);

?>
