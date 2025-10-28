<?php

echo "\n" . str_repeat("═", 80) . "\n";
echo "🔐 COMET API - DETAILLIERTE LOGIN-DEBUG\n";
echo str_repeat("═", 80) . "\n\n";

$baseUrl = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

echo "【Credentials】\n";
echo "  Username: " . $username . "\n";
echo "  Password: " . $password . "\n";
echo "  Password (escaped): " . json_encode($password) . "\n\n";

$loginUrl = $baseUrl . '/api/login';
$loginData = json_encode([
    'username' => $username,
    'password' => $password
]);

echo "【Request Details】\n";
echo "  URL: " . $loginUrl . "\n";
echo "  Method: POST\n";
echo "  Content-Type: application/json\n";
echo "  Body: " . $loginData . "\n\n";

// Test with verbose cURL output
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capture verbose output
$verbose = fopen('php://temp', 'r+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

echo "【Sending Request...】\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);

echo "【Response】\n";
echo "  HTTP Code: " . $httpCode . "\n";
echo "  Response: " . substr($response, 0, 200) . "...\n\n";

if ($curlError) {
    echo "【cURL Error】\n";
    echo "  " . $curlError . "\n\n";
}

// Try with different formats
echo "【Versuche verschiedene Request-Formate】\n\n";

$formats = [
    'JSON' => [
        'data' => json_encode(['username' => $username, 'password' => $password]),
        'headers' => ['Content-Type: application/json']
    ],
    'Form Data' => [
        'data' => http_build_query(['username' => $username, 'password' => $password]),
        'headers' => ['Content-Type: application/x-www-form-urlencoded']
    ],
    'Basic Auth' => [
        'data' => null,
        'headers' => ['Authorization: Basic ' . base64_encode($username . ':' . $password)]
    ]
];

foreach ($formats as $format => $config) {
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_POST, true);

    if ($config['data']) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $config['data']);
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $config['headers']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "  $format: HTTP $httpCode";

    if ($httpCode === 200) {
        echo " ✅\n";
        $result = json_decode($response, true);
        if (isset($result['token'])) {
            echo "    Token: " . substr($result['token'], 0, 30) . "...\n";
        }
    } else {
        echo " ❌\n";
    }
}

curl_close($ch);

?>
