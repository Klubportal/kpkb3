<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== COMET DATA IN CENTRAL (kpkb3) ===\n\n";

echo "Matches für Club 396: " . DB::connection('central')->table('comet_matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', 396)->orWhere('team_fifa_id_away', 396);
    })->count() . "\n";

echo "Players für Club 396: " . DB::connection('central')->table('comet_players')
    ->where('club_fifa_id', 396)->count() . "\n";

echo "Rankings mit Club 396: " . DB::connection('central')->table('comet_rankings')
    ->where('team_fifa_id', 396)->count() . "\n";

echo "\n=== COMET DATA IN TENANT (tenant_nknapijed) ===\n\n";

$tenant = App\Models\Central\Tenant::find('nknapijed');
tenancy()->initialize($tenant);

echo "Matches für Club 396: " . DB::connection('tenant')->table('comet_matches')
    ->where(function($q) {
        $q->where('team_fifa_id_home', 396)->orWhere('team_fifa_id_away', 396);
    })->count() . "\n";

echo "Players für Club 396: " . DB::connection('tenant')->table('comet_players')
    ->where('club_fifa_id', 396)->count() . "\n";

echo "Rankings mit Club 396: " . DB::connection('tenant')->table('comet_rankings')
    ->where('team_fifa_id', 396)->count() . "\n";

echo "\n=== TOTAL IN TENANT ===\n\n";

echo "Alle Matches: " . DB::connection('tenant')->table('comet_matches')->count() . "\n";
echo "Alle Players: " . DB::connection('tenant')->table('comet_players')->count() . "\n";
echo "Alle Rankings: " . DB::connection('tenant')->table('comet_rankings')->count() . "\n";
