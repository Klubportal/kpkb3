<?php

$pdo = new PDO('mysql:host=localhost;dbname=kpkb3;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== NAVIGATION ÜBERSETZUNGEN INITIALISIERUNG ===\n\n";

// Navigation Übersetzungen
$navTranslations = [
    'register_club' => [
        'de' => 'Verein registrieren',
        'en' => 'Register Club',
        'hr' => 'Registriraj klub',
    ],
    'features' => [
        'de' => 'Funktionen',
        'en' => 'Features',
        'hr' => 'Značajke',
    ],
    'news' => [
        'de' => 'Neuigkeiten',
        'en' => 'News',
        'hr' => 'Vijesti',
    ],
    'contact' => [
        'de' => 'Kontakt',
        'en' => 'Contact',
        'hr' => 'Kontakt',
    ],
    'home' => [
        'de' => 'Startseite',
        'en' => 'Home',
        'hr' => 'Početna',
    ],
    'pricing' => [
        'de' => 'Preise',
        'en' => 'Pricing',
        'hr' => 'Cijene',
    ],
    'about' => [
        'de' => 'Über uns',
        'en' => 'About',
        'hr' => 'O nama',
    ],
    'login' => [
        'de' => 'Anmelden',
        'en' => 'Login',
        'hr' => 'Prijava',
    ],
];

// Prüfe bestehende Einträge
$stmt = $pdo->prepare("SELECT COUNT(*) FROM language_lines WHERE `group` = 'nav'");
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo "⚠️  Nav-Übersetzungen existieren bereits ({$count} Einträge)\n";
    echo "Möchten Sie diese neu initialisieren? Alte Werte gehen verloren!\n";
    echo "Drücken Sie ENTER zum Fortfahren oder CTRL+C zum Abbrechen...\n";
    fgets(STDIN);

    $pdo->exec("DELETE FROM language_lines WHERE `group` = 'nav'");
    echo "✅ Alte Übersetzungen gelöscht\n\n";
}

echo "Erstelle Navigation-Übersetzungen:\n";
echo str_repeat('-', 60) . "\n";

$insertStmt = $pdo->prepare("
    INSERT INTO language_lines (`group`, `key`, `text`, `created_at`, `updated_at`)
    VALUES ('nav', ?, ?, NOW(), NOW())
");

$created = 0;

foreach ($navTranslations as $key => $translations) {
    // Speichere als JSON für alle Sprachen
    $textJson = json_encode($translations);

    echo sprintf("%-20s: DE: %s | EN: %s | HR: %s\n",
        $key,
        $translations['de'],
        $translations['en'],
        $translations['hr']
    );

    $insertStmt->execute([$key, $textJson]);
    $created++;
}

echo str_repeat('-', 60) . "\n";
echo "✅ {$created} Navigation-Übersetzungen erstellt!\n\n";

// Prüfe das Ergebnis
$stmt = $pdo->query("SELECT `key`, `text` FROM language_lines WHERE `group` = 'nav' ORDER BY `key`");
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Gespeicherte Übersetzungen (" . count($result) . " Einträge):\n";
foreach ($result as $row) {
    $text = json_decode($row['text'], true);
    echo sprintf("  %-20s: %s\n", $row['key'], $text['de'] ?? 'N/A');
}

echo "\n✅ Fertig! Navigation sollte jetzt übersetzt angezeigt werden.\n";
echo "   Cache leeren mit: php artisan cache:clear\n";
