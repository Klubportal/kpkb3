<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'tenant_nkprigorjem';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔧 Extending groups table for Comet integration...\n";
echo "============================================================\n\n";

// Add new columns
echo "📋 Adding columns to groups table...\n";

$alterQueries = [
    "ALTER TABLE `groups` ADD COLUMN `age_category` VARCHAR(50) NULL AFTER `gender`",
    "ALTER TABLE `groups` ADD COLUMN `age_category_name` VARCHAR(191) NULL AFTER `age_category`",
    "ALTER TABLE `groups` ADD COLUMN `comet_competition_type` VARCHAR(50) NULL AFTER `age_category_name`",
    "ALTER TABLE `groups` ADD INDEX `groups_age_category_index` (`age_category`)"
];

foreach ($alterQueries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ " . substr($sql, 0, 80) . "...\n";
    } else {
        if (strpos($conn->error, 'Duplicate column') !== false || strpos($conn->error, 'Duplicate key') !== false) {
            echo "⚠️  Column/Index already exists: " . substr($sql, 0, 60) . "...\n";
        } else {
            echo "❌ Error: " . $conn->error . "\n";
        }
    }
}

echo "\n============================================================\n";
echo "✅ Table structure updated!\n\n";

// Show new structure
$result = $conn->query("DESCRIBE `groups`");
echo "📋 New groups table structure:\n";
echo "------------------------------------------------------------\n";
while ($col = $result->fetch_assoc()) {
    echo sprintf("%-25s %-25s %s\n", $col['Field'], $col['Type'], $col['Key'] ? "[{$col['Key']}]" : '');
}

$conn->close();
