<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

echo "=== Migration Generator aus kpkb3 Datenbank ===\n\n";

// Hole alle Tabellen
$tables = DB::select("SHOW TABLES");
$database = 'kpkb3';
$tableKey = "Tables_in_{$database}";

$generatedCount = 0;

foreach ($tables as $table) {
    $tableName = $table->$tableKey;

    // Überspringe migrations und system-Tabellen
    if (in_array($tableName, ['migrations', 'telescope_entries', 'telescope_entries_tags', 'telescope_monitoring'])) {
        continue;
    }

    echo "Generiere Migration für: {$tableName}\n";

    // Hole CREATE TABLE Statement
    $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0]->{'Create Table'};

    // Erstelle Migration-Dateinamen
    $timestamp = '2025_01_01_' . str_pad($generatedCount, 6, '0', STR_PAD_LEFT);
    $migrationName = "create_{$tableName}_table";
    $fileName = "{$timestamp}_{$migrationName}.php";

    // Bestimme Zielordner
    if (str_starts_with($tableName, 'comet_')) {
        $targetDir = __DIR__ . '/database/migrations/comet';
    } elseif (in_array($tableName, ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'])) {
        $targetDir = __DIR__ . '/database/migrations';
    } else {
        $targetDir = __DIR__ . '/database/migrations';
    }

    // Erstelle Ordner falls nötig
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filePath = "{$targetDir}/{$fileName}";

    // Generiere Migration-Content
    $migrationContent = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
{$createTable}
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `{$tableName}`');
    }
};

PHP;

    file_put_contents($filePath, $migrationContent);
    $generatedCount++;
}

echo "\n✅ {$generatedCount} Migrations erfolgreich generiert!\n";
echo "\nÜberprüfe: database/migrations/\n";
