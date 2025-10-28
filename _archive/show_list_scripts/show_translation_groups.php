<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== VERFÜGBARE ÜBERSETZUNGSGRUPPEN ===\n\n";

$groups = DB::connection('central')->table('language_lines')->distinct()->pluck('group');

foreach($groups as $group) {
    $count = DB::connection('central')->table('language_lines')->where('group', $group)->count();
    echo "📁 $group ($count Einträge)\n";

    // Zeige die ersten 3 Keys
    $keys = DB::connection('central')->table('language_lines')
        ->where('group', $group)
        ->limit(3)
        ->pluck('key');

    foreach($keys as $key) {
        echo "   - $key\n";
    }

    if ($count > 3) {
        echo "   ... und " . ($count - 3) . " weitere\n";
    }
    echo "\n";
}

echo "\n💡 WIE BEARBEITEN:\n";
echo "   1. Öffne: http://localhost:8000/admin/translation-manager\n";
echo "   2. Suche nach Group oder Key (z.B. 'landing' oder 'welcome')\n";
echo "   3. Klicke auf 'Edit' (Stift-Symbol)\n";
echo "   4. Bearbeite die Übersetzungen für jede Sprache\n";
echo "   5. Klicke 'Save'\n\n";

echo "🔍 ODER benutze Quick Translate:\n";
echo "   1. Öffne: http://localhost:8000/admin/translation-manager/quick-translate\n";
echo "   2. Wähle fehlende Übersetzungen aus\n";
echo "   3. Übersetze schnell mehrere auf einmal\n\n";
