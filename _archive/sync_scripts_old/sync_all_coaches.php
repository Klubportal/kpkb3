<?php
// SYNC ALL COACHES FOR ALL CLUBS FROM COMET API
// Erstellt: 2025-10-27

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Alle club_fifa_id aus comet_clubs_extended holen
$clubs = $mysqli->query("SELECT club_fifa_id, name FROM comet_clubs_extended");
if (!$clubs) {
    die("Fehler beim Laden der Clubs: " . $mysqli->error);
}

$totalClubs = 0;
$totalCoaches = 0;
$foundFields = [];

while ($club = $clubs->fetch_assoc()) {
    $clubFifaId = $club['club_fifa_id'];
    $clubName = $club['name'];
    $totalClubs++;
    echo "\n==============================\n";
    echo "Verein: {$clubName} [{$clubFifaId}]\n";

    // API Call
    $ch = curl_init("{$apiUrl}/team/{$clubFifaId}/teamOfficials?status=ALL");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode != 200) {
        echo "❌ API Fehler: HTTP {$httpCode}\n";
        continue;
    }
    $officials = json_decode($response, true);
    if (empty($officials)) {
        echo "⚠️  Keine Officials gefunden\n";
        continue;
    }
    // Lösche alte Coaches für diesen Club
    $mysqli->query("DELETE FROM comet_coaches WHERE club_fifa_id = {$clubFifaId}");
    foreach ($officials as $officialData) {
        $role = strtoupper($officialData['role'] ?? '');
        $status = strtoupper($officialData['status'] ?? 'ACTIVE');
        $isCoach = in_array($role, ['COACH', 'ASSISTANT_COACH', 'GOALKEEPER_COACH', 'FITNESS_COACH', 'HEAD_COACH']);
        if (!$isCoach || $status != 'ACTIVE') continue;
        $person = $officialData['person'] ?? [];
        $personFifaId = $person['personFifaId'] ?? null;
        if (!$personFifaId) continue;
        $firstName = $person['internationalFirstName'] ?? '';
        $lastName = $person['internationalLastName'] ?? '';
        $name = trim($firstName . ' ' . $lastName);
        $dateOfBirth = $person['dateOfBirth'] ?? null;
        $placeOfBirth = $person['placeOfBirth'] ?? null;
        $countryOfBirth = $person['countryOfBirth'] ?? null;
        $gender = $person['gender'] ?? null;
        $nationality = $person['nationality'] ?? null;
        $nationalityFIFA = $person['nationalityFIFA'] ?? null;
        $roleDescription = $officialData['roleDescription'] ?? $officialData['cometRoleName'] ?? null;
        $popularName = '';
        $localNamesJson = null;
        if (isset($person['localPersonNames'][0])) {
            $localFirst = $person['localPersonNames'][0]['firstName'] ?? '';
            $localLast = $person['localPersonNames'][0]['lastName'] ?? '';
            $popularName = trim($localFirst . ' ' . $localLast);
            $localNamesJson = json_encode($person['localPersonNames']);
        }
        // Prüfe auf weitere Felder (z.B. email, phone)
        foreach ($person as $k => $v) {
            $foundFields[$k] = true;
        }
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
            $clubFifaId, $personFifaId, $name, $firstName, $lastName, $popularName,
            $dateOfBirth, $placeOfBirth, $countryOfBirth, $gender,
            $nationality, $nationalityFIFA, $role, $roleDescription,
            $localNamesJson
        );
        if ($stmt->execute()) {
            echo "✅ {$name} ({$role})\n";
            $totalCoaches++;
        } else {
            echo "❌ Fehler bei {$name}: " . $stmt->error . "\n";
        }
        $stmt->close();
    }
}

// Zeige alle gefundenen Felder aus der API
if (!empty($foundFields)) {
    echo "\nGefundene Felder in den API-Responses (person):\n";
    foreach (array_keys($foundFields) as $field) {
        echo "- {$field}\n";
    }
}

$mysqli->close();
echo "\nFERTIG! Synchronisiert: {$totalCoaches} Coaches aus {$totalClubs} Vereinen.\n";
