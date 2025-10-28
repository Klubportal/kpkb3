<?php

// SYNC TEAM OFFICIALS (Coaches + Club Representatives + Static Team Officials)
// Für eine Liste von club_fifa_id/team_fifa_id Werten

echo "👔 SYNC TEAM OFFICIALS - Mehrere Vereine\n";
echo str_repeat("=", 70) . "\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) die("DB connect error: " . $mysqli->connect_error);
$mysqli->set_charset('utf8mb4');

$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Liste der Teams/Vereine – anpassbar/parametrisierbar
$clubs = [598, 396, 601];

$totalInsertedCoaches = 0;
$totalInsertedReps = 0;
$totalInsertedTeamOfficials = 0;
$totalErrors = 0;

foreach ($clubs as $teamFifaId) {
    echo "\n🏷️  Team {$teamFifaId}\n";

    // API Call
    $ch = curl_init("{$apiUrl}/team/{$teamFifaId}/teamOfficials");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        echo "❌ API Fehler: HTTP {$httpCode}\n";
        $totalErrors++;
        continue;
    }

    $officials = json_decode($response, true) ?: [];
    echo "📊 Officials: " . count($officials) . "\n";

    // Vorherige Datensätze optional gezielt entfernen
    $mysqli->query("DELETE FROM comet_coaches WHERE club_fifa_id = {$teamFifaId}");
    $mysqli->query("DELETE FROM comet_club_representatives WHERE club_fifa_id = {$teamFifaId}");
    $mysqli->query("DELETE FROM comet_team_officials WHERE team_fifa_id = {$teamFifaId}");

    foreach ($officials as $officialData) {
        $role = strtoupper($officialData['role'] ?? '');
        $status = strtoupper($officialData['status'] ?? 'ACTIVE');
        if ($status !== 'ACTIVE') continue; // nur aktive

        $person = $officialData['person'] ?? [];
        $personFifaId = $person['personFifaId'] ?? null;
        if (!$personFifaId) continue;

        // Names
        $intFirst = $mysqli->real_escape_string($person['internationalFirstName'] ?? '');
        $intLast  = $mysqli->real_escape_string($person['internationalLastName'] ?? '');
        $name     = trim($intFirst . ' ' . $intLast);
        $localName = '';
        if (!empty($person['localPersonNames'][0])) {
            $lFirst = $mysqli->real_escape_string($person['localPersonNames'][0]['firstName'] ?? '');
            $lLast  = $mysqli->real_escape_string($person['localPersonNames'][0]['lastName'] ?? '');
            $localName = trim($lFirst . ' ' . $lLast);
        }

        $dob = $person['dateOfBirth'] ?? null;
        $pob = $mysqli->real_escape_string($person['placeOfBirth'] ?? '');
        $nat = $mysqli->real_escape_string($person['nationality'] ?? '');
        $natCode = $mysqli->real_escape_string($person['nationalityFIFA'] ?? '');
        $roleDesc = $mysqli->real_escape_string($officialData['roleDescription'] ?? ($officialData['cometRoleName'] ?? ''));
        $cometRoleName = $mysqli->real_escape_string($officialData['cometRoleName'] ?? '');

        // Unterscheide Coach vs Club Representative anhand Role
        $isCoach = in_array($role, ['COACH','ASSISTANT_COACH','GOALKEEPER_COACH','FITNESS_COACH','HEAD_COACH','PHYSICAL_TRAINER']);
        if ($isCoach) {
            $sql = "INSERT INTO comet_coaches (\n                club_fifa_id, person_fifa_id, name, first_name, last_name, popular_name,\n                date_of_birth, place_of_birth, country_of_birth, gender,\n                nationality, nationality_code, role, role_description,\n                status, is_synced, local_names, last_synced_at, created_at, updated_at\n            ) VALUES (\n                {$teamFifaId}, {$personFifaId}, '{$name}', '{$intFirst}', '{$intLast}', '{$localName}',\n                " . ($dob ? "'{$dob}'" : "NULL") . ", '{$pob}', NULL, NULL,\n                '{$nat}', '{$natCode}', '{$role}', '{$roleDesc}',\n                'active', 1, NULL, NOW(), NOW(), NOW()\n            ) ON DUPLICATE KEY UPDATE\n                name=VALUES(name), first_name=VALUES(first_name), last_name=VALUES(last_name),\n                popular_name=VALUES(popular_name), role=VALUES(role), role_description=VALUES(role_description),\n                nationality=VALUES(nationality), nationality_code=VALUES(nationality_code), last_synced_at=NOW(), updated_at=NOW()";
            if ($mysqli->query($sql)) $totalInsertedCoaches++; else $totalErrors++;
        } else {
            $sql = "INSERT INTO comet_club_representatives (\n                club_fifa_id, person_fifa_id, name, first_name, last_name, popular_name,\n                date_of_birth, place_of_birth, country_of_birth, gender,\n                nationality, nationality_code, role, role_description,\n                status, is_synced, local_names, last_synced_at, created_at, updated_at\n            ) VALUES (\n                {$teamFifaId}, {$personFifaId}, '{$name}', '{$intFirst}', '{$intLast}', '{$localName}',\n                " . ($dob ? "'{$dob}'" : "NULL") . ", '{$pob}', NULL, NULL,\n                '{$nat}', '{$natCode}', '{$role}', '{$roleDesc}',\n                'active', 1, NULL, NOW(), NOW(), NOW()\n            ) ON DUPLICATE KEY UPDATE\n                name=VALUES(name), first_name=VALUES(first_name), last_name=VALUES(last_name),\n                popular_name=VALUES(popular_name), role=VALUES(role), role_description=VALUES(role_description),\n                nationality=VALUES(nationality), nationality_code=VALUES(nationality_code), last_synced_at=NOW(), updated_at=NOW()";
            if ($mysqli->query($sql)) $totalInsertedReps++; else $totalErrors++;
        }

        // Zusätzlich: in statische comet_team_officials schreiben
        $roleClean = $mysqli->real_escape_string($role);
        $sql2 = "INSERT INTO comet_team_officials (\n            team_fifa_id, person_fifa_id, competition_fifa_id, person_name, local_person_name,\n            international_first_name, international_last_name, role, role_description, comet_role_name, status,\n            date_of_birth, nationality, nationality_code, place_of_birth, last_synced_at, created_at, updated_at\n        ) VALUES (\n            {$teamFifaId}, {$personFifaId}, NULL, '{$name}', '{$localName}',\n            '{$intFirst}', '{$intLast}', '{$roleClean}', '{$roleDesc}', '{$cometRoleName}', 'ACTIVE',\n            " . ($dob ? "'{$dob}'" : "NULL") . ", '{$nat}', '{$natCode}', '{$pob}', NOW(), NOW(), NOW()\n        ) ON DUPLICATE KEY UPDATE\n            role=VALUES(role), role_description=VALUES(role_description), comet_role_name=VALUES(comet_role_name),\n            status='ACTIVE', last_synced_at=NOW(), updated_at=NOW()";
        if ($mysqli->query($sql2)) $totalInsertedTeamOfficials++; else $totalErrors++;
    }

    // small delay
    usleep(100000);
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ TEAM OFFICIALS SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Coaches eingefügt/aktualisiert: {$totalInsertedCoaches}\n";
echo "   - Representatives eingefügt/aktualisiert: {$totalInsertedReps}\n";
echo "   - Team Officials (static) eingefügt/aktualisiert: {$totalInsertedTeamOfficials}\n";
echo "   - Fehler: {$totalErrors}\n\n";

$mysqli->close();
