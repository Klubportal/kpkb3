<?php

// Test-Daten laden in Comet-Tabellen
echo "\n" . str_repeat("═", 80) . "\n";
echo "⚽ COMET DATA SYNC - TEST\n";
echo str_repeat("═", 80) . "\n\n";

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\CometClub;
use App\Models\CometTeam;
use App\Models\CometPlayer;
use App\Models\CometMatch;
use App\Models\CometMatchEvent;
use App\Services\CometSyncService;

// Create test data
echo "【1】 Creating test clubs...\n";

$club1 = CometClub::findOrCreateByComet(654321, [
    'name' => 'FC Bayern München',
    'city' => 'Munich',
    'country' => 'Germany',
    'logo_url' => 'https://example.com/bayern.png',
    'website' => 'https://www.fcbayern.com',
    'founded_year' => 1900,
]);
echo "  ✓ Bayern München (ID: {$club1->comet_id})\n";

$club2 = CometClub::findOrCreateByComet(123456, [
    'name' => 'Borussia Dortmund',
    'city' => 'Dortmund',
    'country' => 'Germany',
    'logo_url' => 'https://example.com/bvb.png',
    'website' => 'https://www.bvb.de',
    'founded_year' => 1909,
]);
echo "  ✓ Borussia Dortmund (ID: {$club2->comet_id})\n";

echo "\n【2】 Creating test teams...\n";

$team1 = CometTeam::findOrCreateByComet(5981, 654321, [
    'name' => 'Bayern München Senior',
    'team_type' => 'Senior',
    'player_count' => 25,
]);
echo "  ✓ Bayern Team (ID: {$team1->comet_id})\n";

$team2 = CometTeam::findOrCreateByComet(5982, 123456, [
    'name' => 'Borussia Dortmund Senior',
    'team_type' => 'Senior',
    'player_count' => 24,
]);
echo "  ✓ Dortmund Team (ID: {$team2->comet_id})\n";

echo "\n【3】 Creating test players...\n";

$playerData = [
    [654987, 'Manuel', 'Neuer', 'Goalkeeper', 1],
    [654988, 'Thomas', 'Müller', 'Forward', 25],
    [654989, 'Serge', 'Gnabry', 'Midfielder', 7],
    [654990, 'Joshua', 'Kimmich', 'Defender', 32],
    [654991, 'Robert', 'Lewandowski', 'Forward', 9],
];

foreach ($playerData as [$comet_id, $first, $last, $pos, $jersey]) {
    $player = CometPlayer::findOrCreateByComet($comet_id, 5981, 654321, [
        'first_name' => $first,
        'last_name' => $last,
        'full_name' => "$first $last",
        'position' => $pos,
        'jersey_number' => $jersey,
        'status' => 'active',
    ]);
    echo "  ✓ $first $last (#{$player->jersey_number})\n";
}

echo "\n【4】 Creating test match...\n";

$match = CometMatch::findOrCreateByComet(13901536, [
    'comet_home_team_id' => 5981,
    'comet_away_team_id' => 5982,
    'comet_home_club_id' => 654321,
    'comet_away_club_id' => 123456,
    'match_date' => now()->addDay(),
    'match_day' => '15',
    'location' => 'Allianz Arena',
    'status' => 'scheduled',
]);
echo "  ✓ Bayern vs Dortmund (ID: {$match->comet_id})\n";

echo "\n【5】 Creating test match events...\n";

// Goal events
$goal1 = CometMatchEvent::findOrCreateByComet(1001, [
    'comet_match_id' => 13901536,
    'comet_player_id' => 654988,
    'comet_team_id' => 5981,
    'event_type' => 'goal',
    'minute' => 12,
]);
echo "  ✓ Goal by Müller (Min 12)\n";

$goal2 = CometMatchEvent::findOrCreateByComet(1002, [
    'comet_match_id' => 13901536,
    'comet_player_id' => 654991,
    'comet_team_id' => 5981,
    'event_type' => 'goal',
    'minute' => 45,
]);
echo "  ✓ Goal by Lewandowski (Min 45)\n";

// Yellow card
$yellow = CometMatchEvent::findOrCreateByComet(1003, [
    'comet_match_id' => 13901536,
    'comet_player_id' => 654990,
    'comet_team_id' => 5981,
    'event_type' => 'yellow_card',
    'minute' => 38,
]);
echo "  ✓ Yellow Card for Kimmich (Min 38)\n";

echo "\n【6】 Database Summary:\n";
echo "  • Clubs: " . CometClub::count() . "\n";
echo "  • Teams: " . CometTeam::count() . "\n";
echo "  • Players: " . CometPlayer::count() . "\n";
echo "  • Matches: " . CometMatch::count() . "\n";
echo "  • Match Events: " . CometMatchEvent::count() . "\n";

echo "\n✅ Test data successfully loaded!\n\n";
