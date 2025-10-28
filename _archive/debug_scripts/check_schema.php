<?php
$conn = mysqli_connect('localhost', 'root', '', 'kp_club_management');
if (!$conn) die('Connection error');
mysqli_set_charset($conn, 'utf8');

echo "comet_teams columns:\n";
$result = mysqli_query($conn, 'DESC comet_teams');
while ($row = mysqli_fetch_assoc($result)) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\ncomet_players columns:\n";
$result = mysqli_query($conn, 'DESC comet_players');
while ($row = mysqli_fetch_assoc($result)) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\ncomet_matches columns:\n";
$result = mysqli_query($conn, 'DESC comet_matches');
while ($row = mysqli_fetch_assoc($result)) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\ntop_scorers columns:\n";
$result = mysqli_query($conn, 'DESC top_scorers');
while ($row = mysqli_fetch_assoc($result)) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

mysqli_close($conn);
