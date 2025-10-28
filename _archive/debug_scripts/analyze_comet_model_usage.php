<?php

echo "=== COMET MODELS VERWENDUNGS-ANALYSE ===\n\n";

// Verzeichnisse die durchsucht werden sollen
$searchDirs = [
    'app',
    'database',
    'routes',
    'config',
];

// Model-Namespaces die gesucht werden
$modelNamespaces = [
    'Integration' => 'App\\Models\\Integration\\',
    'Comet' => 'App\\Models\\Comet\\',
];

// Comet-Model-Namen die in beiden Ordnern existieren könnten
$cometModels = [
    'CometClub',
    'CometPlayer',
    'CometPlayerStat',
    'CometSync',
    'CometTeam',
    'CometMatch',
    'CometMatchEvent',
    'CometRanking',
    'CometTopScorer',
    'CometClubCompetition',
    'CometClubExtended',
];

$results = [
    'Integration' => [],
    'Comet' => [],
    'Ambiguous' => [], // use Statements ohne vollständigen Namespace
];

$fileCount = 0;

echo "Durchsuche Verzeichnisse...\n\n";

foreach ($searchDirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $fileCount++;
            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Suche nach Integration-Models
            foreach ($cometModels as $model) {
                $pattern = $modelNamespaces['Integration'] . $model;
                if (stripos($content, $pattern) !== false) {
                    if (!isset($results['Integration'][$model])) {
                        $results['Integration'][$model] = [];
                    }
                    $results['Integration'][$model][] = $filePath;
                }
            }

            // Suche nach Comet-Models
            foreach ($cometModels as $model) {
                $pattern = $modelNamespaces['Comet'] . $model;
                if (stripos($content, $pattern) !== false) {
                    if (!isset($results['Comet'][$model])) {
                        $results['Comet'][$model] = [];
                    }
                    $results['Comet'][$model][] = $filePath;
                }
            }

            // Suche nach ambiguen use Statements (ohne vollständigen Namespace)
            foreach ($cometModels as $model) {
                if (preg_match('/^use\s+.*\\\\' . $model . '\s*;/m', $content)) {
                    if (!isset($results['Ambiguous'][$model])) {
                        $results['Ambiguous'][$model] = [];
                    }
                    $results['Ambiguous'][$model][] = $filePath;
                }
            }
        }
    }
}

echo "Durchsuchte Dateien: {$fileCount}\n\n";
echo str_repeat('=', 80) . "\n";
echo "ERGEBNISSE\n";
echo str_repeat('=', 80) . "\n\n";

// Integration Models
echo "📂 MODELS/INTEGRATION/ - Verwendung:\n";
echo str_repeat('-', 80) . "\n";
if (empty($results['Integration'])) {
    echo "   ✅ KEINE Verwendungen gefunden!\n";
    echo "   → Models/Integration/ kann sicher gelöscht werden.\n\n";
} else {
    foreach ($results['Integration'] as $model => $files) {
        echo "   ⚠️  {$model} - " . count($files) . " Verwendung(en)\n";
        foreach (array_slice($files, 0, 5) as $file) {
            echo "      - {$file}\n";
        }
        if (count($files) > 5) {
            echo "      ... und " . (count($files) - 5) . " weitere\n";
        }
        echo "\n";
    }
}

// Comet Models
echo "\n📂 MODELS/COMET/ - Verwendung:\n";
echo str_repeat('-', 80) . "\n";
if (empty($results['Comet'])) {
    echo "   ⚠️  KEINE Verwendungen gefunden!\n";
    echo "   → Prüfen Sie ob Models/Comet/ wirklich benötigt wird.\n\n";
} else {
    foreach ($results['Comet'] as $model => $files) {
        echo "   ✅ {$model} - " . count($files) . " Verwendung(en)\n";
        foreach (array_slice($files, 0, 3) as $file) {
            echo "      - {$file}\n";
        }
        if (count($files) > 3) {
            echo "      ... und " . (count($files) - 3) . " weitere\n";
        }
        echo "\n";
    }
}

