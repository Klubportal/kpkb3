<?php

echo "\n" . str_repeat("═", 80) . "\n";
echo "🔐 COMET REST API - SESSION-BASIERTE AUTHENTIFIZIERUNG\n";
echo str_repeat("═", 80) . "\n\n";

$baseUrl = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Create cookie jar
$cookieJar = tempnam(sys_get_temp_dir(), 'cookies');

echo "【Cookie Jar: " . $cookieJar . "】\n\n";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Try different login endpoints
$loginEndpoints = [
    '/j_security_check' => ['j_username' => $username, 'j_password' => $password],
    '/login' => ['username' => $username, 'password' => $password],
    '/api/login' => ['username' => $username, 'password' => $password],
];

echo "【Versuche verschiedene Login-Endpoints】\n";
echo str_repeat("─", 80) . "\n";

foreach ($loginEndpoints as $endpoint => $postData) {
    echo "\n▶ Endpoint: $endpoint\n";

    $url = $baseUrl . $endpoint;
    $payload = http_build_query($postData);

    echo "  URL: $url\n";
    echo "  Data: $payload\n";

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    echo "  HTTP: $httpCode\n";

    if ($curlError) {
        echo "  Error: $curlError\n";
    } else {
        echo "  Response: " . substr($response, 0, 100) . "...\n";

        // Try to access API with session
        if ($httpCode === 200 || $httpCode === 302) {
            echo "\n  ✓ Versuche API-Zugriff mit Session...\n";

            curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/organisations/598');
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

            $apiResponse = curl_exec($ch);
            $apiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            echo "  API HTTP: $apiHttpCode\n";

            if ($apiHttpCode === 200) {
                echo "  ✅ ERFOLG! Session funktioniert!\n";

                $data = json_decode($apiResponse, true);
                echo "\n【API Response】\n";
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

                curl_close($ch);
                unlink($cookieJar);
                exit(0);
            }
        }
    }
}

curl_close($ch);
unlink($cookieJar);

echo "\n\n❌ KEINE Login-Methode funktioniert.\n";
echo "\n📌 EMPFEHLUNG:\n";
echo "   Die REST API der Comet scheint derzeit nicht erreichbar zu sein.\n";
echo "   Wir haben aber bereits echte, authentifizierte Daten in kp_server.matches\n";
echo "   Die 10 Wettbewerbe wurden bereits erfolgreich in kp_club_management eingefügt.\n";
echo "\n";

?>
