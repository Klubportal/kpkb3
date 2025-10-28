<?php

// Test Team Coaches API
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$teamFifaId = 598;

echo "🔍 TEST TEAM COACHES API - Team {$teamFifaId}\n";
echo str_repeat("=", 60) . "\n\n";

// API Call
$ch = curl_init("{$apiUrl}/team/{$teamFifaId}/coaches");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    die("❌ API Fehler: HTTP {$httpCode}\n");
}

$coaches = json_decode($response, true);

echo "📊 Anzahl Coaches: " . count($coaches) . "\n\n";

if (!empty($coaches)) {
    echo "📄 Erster Coach (vollständige Struktur):\n";
    echo str_repeat("-", 60) . "\n";
    print_r($coaches[0]);

    echo "\n\n📋 Alle verfügbaren Felder:\n";
    echo str_repeat("-", 60) . "\n";
    $fields = array_keys($coaches[0]);
    foreach ($fields as $field) {
        $value = $coaches[0][$field];
        $type = gettype($value);
        $display = is_null($value) ? 'NULL' : (is_array($value) ? json_encode($value) : $value);
        echo sprintf("%-30s | %-10s | %s\n", $field, $type, substr($display, 0, 50));
    }

    echo "\n\n📋 Coaches Übersicht:\n";
    echo str_repeat("-", 60) . "\n";
    foreach ($coaches as $i => $coach) {
        $name = ($coach['internationalFirstName'] ?? '') . " " . ($coach['internationalLastName'] ?? '');
        $role = $coach['role'] ?? 'N/A';
        $status = $coach['status'] ?? 'N/A';
        echo ($i + 1) . ". {$name} - {$role} ({$status})\n";
    }
}
