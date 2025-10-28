<?php

// SYNC TEAM COACHES & REPRESENTATIVES - Alle aktiven Officials
echo "👔 SYNC TEAM OFFICIALS - NK PRIGORJE (Team 598)\n";
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

echo "📥 Hole alle Team Officials von Team {$teamFifaId}...\n";

// API Call - Direkt Team Officials (alle aktiven!)
$ch = curl_init("{$apiUrl}/team/{$teamFifaId}/teamOfficials");
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

$officials = json_decode($response, true);

if (empty($officials)) {
    die("⚠️  Keine Officials gefunden\n");
}

echo "📊 API Officials gefunden: " . count($officials) . "\n\n";

// Lösche alte Daten
echo "🗑️  Lösche alte Daten...\n";
$mysqli->query("DELETE FROM comet_coaches WHERE club_fifa_id = {$teamFifaId}");
$mysqli->query("DELETE FROM comet_club_representatives WHERE club_fifa_id = {$teamFifaId}");
echo "✅ Alte Daten gelöscht\n\n";

$totalCoaches = 0;
$totalRepresentatives = 0;
$insertedCoaches = 0;
$insertedRepresentatives = 0;
$errors = 0;

echo "📋 Verarbeite Officials:\n";
echo str_repeat("-", 60) . "\n";

foreach ($officials as $officialData) {
    $role = strtoupper($officialData['role'] ?? '');
    $status = strtoupper($officialData['status'] ?? 'ACTIVE');

    // Nur aktive Officials
    if ($status != 'ACTIVE') {
        continue;
    }

    // Prüfe ob es ein Coach ist
    $isCoach = in_array($role, ['COACH', 'ASSISTANT_COACH', 'GOALKEEPER_COACH', 'FITNESS_COACH', 'HEAD_COACH']);

    $person = $officialData['person'] ?? [];
    $personFifaId = $person['personFifaId'] ?? null;

    if (!$personFifaId) {
        echo "⚠️  Official ohne personFifaId übersprungen\n";
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
    $roleDescription = $officialData['roleDescription'] ?? $officialData['cometRoleName'] ?? null;

    // Local Name
    $popularName = '';
    $localNamesJson = null;
    if (isset($person['localPersonNames'][0])) {
        $localFirst = $person['localPersonNames'][0]['firstName'] ?? '';
        $localLast = $person['localPersonNames'][0]['lastName'] ?? '';
        $popularName = trim($localFirst . ' ' . $localLast);
        $localNamesJson = json_encode($person['localPersonNames']);
    }

    // Wähle die richtige Tabelle
    $tableName = $isCoach ? 'comet_coaches' : 'comet_club_representatives';

    // Insert in entsprechende Tabelle
    $stmt = $mysqli->prepare("
        INSERT INTO {$tableName} (
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
        if ($isCoach) {
            echo "✅ COACH: {$name} ({$role})\n";
            $totalCoaches++;
            $insertedCoaches++;
        } else {
            echo "✅ REPRESENTATIVE: {$name} ({$role})\n";
            $totalRepresentatives++;
            $insertedRepresentatives++;
        }
    } else {
        echo "❌ Fehler bei {$name}: " . $stmt->error . "\n";
        $errors++;
    }

    $stmt->close();
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ TEAM OFFICIALS SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Gesamt Officials: " . count($officials) . "\n";
echo "   - Coaches: {$totalCoaches}\n";
echo "   - Club Representatives: {$totalRepresentatives}\n";
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
            WHEN 'HEAD_COACH' THEN 1
            WHEN 'COACH' THEN 2
            WHEN 'ASSISTANT_COACH' THEN 3
            WHEN 'GOALKEEPER_COACH' THEN 4
            WHEN 'FITNESS_COACH' THEN 5
            ELSE 6
        END,
        name
";

$result = $mysqli->query($coachesQuery);

while ($row = $result->fetch_assoc()) {
    $roleLabel = str_replace('_', ' ', $row['role']);
    $age = null;
    if ($row['date_of_birth']) {
        $birthDate = new DateTime($row['date_of_birth']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
    }

    echo sprintf("%-30s | %-20s | %s (%d Jahre)\n",
        $row['name'],
        $roleLabel,
        $row['nationality'] ?? 'N/A',
        $age ?? 0
    );
}

// Zeige alle Representatives
echo "\n📋 CLUB REPRESENTATIVES - NK PRIGORJE:\n";
echo str_repeat("-", 60) . "\n";

$repsQuery = "
    SELECT name, role, role_description, date_of_birth, nationality
    FROM comet_club_representatives
    WHERE club_fifa_id = {$teamFifaId}
    ORDER BY name
";

$result = $mysqli->query($repsQuery);

while ($row = $result->fetch_assoc()) {
    $roleLabel = $row['role_description'] ?? str_replace('_', ' ', $row['role']);
    $age = null;
    if ($row['date_of_birth']) {
        $birthDate = new DateTime($row['date_of_birth']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
    }

    echo sprintf("%-30s | %-35s | %s (%d Jahre)\n",
        $row['name'],
        $roleLabel,
        $row['nationality'] ?? 'N/A',
        $age ?? 0
    );
}

$mysqli->close();
