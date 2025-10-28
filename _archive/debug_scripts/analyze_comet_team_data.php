<?php

$host = 'localhost';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔍 Analyzing Comet data for team/group information...\n";
echo "============================================================\n\n";

// 1. Check comet_matches for team names
echo "📋 Unique team names from comet_matches:\n";
echo "------------------------------------------------------------\n";

$teams = $conn->query("
    SELECT DISTINCT team_name_home as team_name
    FROM kpkb3.comet_matches
    WHERE team_fifa_id_home = 598
    UNION
    SELECT DISTINCT team_name_away as team_name
    FROM kpkb3.comet_matches
    WHERE team_fifa_id_away = 598
    ORDER BY team_name
");

if ($teams && $teams->num_rows > 0) {
    while ($team = $teams->fetch_assoc()) {
        echo "  - {$team['team_name']}\n";
    }
    echo "\nTotal unique team names: " . $teams->num_rows . "\n\n";
} else {
    echo "❌ No team names found in matches\n\n";
}

// 2. Check competitions for age categories
echo "📋 Competitions with age categories:\n";
echo "------------------------------------------------------------\n";

$comps = $conn->query("
    SELECT DISTINCT
        competition_name,
        age_category,
        gender,
        competition_type
    FROM kpkb3.comet_competitions
    WHERE team_fifa_id = 598
    ORDER BY age_category, gender
");

if ($comps && $comps->num_rows > 0) {
    while ($comp = $comps->fetch_assoc()) {
        echo sprintf("%-50s | Age: %-15s | Gender: %-10s | Type: %s\n",
            $comp['competition_name'],
            $comp['age_category'] ?: 'N/A',
            $comp['gender'] ?: 'N/A',
            $comp['competition_type']
        );
    }
    echo "\nTotal competitions: " . $comps->num_rows . "\n\n";
} else {
    echo "❌ No competitions found\n\n";
}

// 3. Check if there are team officials with team info
echo "📋 Teams from match team officials:\n";
echo "------------------------------------------------------------\n";

$match_teams = $conn->query("
    SELECT DISTINCT
        mto.team_fifa_id,
        m.team_name_home,
        m.team_name_away,
        m.team_fifa_id_home,
        m.team_fifa_id_away
    FROM kpkb3.comet_match_team_officials mto
    JOIN kpkb3.comet_matches m ON mto.match_fifa_id = m.match_fifa_id
    WHERE mto.team_fifa_id = 598
    LIMIT 5
");

if ($match_teams && $match_teams->num_rows > 0) {
    while ($mt = $match_teams->fetch_assoc()) {
        $team_name = ($mt['team_fifa_id_home'] == 598) ? $mt['team_name_home'] : $mt['team_name_away'];
        echo "  FIFA ID: {$mt['team_fifa_id']} - {$team_name}\n";
    }
} else {
    echo "❌ No team officials data\n";
}echo "\n============================================================\n";
echo "\n💡 RECOMMENDATION:\n";
echo "Add these columns to 'groups' table:\n";
echo "  - comet_team_name VARCHAR(191) NULL (exact name from Comet API)\n";
echo "  - age_category VARCHAR(50) NULL (U7, U8, U9, U10, U11, etc.)\n";
echo "  - competition_type VARCHAR(50) NULL (for filtering)\n";
echo "\nThis allows automatic matching:\n";
echo "  groups.comet_team_name = comet_matches.home_club_name\n";
echo "  groups.age_category = comet_competitions.age_category\n";

$conn->close();
