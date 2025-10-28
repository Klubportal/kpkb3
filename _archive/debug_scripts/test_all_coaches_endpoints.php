<?php

// Systematischer Test aller Officials/Coaches Endpoints
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$teamFifaId = 598;
$matchFifaId = 100629577;
$competitionFifaId = 100629542;

echo "🔍 SYSTEMATISCHER TEST - OFFICIALS/COACHES ENDPOINTS\n";
echo str_repeat("=", 70) . "\n\n";

// Alle möglichen Endpoint-Kombinationen
$endpoints = [
    // Team-basiert
    "team/{$teamFifaId}/officials",
    "team/{$teamFifaId}/teamOfficials",
    "team/{$teamFifaId}/coaches",
    "team/{$teamFifaId}/staff",
    "team/{$teamFifaId}/management",

    // Match-basiert
    "match/{$matchFifaId}/officials",
    "match/{$matchFifaId}/teamOfficials",
    "match/{$matchFifaId}/coaches",
    "match/{$matchFifaId}/staff",

    // Competition-basiert
    "competition/{$competitionFifaId}/officials",
    "competition/{$competitionFifaId}/teamOfficials",
    "competition/{$competitionFifaId}/{$teamFifaId}/officials",
    "competition/{$competitionFifaId}/{$teamFifaId}/teamOfficials",
    "competition/{$competitionFifaId}/{$teamFifaId}/coaches",
    "competition/{$competitionFifaId}/{$teamFifaId}/staff",
];

$successfulEndpoints = [];

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
        echo "   ✅ HTTP 200 - SUCCESS!\n";
        $data = json_decode($response, true);

        if (is_array($data) && !empty($data)) {
            $count = count($data);
            echo "   📊 Einträge: {$count}\n";

            // Speichere erfolgreichen Endpoint
            $successfulEndpoints[$endpoint] = [
                'count' => $count,
                'data' => $data
            ];

            // Zeige Struktur des ersten Elements
            if (isset($data[0])) {
                echo "   📋 Felder: " . implode(', ', array_keys($data[0])) . "\n";

                // Prüfe auf spezielle Felder
                if (isset($data[0]['officials'])) {
                    echo "   🎯 Hat 'officials' Array mit " . count($data[0]['officials']) . " Einträgen\n";
                }
                if (isset($data[0]['role'])) {
                    echo "   🎯 Hat 'role' Feld: " . $data[0]['role'] . "\n";
                }
                if (isset($data[0]['person'])) {
                    echo "   🎯 Hat 'person' Objekt\n";
                }
            }
        } else {
            echo "   ⚠️  Leere Antwort oder kein Array\n";
        }
    } else {
        echo "   ❌ HTTP {$httpCode}\n";
    }

    echo "\n";
    usleep(200000); // 200ms pause zwischen Requests
}

// Zusammenfassung
echo str_repeat("=", 70) . "\n";
echo "✅ ERFOLGREICHE ENDPOINTS: " . count($successfulEndpoints) . "\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($successfulEndpoints as $endpoint => $info) {
    echo "📍 /{$endpoint}\n";
    echo "   Einträge: {$info['count']}\n";

    // Detaillierte Analyse
    $data = $info['data'];

    // Zähle verschiedene Rollen
    $roles = [];
    foreach ($data as $item) {
        // Direktes role Feld
        if (isset($item['role'])) {
            $roles[$item['role']] = ($roles[$item['role']] ?? 0) + 1;
        }
        // Oder in officials Array
        if (isset($item['officials'])) {
            foreach ($item['officials'] as $official) {
                if (isset($official['role'])) {
                    $roles[$official['role']] = ($roles[$official['role']] ?? 0) + 1;
                }
            }
        }
    }

    if (!empty($roles)) {
        echo "   Rollen gefunden:\n";
        foreach ($roles as $role => $count) {
            echo "      - {$role}: {$count}\n";
        }
    }

    // Zeige ersten Eintrag vollständig
    echo "\n   📄 BEISPIEL (erster Eintrag):\n";
    echo "   " . str_repeat("-", 66) . "\n";
    print_r($data[0]);
    echo "   " . str_repeat("-", 66) . "\n\n";
}

// Spezifische Empfehlungen
echo "\n💡 EMPFEHLUNGEN:\n";
echo str_repeat("-", 70) . "\n";

if (count($successfulEndpoints) > 0) {
    echo "Verwende diese Endpoints für vollständige Daten:\n\n";
    foreach (array_keys($successfulEndpoints) as $endpoint) {
        echo "  ✓ /{$endpoint}\n";
    }
} else {
    echo "Keine zusätzlichen Endpoints gefunden.\n";
    echo "Verwende weiterhin: /match/{matchFifaId}/teamOfficials\n";
}
