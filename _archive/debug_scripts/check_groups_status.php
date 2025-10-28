<?php
$c = new mysqli('localhost', 'root', '');

echo "📊 Checking groups status...\n\n";

$result = $c->query("
    SELECT
        id, name, label, gender, active, published, age_category
    FROM tenant_nkprigorjem.groups
    ORDER BY active DESC, gender, `order`
");

echo "Active Groups:\n";
echo "------------------------------------------------------------\n";
$activeCount = 0;
while ($row = $result->fetch_assoc()) {
    if ($row['active'] == 1) {
        $activeCount++;
        echo sprintf("ID %-3d | %-20s | Gender: %s | Active: %d | Published: %d | Age: %s\n",
            $row['id'],
            $row['name'],
            $row['gender'],
            $row['active'],
            $row['published'],
            $row['age_category'] ?: 'N/A'
        );
    }
}

echo "\nInactive Groups:\n";
echo "------------------------------------------------------------\n";
$c->data_seek(0);
$result = $c->query("
    SELECT
        id, name, label, gender, active, published, age_category
    FROM tenant_nkprigorjem.groups
    ORDER BY active DESC, gender, `order`
");

$inactiveCount = 0;
while ($row = $result->fetch_assoc()) {
    if ($row['active'] == 0) {
        $inactiveCount++;
        echo sprintf("ID %-3d | %-20s | Gender: %s | Active: %d\n",
            $row['id'],
            $row['name'],
            $row['gender'],
            $row['active']
        );
    }
}

echo "\n📊 Summary:\n";
echo "   Active: $activeCount\n";
echo "   Inactive: $inactiveCount\n";
echo "   Total: " . ($activeCount + $inactiveCount) . "\n";

$c->close();
