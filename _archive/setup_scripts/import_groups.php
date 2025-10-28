<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'tenant_nkprigorjem';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "📋 Importing groups into tenant_nkprigorjem...\n";
echo "============================================================\n\n";

$groups = [
    [1, 'Veterani', 'label.category.veterans', 0, 0, 0, 'veterani', 'm', 1, '2025-01-11 10:14:01'],
    [2, 'Seniori', 'label.seniors', 0, 0, 0, 'seniori', 'm', 2, '2025-01-11 10:14:06'],
    [3, 'Juniori', 'label.juniors', 0, 0, 0, 'juniori', 'm', 3, '2025-01-11 10:14:08'],
    [4, 'Kadeti', 'label.cadets', 0, 0, 0, 'kadeti', 'm', 4, '2025-01-11 10:14:10'],
    [5, 'Pioniri', 'label.pioneers', 0, 0, 0, 'pioniri', 'm', 5, '2025-01-11 10:14:13'],
    [6, 'Mlađi pioniri', 'label.youngPioneers', 0, 0, 0, 'mladi_pioniri', 'm', 6, '2025-01-11 10:14:15'],
    [7, 'Limači', 'label.category.beginners.8+1', 1, 0, 0, 'limaci', 'm', 15, '2025-10-02 19:47:11'],
    [8, 'Zagići', 'zagići', 1, 0, 0, 'zagici', 'm', 8, NULL],
    [9, 'U 11', 'label.category.beginners.8+1', 1, 1, 0, 'u_11', 'm', 1, '2025-10-02 19:47:07'],
    [10, 'U 10', 'U - 10', 1, 0, 0, 'u_10', 'm', 10, NULL],
    [11, 'U 9', 'label.category.beginners.8+1', 1, 1, 0, 'u_9', 'm', 16, '2025-10-02 19:47:02'],
    [12, 'U 8', 'U - 8', 1, 0, 0, 'u_8', 'm', 12, NULL],
    [13, 'U 7', 'U - 7', 1, 0, 0, 'u_7', 'm', 13, NULL],
    [14, 'Veteranke', 'label.category.veterans', 0, 0, 0, 'veteranke', 'f', 1, NULL],
    [15, 'Seniorke', 'label.seniors', 0, 0, 0, 'seniorke', 'f', 2, NULL],
    [16, 'Juniorke', 'label.juniors', 0, 0, 0, 'juniorke', 'f', 3, NULL],
    [17, 'Kadetkinje', 'label.cadets', 0, 0, 0, 'kadetkinje', 'f', 4, NULL],
    [18, 'Pionirke', 'label.pioneers', 0, 0, 0, 'pionirke', 'f', 5, NULL],
    [19, 'Mlađe pionirke', 'label.youngPioneers', 0, 0, 0, 'mlade_pionirke', 'f', 6, NULL],
    [20, 'Limačice', 'limači', 0, 0, 0, 'limacice', 'f', 7, NULL],
    [21, 'Zagićke', 'zagići', 0, 0, 0, 'zagicke', 'f', 8, NULL],
    [22, 'U 11', 'U - 11', 0, 0, 0, 'u_11_f', 'f', 9, NULL], // Changed slug to avoid duplicate
    [23, 'U 10', 'U - 10', 0, 0, 0, 'u_10_f', 'f', 10, NULL], // Changed slug to avoid duplicate
    [24, 'U 9', 'U - 9', 0, 0, 0, 'u_9_f', 'f', 11, NULL], // Changed slug to avoid duplicate
    [25, 'U 8', 'U - 8', 0, 0, 0, 'u_8_f', 'f', 12, NULL], // Changed slug to avoid duplicate
];

$inserted = 0;
$errors = 0;

foreach ($groups as $group) {
    list($id, $name, $label, $active, $published, $user_custom, $slug, $gender, $order, $created_at) = $group;

    $created_at_sql = $created_at ? "'$created_at'" : "NULL";

    $sql = "INSERT INTO `groups`
            (id, name, label, active, published, user_custom_group, slug, gender, `order`, created_at, updated_at)
            VALUES
            ($id, '$name', '$label', $active, $published, $user_custom, '$slug', '$gender', $order, $created_at_sql, $created_at_sql)
            ON DUPLICATE KEY UPDATE
            name = '$name',
            label = '$label',
            active = $active,
            published = $published,
            user_custom_group = $user_custom,
            `order` = $order,
            updated_at = NOW()";

    if ($conn->query($sql) === TRUE) {
        $inserted++;
        echo "✅ ID $id: $name ($gender) - $label\n";
    } else {
        $errors++;
        echo "❌ Error inserting $name: " . $conn->error . "\n";
    }
}

echo "\n============================================================\n";
echo "✅ Import completed!\n";
echo "   Inserted/Updated: $inserted\n";
echo "   Errors: $errors\n\n";

// Show final count
$result = $conn->query("SELECT COUNT(*) as total FROM `groups`");
$count = $result->fetch_assoc();
echo "📊 Total groups in database: {$count['total']}\n";

// Show active groups
$result = $conn->query("SELECT COUNT(*) as total FROM `groups` WHERE active = 1");
$count = $result->fetch_assoc();
echo "📊 Active groups: {$count['total']}\n";

$conn->close();
