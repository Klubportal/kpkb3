<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking for comet_top_scorers table in kpkb3...\n";
echo "=================================================\n\n";

// Check if table exists in central database
$centralTables = DB::connection('mysql')->select("SHOW TABLES FROM kpkb3");
$centralTableNames = array_map(function($table) {
    return array_values((array)$table)[0];
}, $centralTables);

$hasTopScorers = in_array('comet_top_scorers', $centralTableNames);

if ($hasTopScorers) {
    echo "✓ comet_top_scorers already exists in kpkb3\n";
} else {
    echo "✗ comet_top_scorers does NOT exist in kpkb3\n";
    echo "\nCreating comet_top_scorers table...\n";

    DB::connection('mysql')->statement("
        CREATE TABLE kpkb3.comet_top_scorers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            player_id BIGINT UNSIGNED NOT NULL,
            club_id BIGINT NOT NULL,
            competition_id BIGINT UNSIGNED NULL,
            player_name VARCHAR(255) NOT NULL,
            international_first_name VARCHAR(255) NULL,
            international_last_name VARCHAR(255) NULL,
            goals INT NOT NULL DEFAULT 0,
            assists INT NULL DEFAULT 0,
            matches INT NULL DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            KEY idx_club_id (club_id),
            KEY idx_player_id (player_id),
            KEY idx_goals (goals)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "✓ comet_top_scorers table created successfully!\n";
}
