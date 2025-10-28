<?php

// Example usage of lib/sync_helpers.php
// Edit the connection settings below for your environment, then run:
//   php .\lib\example_upsert_test.php

require __DIR__ . '/sync_helpers.php';

$DB_HOST = '127.0.0.1';
$DB_PORT = 3306;
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'kpkb3';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($conn->connect_error) {
    echo "DB connection failed: " . $conn->connect_error . PHP_EOL;
    exit(2);
}

$table = 'comet_match_team_officials';

// Example identifying key(s) for the row we're upserting
$key = [
    'match_id' => 123456,
    'person_fifa_id' => 'ABC123'
];

// Example incoming data (fill with real values from Comet)
$data = [
    'match_id' => 123456,
    'person_fifa_id' => 'ABC123',
    'person_first_name' => 'Max',
    'person_last_name' => 'Mustermann',
    'role' => 'COACH',
    'team_nature' => 'HOME',
    'yellow_cards' => 0,
    'red_cards' => 0,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

try {
    $result = upsert_if_changed($conn, $table, $key, $data);
    echo 'Result: ' . json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

$conn->close();

?>
