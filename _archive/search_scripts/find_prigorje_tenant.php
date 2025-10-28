<?php

echo "🔍 SEARCHING FOR NK PRIGORJE TENANT DATABASE\n";
echo str_repeat("=", 80) . "\n\n";

$mysqli = new mysqli('localhost', 'root', '');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Find all tenant databases
echo "📊 All Tenant Databases:\n";
$result = $mysqli->query("SHOW DATABASES LIKE 'tenant%'");
while ($row = $result->fetch_row()) {
    echo "   - {$row[0]}\n";
}

echo "\n📊 Searching for Prigorje:\n";
$result = $mysqli->query("SHOW DATABASES LIKE '%prigorje%'");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_row()) {
        echo "   ✅ Found: {$row[0]}\n";
    }
} else {
    echo "   ❌ No database with 'prigorje' found\n";
}

// Check tenants table
echo "\n📊 Checking tenants table in kpkb3:\n";
$mysqli->select_db('kpkb3');
$result = $mysqli->query("SELECT id, name, data FROM tenants WHERE name LIKE '%prigorje%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "   ID: {$row['id']}\n";
        echo "   Name: {$row['name']}\n";
        echo "   Data: {$row['data']}\n\n";
    }
} else {
    echo "   ❌ No tenant with 'prigorje' found\n";
}

$mysqli->close();
