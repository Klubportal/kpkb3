<?php

echo "=== Direkte SQL Migration ohne Laravel Bootstrap ===\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Hole alle Migration-Dateien
$migrationFiles = array_merge(
    glob(__DIR__ . '/database/migrations/*.php'),
    glob(__DIR__ . '/database/migrations/comet/*.php')
);

sort($migrationFiles);

$executed = 0;
$skipped = 0;

foreach ($migrationFiles as $file) {
    $migrationName = basename($file, '.php');
    echo "Verarbeite: {$migrationName}\n";

    // Lese Migration-Datei
    $content = file_get_contents($file);

    // Extrahiere SQL aus DB::statement()
    if (preg_match('/DB::statement\(<<<SQL\s*(.*?)\s*SQL\s*\)/s', $content, $matches)) {
        $sql = trim($matches[1]);

        // Führe SQL aus
        try {
            $mysqli->query($sql);
            echo "  ✓ Erfolgreich\n";
            $executed++;
        } catch (Exception $e) {
            echo "  ⚠ Übersprungen (bereits vorhanden oder Fehler)\n";
            $skipped++;
        }
    } else {
        echo "  ⚠ Kein SQL gefunden\n";
        $skipped++;
    }
}

echo "\n✅ Migration abgeschlossen!\n";
echo "Ausgeführt: {$executed}\n";
echo "Übersprungen: {$skipped}\n";

$mysqli->close();
