<?php

require_once __DIR__ . '/lib/sync_helpers.php';

// SYNC MATCH TEAM OFFICIALS
echo "⏱️  SYNC MATCH TEAM OFFICIALS\n";
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

// Competition IDs wo NK Prigorje teilnimmt
$competitionsQuery = "
    SELECT DISTINCT competition_fifa_id
    FROM comet_matches
    WHERE team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId}
";
$competitionsResult = $mysqli->query($competitionsQuery);
$compIds = [];
while ($row = $competitionsResult->fetch_assoc()) {
    $compIds[] = $row['competition_fifa_id'];
}
$compIdsList = implode(',', $compIds);

// Hole gespielte Matches
$matchesQuery = "
    SELECT match_fifa_id
    FROM comet_matches
    WHERE competition_fifa_id IN ({$compIdsList})
      AND match_status = 'played'
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
echo "📊 Erwartete Team Officials: ca. " . (count($matches) * 2) . " Einträge\n\n";

// Lösche alte Daten
echo "🗑️  Lösche alte Match Team Officials...\n";
$deleteQuery = "TRUNCATE TABLE comet_match_team_officials";
$mysqli->query($deleteQuery);
echo "✅ Alte Daten gelöscht\n\n";

$totalProcessed = 0;
$totalOfficials = 0;
$statsInserted = 0;
$statsUpdated = 0;
$statsSkipped = 0;
$errors = 0;
$noData = 0;

// Sync Team Officials für jedes Match
foreach ($matches as $matchFifaId) {

    // Progress indicator every 50 matches
    if ($totalProcessed % 50 == 0 && $totalProcessed > 0) {
        echo "\n📊 Progress: {$totalProcessed}/" . count($matches) . " matches, I:{$statsInserted} U:{$statsUpdated} S:{$statsSkipped}...\n\n";
    }

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
        $totalProcessed++;
        continue;
    }

    $teams = json_decode($response, true);
    if (empty($teams)) {
        echo " ⚠️  Keine Daten\n";
        $noData++;
        $totalProcessed++;
        continue;
    }

    $officialsInMatch = 0;

    // Loop durch beide Teams
    foreach ($teams as $teamData) {
        $teamFifaId = $teamData['teamFifaId'] ?? null;
        $teamNature = $teamData['teamNature'] ?? null; // HOME or AWAY
        $officials = $teamData['officials'] ?? [];

        foreach ($officials as $official) {
            $personFifaId = $official['personFifaId'] ?? null;
            $role = $official['role'] ?? null;

            if (!$personFifaId || !$role || !$teamFifaId) continue;

            $person = $official['person'] ?? [];
            $personName = $mysqli->real_escape_string(($person['internationalFirstName'] ?? '') . ' ' . ($person['internationalLastName'] ?? ''));
            $localPersonName = $mysqli->real_escape_string(($person['localFirstName'] ?? '') . ' ' . ($person['localLastName'] ?? ''));

            $roleDescription = $mysqli->real_escape_string($official['roleDescription'] ?? '');
            $cometRoleName = $mysqli->real_escape_string($official['cometRoleName'] ?? '');
            $cometRoleNameKey = $mysqli->real_escape_string($official['cometRoleNameKey'] ?? '');

            // Match Events für Karten
            $matchEvents = $official['matchEvents'] ?? [];
            $yellowCards = 0;
            $redCards = 0;

            foreach ($matchEvents as $event) {
                if ($event['event'] == 'yellow_card') $yellowCards++;
                if ($event['event'] == 'red_card') $redCards++;
            }

            // Prepare data for upsert_if_changed
            $keyCols = [
                'match_fifa_id' => $matchFifaId,
                'person_fifa_id' => $personFifaId
            ];

            $data = [
                'match_fifa_id' => $matchFifaId,
                'team_fifa_id' => $teamFifaId,
                'person_fifa_id' => $personFifaId,
                'person_name' => ($person['internationalFirstName'] ?? '') . ' ' . ($person['internationalLastName'] ?? ''),
                'local_person_name' => ($person['localFirstName'] ?? '') . ' ' . ($person['localLastName'] ?? ''),
                'team_nature' => $teamNature,
                'role' => $role,
                'role_description' => $official['roleDescription'] ?? '',
                'comet_role_name' => $official['cometRoleName'] ?? '',
                'comet_role_name_key' => $official['cometRoleNameKey'] ?? '',
                'yellow_cards' => $yellowCards,
                'red_cards' => $redCards
                // Note: created_at/updated_at will be set by upsert_if_changed only on insert/update
            ];

            try {
                $result = upsert_if_changed($mysqli, 'comet_match_team_officials', $keyCols, $data);
                if ($result['action'] === 'inserted') {
                    $statsInserted++;
                    $officialsInMatch++;
                } elseif ($result['action'] === 'updated') {
                    $statsUpdated++;
                    $officialsInMatch++;
                } else {
                    $statsSkipped++;
                }
            } catch (Exception $e) {
                echo " ⚠️  Error upserting official {$personFifaId}: " . $e->getMessage() . "\n";
                $errors++;
            }
        }
    }

    echo " ✅ {$officialsInMatch} Official(s)\n";
    $totalProcessed++;
    $totalOfficials += $officialsInMatch;

    // Pause
    usleep(100000); // 100ms
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ MATCH TEAM OFFICIALS SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Officials eingefügt: {$statsInserted}\n";
echo "   - Officials aktualisiert: {$statsUpdated}\n";
echo "   - Officials übersprungen (unverändert): {$statsSkipped}\n";
echo "   - Keine Daten: {$noData}\n";
echo "   - Fehler: {$errors}\n\n";

// Finale Statistik
$finalCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_match_team_officials")->fetch_assoc()['count'];

// Nach Rolle gruppiert
$roleQuery = "SELECT role, role_description, COUNT(*) as count, COUNT(DISTINCT person_fifa_id) as unique_persons
              FROM comet_match_team_officials
              GROUP BY role, role_description
              ORDER BY count DESC";
$roleResult = $mysqli->query($roleQuery);

echo "📊 FINALE STATISTIK:\n";
echo "   - Total Match Team Officials: {$finalCount}\n\n";

echo "📈 NACH ROLLE:\n";
echo str_repeat("-", 60) . "\n";
while ($row = $roleResult->fetch_assoc()) {
    $role = str_pad($row['role'], 25);
    $count = str_pad($row['count'], 5, ' ', STR_PAD_LEFT);
    $unique = str_pad($row['unique_persons'], 5, ' ', STR_PAD_LEFT);
    echo "{$role}: {$count} Einsätze ({$unique} Personen)\n";
}

$mysqli->close();