// Ambigue Verwendungen
if (!empty($results['Ambiguous'])) {
    echo "\n⚠️  AMBIGUE USE-STATEMENTS (ohne vollständigen Namespace):\n";
    echo str_repeat('-', 80) . "\n";
    foreach ($results['Ambiguous'] as $model => $files) {
        echo "   {$model} - " . count($files) . " Datei(en)\n";
        foreach ($files as $file) {
            echo "      - {$file}\n";
        }
        echo "\n";
    }
}

// Zusammenfassung & Empfehlung
echo "\n" . str_repeat('=', 80) . "\n";
echo "ZUSAMMENFASSUNG & EMPFEHLUNG\n";
echo str_repeat('=', 80) . "\n\n";

$integrationCount = array_sum(array_map('count', $results['Integration']));
$cometCount = array_sum(array_map('count', $results['Comet']));

echo "📊 Statistik:\n";
echo "   - Models/Integration/: {$integrationCount} Verwendungen\n";
echo "   - Models/Comet/:       {$cometCount} Verwendungen\n\n";

if ($integrationCount === 0) {
    echo "✅ EMPFEHLUNG: Models/Integration/ kann GELÖSCHT werden!\n\n";
    echo "Folgende Dateien können sicher entfernt werden:\n";

    $integrationPath = 'app/Models/Integration';
    if (is_dir($integrationPath)) {
        $files = glob($integrationPath . '/*.php');
        foreach ($files as $file) {
            echo "   - " . basename($file) . "\n";
        }
    }

    echo "\nBefehl zum Löschen:\n";
    echo "   rm -rf app/Models/Integration/\n";
    echo "   oder PowerShell:\n";
    echo "   Remove-Item -Recurse -Force app\\Models\\Integration\\\n";
} else {
    echo "⚠️  ACHTUNG: Models/Integration/ wird noch verwendet!\n\n";
    echo "Bevor Sie löschen:\n";
    echo "1. Alle Verwendungen auf Models/Comet/ umstellen\n";
    echo "2. Tests durchführen\n";
    echo "3. Dann Models/Integration/ löschen\n\n";

    echo "Dateien die aktualisiert werden müssen:\n";
    $allFiles = [];
    foreach ($results['Integration'] as $files) {
        $allFiles = array_merge($allFiles, $files);
    }
    $allFiles = array_unique($allFiles);
    foreach ($allFiles as $file) {
        echo "   - {$file}\n";
    }
}

// Prüfe auf Model-Duplikate im Dateisystem
echo "\n" . str_repeat('=', 80) . "\n";
echo "DATEISYSTEM-PRÜFUNG\n";
echo str_repeat('=', 80) . "\n\n";

$integrationPath = 'app/Models/Integration';
$cometPath = 'app/Models/Comet';

if (is_dir($integrationPath)) {
    $integrationFiles = array_map('basename', glob($integrationPath . '/*.php'));
    echo "📂 Models/Integration/ - " . count($integrationFiles) . " Dateien:\n";
    foreach ($integrationFiles as $file) {
        echo "   - {$file}\n";
    }
    echo "\n";
}

if (is_dir($cometPath)) {
    $cometFiles = array_map('basename', glob($cometPath . '/*.php'));
    echo "📂 Models/Comet/ - " . count($cometFiles) . " Dateien:\n";
    foreach ($cometFiles as $file) {
        echo "   - {$file}\n";
    }
    echo "\n";
}

// Finde Duplikate
if (isset($integrationFiles) && isset($cometFiles)) {
    $duplicates = array_intersect($integrationFiles, $cometFiles);
    if (!empty($duplicates)) {
        echo "⚠️  DUPLIKATE gefunden:\n";
        foreach ($duplicates as $dup) {
            echo "   - {$dup} existiert in BEIDEN Ordnern!\n";
        }
        echo "\n";
    }
}

echo str_repeat('=', 80) . "\n";
echo "✅ Analyse abgeschlossen!\n";
echo str_repeat('=', 80) . "\n";
