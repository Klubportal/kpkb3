<?php

$host = 'localhost';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔍 Checking Tenant Database Structure...\n";
echo "============================================================\n\n";

// Get first tenant from landlord
$result = $conn->query("SELECT id FROM kpkb3.tenants LIMIT 1");
if ($result->num_rows == 0) {
    die("❌ No tenants found\n");
}

$tenant = $result->fetch_assoc();
$tenantDb = $tenant['id'];

echo "📊 Tenant Database: {$tenantDb}\n\n";

echo "📋 Tables containing 'coach' or 'group':\n";
echo "------------------------------------------------------------\n";

$query = "
    SELECT TABLE_NAME
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = '{$tenantDb}'
    AND (TABLE_NAME LIKE '%coach%' OR TABLE_NAME LIKE '%group%')
    ORDER BY TABLE_NAME
";

$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "❌ No coach or group tables found\n\n";
} else {
    while ($table = $result->fetch_assoc()) {
        $tableName = $table['TABLE_NAME'];
        echo "✅ {$tableName}\n";

        // Show columns
        $colQuery = "
            SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '{$tenantDb}'
            AND TABLE_NAME = '{$tableName}'
            ORDER BY ORDINAL_POSITION
        ";

        $colResult = $conn->query($colQuery);
        while ($col = $colResult->fetch_assoc()) {
            echo "   - {$col['COLUMN_NAME']} ({$col['COLUMN_TYPE']})";
            if ($col['COLUMN_KEY']) echo " [{$col['COLUMN_KEY']}]";
            echo "\n";
        }
        echo "\n";
    }
}

echo "============================================================\n";

$conn->close();
