<?php

// Test Team Officials/Coaches API
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$teamFifaId = 598;
$matchFifaId = 100629577;
$competitionFifaId = 100629542; // Eine Competition ID aus comet_competitions

echo "🔍 TEST TEAM OFFICIALS/COACHES API\n";
echo str_repeat("=", 60) . "\n\n";

// Test verschiedene Endpoints
$endpoints = [
    "match/{$matchFifaId}/teamOfficials",
    "team/{$teamFifaId}/teamOfficials",
    "match/{$matchFifaId}/officials",
    "competition/{$competitionFifaId}/{$teamFifaId}/teamOfficials",
];

foreach ($endpoints as $endpoint) {
    echo "📥 Teste: /{$endpoint}\n";

    $ch = curl_init("{$apiUrl}/{$endpoint}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        echo "   ✅ HTTP {$httpCode} - SUCCESS!\n";
        $data = json_decode($response, true);

        if (is_array($data) && !empty($data)) {
            echo "   📊 Anzahl Einträge: " . count($data) . "\n";
            echo "   📄 Erster Eintrag:\n";
            echo str_repeat("-", 60) . "\n";
            print_r($data[0]);
            echo "\n" . str_repeat("-", 60) . "\n";

            echo "\n   📋 Verfügbare Felder:\n";
            foreach (array_keys($data[0]) as $field) {
                $value = $data[0][$field];
                $type = gettype($value);
                $display = is_null($value) ? 'NULL' : (is_array($value) ? 'ARRAY' : $value);
                echo sprintf("   %-30s | %-10s | %s\n", $field, $type, substr($display, 0, 40));
            }

            // Zeige alle Coaches/Officials
            echo "\n   👥 Übersicht:\n";
            foreach ($data as $i => $official) {
                $firstName = $official['internationalFirstName'] ?? $official['firstName'] ?? '';
                $lastName = $official['internationalLastName'] ?? $official['lastName'] ?? '';
                $name = trim($firstName . ' ' . $lastName);
                $role = $official['role'] ?? $official['function'] ?? 'N/A';
                echo "   " . ($i + 1) . ". {$name} - {$role}\n";
            }
        } else {
            echo "   ⚠️  Leere Antwort oder kein Array\n";
            print_r($data);
        }

        echo "\n" . str_repeat("=", 60) . "\n\n";
        break; // Ersten erfolgreichen Endpoint gefunden

    } else {
        echo "   ❌ HTTP {$httpCode}\n\n";
    }
}
