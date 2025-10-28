<?php

echo "\n" . str_repeat("═", 80) . "\n";
echo "🔐 COMET REST API - SESSION-BASIERTE AUTHENTIFIZIERUNG\n";
echo str_repeat("═", 80) . "\n\n";

// Load config
$config = require __DIR__ . '/../kp_server/config/kp_api.php';

$baseUrl = $config['settings']['baseUrl'];
$username = $config['settings']['username'];
$password = $config['settings']['password'];

echo "【1】 Konfiguration geladen\n";
echo str_repeat("─", 80) . "\n";
echo "Base URL: " . $baseUrl . "\n";
echo "Username: " . $username . "\n";
echo "Password: " . substr($password, 0, 3) . "***\n\n";

// Initialize persistent cURL session with cookie jar
$cookieJar = sys_get_temp_dir() . '/comet_api_cookies.txt';
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_COOKIEJAR => $cookieJar,      // Save cookies
    CURLOPT_COOKIEFILE => $cookieJar,     // Reuse cookies
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 5,
]);

echo "【2】 Erste Anfrage (um Cookies zu erhalten)\n";
echo str_repeat("─", 80) . "\n";

// First, GET the login page to get initial cookies/CSRF tokens
$homeUrl = $baseUrl . '/';
curl_setopt($ch, CURLOPT_URL, $homeUrl);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

echo "GET: $homeUrl\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP: $httpCode\n\n";

// Try login with various methods
echo "【3】 Login-Versuche\n";
echo str_repeat("─", 80) . "\n\n";

// Method 1: Form-based login (most common for web apps)
echo "Method 1: Form-basiertes Login\n";
$loginUrl = $baseUrl . '/j_spring_security_check';
$loginData = http_build_query([
    'j_username' => $username,
    'j_password' => $password,
]);

curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: text/html,application/xhtml+xml',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

echo "  POST: $loginUrl\n";
echo "  Data: j_username=$username&j_password=***\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

echo "  Response HTTP: $httpCode\n";
echo "  Effective URL: $effectiveUrl\n\n";

if ($httpCode === 200 || stripos($effectiveUrl, 'login') === false) {
    echo "  ✅ Login erfolgreich!\n\n";
    $loggedIn = true;
} else {
    echo "  ⚠️  Möglicherweise nicht erfolgreich\n\n";
    $loggedIn = false;
}

// Method 2: Try API endpoint directly with Basic Auth
echo "Method 2: API-Endpunkt mit Session\n";

// Now try to access API with established session
$apiUrl = $baseUrl . '/api/organisations/598/competitions';

curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
]);

echo "  GET: $apiUrl\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "  Response HTTP: $httpCode\n";

if ($httpCode === 200) {
    echo "  ✅ Daten erhalten!\n\n";

    $data = json_decode($response, true);

    if (isset($data['data'])) {
        echo "【4】 Wettbewerbe abgerufen\n";
        echo str_repeat("─", 80) . "\n";

        $competitions = $data['data'];
        echo "Gefunden: " . count($competitions) . " Wettbewerbe\n\n";

        foreach ($competitions as $idx => $comp) {
            echo sprintf(
                "  %2d) %-60s\n",
                $idx + 1,
                substr($comp['name'] ?? $comp['international_competition_name'] ?? 'Unknown', 0, 60)
            );

            if (isset($comp['id'])) {
                echo sprintf("       ID: %s\n", $comp['id']);
            }
            if (isset($comp['season'])) {
                echo sprintf("       Season: %s\n", $comp['season']);
            }
            if (isset($comp['status'])) {
                echo sprintf("       Status: %s\n", $comp['status']);
            }
            echo "\n";
        }
    } else {
        echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "  ❌ Fehler beim Abrufen\n";
    echo "  Response: " . substr($response, 0, 200) . "...\n\n";
}

// Show cookie jar contents
echo "\n【Cookies gespeichert】\n";
echo str_repeat("─", 80) . "\n";
if (file_exists($cookieJar)) {
    echo "Cookie-Datei: " . $cookieJar . "\n";
    $cookies = file_get_contents($cookieJar);
    echo substr($cookies, 0, 300) . "...\n";
}

curl_close($ch);

echo "\n";
?>
