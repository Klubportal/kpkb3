<?php

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Erstelle Test-Tenant mit COMET-Sync...\n";

// Alten Tenant löschen falls vorhanden
$oldTenant = Tenant::find('testcometsync');
if ($oldTenant) {
    echo "Lösche alten Tenant...\n";
    try {
        DB::connection('central')->statement('DROP DATABASE IF EXISTS tenant_testcometsync');
    } catch (\Exception $e) {
        // Ignorieren
    }
    $oldTenant->delete();
}

// Tenant mit FIFA ID 598 (NK Prigorjem) erstellen
$tenant = Tenant::create([
    'id' => 'testcometsync',
    'name' => 'Test COMET Sync',
    'email' => 'test@example.com',
    'club_fifa_id' => 598, // NK Prigorjem
]);

$tenant->domains()->create(['domain' => 'testcometsync.localhost']);

echo "✓ Tenant erstellt\n";
echo "Warte auf Jobs...\n";

// Warten bis Jobs fertig sind
sleep(5);

// Prüfen was synchronisiert wurde
tenancy()->initialize($tenant);

$stats = [
    'comet_players' => DB::table('comet_players')->count(),
    'comet_coaches' => DB::table('comet_coaches')->count(),
    'comet_competitions' => DB::table('comet_competitions')->count(),
    'comet_matches' => DB::table('comet_matches')->count(),
    'comet_match_players' => DB::table('comet_match_players')->count(),
    'comet_match_events' => DB::table('comet_match_events')->count(),
    'comet_rankings' => DB::table('comet_rankings')->count(),
    'comet_top_scorers' => DB::table('comet_top_scorers')->count(),
];

tenancy()->end();

echo "\n✅ COMET-Daten synchronisiert:\n";
foreach ($stats as $table => $count) {
    $icon = $count > 0 ? '✓' : '✗';
    echo "  {$icon} {$table}: {$count}\n";
}

echo "\n✓ Test erfolgreich!\n";
echo "  URL: http://testcometsync.localhost:8000/club\n";
