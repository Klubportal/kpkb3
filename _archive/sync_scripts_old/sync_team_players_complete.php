<?php

// SYNC TEAM PLAYERS - Alle Spielerdaten von NK Prigorje
echo "👥 SYNC TEAM PLAYERS - NK PRIGORJE (Team 598)\n";
echo str_repeat("=", 60) . "\n\n";

// MySQL Verbindung
$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// API Settings
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$teamFifaId = 598;
$clubFifaId = 598;

echo "📥 Hole nur AKTIVE Spieler von Team {$teamFifaId}...\n";

// API Call - NUR AKTIVE Spieler
$ch = curl_init("{$apiUrl}/team/{$teamFifaId}/players?status=ACTIVE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    die("❌ API Fehler: HTTP {$httpCode}\n");
}

$players = json_decode($response, true);
echo "📊 API Spieler gefunden: " . count($players) . "\n\n";

$inserted = 0;
$updated = 0;
$errors = 0;

foreach ($players as $player) {
    $personFifaId = $player['personFifaId'] ?? null;

    if (!$personFifaId) {
        $errors++;
        continue;
    }

    // Position mapping
    $positionMap = [
        'Goalkeeper' => 'goalkeeper',
        'Defender' => 'defender',
        'Midfielder' => 'midfielder',
        'Forward' => 'forward',
    ];

    $apiPosition = $player['playerPosition'] ?? null;
    $position = isset($positionMap[$apiPosition]) ? $positionMap[$apiPosition] : 'unknown';

    // Namen
    $firstName = $mysqli->real_escape_string($player['internationalFirstName'] ?? '');
    $lastName = $mysqli->real_escape_string($player['internationalLastName'] ?? '');
    $fullName = $mysqli->real_escape_string(trim("{$firstName} {$lastName}"));

    // Geburtsdaten
    $dateOfBirth = $player['dateOfBirth'] ?? null;
    $placeOfBirth = $mysqli->real_escape_string($player['placeOfBirth'] ?? '');
    $countryOfBirth = $player['countryOfBirthFIFA'] ?? $player['countryOfBirth'] ?? null;

    // Nationalität
    $nationality = $player['nationality'] ?? '';
    $nationalityCode = $player['nationalityFIFA'] ?? $player['nationality'] ?? '';

    // Gender
    $gender = strtolower($player['gender'] ?? 'MALE');

    // Local Names (JSON)
    $localNames = null;
    if (!empty($player['localPersonNames'])) {
        $localNames = $mysqli->real_escape_string(json_encode($player['localPersonNames']));
    }

    // Prüfe ob Spieler existiert
    $checkQuery = "SELECT id FROM comet_players WHERE person_fifa_id = {$personFifaId}";
    $result = $mysqli->query($checkQuery);
    $exists = $result->num_rows > 0;

    if ($exists) {
        // UPDATE
        $sql = "UPDATE comet_players SET
            club_fifa_id = {$clubFifaId},
            name = '{$fullName}',
            first_name = '{$firstName}',
            last_name = '{$lastName}',
            date_of_birth = " . ($dateOfBirth ? "'{$dateOfBirth}'" : "NULL") . ",
            place_of_birth = '{$placeOfBirth}',
            country_of_birth = " . ($countryOfBirth ? "'{$countryOfBirth}'" : "NULL") . ",
            gender = '{$gender}',
            nationality = '{$nationality}',
            nationality_code = '{$nationalityCode}',
            position = '{$position}',
            local_names = " . ($localNames ? "'{$localNames}'" : "NULL") . ",
            is_synced = 1,
            last_synced_at = NOW(),
            updated_at = NOW()
        WHERE person_fifa_id = {$personFifaId}";

        if ($mysqli->query($sql)) {
            $updated++;
        } else {
            echo "❌ UPDATE Fehler: " . $mysqli->error . "\n";
            $errors++;
        }
    } else {
        // INSERT
        $sql = "INSERT INTO comet_players (
            club_fifa_id, person_fifa_id, name, first_name, last_name,
            date_of_birth, place_of_birth, country_of_birth, gender,
            nationality, nationality_code, position, local_names,
            status, is_synced, last_synced_at, created_at, updated_at
        ) VALUES (
            {$clubFifaId},
            {$personFifaId},
            '{$fullName}',
            '{$firstName}',
            '{$lastName}',
            " . ($dateOfBirth ? "'{$dateOfBirth}'" : "NULL") . ",
            '{$placeOfBirth}',
            " . ($countryOfBirth ? "'{$countryOfBirth}'" : "NULL") . ",
            '{$gender}',
            '{$nationality}',
            '{$nationalityCode}',
            '{$position}',
            " . ($localNames ? "'{$localNames}'" : "NULL") . ",
            'active',
            1,
            NOW(),
            NOW(),
            NOW()
        )";

        if ($mysqli->query($sql)) {
            $inserted++;
        } else {
            echo "❌ INSERT Fehler: " . $mysqli->error . "\n";
            $errors++;
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ SPIELER SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - API Spieler: " . count($players) . "\n";
echo "   - Neu eingefügt: {$inserted}\n";
echo "   - Aktualisiert: {$updated}\n";
echo "   - Fehler: {$errors}\n\n";

// Jetzt berechne Statistiken aus Match Events
echo "📊 Berechne Spieler-Statistiken aus Match Events...\n\n";

// Tore pro Spieler
$goalsQuery = "
    UPDATE comet_players cp
    LEFT JOIN (
        SELECT player_fifa_id, COUNT(*) as goals
        FROM comet_match_events
        WHERE event_type IN ('goal', 'penalty_goal')
        AND player_fifa_id IS NOT NULL
        GROUP BY player_fifa_id
    ) goals ON cp.person_fifa_id = goals.player_fifa_id
    SET cp.total_goals = COALESCE(goals.goals, 0),
        cp.season_goals = COALESCE(goals.goals, 0)
    WHERE cp.club_fifa_id = {$clubFifaId}
";

if ($mysqli->query($goalsQuery)) {
    echo "✅ Tore aktualisiert\n";
} else {
    echo "❌ Tore Fehler: " . $mysqli->error . "\n";
}

// Gelbe Karten pro Spieler
$yellowCardsQuery = "
    UPDATE comet_players cp
    LEFT JOIN (
        SELECT player_fifa_id, COUNT(*) as yellows
        FROM comet_match_events
        WHERE event_type = 'yellow_card'
        AND player_fifa_id IS NOT NULL
        GROUP BY player_fifa_id
    ) cards ON cp.person_fifa_id = cards.player_fifa_id
    SET cp.total_yellow_cards = COALESCE(cards.yellows, 0),
        cp.season_yellow_cards = COALESCE(cards.yellows, 0)
    WHERE cp.club_fifa_id = {$clubFifaId}
";

if ($mysqli->query($yellowCardsQuery)) {
    echo "✅ Gelbe Karten aktualisiert\n";
} else {
    echo "❌ Gelbe Karten Fehler: " . $mysqli->error . "\n";
}

// Rote Karten pro Spieler
$redCardsQuery = "
    UPDATE comet_players cp
    LEFT JOIN (
        SELECT player_fifa_id, COUNT(*) as reds
        FROM comet_match_events
        WHERE event_type IN ('red_card', 'yellow_red_card')
        AND player_fifa_id IS NOT NULL
        GROUP BY player_fifa_id
    ) cards ON cp.person_fifa_id = cards.player_fifa_id
    SET cp.total_red_cards = COALESCE(cards.reds, 0),
        cp.season_red_cards = COALESCE(cards.reds, 0)
    WHERE cp.club_fifa_id = {$clubFifaId}
";

if ($mysqli->query($redCardsQuery)) {
    echo "✅ Rote Karten aktualisiert\n";
} else {
    echo "❌ Rote Karten Fehler: " . $mysqli->error . "\n";
}

// Anzahl Matches (aus Match Players wenn vorhanden, sonst aus Events schätzen)
$matchesQuery = "
    UPDATE comet_players cp
    LEFT JOIN (
        SELECT player_fifa_id, COUNT(DISTINCT match_fifa_id) as matches
        FROM comet_match_events
        WHERE player_fifa_id IS NOT NULL
        GROUP BY player_fifa_id
    ) matches ON cp.person_fifa_id = matches.player_fifa_id
    SET cp.total_matches = COALESCE(matches.matches, 0),
        cp.season_matches = COALESCE(matches.matches, 0)
    WHERE cp.club_fifa_id = {$clubFifaId}
";

if ($mysqli->query($matchesQuery)) {
    echo "✅ Matches aktualisiert\n";
} else {
    echo "❌ Matches Fehler: " . $mysqli->error . "\n";
}

echo "\n📈 Top 10 Torschützen:\n";
echo str_repeat("-", 60) . "\n";

$topScorersQuery = "
    SELECT name, position, total_goals, total_matches, total_yellow_cards, total_red_cards
    FROM comet_players
    WHERE club_fifa_id = {$clubFifaId}
    AND total_goals > 0
    ORDER BY total_goals DESC
    LIMIT 10
";

$result = $mysqli->query($topScorersQuery);
$rank = 1;
while ($row = $result->fetch_assoc()) {
    echo sprintf(
        "%2d. %-30s %s | %2d Tore | %2d Spiele | %d Gelb | %d Rot\n",
        $rank++,
        $row['name'],
        str_pad($row['position'], 12),
        $row['total_goals'],
        $row['total_matches'],
        $row['total_yellow_cards'],
        $row['total_red_cards']
    );
}

$mysqli->close();
