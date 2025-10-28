<?php

$host = 'localhost';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔍 Analyzing Comet data for automatic team matching...\n";
echo "============================================================\n\n";

// 1. Age categories from matches
echo "📋 Age categories in comet_matches:\n";
echo "------------------------------------------------------------\n";

$age_cats = $conn->query("
    SELECT DISTINCT
        age_category,
        age_category_name,
        COUNT(*) as match_count
    FROM kpkb3.comet_matches
    WHERE (team_fifa_id_home = 598 OR team_fifa_id_away = 598)
    AND age_category IS NOT NULL
    GROUP BY age_category, age_category_name
    ORDER BY age_category
");

if ($age_cats && $age_cats->num_rows > 0) {
    while ($cat = $age_cats->fetch_assoc()) {
        echo sprintf("  %-20s | %-40s | %d matches\n",
            $cat['age_category'] ?: 'N/A',
            $cat['age_category_name'] ?: 'N/A',
            $cat['match_count']
        );
    }
    echo "\nTotal age categories: " . $age_cats->num_rows . "\n\n";
} else {
    echo "❌ No age categories found\n\n";
}

// 2. Competitions with details
echo "📋 Competitions breakdown:\n";
echo "------------------------------------------------------------\n";

$comps = $conn->query("
    SELECT
        name,
        age_category,
        gender,
        type,
        season,
        COUNT(*) as comp_count
    FROM kpkb3.comet_competitions
    WHERE organisation_fifa_id = 598 OR comet_id IN (
        SELECT DISTINCT competition_fifa_id
        FROM kpkb3.comet_matches
        WHERE team_fifa_id_home = 598 OR team_fifa_id_away = 598
    )
    GROUP BY name, age_category, gender, type, season
    ORDER BY age_category, gender
");

if ($comps && $comps->num_rows > 0) {
    while ($comp = $comps->fetch_assoc()) {
        echo sprintf("%-60s | Age: %-10s | Gender: %-6s | Type: %-15s\n",
            substr($comp['name'], 0, 60),
            $comp['age_category'] ?: 'N/A',
            $comp['gender'] ?: 'N/A',
            $comp['type'] ?: 'N/A'
        );
    }
    echo "\nTotal competitions: " . $comps->num_rows . "\n\n";
}

echo "============================================================\n";
echo "\n💡 RECOMMENDATION - Add to groups table:\n\n";
echo "ALTER TABLE tenant_nkprigorjem.groups ADD COLUMN:\n";
echo "  - age_category VARCHAR(50) NULL\n";
echo "  - age_category_name VARCHAR(191) NULL  \n";
echo "  - comet_competition_type VARCHAR(50) NULL\n\n";
echo "Then you can auto-match:\n";
echo "  groups WHERE age_category = 'U9' => Zagići (U9)\n";
echo "  groups WHERE age_category = 'U8' => Limači (U8)\n";
echo "  groups WHERE age_category = 'U7' => matches U7\n\n";

$conn->close();
