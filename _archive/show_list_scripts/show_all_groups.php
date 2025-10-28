<?php
$c = new mysqli('localhost', 'root', '');

echo "📊 All groups status...\n\n";

$result = $c->query("
    SELECT
        id, name, label, gender, active, published, age_category
    FROM tenant_nkprigorjem.groups
    ORDER BY gender, `order`, id
");

echo "ALL GROUPS:\n";
echo "------------------------------------------------------------\n";
$activeCount = 0;
$inactiveCount = 0;

while ($row = $result->fetch_assoc()) {
    $status = $row['active'] ? '✅ ACTIVE  ' : '❌ INACTIVE';
    echo sprintf("%s | ID %-3d | %-20s | Gender: %s | Age: %-10s\n",
        $status,
        $row['id'],
        $row['name'],
        $row['gender'],
        $row['age_category'] ?: 'N/A'
    );

    if ($row['active']) {
        $activeCount++;
    } else {
        $inactiveCount++;
    }
}

echo "\n📊 Summary:\n";
echo "   ✅ Active: $activeCount\n";
echo "   ❌ Inactive: $inactiveCount\n";
echo "   📊 Total: " . ($activeCount + $inactiveCount) . "\n";

$c->close();
