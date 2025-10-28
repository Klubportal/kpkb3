<?php

echo "\n" . str_repeat("═", 80) . "\n";
echo "📥 WETTBEWERBE IN DATENBANK EINFÜGEN\n";
echo str_repeat("═", 80) . "\n\n";

// Connect to databases
try {
    $sourcePdo = new PDO('mysql:host=localhost;dbname=kp_server;charset=utf8mb4', 'root', '');
    $targetPdo = new PDO('mysql:host=localhost;dbname=kp_club_management;charset=utf8mb4', 'root', '');

    $sourcePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $targetPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "❌ Datenbankfehler: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Get all competitions from source
$sql = "
    SELECT DISTINCT
        competition_fifa_id,
        international_competition_name,
        season,
        competition_status,
        age_category
    FROM kp_server.matches
    WHERE team_fifa_id_home = '598' OR team_fifa_id_away = '598'
    ORDER BY international_competition_name
";

$stmt = $sourcePdo->query($sql);
$competitions = $stmt->fetchAll(PDO::FETCH_OBJ);

echo "✅ " . count($competitions) . " Wettbewerbe gelesen aus kp_server\n\n";

// 2. Clear existing competitions
echo "🗑️  Lösche alte Wettbewerbe aus kp_club_management.competitions...\n";
$deleteStmt = $targetPdo->prepare("DELETE FROM competitions");
$deleteStmt->execute();
$deleted = $deleteStmt->rowCount();
echo "   Gelöschte Einträge: " . $deleted . "\n\n";

// 3. Insert new competitions
echo "📝 Füge neue Wettbewerbe ein...\n";
$insertStmt = $targetPdo->prepare("
    INSERT INTO competitions (comet_id, name, slug, season, status, type, created_at, updated_at)
    VALUES (:comet_id, :name, :slug, :season, :status, :type, :created_at, :updated_at)
");

$now = date('Y-m-d H:i:s');
$insertedCount = 0;

foreach ($competitions as $idx => $comp) {
    try {
        // Determine type based on name
        $type = (stripos($comp->international_competition_name, 'cup') !== false ||
                 stripos($comp->international_competition_name, 'kup') !== false) ? 'cup' : 'league';

        // Convert status to enum value
        $status = strtolower($comp->competition_status);
        if ($status === 'active') {
            $status = 'active';
        } elseif ($status === 'scheduled' || $status === 'upcoming') {
            $status = 'upcoming';
        } else {
            $status = 'completed';
        }

        // Generate slug from FIFA ID and name
        $slug = strtolower(str_replace(['ć', 'č', 'š', 'ž', 'đ', ' ', '"', "'"], ['c', 'c', 's', 'z', 'd', '-', '', ''],
                    preg_replace('/[^a-z0-9ćčšžđ\s\-]/i', '', $comp->international_competition_name)));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-') . '-' . $comp->competition_fifa_id;

        $insertStmt->execute([
            ':comet_id' => (string)$comp->competition_fifa_id,
            ':name' => $comp->international_competition_name,
            ':slug' => $slug,
            ':season' => (int)$comp->season,
            ':status' => $status,
            ':type' => $type,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        $insertedCount++;
        echo sprintf(
            "  ✓ %2d) %-55s [%s, %s]\n",
            $idx + 1,
            substr($comp->international_competition_name, 0, 55),
            $comp->season,
            $type
        );
    } catch (\Exception $e) {
        echo sprintf(
            "  ✗ %2d) %-55s - FEHLER: %s\n",
            $idx + 1,
            substr($comp->international_competition_name, 0, 55),
            $e->getMessage()
        );
    }
}

// 4. Verify insertion
echo "\n" . str_repeat("─", 80) . "\n";
echo "✔️  ÜBERPRÜFUNG:\n";
echo str_repeat("─", 80) . "\n";

$verifyStmt = $targetPdo->query("SELECT * FROM competitions ORDER BY name");
$verifyComps = $verifyStmt->fetchAll(PDO::FETCH_OBJ);
$verifyCount = count($verifyComps);

echo "Wettbewerbe in kp_club_management.competitions: " . $verifyCount . "\n\n";

foreach ($verifyComps as $idx => $comp) {
    echo sprintf(
        "  %2d) %-55s [Season: %s]\n",
        $idx + 1,
        substr($comp->name, 0, 55),
        $comp->season
    );
}

// 5. Summary
echo "\n" . str_repeat("═", 80) . "\n";
echo "📊 ZUSAMMENFASSUNG:\n";
echo str_repeat("═", 80) . "\n";
echo "Gelesen: " . count($competitions) . " Wettbewerbe\n";
echo "Eingefügt: " . $insertedCount . " Wettbewerbe\n";
echo "Bestätigt: " . $verifyCount . " Wettbewerbe in Datenbank\n";

if ($insertedCount === count($competitions) && $verifyCount === count($competitions)) {
    echo "\n✅ ERFOLGREICH! Alle Wettbewerbe wurden eingefügt.\n";
} else {
    echo "\n⚠️  WARNUNG: Anzahl stimmt nicht überein!\n";
}

echo "\n";
?>
