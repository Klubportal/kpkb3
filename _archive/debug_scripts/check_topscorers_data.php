<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking TOP SCORERS data completeness:\n";
echo str_repeat("=", 70) . "\n\n";

$total = DB::connection('central')->table('comet_top_scorers')->count();
$withPlayerFifaId = DB::connection('central')->table('comet_top_scorers')->whereNotNull('player_fifa_id')->count();
$withClub = DB::connection('central')->table('comet_top_scorers')->whereNotNull('club')->count();
$withClubId = DB::connection('central')->table('comet_top_scorers')->whereNotNull('club_id')->count();
$withTeamLogo = DB::connection('central')->table('comet_top_scorers')->whereNotNull('team_logo')->count();

echo "Total Top Scorers: $total\n\n";
echo "Fields:\n";
echo "  - player_fifa_id: $withPlayerFifaId / $total\n";
echo "  - club:           $withClub / $total\n";
echo "  - club_id:        $withClubId / $total\n";
echo "  - team_logo:      $withTeamLogo / $total\n\n";

echo "Sample records:\n";
echo str_repeat("-", 70) . "\n";

$samples = DB::connection('central')
    ->table('comet_top_scorers')
    ->limit(5)
    ->get();

foreach ($samples as $s) {
    echo "{$s->international_first_name} {$s->international_last_name}\n";
    echo "  player_fifa_id: " . ($s->player_fifa_id ?? 'NULL') . "\n";
    echo "  club: " . ($s->club ?? 'NULL') . "\n";
    echo "  club_id: " . ($s->club_id ?? 'NULL') . "\n";
    echo "  team_logo: " . ($s->team_logo ?? 'NULL') . "\n\n";
}
