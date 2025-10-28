<?php

/**
 * COMET Data Sync Script
 *
 * Synchronisiert COMET-Daten für alle Vereine in die zentrale kpkb3 Datenbank
 * Jeder Tenant kann dann seine Daten aus der zentralen DB abrufen
 *
 * Verwendung: php sync_comet_data.php
 */

echo "\n=== COMET DATA SYNC ===\n";
echo "Synchronisiere COMET-Daten in zentrale DB (kpkb3)\n\n";

// Verein-Konfiguration
$clubs = [
    [
        'name' => 'NK Prigorje Markuševec',
        'tenant_id' => 'nkprigorjem',
        'fifa_id' => 598,
    ],
    [
        'name' => 'NK Naprijed Savice',
        'tenant_id' => 'nknapijed',
        'fifa_id' => null, // TODO: FIFA ID für NK Naprijed hinzufügen
    ],
];

foreach ($clubs as $club) {
    if (!$club['fifa_id']) {
        echo "⚠️  {$club['name']}: Keine FIFA ID hinterlegt - überspringe\n\n";
        continue;
    }

    echo "🔄 Synchronisiere: {$club['name']} (FIFA ID: {$club['fifa_id']})\n";
    echo str_repeat('-', 60) . "\n";

    // Führe Artisan Command aus
    echo "   Starte: php artisan comet:sync-club {$club['fifa_id']}\n\n";

    passthru("php artisan comet:sync-club {$club['fifa_id']}", $exitCode);

    if ($exitCode === 0) {
        echo "\n✅ {$club['name']}: Erfolgreich synchronisiert\n";
    } else {
        echo "\n❌ {$club['name']}: Sync fehlgeschlagen (Exit Code: {$exitCode})\n";
    }

    echo "\n" . str_repeat('=', 60) . "\n\n";
}

echo "✅ COMET Sync abgeschlossen!\n";
echo "\nℹ️  Daten wurden in zentrale DB (kpkb3) gespeichert.\n";
echo "   Tenants können jetzt über Cross-Database Queries darauf zugreifen.\n\n";
