<?php

// SYNC TEAM COACHES - NK Prigorje (Team 598)
echo "👔 SYNC TEAM COACHES - NK PRIGORJE (Team 598)\n";
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

// Hole alle Matches von NK Prigorje
$matchesQuery = "SELECT match_fifa_id FROM comet_matches
                 WHERE team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId}
                 ORDER BY match_fifa_id DESC";
$matchesResult = $mysqli->query($matchesQuery);

if (!$matchesResult) {
    die("Query Error: " . $mysqli->error . "\n");
}

$matches = [];
while ($row = $matchesResult->fetch_assoc()) {
    $matches[] = $row['match_fifa_id'];
}

echo "📊 Gefundene Matches: " . count($matches) . "\n\n";

$totalProcessed = 0;
$coachesFound = [];
$errors = 0;

// Lösche alte Coaches von Team 598
echo "🗑️  Lösche alte Coaches...\n";
$mysqli->query("DELETE FROM comet_coaches WHERE club_fifa_id = {$teamFifaId}");
echo "✅ Alte Daten gelöscht\n\n";

// Sync Team Officials für jedes Match
foreach ($matches as $matchFifaId) {
    echo "📥 Match {$matchFifaId}...";

    // API Call
    $ch = curl_init("{$apiUrl}/match/{$matchFifaId}/teamOfficials");
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
        continue;
    }

    $data = json_decode($response, true);
    if (empty($data)) {
        echo " ⚠️  Keine Daten\n";
        continue;
    }

    $coachesInMatch = 0;

    // Beide Teams durchgehen
    foreach ($data as $teamData) {
        $teamFifaIdFromApi = $teamData['teamFifaId'] ?? null;
        $officials = $teamData['officials'] ?? [];

        // Nur Officials vom Team 598
        if ($teamFifaIdFromApi != $teamFifaId) {
            continue;
        }

        foreach ($officials as $officialData) {
            $role = strtoupper($officialData['role'] ?? '');

            // Nur Coaches syncen (COACH, ASSISTANT_COACH, etc.), nicht Club Representatives
            if (!in_array($role, ['COACH', 'ASSISTANT_COACH', 'GOALKEEPER_COACH', 'FITNESS_COACH'])) {
                continue;
            }

            $person = $officialData['person'] ?? [];
            $personFifaId = $person['personFifaId'] ?? null;

            if (!$personFifaId) {
                continue;
            }

            // Speichere Coach nur einmal (über alle Matches hinweg)
            if (isset($coachesFound[$personFifaId])) {
                continue;
            }

            $firstName = $person['internationalFirstName'] ?? '';
            $lastName = $person['internationalLastName'] ?? '';
            $name = trim($firstName . ' ' . $lastName);
            $dateOfBirth = $person['dateOfBirth'] ?? null;
            $placeOfBirth = $person['placeOfBirth'] ?? null;
            $countryOfBirth = $person['countryOfBirth'] ?? null;
            $gender = $person['gender'] ?? null;
            $nationality = $person['nationality'] ?? null;
            $nationalityFIFA = $person['nationalityFIFA'] ?? null;
            $roleDescription = $officialData['roleDescription'] ?? null;

            // Local Name
            $popularName = '';
            $localNamesJson = null;
            if (isset($person['localPersonNames'][0])) {
                $localFirst = $person['localPersonNames'][0]['firstName'] ?? '';
                $localLast = $person['localPersonNames'][0]['lastName'] ?? '';
                $popularName = trim($localFirst . ' ' . $localLast);
                $localNamesJson = json_encode($person['localPersonNames']);
            }

            // Insert Coach
            $stmt = $mysqli->prepare("
                INSERT INTO comet_coaches (
                    club_fifa_id, person_fifa_id, name, first_name, last_name, popular_name,
                    date_of_birth, place_of_birth, country_of_birth, gender,
                    nationality, nationality_code, role, role_description,
                    status, is_synced, local_names, last_synced_at, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1, ?, NOW(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    first_name = VALUES(first_name),
                    last_name = VALUES(last_name),
                    popular_name = VALUES(popular_name),
                    date_of_birth = VALUES(date_of_birth),
                    place_of_birth = VALUES(place_of_birth),
                    country_of_birth = VALUES(country_of_birth),
                    gender = VALUES(gender),
                    nationality = VALUES(nationality),
                    nationality_code = VALUES(nationality_code),
                    role = VALUES(role),
                    role_description = VALUES(role_description),
                    local_names = VALUES(local_names),
                    last_synced_at = NOW(),
                    updated_at = NOW()
            ");

            $stmt->bind_param(
                "iisssssssssssss",
                $teamFifaId, $personFifaId, $name, $firstName, $lastName, $popularName,
                $dateOfBirth, $placeOfBirth, $countryOfBirth, $gender,
                $nationality, $nationalityFIFA, $role, $roleDescription,
                $localNamesJson
            );

            if ($stmt->execute()) {
                $coachesFound[$personFifaId] = $name;
                $coachesInMatch++;
            } else {
                $errors++;
            }

            $stmt->close();
        }
    }

    if ($coachesInMatch > 0) {
        echo " ✅ {$coachesInMatch} Coach(es)\n";
    } else {
        echo " -\n";
    }

    $totalProcessed++;

    // Rate limiting
    usleep(100000); // 100ms pause
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ COACHES SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Coaches gefunden: " . count($coachesFound) . "\n";
echo "   - Fehler: {$errors}\n\n";

// Zeige alle Coaches
echo "👔 COACHES - NK PRIGORJE:\n";
echo str_repeat("-", 60) . "\n";

$coachesQuery = "
    SELECT name, role, role_description, date_of_birth, nationality
    FROM comet_coaches
    WHERE club_fifa_id = {$teamFifaId}
    ORDER BY
        CASE role
            WHEN 'COACH' THEN 1
            WHEN 'ASSISTANT_COACH' THEN 2
            WHEN 'GOALKEEPER_COACH' THEN 3
            WHEN 'FITNESS_COACH' THEN 4
            ELSE 5
        END,
        name
";

$result = $mysqli->query($coachesQuery);

while ($row = $result->fetch_assoc()) {
    $roleLabel = str_replace('_', ' ', $row['role']);
    echo sprintf("%-30s | %-20s | Geboren: %s (%s)\n",
        $row['name'],
        $roleLabel,
        $row['date_of_birth'] ?? 'N/A',
        $row['nationality'] ?? 'N/A'
    );
}

$mysqli->close();
