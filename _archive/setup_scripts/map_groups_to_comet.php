<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'tenant_nkprigorjem';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔄 Mapping groups to Comet age categories...\n";
echo "============================================================\n\n";

// Mapping basierend auf den Comet Daten
$mappings = [
    // Männlich
    ['slug' => 'veterani', 'age_category' => 'OTHER', 'age_category_name' => 'label.category.veterans.2x40', 'type' => 'league'],
    ['slug' => 'seniori', 'age_category' => 'SENIORS', 'age_category_name' => 'label.seniors', 'type' => 'league'],
    ['slug' => 'juniori', 'age_category' => 'OTHER', 'age_category_name' => 'label.juniors', 'type' => 'league'],
    ['slug' => 'kadeti', 'age_category' => 'OTHER', 'age_category_name' => 'label.cadets', 'type' => 'league'],
    ['slug' => 'pioniri', 'age_category' => 'OTHER', 'age_category_name' => 'label.pioneers', 'type' => 'league'],
    ['slug' => 'mladi_pioniri', 'age_category' => 'OTHER', 'age_category_name' => 'label.youngPioneers', 'type' => 'league'],
    ['slug' => 'limaci', 'age_category' => 'OTHER', 'age_category_name' => 'label.category.beginners', 'type' => 'league'],
    ['slug' => 'zagici', 'age_category' => 'OTHER', 'age_category_name' => 'label.category.beginners', 'type' => 'league'],
    ['slug' => 'u_11', 'age_category' => 'U11', 'age_category_name' => 'U11', 'type' => 'league'],
    ['slug' => 'u_10', 'age_category' => 'U10', 'age_category_name' => 'U10', 'type' => 'league'],
    ['slug' => 'u_9', 'age_category' => 'U9', 'age_category_name' => 'U9', 'type' => 'league'],
    ['slug' => 'u_8', 'age_category' => 'U8', 'age_category_name' => 'U8', 'type' => 'league'],
    ['slug' => 'u_7', 'age_category' => 'U7', 'age_category_name' => 'U7', 'type' => 'league'],

    // Weiblich
    ['slug' => 'veteranke', 'age_category' => 'OTHER', 'age_category_name' => 'label.category.veterans', 'type' => 'league'],
    ['slug' => 'seniorke', 'age_category' => 'SENIORS', 'age_category_name' => 'label.seniors', 'type' => 'league'],
    ['slug' => 'juniorke', 'age_category' => 'OTHER', 'age_category_name' => 'label.juniors', 'type' => 'league'],
    ['slug' => 'kadetkinje', 'age_category' => 'OTHER', 'age_category_name' => 'label.cadets', 'type' => 'league'],
    ['slug' => 'pionirke', 'age_category' => 'OTHER', 'age_category_name' => 'label.pioneers', 'type' => 'league'],
    ['slug' => 'mlade_pionirke', 'age_category' => 'OTHER', 'age_category_name' => 'label.youngPioneers', 'type' => 'league'],
    ['slug' => 'limacice', 'age_category' => 'OTHER', 'age_category_name' => 'label.category.beginners', 'type' => 'league'],
    ['slug' => 'zagicke', 'age_category' => 'OTHER', 'age_category_name' => 'label.category.beginners', 'type' => 'league'],
    ['slug' => 'u_11_f', 'age_category' => 'U11', 'age_category_name' => 'U11', 'type' => 'league'],
    ['slug' => 'u_10_f', 'age_category' => 'U10', 'age_category_name' => 'U10', 'type' => 'league'],
    ['slug' => 'u_9_f', 'age_category' => 'U9', 'age_category_name' => 'U9', 'type' => 'league'],
    ['slug' => 'u_8_f', 'age_category' => 'U8', 'age_category_name' => 'U8', 'type' => 'league'],
];

$updated = 0;
$notFound = 0;

foreach ($mappings as $map) {
    $sql = "UPDATE `groups` SET
            age_category = '{$map['age_category']}',
            age_category_name = '{$map['age_category_name']}',
            comet_competition_type = '{$map['type']}'
            WHERE slug = '{$map['slug']}'";

    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            $updated++;
            echo "✅ Updated: {$map['slug']} => {$map['age_category']} ({$map['age_category_name']})\n";
        } else {
            $notFound++;
            echo "⚠️  Not found: {$map['slug']}\n";
        }
    } else {
        echo "❌ Error: " . $conn->error . "\n";
    }
}

echo "\n============================================================\n";
echo "✅ Mapping completed!\n";
echo "   Updated: $updated groups\n";
echo "   Not found: $notFound groups\n\n";

// Show result
echo "📋 Groups with Comet mapping:\n";
echo "------------------------------------------------------------\n";
$result = $conn->query("
    SELECT slug, name, gender, age_category, age_category_name, active
    FROM `groups`
    WHERE age_category IS NOT NULL
    ORDER BY gender, age_category
");

while ($row = $result->fetch_assoc()) {
    echo sprintf("%-20s | %-25s | %s | %-10s | %-40s | Active: %d\n",
        $row['slug'],
        $row['name'],
        $row['gender'],
        $row['age_category'],
        $row['age_category_name'],
        $row['active']
    );
}

$conn->close();
