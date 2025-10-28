<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "🏗️  ERSTELLE COMET_MATCH_OFFICIALS TABELLE\n";
echo str_repeat("=", 60) . "\n\n";

// Drop if exists
$dropQuery = "DROP TABLE IF EXISTS comet_match_officials";
if ($mysqli->query($dropQuery)) {
    echo "✅ Alte Tabelle gelöscht (falls vorhanden)\n";
}

// Create table
$createQuery = "CREATE TABLE comet_match_officials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_fifa_id INT NOT NULL,
    person_fifa_id INT NOT NULL,

    -- Namen
    international_first_name VARCHAR(255),
    international_last_name VARCHAR(255),
    local_first_name VARCHAR(255),
    local_last_name VARCHAR(255),

    -- Rolle
    role VARCHAR(50) NOT NULL,
    role_description VARCHAR(255),
    comet_role_name VARCHAR(255),

    -- Persönliche Daten
    gender ENUM('MALE', 'FEMALE', 'OTHER') DEFAULT 'MALE',
    date_of_birth DATE,
    nationality VARCHAR(3),
    nationality_fifa VARCHAR(3),

    -- Geburtsort
    country_of_birth VARCHAR(3),
    country_of_birth_fifa VARCHAR(3),
    region_of_birth VARCHAR(255),
    place_of_birth VARCHAR(255),
    current_place VARCHAR(255),

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_match (match_fifa_id),
    INDEX idx_person (person_fifa_id),
    INDEX idx_role (role),
    UNIQUE KEY unique_match_person_role (match_fifa_id, person_fifa_id, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($createQuery)) {
    echo "✅ Tabelle comet_match_officials erstellt\n\n";

    // Zeige Struktur
    echo "📋 TABELLENSTRUKTUR:\n";
    echo str_repeat("-", 60) . "\n";
    $result = $mysqli->query("DESCRIBE comet_match_officials");
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-30s %-30s %s\n", $row['Field'], $row['Type'], $row['Key'] ? "KEY: {$row['Key']}" : "");
    }
} else {
    die("❌ Fehler beim Erstellen: " . $mysqli->error . "\n");
}

$mysqli->close();

echo "\n✅ FERTIG! Tabelle bereit für Schiedsrichter-Daten\n";
