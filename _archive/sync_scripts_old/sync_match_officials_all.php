<?php

// SYNC MATCH OFFICIALS - Schiedsrichter für alle gespielten Matches
echo "⏱️  SYNC MATCH OFFICIALS - ALLE GESPIELTEN MATCHES\n";
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
$teamFifaId = null; // not used in ALL-mode

// Hole alle gespielten Matches (ALLE Clubs)
$matchesQuery = "
    SELECT match_fifa_id
    FROM comet_matches
    WHERE match_status = 'played'
    ORDER BY match_fifa_id DESC
";
$matchesResult = $mysqli->query($matchesQuery);

if (!$matchesResult) {
    die("Query Error: " . $mysqli->error . "\n");
}

$matches = [];
while ($row = $matchesResult->fetch_assoc()) {
    $matches[] = $row['match_fifa_id'];
}

echo "📊 Gespielte Matches gefunden: " . count($matches) . "\n";
echo "📊 Erwartete Officials: ca. " . (count($matches) * 3) . " - " . (count($matches) * 4) . " Einträge\n\n";

// Lösche alte Daten
echo "🗑️  Lösche alte Match Officials...\n";
$deleteQuery = "TRUNCATE TABLE comet_match_officials";
$mysqli->query($deleteQuery);
echo "✅ Alte Daten gelöscht\n\n";

$totalProcessed = 0;
$totalOfficials = 0;
$totalInserted = 0;
$errors = 0;

// Sync Officials für jedes Match
foreach ($matches as $matchFifaId) {

    // Progress indicator every 50 matches
    if ($totalProcessed % 50 == 0 && $totalProcessed > 0) {
        echo "\n📊 Progress: {$totalProcessed}/" . count($matches) . " matches, {$totalInserted} officials inserted...\n\n";
    }

    echo "📥 Match {$matchFifaId}...";

    // API Call
    $ch = curl_init("{$apiUrl}/match/{$matchFifaId}/officials");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        echo " ❌ HTTP {$httpCode}\n";
        $errors++;
        $totalProcessed++;
        continue;
    }

    $officials = json_decode($response, true);
    if (empty($officials)) {
        echo " ⚠️  Keine Officials\n";
        $totalProcessed++;
        continue;
    }

    $officialsInMatch = 0;

    foreach ($officials as $officialData) {
        $personFifaId = $officialData['personFifaId'] ?? null;
        $role = $officialData['role'] ?? null;

        if (!$personFifaId || !$role) {
            continue;
        }

        $person = $officialData['person'] ?? [];

        // Extract names
        $intFirstName = $mysqli->real_escape_string($person['internationalFirstName'] ?? '');
        $intLastName = $mysqli->real_escape_string($person['internationalLastName'] ?? '');
        $localFirstName = $mysqli->real_escape_string($person['localFirstName'] ?? '');
        $localLastName = $mysqli->real_escape_string($person['localLastName'] ?? '');

        $roleDescription = $mysqli->real_escape_string($officialData['roleDescription'] ?? '');
        $cometRoleName = $mysqli->real_escape_string($officialData['cometRoleName'] ?? '');

        $gender = $person['gender'] ?? 'MALE';
        $dateOfBirth = $person['dateOfBirth'] ?? null;
        $nationality = $person['nationality'] ?? null;
        $nationalityFifa = $person['nationalityFIFA'] ?? null;

        $countryOfBirth = $person['countryOfBirth'] ?? null;
        $countryOfBirthFifa = $person['countryOfBirthFIFA'] ?? null;
        $regionOfBirth = $mysqli->real_escape_string($person['regionOfBirth'] ?? '');
        $placeOfBirth = $mysqli->real_escape_string($person['placeOfBirth'] ?? '');
        $currentPlace = $mysqli->real_escape_string($person['place'] ?? '');

        // Insert into database
        $insertQuery = "INSERT INTO comet_match_officials (
            match_fifa_id,
            person_fifa_id,
            international_first_name,
            international_last_name,
            local_first_name,
            local_last_name,
            role,
            role_description,
            comet_role_name,
            gender,
            date_of_birth,
            nationality,
            nationality_fifa,
            country_of_birth,
            country_of_birth_fifa,
            region_of_birth,
            place_of_birth,
            current_place,
            created_at,
            updated_at
        ) VALUES (
            {$matchFifaId},
            {$personFifaId},
            '{$intFirstName}',
            '{$intLastName}',
            '{$localFirstName}',
            '{$localLastName}',
            '{$role}',
            '{$roleDescription}',
            '{$cometRoleName}',
            '{$gender}',
            " . ($dateOfBirth ? "'{$dateOfBirth}'" : "NULL") . ",
            " . ($nationality ? "'{$nationality}'" : "NULL") . ",
            " . ($nationalityFifa ? "'{$nationalityFifa}'" : "NULL") . ",
            " . ($countryOfBirth ? "'{$countryOfBirth}'" : "NULL") . ",
            " . ($countryOfBirthFifa ? "'{$countryOfBirthFifa}'" : "NULL") . ",
            '{$regionOfBirth}',
            '{$placeOfBirth}',
            '{$currentPlace}',
            NOW(),
            NOW()
        ) ON DUPLICATE KEY UPDATE
            international_first_name = VALUES(international_first_name),
            international_last_name = VALUES(international_last_name),
            updated_at = NOW()";

        if ($mysqli->query($insertQuery)) {
            $totalInserted++;
            $officialsInMatch++;
        }
    }

    echo " ✅ {$officialsInMatch} Official(s)\n";
    $totalProcessed++;
    $totalOfficials += $officialsInMatch;

    // Kurze Pause
    usleep(100000); // 100ms
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ MATCH OFFICIALS SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Officials eingefügt: {$totalInserted}\n";
echo "   - Fehler: {$errors}\n\n";

// Zeige Statistik nach Rolle
$statsQuery = "SELECT
    role,
    role_description,
    COUNT(*) as count,
    COUNT(DISTINCT person_fifa_id) as unique_persons
    FROM comet_match_officials
    GROUP BY role, role_description
    ORDER BY count DESC";
$statsResult = $mysqli->query($statsQuery);

echo "📈 OFFICIALS NACH ROLLE:\n";
echo str_repeat("-", 60) . "\n";
while ($stat = $statsResult->fetch_assoc()) {
    $role = str_pad($stat['role'], 25);
    $count = str_pad($stat['count'], 5, ' ', STR_PAD_LEFT);
    $unique = str_pad($stat['unique_persons'], 5, ' ', STR_PAD_LEFT);
    echo "{$role}: {$count} Einsätze ({$unique} Personen)\n";
}

// Zeige Top Schiedsrichter
echo "\n📋 TOP 10 SCHIEDSRICHTER (nach Einsätzen):\n";
echo str_repeat("-", 60) . "\n";
$topQuery = "SELECT
    CONCAT(international_first_name, ' ', international_last_name) as name,
    COUNT(*) as matches,
    nationality_fifa
    FROM comet_match_officials
    WHERE role = 'REFEREE'
    GROUP BY person_fifa_id, name, nationality_fifa
    ORDER BY matches DESC
    LIMIT 10";
$topResult = $mysqli->query($topQuery);
$pos = 1;
while ($ref = $topResult->fetch_assoc()) {
    $name = str_pad($ref['name'], 30);
    $matches = str_pad($ref['matches'], 4, ' ', STR_PAD_LEFT);
    $country = $ref['nationality_fifa'] ?? 'N/A';
    echo "{$pos}. {$name} - {$matches} Spiele ({$country})\n";
    $pos++;
}

$mysqli->close();
