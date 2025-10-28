<?php

$pdo = new PDO('mysql:host=localhost;dbname=kpkb3;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== THEME SETTINGS INITIALISIERUNG ===\n\n";

// Prüfe ob Settings bereits existieren
$stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `group` = 'theme'");
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo "⚠️  ThemeSettings existieren bereits ({$count} Einträge)\n";
    echo "Möchten Sie diese neu initialisieren? Alte Werte gehen verloren!\n";
    echo "Drücken Sie ENTER zum Fortfahren oder CTRL+C zum Abbrechen...\n";
    fgets(STDIN);

    $pdo->exec("DELETE FROM settings WHERE `group` = 'theme'");
    echo "✅ Alte Settings gelöscht\n\n";
}

// Default-Werte für ThemeSettings
$settings = [
    'active_theme' => 'default',
    'header_bg_color' => '#ffffff',
    'footer_bg_color' => '#1f2937',
    'text_color' => '#1f2937',
    'link_color' => '#3b82f6',
    'button_style' => 'rounded',
    'dark_mode_enabled' => false,
    'layout_style' => 'full-width',
    'font_family' => 'Inter',
    'border_radius' => 'md',
    'sidebar_width' => 'normal',
];

echo "Erstelle ThemeSettings mit folgenden Werten:\n";
echo str_repeat('-', 60) . "\n";

$stmt = $pdo->prepare("INSERT INTO settings (`group`, `name`, `payload`, `locked`) VALUES ('theme', ?, ?, 0)");

foreach ($settings as $name => $value) {
    $payload = json_encode($value);
    $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : ($value === null ? 'NULL' : $value);
    echo sprintf("%-25s: %s\n", $name, $displayValue);
    $stmt->execute([$name, $payload]);
}

echo str_repeat('-', 60) . "\n";
echo "✅ ThemeSettings erfolgreich initialisiert!\n\n";

// Prüfe das Ergebnis
$stmt = $pdo->prepare("SELECT `name`, `payload` FROM settings WHERE `group` = 'theme' ORDER BY `name`");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Gespeicherte Settings (" . count($result) . " Einträge):\n";
foreach ($result as $row) {
    $value = json_decode($row['payload'], true);
    $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : ($value === null ? 'NULL' : $value);
    echo sprintf("  %-25s: %s\n", $row['name'], $displayValue);
}

echo "\n✅ Fertig! Landing Page sollte jetzt funktionieren.\n";
echo "   Theme-Einstellungen können später im Admin-Panel angepasst werden.\n";
echo "   Cache leeren mit: php artisan cache:clear\n";
