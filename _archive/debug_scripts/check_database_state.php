<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometMatch;
use App\Models\CometTeam;
use App\Models\CometPlayer;
use App\Models\CometMatchEvent;
use App\Models\CometPlayerStat;
use App\Models\CometMatchStat;

echo "【 Current Database Record Count 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";
echo "comet_matches: " . CometMatch::count() . " records\n";
echo "comet_teams: " . CometTeam::count() . " records\n";
echo "comet_players: " . CometPlayer::count() . " records\n";
echo "comet_match_events: " . CometMatchEvent::count() . " records\n";
echo "comet_player_stats: " . CometPlayerStat::count() . " records\n";
echo "comet_match_stats: " . CometMatchStat::count() . " records\n";
