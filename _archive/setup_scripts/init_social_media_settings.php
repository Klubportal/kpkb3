<?php

$pdo = new PDO('mysql:host=localhost;dbname=kpkb3;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== SOCIAL MEDIA SETTINGS INITIALISIERUNG ===\n\n";

// Prüfe ob Settings bereits existieren
$stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `group` = 'social_media'");
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo "⚠️  SocialMediaSettings existieren bereits ({$count} Einträge)\n";
    echo "Möchten Sie diese neu initialisieren? Alte Werte gehen verloren!\n";
    echo "Drücken Sie ENTER zum Fortfahren oder CTRL+C zum Abbrechen...\n";
    fgets(STDIN);

    $pdo->exec("DELETE FROM settings WHERE `group` = 'social_media'");
    echo "✅ Alte Settings gelöscht\n\n";
}

// Default-Werte für SocialMediaSettings (alle NULL)
$settings = [
    'facebook_url' => null,
    'instagram_url' => null,
    'twitter_url' => null,
    'youtube_url' => null,
    'linkedin_url' => null,
    'tiktok_url' => null,
];

echo "Erstelle SocialMediaSettings mit folgenden Werten:\n";
echo str_repeat('-', 60) . "\n";

$stmt = $pdo->prepare("INSERT INTO settings (`group`, `name`, `payload`, `locked`) VALUES ('social_media', ?, ?, 0)");

foreach ($settings as $name => $value) {
    $payload = json_encode($value);
    $displayValue = $value === null ? 'NULL' : $value;
    echo sprintf("%-25s: %s\n", $name, $displayValue);
    $stmt->execute([$name, $payload]);
}

echo str_repeat('-', 60) . "\n";
echo "✅ SocialMediaSettings erfolgreich initialisiert!\n\n";

// Prüfe das Ergebnis
$stmt = $pdo->prepare("SELECT `name`, `payload` FROM settings WHERE `group` = 'social_media' ORDER BY `name`");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Gespeicherte Settings (" . count($result) . " Einträge):\n";
foreach ($result as $row) {
    $value = json_decode($row['payload'], true);
    $displayValue = $value === null ? 'NULL' : $value;
    echo sprintf("  %-25s: %s\n", $row['name'], $displayValue);
}

echo "\n✅ Fertig! Landing Page sollte jetzt funktionieren.\n";
echo "   Social Media URLs können später im Admin-Panel konfiguriert werden.\n";
echo "   Cache leeren mit: php artisan cache:clear\n";
