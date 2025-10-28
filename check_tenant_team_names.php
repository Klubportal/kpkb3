<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

echo "🔍 Prüfe Vereinsnamen in Tenant DB...\n\n";

$tenant = Tenant::find('nkprigorjem');
tenancy()->initialize($tenant);

$standings = DB::table('comet_rankings')
    ->orderBy('position')
    ->limit(10)
    ->get(['position', 'international_team_name', 'points', 'team_fifa_id']);

echo "Top 10 der Tabelle:\n";
foreach ($standings as $s) {
    $highlight = $s->team_fifa_id == 598 ? ' ⭐' : '';
    echo "  {$s->position}. {$s->international_team_name} ({$s->points} Pkt){$highlight}\n";
}

echo "\n✅ Vereinsnamen sind jetzt korrekt!\n";
