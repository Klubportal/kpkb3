<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== WIE ÜBERSETZUNGEN GESPEICHERT SIND ===\n\n";

// Zeige ein Beispiel
$entry = DB::connection('central')->table('language_lines')->first();

echo "📍 SPEICHERORT: Datenbank 'central' -> Tabelle 'language_lines'\n\n";

echo "📋 STRUKTUR:\n";
echo "   - ID: " . $entry->id . "\n";
echo "   - Group: " . $entry->group . "\n";
echo "   - Key: " . $entry->key . "\n";
echo "   - Text: JSON mit ALLEN Sprachen\n\n";

echo "📄 TEXT FELD (JSON FORMAT):\n";
$translations = json_decode($entry->text, true);
echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "🌍 VERFÜGBARE SPRACHEN IN DIESEM EINTRAG:\n";
foreach ($translations as $lang => $text) {
    echo "   - $lang: $text\n";
}

echo "\n📊 STATISTIK:\n";
$total = DB::connection('central')->table('language_lines')->count();
echo "   - Gesamt Einträge: $total\n";
echo "   - Jeder Eintrag enthält ALLE Sprachen (10 Sprachen)\n";
echo "   - Total Übersetzungen: " . ($total * 10) . "\n";

echo "\n💾 ZUSAMMENFASSUNG:\n";
echo "   ✅ Gespeichert in: MySQL Datenbank (central)\n";
echo "   ✅ Tabelle: language_lines\n";
echo "   ✅ Format: JSON im 'text' Feld\n";
echo "   ✅ Alle Sprachen in EINEM Eintrag pro Key\n";
echo "   ❌ NICHT in separaten JSON Dateien\n\n";

echo "🔍 BEISPIEL ABFRAGE:\n";
echo "   SELECT * FROM language_lines WHERE `group` = 'actions' AND `key` = 'accept';\n";
echo "   => Gibt 1 Zeile mit ALLEN 10 Übersetzungen zurück\n\n";
