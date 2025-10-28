<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Tenant-Migration Generator ===\n\n";

// Diese Tabellen gehören zu Tenants
$tenantTables = [
    'activity_log',
    'cache',
    'cache_locks',
    'club_players',
    'coach_group',
    'failed_jobs',
    'groups',
    'jobs',
    'job_batches',
    'language_lines',
    'media',
    'model_has_permissions',
    'model_has_roles',
    'news',
    'notifications',
    'pages',
    'password_reset_tokens',
    'permissions',
    'player_group',
    'roles',
    'role_has_permissions',
    'sessions',
    'settings',
    'taggables',
    'tags',
    'template_settings',
    'users'
];

$database = 'tenant_nkprigorjem';
$targetDir = __DIR__ . '/database/migrations/tenant';
$generatedCount = 15; // Start nach den 15 COMET-Migrations

foreach ($tenantTables as $tableName) {
    echo "Generiere Migration für: {$tableName}\n";

    // Hole CREATE TABLE Statement
    $result = DB::connection('mysql')->select("SHOW CREATE TABLE `{$database}`.`{$tableName}`");

    if (empty($result)) {
        echo "  ⚠️  Tabelle nicht gefunden, überspringe...\n";
        continue;
    }

    $createTable = $result[0]->{'Create Table'};

    // Erstelle Migration-Dateinamen
    $timestamp = '2025_01_01_' . str_pad($generatedCount, 6, '0', STR_PAD_LEFT);
    $migrationName = "create_{$tableName}_table";
    $fileName = "{$timestamp}_{$migrationName}.php";
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

echo "\n✅ " . ($generatedCount - 15) . " Tenant-Migrations erfolgreich generiert!\n";
echo "Gesamt in tenant/: {$generatedCount} Migrations\n";
