<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔄 Erstelle Migrations aus Central COMET-Tabellen...\n\n";

$cometTables = [
    'comet_cases',
    'comet_clubs_extended',
    'comet_club_competitions',
    'comet_club_representatives',
    'comet_coaches',
    'comet_competitions',
    'comet_facilities',
    'comet_facility_fields',
    'comet_matches',
    'comet_match_events',
    'comet_match_officials',
    'comet_match_phases',
    'comet_match_players',
    'comet_match_team_officials',
    'comet_own_goal_scorers',
    'comet_players',
    'comet_player_competition_stats',
    'comet_rankings',
    'comet_sanctions',
    'comet_syncs',
    'comet_team_officials',
    'comet_top_scorers',
];

$migrationsDir = __DIR__ . '/database/migrations/tenant_comet';

// Ordner erstellen
if (!file_exists($migrationsDir)) {
    mkdir($migrationsDir, 0755, true);
    echo "✓ Ordner erstellt: {$migrationsDir}\n";
}

$timestamp = date('Y_m_d_His');
$counter = 0;

foreach ($cometTables as $table) {
    $counter++;
    $paddedCounter = str_pad($counter, 2, '0', STR_PAD_LEFT);

    echo "Erstelle Migration für {$table}...\n";

    // SHOW CREATE TABLE aus Central DB holen
    $result = DB::connection('central')->select("SHOW CREATE TABLE {$table}");
    $createStatement = $result[0]->{'Create Table'};

    // CREATE TABLE Statement bereinigen und escapen
    $createStatement = str_replace('CREATE TABLE `' . $table . '`', 'CREATE TABLE IF NOT EXISTS `' . $table . '`', $createStatement);
    $createStatement = str_replace("'", "\\'", $createStatement);
    $createStatement = str_replace("\n", " ", $createStatement);    // Migration-Datei erstellen
    $className = 'Create' . str_replace('_', '', ucwords($table, '_')) . 'Table';
    $migrationFile = $migrationsDir . '/' . $timestamp . '_' . $paddedCounter . '_create_' . $table . '_table.php';

    $migrationContent = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('$createStatement');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `$table`');
    }
};

PHP;

    file_put_contents($migrationFile, $migrationContent);
    echo "  ✓ {$migrationFile}\n";
}

echo "\n✅ {$counter} Migrations erstellt in: {$migrationsDir}\n";
echo "\n📝 Nächste Schritte:\n";
echo "1. php artisan tenants:run -- --path=database/migrations/tenant_comet\n";
echo "2. Dann sync_prigorjem_comet_data.php ausführen\n";
