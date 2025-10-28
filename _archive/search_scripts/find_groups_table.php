<?php

$host = 'localhost';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔍 Searching for 'groups' table...\n";
echo "============================================================\n\n";

// Check landlord
echo "📊 LANDLORD (kpkb3):\n";
$result = $conn->query("SHOW TABLES FROM kpkb3 LIKE '%group%'");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        echo "   ✅ {$row[0]}\n";
    }
} else {
    echo "   ❌ No group tables\n";
}
echo "\n";

// Check tenant
$result = $conn->query("SELECT id FROM kpkb3.tenants LIMIT 1");
if ($result->num_rows > 0) {
    $tenant = $result->fetch_assoc();
    $tenantDb = $tenant['id'];

    echo "📊 TENANT ({$tenantDb}):\n";
    $result = $conn->query("SHOW TABLES FROM {$tenantDb} LIKE '%group%'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            echo "   ✅ {$row[0]}\n";
        }
    } else {
        echo "   ❌ No group tables\n";
    }
}

echo "\n============================================================\n";

$conn->close();
