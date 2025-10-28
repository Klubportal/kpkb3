<?php
$c = new mysqli('localhost', 'root', '');

echo "🔧 Activating teams that play in competitions...\n";
echo "============================================================\n\n";

// Teams die in Competitions spielen sollten aktiv sein
$teamsToActivate = [
    'seniori',
    'juniori',
    'kadeti',
    'pioniri',
    'mladi_pioniri',
    'limaci',
    'zagici',
    'veterani',
    'u_11',
    'u_10',
    'u_9',
    'u_8',
    'u_7'
];

$updated = 0;
foreach ($teamsToActivate as $slug) {
    $sql = "UPDATE tenant_nkprigorjem.groups SET active = 1 WHERE slug = '$slug'";
    if ($c->query($sql)) {
        if ($c->affected_rows > 0) {
            $updated++;
            echo "✅ Activated: $slug\n";
        }
    }
}

echo "\n📊 Updated: $updated teams\n\n";

// Show active teams now
echo "📋 Active teams now:\n";
echo "------------------------------------------------------------\n";
$result = $c->query("
    SELECT id, name, slug, gender, age_category
    FROM tenant_nkprigorjem.groups
    WHERE active = 1
    ORDER BY gender, `order`
");

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    echo sprintf("%2d. %-20s | Gender: %s | Age: %-10s | Slug: %s\n",
        $count,
        $row['name'],
        $row['gender'],
        $row['age_category'] ?: 'N/A',
        $row['slug']
    );
}

echo "\n✅ Total active teams: $count\n";

$c->close();
