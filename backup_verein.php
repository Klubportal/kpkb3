<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

if ($argc < 2) {
    echo "===========================================\n";
    echo "  KLUBPORTAL VEREIN BACKUP (PHP VERSION)\n";
    echo "===========================================\n\n";

    echo "Verfügbare Vereine:\n";
    echo "-------------------\n";

    try {
        // Load Laravel environment
        $app = require_once 'bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $tenants = DB::connection('central')->table('tenants')->select('id', 'name')->get();

        foreach ($tenants as $tenant) {
            echo "ID: {$tenant->id} - Name: {$tenant->name}\n";
        }

    } catch (Exception $e) {
        echo "Fehler beim Laden der Vereine: " . $e->getMessage() . "\n";

        // Fallback: Direct MySQL
        $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=kpkb3', 'root', '');
        $stmt = $pdo->query('SELECT id, name FROM tenants');
        while ($row = $stmt->fetch()) {
            echo "ID: {$row['id']} - Name: {$row['name']}\n";
        }
    }

    echo "\nVerwendung: php backup_verein.php [VEREIN_ID]\n";
    echo "Beispiel:   php backup_verein.php nkprigorjem\n\n";
    exit(1);
}

$tenantId = $argv[1];
$timestamp = date('Ymd_His');
$backupDir = __DIR__ . "/backups/verein_{$tenantId}_{$timestamp}";

echo "===========================================\n";
echo "  VEREIN BACKUP: {$tenantId}\n";
echo "===========================================\n\n";

// Create backup directory
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    mkdir($backupDir . '/database', 0755, true);
    mkdir($backupDir . '/uploads', 0755, true);
}

echo "Backup-Verzeichnis erstellt: {$backupDir}\n\n";

// Database connection
$dbConfig = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'username' => 'root',
    'password' => '',
];

