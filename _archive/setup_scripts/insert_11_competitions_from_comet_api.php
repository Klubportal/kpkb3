<?php

echo "\n" . str_repeat("═", 80) . "\n";
echo "📥 COMET REST API - 11 WETTBEWERBE IN DATENBANK EINFÜGEN\n";
echo str_repeat("═", 80) . "\n\n";

// API Credentials
$baseUrl = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

echo "【1】 Wettbewerbe von Comet REST API abrufen\n";
echo str_repeat("─", 80) . "\n";

// Initialize cURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => $username . ':' . $password,
]);

// Get competitions from Comet API
$competitionsUrl = $baseUrl . '/api/export/comet/competitions?active=true&teamFifaId=598';
curl_setopt($ch, CURLOPT_URL, $competitionsUrl);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

echo "GET: $competitionsUrl\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode !== 200) {
    echo "❌ Fehler beim Abrufen (HTTP $httpCode)\n";
    curl_close($ch);
    exit(1);
}

$competitions = json_decode($response, true);
echo "✅ " . count($competitions) . " Wettbewerbe von API erhalten\n\n";

curl_close($ch);

// Prepare data for insertion
$toInsert = [];
foreach ($competitions as $comp) {
    $toInsert[] = [
        'comet_id' => (string)($comp['competitionFifaId'] ?? ''),
        'name' => $comp['internationalName'] ?? '',
        'season' => (int)($comp['season'] ?? 2026),
        'status' => 'active',
        'type' => (stripos($comp['internationalName'] ?? '', 'kup') !== false) ? 'cup' : 'league',
    ];
}

echo "【2】 Datenbank vorbereiten\n";
echo str_repeat("─", 80) . "\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=kp_club_management;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Clear existing competitions
    $deleteStmt = $pdo->prepare("DELETE FROM competitions");
    $deleteStmt->execute();
    $deleted = $deleteStmt->rowCount();

    echo "🗑️  Alte Wettbewerbe gelöscht: " . $deleted . "\n\n";

    // Insert new competitions
    echo "【3】 Wettbewerbe einfügen\n";
    echo str_repeat("─", 80) . "\n";

    $insertStmt = $pdo->prepare("
        INSERT INTO competitions (comet_id, name, slug, season, status, type, created_at, updated_at)
        VALUES (:comet_id, :name, :slug, :season, :status, :type, :created_at, :updated_at)
    ");

    $now = date('Y-m-d H:i:s');
    $insertedCount = 0;

    foreach ($toInsert as $idx => $data) {
        try {
            // Generate unique slug
            $slug = strtolower(str_replace(
                ['ć', 'č', 'š', 'ž', 'đ', ' ', '"', "'", '.', ','],
                ['c', 'c', 's', 'z', 'd', '-', '', '', '', ''],
                preg_replace('/[^a-z0-9ćčšžđ\s\-]/i', '', $data['name'])
            ));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-') . '-' . $data['comet_id'];

            $insertStmt->execute([
                ':comet_id' => $data['comet_id'],
                ':name' => $data['name'],
                ':slug' => $slug,
                ':season' => $data['season'],
                ':status' => $data['status'],
                ':type' => $data['type'],
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);

            $insertedCount++;
            $icon = $data['type'] === 'cup' ? '🏆' : '⚽';
            echo sprintf(
                "  %s %2d) %-55s\n",
                $icon,
                $idx + 1,
                substr($data['name'], 0, 55)
            );
        } catch (\Exception $e) {
            echo sprintf(
                "  ✗ %2d) %-55s - FEHLER: %s\n",
                $idx + 1,
                substr($data['name'], 0, 55),
                $e->getMessage()
            );
        }
    }

    echo "\n" . str_repeat("─", 80) . "\n";
    echo "【4】 Verifizierung\n";
    echo str_repeat("─", 80) . "\n";

    $verifyStmt = $pdo->query("SELECT * FROM competitions ORDER BY name");
    $verifyComps = $verifyStmt->fetchAll(PDO::FETCH_OBJ);
    $verifyCount = count($verifyComps);

    echo "Wettbewerbe in Datenbank: " . $verifyCount . "\n\n";

    foreach ($verifyComps as $idx => $comp) {
        $icon = $comp->type === 'cup' ? '🏆' : '⚽';
        echo sprintf(
            "  %s %2d) %-55s [Season: %s, Status: %s]\n",
            $icon,
            $idx + 1,
            substr($comp->name, 0, 55),
            $comp->season,
            $comp->status
        );
    }

    echo "\n" . str_repeat("═", 80) . "\n";
    echo "📊 ZUSAMMENFASSUNG:\n";
    echo str_repeat("═", 80) . "\n";

    $typeStats = [];
    foreach ($verifyComps as $comp) {
        $typeStats[$comp->type] = ($typeStats[$comp->type] ?? 0) + 1;
    }

    echo "Eingefügt: " . $insertedCount . " Wettbewerbe\n";
    echo "Bestätigt: " . $verifyCount . " Wettbewerbe in Datenbank\n\n";

    echo "Nach Typ:\n";
    foreach ($typeStats as $type => $count) {
        $icon = $type === 'cup' ? '🏆' : '⚽';
        echo "  $icon $type: $count\n";
    }

    if ($insertedCount === count($toInsert) && $verifyCount === count($toInsert)) {
        echo "\n✅ ERFOLGREICH!\n";
        echo "   11 echte Wettbewerbe von Comet REST API\n";
        echo "   wurden in kp_club_management.competitions eingefügt.\n";
    } else {
        echo "\n⚠️  WARNUNG: Anzahl stimmt nicht überein!\n";
        echo "   Erwartet: " . count($toInsert) . "\n";
        echo "   Eingefügt: " . $insertedCount . "\n";
        echo "   Bestätigt: " . $verifyCount . "\n";
    }

    echo "\n";

} catch (\PDOException $e) {
    echo "❌ Datenbankfehler: " . $e->getMessage() . "\n";
    exit(1);
}

?>
