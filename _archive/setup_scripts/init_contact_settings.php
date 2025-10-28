<?php

$pdo = new PDO('mysql:host=localhost;dbname=kpkb3;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== CONTACT SETTINGS INITIALISIERUNG ===\n\n";

// Prüfe ob Settings bereits existieren
$stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `group` = 'contact'");
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo "⚠️  ContactSettings existieren bereits ({$count} Einträge)\n";
    echo "Möchten Sie diese neu initialisieren? Alte Werte gehen verloren!\n";
    echo "Drücken Sie ENTER zum Fortfahren oder CTRL+C zum Abbrechen...\n";
    fgets(STDIN);

    $pdo->exec("DELETE FROM settings WHERE `group` = 'contact'");
    echo "✅ Alte Settings gelöscht\n\n";
}

// Default-Werte für ContactSettings
$settings = [
    'company_name' => 'Klubportal',
    'street' => null,
    'postal_code' => null,
    'city' => null,
    'country' => 'Hrvatska',
    'phone' => null,
    'fax' => null,
    'mobile' => null,
    'email' => 'info@klubportal.local',
    'google_maps_url' => null,
    'google_maps_embed' => null,
];

echo "Erstelle ContactSettings mit folgenden Werten:\n";
echo str_repeat('-', 60) . "\n";

$stmt = $pdo->prepare("INSERT INTO settings (`group`, `name`, `payload`, `locked`) VALUES ('contact', ?, ?, 0)");

foreach ($settings as $name => $value) {
    $payload = json_encode($value);
    $displayValue = $value === null ? 'NULL' : $value;
    echo sprintf("%-25s: %s\n", $name, $displayValue);
    $stmt->execute([$name, $payload]);
}

echo str_repeat('-', 60) . "\n";
echo "✅ ContactSettings erfolgreich initialisiert!\n\n";

// Prüfe das Ergebnis
$stmt = $pdo->prepare("SELECT `name`, `payload` FROM settings WHERE `group` = 'contact' ORDER BY `name`");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Gespeicherte Settings (" . count($result) . " Einträge):\n";
foreach ($result as $row) {
    $value = json_decode($row['payload'], true);
    $displayValue = $value === null ? 'NULL' : $value;
    echo sprintf("  %-25s: %s\n", $row['name'], $displayValue);
}

echo "\n✅ Fertig! Landing Page sollte jetzt funktionieren.\n";
echo "   Cache leeren mit: php artisan cache:clear\n";
