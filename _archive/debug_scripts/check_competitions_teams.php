<?php
$c = new mysqli('localhost', 'root', '');

echo "🔍 Checking competitions in tenant_nkprigorjem...\n";
echo "============================================================\n\n";

$result = $c->query("
    SELECT
        id,
        name,
        age_category,
        gender,
        type,
        season
    FROM tenant_nkprigorjem.comet_competitions
    ORDER BY age_category, name
");

echo "COMPETITIONS (NK Prigorje participates in):\n";
echo "------------------------------------------------------------\n";

$teams = [];
while ($row = $result->fetch_assoc()) {
    echo sprintf("ID %-3d | %-60s | Age: %-10s | Gender: %-6s\n",
        $row['id'],
        substr($row['name'], 0, 60),
        $row['age_category'] ?: 'N/A',
        $row['gender'] ?: 'N/A'
    );

    // Extract team info from competition name
    if (preg_match('/(ZAGIĆI|LIMAČI|MLAĐI PIONIRI|PIONIRI|KADETI|JUNIORI|SENIORI|VETERANI)/i', $row['name'], $matches)) {
        $teamName = $matches[1];
        if (!in_array($teamName, $teams)) {
            $teams[] = $teamName;
        }
    }
}

echo "\n📊 Total competitions: " . $result->num_rows . "\n";

echo "\n🎯 Teams identified from competition names:\n";
echo "------------------------------------------------------------\n";
foreach ($teams as $team) {
    echo "  - $team\n";
}
echo "\nTotal teams: " . count($teams) . "\n";

$c->close();
