<?php

use Illuminate\Support\Facades\DB;
use App\Models\Central\Tenant;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Multi-Tenancy Isolation Bereinigung\n";
echo "═══════════════════════════════════════\n\n";

$cometTables = [
    'comet_club_competitions',
    'comet_club_representatives',
    'comet_clubs_extended',
    'comet_coaches',
    'comet_match_events',
    'comet_match_officials',
    'comet_match_phases',
    'comet_match_players',
    'comet_match_team_officials',
    'comet_matches',
    'comet_own_goal_scorers',
    'comet_rankings',
    'comet_team_officials',
    'comet_top_scorers',
];

// Hole alle Tenants
$tenants = Tenant::all();

echo "📊 Gefundene Tenants: " . $tenants->count() . "\n\n";

foreach ($tenants as $tenant) {
    $dbName = $tenant->tenancy_db_name ?? 'tenant_' . $tenant->id;
    
    echo "┌─ Tenant: {$tenant->id} (DB: {$dbName})\n";
    
    try {
        // Prüfe welche Comet-Tabellen existieren
        $existingTables = DB::connection('mysql')
            ->select("SHOW TABLES FROM `{$dbName}` LIKE 'comet_%'");
        
        if (count($existingTables) === 0) {
            echo "│  ✅ Keine Comet-Tabellen gefunden - bereits sauber!\n";
            echo "└─────────────────────\n\n";
            continue;
        }
        
        echo "│  ⚠️  Gefunden: " . count($existingTables) . " Comet-Tabellen\n";
        
        // Lösche jede Comet-Tabelle
        $deleted = 0;
        foreach ($cometTables as $table) {
            try {
                DB::connection('mysql')->statement("DROP TABLE IF EXISTS `{$dbName}`.`{$table}`");
                $deleted++;
            } catch (\Exception $e) {
                echo "│  ❌ Fehler bei {$table}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "│  ✅ Gelöscht: {$deleted} Tabellen\n";
        
        // Bereinige migrations Tabelle
        try {
            $deletedMigrations = DB::connection('mysql')
                ->table($dbName . '.migrations')
                ->where('migration', 'LIKE', '%comet%')
                ->delete();
            
            echo "│  ✅ Bereinigt: {$deletedMigrations} Migration-Einträge\n";
        } catch (\Exception $e) {
            echo "│  ⚠️  Migrations-Bereinigung übersprungen: " . $e->getMessage() . "\n";
        }
        
    } catch (\Exception $e) {
        echo "│  ❌ Fehler: " . $e->getMessage() . "\n";
    }
    
    echo "└─────────────────────\n\n";
}

// Verifizierung
echo "🔍 Verifikation:\n";
echo "═══════════════\n\n";

foreach ($tenants as $tenant) {
    $dbName = $tenant->tenancy_db_name ?? 'tenant_' . $tenant->id;
    
    $remainingTables = DB::connection('mysql')
        ->select("SHOW TABLES FROM `{$dbName}` LIKE 'comet_%'");
    
    if (count($remainingTables) === 0) {
        echo "✅ {$dbName}: SAUBER (keine Comet-Tabellen)\n";
    } else {
        echo "❌ {$dbName}: PROBLEM (" . count($remainingTables) . " Comet-Tabellen verbleibend)\n";
    }
}

// Prüfe Central DB
echo "\n📊 Central DB (kpkb3) Comet-Tabellen:\n";
$centralCometTables = DB::connection('central')
    ->select("SHOW TABLES LIKE 'comet_%'");
echo "   ✅ " . count($centralCometTables) . " Comet-Tabellen in Central DB (korrekt)\n";

echo "\n✅ Bereinigung abgeschlossen!\n";
echo "\n📝 Nächster Schritt:\n";
echo "   Archiviere Comet-Migrations aus database/migrations/tenant/\n";
echo "   → Siehe MULTI_TENANCY_ISOLATION_PROBLEM.md\n\n";
