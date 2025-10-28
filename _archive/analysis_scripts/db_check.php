<?php
$conn = mysqli_connect('localhost', 'root', '', 'kp_club_management');
if (!$conn) die('Connection error: ' . mysqli_connect_error());
mysqli_set_charset($conn, 'utf8');

echo "【 Database Competitions Overview 】\n";
echo str_repeat("=", 80) . "\n\n";

$result = mysqli_query($conn, 'SELECT COUNT(*) as total FROM comet_competitions');
$row = mysqli_fetch_assoc($result);
echo "Total competitions in database: " . $row['total'] . "\n\n";

$result = mysqli_query($conn, 'SELECT DISTINCT organisation_fifa_id FROM comet_competitions ORDER BY organisation_fifa_id');
echo "Organisations:\n";
$orgs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orgs[] = $row['organisation_fifa_id'];
    $countResult = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM comet_competitions WHERE organisation_fifa_id = " . intval($row['organisation_fifa_id']));
    $countRow = mysqli_fetch_assoc($countResult);
    echo "  - FIFA ID " . $row['organisation_fifa_id'] . ": " . $countRow['cnt'] . " competitions\n";
}

echo "\nActive competitions by status:\n";
$result = mysqli_query($conn, 'SELECT status, COUNT(*) as cnt FROM comet_competitions GROUP BY status');
while ($row = mysqli_fetch_assoc($result)) {
    echo "  - Status " . $row['status'] . ": " . $row['cnt'] . " competitions\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Sample active competitions:\n";
$result = mysqli_query($conn, "SELECT comet_fifa_id, international_name, organisation_fifa_id, status FROM comet_competitions WHERE status='ACTIVE' LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    echo "  - {$row['comet_fifa_id']}: {$row['international_name']} (Org: {$row['organisation_fifa_id']})\n";
}

mysqli_close($conn);
