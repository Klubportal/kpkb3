<?php

$landlord = new PDO('mysql:host=localhost;dbname=kpkb3', 'root', '');
$tenant = new PDO('mysql:host=localhost;dbname=tenant_nkprigorjem', 'root', '');

echo "=== comet_team_officials Check ===\n\n";

// Landlord
$countLandlord = $landlord->query('SELECT COUNT(*) FROM comet_team_officials')->fetchColumn();
echo "Landlord: $countLandlord Einträge\n";

if ($countLandlord > 0) {
    echo "\nBeispiel Daten:\n";
    $rows = $landlord->query('SELECT * FROM comet_team_officials LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
}

// Tenant
$countTenant = $tenant->query('SELECT COUNT(*) FROM comet_team_officials')->fetchColumn();
echo "\nTenant: $countTenant Einträge\n";

if ($countLandlord > 0 && $countTenant === 0) {
    echo "\n=== Kopiere Daten ===\n";

    $data = $landlord->query('SELECT * FROM comet_team_officials')->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        $columns = array_keys($data[0]);
        $placeholders = ':' . implode(', :', $columns);
        $columnList = '`' . implode('`, `', $columns) . '`';

        $insertSql = "INSERT INTO comet_team_officials ($columnList) VALUES ($placeholders)";
        $stmt = $tenant->prepare($insertSql);

        $count = 0;
        foreach ($data as $row) {
            $stmt->execute($row);
            $count++;
        }

        echo "✓ $count Einträge kopiert\n";
    }
}
