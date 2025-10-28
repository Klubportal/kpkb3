<?php

/**
 * COMET Complete Sync
 *
 * Synchronisiert alle COMET-Daten für NK Prigorje
 */

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║       COMET COMPLETE SYNC - NK PRIGORJE (FIFA 598)          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$scripts = [
    'sync_match_phases_all.php' => 'Match Phases (Spielphasen)',
    'sync_match_players_nk_prigorje.php' => 'Match Players (Spieleraufstellungen)',
    'sync_match_officials_all.php' => 'Match Officials (Schiedsrichter)',
    'sync_match_team_officials.php' => 'Team Officials (Trainer, Betreuer)',
];

$results = [];

foreach ($scripts as $script => $description) {
    echo "\n🔄 Synchronisiere: $description\n";
    echo str_repeat('-', 70) . "\n";

    if (!file_exists($script)) {
        echo "⚠️  Script nicht gefunden: $script\n";
        $results[$description] = 'NOT_FOUND';
        continue;
    }

    $startTime = microtime(true);
    passthru("php $script 2>&1", $exitCode);
    $duration = round(microtime(true) - $startTime, 2);

    if ($exitCode === 0) {
        echo "\n✅ $description: Erfolgreich ({$duration}s)\n";
        $results[$description] = 'SUCCESS';
    } else {
        echo "\n❌ $description: Fehlgeschlagen (Exit Code: $exitCode)\n";
        $results[$description] = 'FAILED';
    }

    echo str_repeat('=', 70) . "\n";
}

// Zusammenfassung
echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                       SYNC ZUSAMMENFASSUNG                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

foreach ($results as $description => $status) {
    $icon = $status === 'SUCCESS' ? '✅' : ($status === 'FAILED' ? '❌' : '⚠️');
    echo "  $icon $description: $status\n";
}

echo "\n✅ COMET Complete Sync abgeschlossen!\n\n";