try {
    $centralPdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname=kpkb3",
        $dbConfig['username'],
        $dbConfig['password']
    );

    // Check if tenant exists
    $stmt = $centralPdo->prepare('SELECT * FROM tenants WHERE id = ?');
    $stmt->execute([$tenantId]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tenant) {
        echo "FEHLER: Verein '{$tenantId}' nicht gefunden!\n";
        exit(1);
    }

    echo "Verein gefunden: {$tenant['name']}\n\n";

    // 1. Database Backup
    echo "========================================\n";
    echo "  1. DATENBANK BACKUP\n";
    echo "========================================\n\n";

    $tenantDbName = "tenant_{$tenantId}";

    // Check if tenant database exists
    $stmt = $centralPdo->query("SHOW DATABASES LIKE '{$tenantDbName}'");
    if ($stmt->rowCount() === 0) {
        echo "FEHLER: Datenbank '{$tenantDbName}' existiert nicht!\n";
        exit(1);
    }

    echo "[1/3] Sichere Vereins-Datenbank {$tenantDbName}...\n";

    $dumpPath = escapeshellarg($backupDir . "/database/{$tenantDbName}.sql");
    $dumpCmd = "C:\\xampp\\mysql\\bin\\mysqldump.exe -u root --single-transaction --routines --triggers {$tenantDbName} > {$dumpPath}";

    exec($dumpCmd, $output, $returnCode);
    if ($returnCode === 0) {
        echo "     ✓ OK - {$tenantDbName}.sql erstellt\n";
    } else {
        echo "     ✗ FEHLER beim Backup der Vereins-Datenbank\n";
        exit(1);
    }

    echo "[2/3] Sichere Vereins-Eintrag...\n";
    $entryPath = escapeshellarg($backupDir . "/database/tenant_entry.sql");
    $entryCmd = "C:\\xampp\\mysql\\bin\\mysqldump.exe -u root --single-transaction --where=\"id='{$tenantId}'\" kpkb3 tenants > {$entryPath}";

    exec($entryCmd, $output, $returnCode);
    if ($returnCode === 0) {
        echo "     ✓ OK - tenant_entry.sql erstellt\n";
    } else {
        echo "     ✗ FEHLER beim Backup des Vereins-Eintrags\n";
    }

    echo "[3/3] Exportiere Vereins-Konfiguration...\n";
    file_put_contents($backupDir . '/database/tenant_config.json', json_encode($tenant, JSON_PRETTY_PRINT));
    echo "     ✓ OK - tenant_config.json erstellt\n";

    // 2. Files Backup
    echo "\n========================================\n";
    echo "  2. DATEIEN BACKUP\n";
    echo "========================================\n\n";

    $uploadDirs = [
        "storage/app/tenant_{$tenantId}" => "uploads/tenant_{$tenantId}",
        "storage/app/public/tenants/{$tenantId}" => "uploads/public/{$tenantId}",
        "public/storage/tenants/{$tenantId}" => "uploads/public_link/{$tenantId}"
    ];

    $copiedFiles = 0;
    foreach ($uploadDirs as $source => $target) {
        if (is_dir($source)) {
            echo "Kopiere {$source}...\n";
            $targetDir = $backupDir . '/' . $target;

            if (!is_dir(dirname($targetDir))) {
                mkdir(dirname($targetDir), 0755, true);
            }

            $files = copyDirectory($source, $targetDir);
            $copiedFiles += $files;
            echo "     ✓ {$files} Dateien kopiert\n";
        } else {
            echo "     - {$source} nicht gefunden\n";
        }
    }

    // 3. Create Summary
    echo "\n========================================\n";
    echo "  3. BACKUP ZUSAMMENFASSUNG\n";
    echo "========================================\n\n";

    // Get database stats
    $tenantPdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$tenantDbName}",
        $dbConfig['username'],
        $dbConfig['password']
    );

    $stmt = $tenantPdo->query("
        SELECT
            COUNT(*) as table_count,
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.tables
        WHERE table_schema = '{$tenantDbName}'
    ");
    $dbStats = $stmt->fetch(PDO::FETCH_ASSOC);

    $backupSize = getDirSize($backupDir);

    $summary = [
        'tenant_id' => $tenantId,
        'tenant_name' => $tenant['name'],
        'backup_date' => date('Y-m-d H:i:s'),
        'backup_directory' => $backupDir,
        'database' => [
            'name' => $tenantDbName,
            'tables' => $dbStats['table_count'],
            'size_mb' => $dbStats['size_mb']
        ],
        'files' => [
            'copied_files' => $copiedFiles,
            'backup_size_mb' => round($backupSize / 1024 / 1024, 2)
        ],
        'restore_instructions' => [
            '1. Datenbank erstellen' => "mysql -u root -e \"CREATE DATABASE {$tenantDbName};\"",
            '2. Datenbank importieren' => "mysql -u root {$tenantDbName} < database/{$tenantDbName}.sql",
            '3. Vereins-Eintrag importieren' => "mysql -u root kpkb3 < database/tenant_entry.sql",
            '4. Dateien kopieren' => "Kopiere uploads/ zurück nach storage/app/"
        ]
    ];

    file_put_contents($backupDir . '/BACKUP_INFO.json', json_encode($summary, JSON_PRETTY_PRINT));

    echo "✓ BACKUP ERFOLGREICH ABGESCHLOSSEN!\n\n";
    echo "Verein: {$tenant['name']} (ID: {$tenantId})\n";
    echo "Datenbank: {$dbStats['table_count']} Tabellen, {$dbStats['size_mb']} MB\n";
    echo "Dateien: {$copiedFiles} Dateien kopiert\n";
    echo "Backup-Größe: " . round($backupSize / 1024 / 1024, 2) . " MB\n";
    echo "Backup-Ort: {$backupDir}\n\n";
    echo "Siehe BACKUP_INFO.json für Details zur Wiederherstellung.\n";

} catch (Exception $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";
    exit(1);
}

function copyDirectory($source, $destination) {
    $files = 0;
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $subPath = str_replace($source . DIRECTORY_SEPARATOR, '', $item->getPathname());
        $destPath = $destination . DIRECTORY_SEPARATOR . $subPath;
        if ($item->isDir()) {
            if (!is_dir($destPath)) {
                mkdir($destPath, 0755, true);
            }
        } else {
            copy($item, $destPath);
            $files++;
        }
    }

    return $files;
}

function getDirSize($directory) {
    $size = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }

    return $size;
}
