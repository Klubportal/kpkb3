<?php

// Direkte PDO-Verbindung zur zentralen DB
$pdo = new PDO('mysql:host=localhost;dbname=kpkb3;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== GENERAL SETTINGS INITIALISIERUNG ===\n\n";

// Prüfe ob Settings bereits existieren
$stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `group` = 'general'");
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo "⚠️  GeneralSettings existieren bereits ({$count} Einträge)\n";
    echo "Möchten Sie diese neu initialisieren? Alte Werte gehen verloren!\n";
    echo "Drücken Sie ENTER zum Fortfahren oder CTRL+C zum Abbrechen...\n";
    fgets(STDIN);

    // Lösche alte Settings
    $pdo->exec("DELETE FROM settings WHERE `group` = 'general'");
    echo "✅ Alte Settings gelöscht\n\n";
}

// Default-Werte für GeneralSettings
$settings = [
    'site_name' => 'Klubportal',
    'site_description' => 'Vereinsverwaltung und Webseite',
    'logo' => null,
    'favicon' => null,
    'logo_height' => '50',
    'primary_color' => '#3B82F6',
    'secondary_color' => '#10B981',
    'font_family' => 'Inter',
    'font_size' => 14,
    'contact_email' => 'info@klubportal.local',
    'phone' => null,
];

echo "Erstelle GeneralSettings mit folgenden Werten:\n";
echo str_repeat('-', 60) . "\n";

$stmt = $pdo->prepare("INSERT INTO settings (`group`, `name`, `payload`, `locked`) VALUES ('general', ?, ?, 0)");

foreach ($settings as $name => $value) {
    // Serialize value as JSON
    $payload = json_encode($value);

    $displayValue = $value === null ? 'NULL' : $value;
    echo sprintf("%-25s: %s\n", $name, $displayValue);

    $stmt->execute([$name, $payload]);
}

echo str_repeat('-', 60) . "\n";
echo "✅ GeneralSettings erfolgreich initialisiert!\n\n";

// Prüfe das Ergebnis
$stmt = $pdo->prepare("SELECT `name`, `payload` FROM settings WHERE `group` = 'general' ORDER BY `name`");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Gespeicherte Settings ({count} Einträge):\n";
foreach ($result as $row) {
    $value = json_decode($row['payload'], true);
    $displayValue = $value === null ? 'NULL' : $value;
    echo sprintf("  %-25s: %s\n", $row['name'], $displayValue);
}

echo "\n✅ Fertig! Sie können jetzt http://localhost:8000/admin/login aufrufen.\n";
echo "   Cache leeren mit: php artisan cache:clear\n";
