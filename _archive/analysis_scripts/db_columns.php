<?php
$conn = mysqli_connect('localhost', 'root', '', 'kp_club_management');
if (!$conn) die('Connection error: ' . mysqli_connect_error());
mysqli_set_charset($conn, 'utf8');

// Get all columns
$result = mysqli_query($conn, 'DESC comet_competitions');
echo "Columns in comet_competitions table:\n";
$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
    echo "  - " . $row['Field'] . "\n";
}

// Get the data
echo "\nSample active competitions:\n";
$result = mysqli_query($conn, "SELECT id, fifa_id, international_name, organisation_fifa_id, status FROM comet_competitions WHERE status='ACTIVE' LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    echo "  ID:{$row['id']}, FIFA:{$row['fifa_id']}, Org:{$row['organisation_fifa_id']}, Name:{$row['international_name']}\n";
}

mysqli_close($conn);
