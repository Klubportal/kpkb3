<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "🔄 KONVERTIERE VEREINSNAMEN ZU SHORT-FORMAT\n";
echo str_repeat("═", 100) . "\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "【1】 Sammle alle einzigartigen Vereinsnamen\n";
    echo str_repeat("─", 100) . "\n\n";

    // Get all unique team names
    $result = $pdo->query("
        SELECT DISTINCT team_name_home as team_name
        FROM matches
        UNION
        SELECT DISTINCT team_name_away as team_name
        FROM matches
        ORDER BY team_name
    ");

    $teamNames = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        if ($row['team_name']) {
            $teamNames[] = $row['team_name'];
        }
    }

    echo "✅ " . count($teamNames) . " einzigartige Vereinsnamen gefunden\n\n";

    echo "【2】 Konvertiere zu Short-Namen\n";
    echo str_repeat("─", 100) . "\n\n";

    $mapping = [];

    foreach ($teamNames as $fullName) {
        // Convert: "Nogometni klub XYZ" → "XYZ"
        // Also handle: "NK XYZ" → "XYZ"

        $shortName = $fullName;

        // Remove "Nogometni klub " prefix
        if (strpos($shortName, 'Nogometni klub ') === 0) {
            $shortName = trim(substr($shortName, strlen('Nogometni klub ')));
        }

        // Remove "NK " prefix if still there
        if (strpos($shortName, 'NK ') === 0) {
            $shortName = trim(substr($shortName, strlen('NK ')));
        }

        $mapping[$fullName] = $shortName;
        echo sprintf("  %50s → %s\n", $fullName, $shortName);
    }

    echo "\n" . str_repeat("─", 100) . "\n";
    echo "【3】 Aktualisiere Datenbank\n";
    echo str_repeat("─", 100) . "\n\n";

    $updateStmt = $pdo->prepare("
        UPDATE matches
        SET team_name_home = :short_name
        WHERE team_name_home = :full_name
    ");

    $updateAwayStmt = $pdo->prepare("
        UPDATE matches
        SET team_name_away = :short_name
        WHERE team_name_away = :full_name
    ");

    $totalUpdated = 0;

    foreach ($mapping as $fullName => $shortName) {
        if ($fullName !== $shortName) {
            // Update home teams
            $updateStmt->execute([
                ':short_name' => $shortName,
                ':full_name' => $fullName
            ]);
            $homeCount = $updateStmt->rowCount();
            $totalUpdated += $homeCount;

            // Update away teams
            $updateAwayStmt->execute([
                ':short_name' => $shortName,
                ':full_name' => $fullName
            ]);
            $awayCount = $updateAwayStmt->rowCount();
            $totalUpdated += $awayCount;

            if (($homeCount + $awayCount) > 0) {
                echo sprintf("  ✓ %s: %d + %d Updates\n", $shortName, $homeCount, $awayCount);
            }
        }
    }

    echo "\n" . str_repeat("─", 100) . "\n";
    echo "【4】 Verifikation\n";
    echo str_repeat("─", 100) . "\n\n";

    echo "✅ Insgesamt " . $totalUpdated . " Einträge aktualisiert\n\n";

    // Show sample
    echo "📋 Beispiele nach Konvertierung:\n";
    $result = $pdo->query("
        SELECT DISTINCT team_name_home as team_name
        FROM matches
        ORDER BY team_name_home
        LIMIT 20
    ");

    $i = 1;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("  %2d. %s\n", $i, $row['team_name']);
        $i++;
    }

    echo "\n" . str_repeat("═", 100) . "\n";
    echo "✅ FERTIG! Alle Vereinsnamen sind jetzt im Short-Format\n";
    echo str_repeat("═", 100) . "\n\n";

} catch (PDOException $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n";
    exit(1);
}

?>
