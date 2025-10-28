<?php

// Test Match Phases API
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$matchFifaId = 100629577;

echo "🔍 TEST MATCH PHASES API - Match {$matchFifaId}\n";
echo str_repeat("=", 60) . "\n\n";

// API Call
$ch = curl_init("{$apiUrl}/match/{$matchFifaId}/phases");
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

$phases = json_decode($response, true);

echo "📊 Anzahl Phases: " . count($phases) . "\n\n";

if (!empty($phases)) {
    echo "📄 Alle Phases:\n";
    echo str_repeat("-", 60) . "\n";
    print_r($phases);

    echo "\n\n📋 Verfügbare Felder (erste Phase):\n";
    echo str_repeat("-", 60) . "\n";
    if (isset($phases[0])) {
        foreach ($phases[0] as $field => $value) {
            $type = gettype($value);
            $display = is_null($value) ? 'NULL' : $value;
            echo sprintf("%-25s | %-10s | %s\n", $field, $type, $display);
        }
    }

    echo "\n\n📊 Phases Übersicht:\n";
    echo str_repeat("-", 60) . "\n";
    foreach ($phases as $i => $phase) {
        $phaseName = $phase['phase'] ?? 'N/A';
        $homeScore = $phase['homeScore'] ?? '?';
        $awayScore = $phase['awayScore'] ?? '?';
        $length = $phase['phaseLength'] ?? '?';
        echo ($i + 1) . ". {$phaseName}: {$homeScore}:{$awayScore} ({$length} min)\n";
    }
}
