<?php

// SYNC TEAM COACHES - Alle aktiven Coaches direkt vom Team-Endpoint
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

// Lösche alte Coaches von Team 598
echo "🗑️  Lösche alte Coaches...\n";
$mysqli->query("DELETE FROM comet_coaches WHERE club_fifa_id = {$teamFifaId}");
echo "✅ Alte Daten gelöscht\n\n";

$totalCoaches = 0;
$totalOthers = 0;
$inserted = 0;
$errors = 0;

echo "📋 Verarbeite Officials:\n";
echo str_repeat("-", 60) . "\n";

foreach ($officials as $officialData) {
    $role = strtoupper($officialData['role'] ?? '');
    $status = strtoupper($officialData['status'] ?? 'ACTIVE');

    // Prüfe ob es ein Coach ist (nicht Club Representative)
    $isCoach = in_array($role, ['COACH', 'ASSISTANT_COACH', 'GOALKEEPER_COACH', 'FITNESS_COACH', 'HEAD_COACH']);

    if ($isCoach) {
        $totalCoaches++;
    } else {
        $totalOthers++;
    }

    // Nur aktive Coaches syncen
    if (!$isCoach || $status != 'ACTIVE') {
        continue;
    }

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
        echo "✅ {$name} ({$role})\n";
        $inserted++;
    } else {
        echo "❌ Fehler bei {$name}: " . $stmt->error . "\n";
        $errors++;
    }

    $stmt->close();
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ COACHES SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Gesamt Officials: " . count($officials) . "\n";
echo "   - Coaches: {$totalCoaches}\n";
echo "   - Andere (Representatives etc.): {$totalOthers}\n";
echo "   - Eingefügt: {$inserted}\n";
echo "   - Fehler: {$errors}\n\n";

// Zeige alle Coaches
echo "👔 COACHES - NK PRIGORJE:\n";
echo str_repeat("-", 60) . "\n";

$coachesQuery = "
    SELECT name, role, role_description, date_of_birth, nationality, created_at
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

    echo sprintf("%-30s | %-20s | %s (%d Jahre) %s\n",
        $row['name'],
        $roleLabel,
        $row['nationality'] ?? 'N/A',
        $age ?? 0,
        $row['date_of_birth'] ? "(*{$row['date_of_birth']})" : ''
    );
}

$mysqli->close();
