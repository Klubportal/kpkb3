<?php

echo "DEBUGGING: Checking API response structure\n";
echo str_repeat("═", 80) . "\n\n";

$baseUrl = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Get first competition matches
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => $username . ':' . $password,
    CURLOPT_URL => $baseUrl . '/api/export/comet/competition/100629221/matches?teamFifaId=598',
    CURLOPT_HTTPGET => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode === 200) {
    $matches = json_decode($response, true);

    if (is_array($matches) && count($matches) > 0) {
        echo "First match structure:\n";
        echo str_repeat("─", 80) . "\n";

        $firstMatch = $matches[0];
        echo json_encode($firstMatch, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo "API Error: HTTP $httpCode\n";
}

curl_close($ch);
?>
