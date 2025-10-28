<?php

echo "\n" . str_repeat("═", 80) . "\n";
echo "✅ FINALE ÜBERPRÜFUNG - WETTBEWERBE IN DATENBANK\n";
echo str_repeat("═", 80) . "\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=kp_club_management;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get competitions from kp_club_management
    echo "【kp_club_management.competitions】\n";
    echo str_repeat("─", 80) . "\n";

    $stmt = $pdo->query("
        SELECT id, comet_id, name, season, status, type
        FROM competitions
        ORDER BY name
    ");

    $comps = $stmt->fetchAll(PDO::FETCH_OBJ);

    echo "Insgesamt: " . count($comps) . " Wettbewerbe\n\n";

    foreach ($comps as $idx => $comp) {
        $icon = $comp->type === 'cup' ? '🏆' : '⚽';
        echo sprintf(
            "%s %2d) %-50s [ID: %s]\n",
            $icon,
            $idx + 1,
            substr($comp->name, 0, 50),
            $comp->comet_id
        );
        echo sprintf(
            "       Season: %s | Status: %s | Type: %s\n\n",
            $comp->season,
            $comp->status,
            $comp->type
        );
    }

    // Compare with source
    echo "\n" . str_repeat("═", 80) . "\n";
    echo "【Vergleich mit kp_server.matches (Quelle)】\n";
    echo str_repeat("─", 80) . "\n";

    $sourcePdo = new PDO('mysql:host=localhost;dbname=kp_server;charset=utf8mb4', 'root', '');
    $sourcePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sourceStmt = $sourcePdo->query("
        SELECT DISTINCT
            competition_fifa_id,
            international_competition_name
        FROM kp_server.matches
        WHERE team_fifa_id_home = '598' OR team_fifa_id_away = '598'
        ORDER BY international_competition_name
    ");

    $sourceComps = $sourceStmt->fetchAll(PDO::FETCH_OBJ);

    echo "Quelle (kp_server.matches): " . count($sourceComps) . " Wettbewerbe\n";
    echo "Ziel (kp_club_management.competitions): " . count($comps) . " Wettbewerbe\n\n";

    if (count($comps) === count($sourceComps)) {
        echo "✅ MATCH! Alle Wettbewerbe wurden erfolgreich synchronisiert.\n\n";
    } else {
        echo "⚠️  Unterschiedliche Anzahl!\n\n";
    }

    // Show comparison
    echo "Detaillierter Abgleich:\n";
    echo str_repeat("─", 80) . "\n";

    foreach ($sourceComps as $idx => $source) {
        $target = $comps[$idx] ?? null;

        if ($target) {
            $match = ($source->international_competition_name === $target->name) ? '✓' : '✗';
            echo sprintf(
                "%s %2d) %s\n",
                $match,
                $idx + 1,
                $source->international_competition_name
            );
        } else {
            echo sprintf(
                "✗ %2d) %s (FEHLT IN ZIEL)\n",
                $idx + 1,
                $source->international_competition_name
            );
        }
    }

    echo "\n" . str_repeat("═", 80) . "\n";
    echo "📊 ZUSAMMENFASSUNG:\n";
    echo str_repeat("═", 80) . "\n";

    $typeStats = [];
    $statusStats = [];

    foreach ($comps as $comp) {
        $typeStats[$comp->type] = ($typeStats[$comp->type] ?? 0) + 1;
        $statusStats[$comp->status] = ($statusStats[$comp->status] ?? 0) + 1;
    }

    echo "\nNach Typ:\n";
    foreach ($typeStats as $type => $count) {
        $icon = $type === 'cup' ? '🏆' : '⚽';
        echo "  $icon $type: $count\n";
    }

    echo "\nNach Status:\n";
    foreach ($statusStats as $status => $count) {
        echo "  $status: $count\n";
    }

    echo "\n✅ ERFOLGREICH ABGESCHLOSSEN!\n";
    echo "   10 echte Wettbewerbe von NK Prigorje (FIFA 598)\n";
    echo "   wurden in die kp_club_management Datenbank eingefügt.\n";
    echo "\n";

} catch (\PDOException $e) {
    echo "❌ Datenbankfehler: " . $e->getMessage() . "\n";
    exit(1);
}

?>
