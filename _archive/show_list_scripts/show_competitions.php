<?php
$conn = mysqli_connect('localhost', 'root', '', 'kp_club_management');
if (!$conn) die('Connection error: ' . mysqli_connect_error());
mysqli_set_charset($conn, 'utf8');

echo "【 Active Competitions in Database 】\n";
echo str_repeat("=", 80) . "\n\n";

$result = mysqli_query($conn, "SELECT competition_fifa_id, international_name, organisation_fifa_id, season, status FROM comet_competitions WHERE status='ACTIVE'");

$count = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $count++;
    echo "$count. FIFA:{$row['competition_fifa_id']}\n";
    echo "   Name: {$row['international_name']}\n";
    echo "   Org: {$row['organisation_fifa_id']}\n";
    echo "   Season: {$row['season']}\n\n";
}

echo "Total active competitions: $count\n";

mysqli_close($conn);
