<?php

$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$testMatchId = 102984019;

echo "🔍 MATCH OFFICIALS DETAILS\n";
echo str_repeat("=", 60) . "\n\n";

$ch = curl_init("{$apiUrl}/match/{$testMatchId}/officials");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $officials = json_decode($response, true);

    echo "✅ {$httpCode} - " . count($officials) . " Schiedsrichter gefunden\n\n";

    foreach ($officials as $official) {
        echo "👤 " . ($official['personName'] ?? 'N/A') . "\n";
        echo "   FIFA ID: " . ($official['personFifaId'] ?? 'N/A') . "\n";
        echo "   Rolle: " . ($official['role'] ?? 'N/A') . " - " . ($official['roleDescription'] ?? 'N/A') . "\n";
        echo "   Local Name: " . ($official['localPersonName'] ?? 'N/A') . "\n";
        echo "   Comet Role: " . ($official['cometRoleName'] ?? 'N/A') . "\n";

        if (isset($official['person'])) {
            $person = $official['person'];
            echo "   Details:\n";
            foreach ($person as $key => $value) {
                if (!is_array($value) && !is_object($value)) {
                    echo "      - {$key}: {$value}\n";
                }
            }
        }
        echo "\n";
    }

    echo "\n📋 VERFÜGBARE FELDER:\n";
    if (count($officials) > 0) {
        $allKeys = [];
        foreach ($officials as $official) {
            $allKeys = array_merge($allKeys, array_keys($official));
        }
        $uniqueKeys = array_unique($allKeys);
        foreach ($uniqueKeys as $key) {
            echo "   - {$key}\n";
        }
    }
} else {
    echo "❌ HTTP {$httpCode}\n";
}

echo "\n\n🎯 FAZIT:\n";
echo str_repeat("=", 60) . "\n";
echo "Die meisten erweiterten Endpoints sind nicht verfügbar.\n";
echo "Verfügbar ist nur:\n";
echo "   ✅ Match Officials (Schiedsrichter pro Spiel)\n\n";

echo "💡 VORSCHLAG:\n";
echo "Wir können Schiedsrichter für alle gespielten Matches syncen.\n";
echo "Das gibt uns:\n";
echo "   - Hauptschiedsrichter\n";
echo "   - Linienrichter (Assistant Referees)\n";
echo "   - Vierte Offizielle\n";
echo "   - Delegierte\n\n";

echo "📊 WAS WIR BEREITS HABEN IST SEHR KOMPLETT:\n";
echo "   ✅ 1508 Matches\n";
echo "   ✅ 1014 Match Phases (alle gespielten)\n";
echo "   ✅ 3499 Match Events (Tore, Karten, etc.)\n";
echo "   ✅ 617 Match Players (Aufstellungen)\n";
echo "   ✅ 254 Players (vollständig)\n";
echo "   ✅ 7 Coaches\n";
echo "   ✅ 137 Rankings (Tabellen)\n";
echo "   ✅ 801 Top Scorers\n\n";

echo "❓ Möchtest du:\n";
echo "   1) Schiedsrichter für alle Matches syncen?\n";
echo "   2) Die vorhandenen Daten optimieren/verbessern?\n";
echo "   3) Etwas anderes?\n";
