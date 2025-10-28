<?php

// Load more realistic test data
echo "\n" . str_repeat("═", 80) . "\n";
echo "⚽ COMET DATA EXPANDED TEST - Laden von 10+ Clubs + 50+ Spielern\n";
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
use App\Models\CometPlayerStat;
use App\Models\CometMatchStat;

echo "【1】 Creating 10 German Bundesliga clubs...\n";

$clubs = [
    [654321, 'FC Bayern München', 'Munich', 1900],
    [123456, 'Borussia Dortmund', 'Dortmund', 1909],
    [654322, 'Bayer Leverkusen', 'Leverkusen', 1904],
    [654323, 'RB Leipzig', 'Leipzig', 2009],
    [654324, 'VfL Wolfsburg', 'Wolfsburg', 1945],
    [654325, 'Eintracht Frankfurt', 'Frankfurt', 1899],
    [654326, 'FC Köln', 'Cologne', 1948],
    [654327, 'Union Berlin', 'Berlin', 1966],
    [654328, 'Borussia Mönchengladbach', 'Mönchengladbach', 1900],
    [654329, 'TSG Hoffenheim', 'Hoffenheim', 1899],
];

foreach ($clubs as [$id, $name, $city, $year]) {
    CometClub::findOrCreateByComet($id, [
        'name' => $name,
        'city' => $city,
        'country' => 'Germany',
        'founded_year' => $year,
    ]);
}
echo "  ✓ 10 clubs created\n";

echo "\n【2】 Creating teams for each club...\n";

$teamCounter = 5981;
foreach ($clubs as [$clubId, $clubName]) {
    // Senior team
    CometTeam::findOrCreateByComet($teamCounter++, $clubId, [
        'name' => "$clubName Senior",
        'team_type' => 'Senior',
        'player_count' => 25,
    ]);
    // Reserve team
    CometTeam::findOrCreateByComet($teamCounter++, $clubId, [
        'name' => "$clubName II",
        'team_type' => 'Reserve',
        'player_count' => 20,
    ]);
}
echo "  ✓ 20 teams created\n";

echo "\n【3】 Creating 50 players...\n";

$players = [
    // Bayern
    ['comet_team_id' => 5981, 'comet_club_id' => 654321, 'players' => [
        [100001, 'Manuel', 'Neuer', 'GK', 1],
        [100002, 'Dayot', 'Upamecano', 'CB', 2],
        [100003, 'Benjamin', 'Pavard', 'CB', 5],
        [100004, 'Alfonso', 'Davies', 'LB', 19],
        [100005, 'Joshua', 'Kimmich', 'RB', 32],
        [100006, 'Leon', 'Goretzka', 'CM', 18],
        [100007, 'Ryan', 'Gravenberch', 'CM', 38],
        [100008, 'Serge', 'Gnabry', 'RW', 7],
        [100009, 'Kingsley', 'Coman', 'LW', 11],
        [100010, 'Robert', 'Lewandowski', 'ST', 9],
    ]],
    // Dortmund
    ['comet_team_id' => 5982, 'comet_club_id' => 123456, 'players' => [
        [100011, 'Gregor', 'Kobel', 'GK', 1],
        [100012, 'Nico', 'Schlotterbeck', 'CB', 4],
        [100013, 'Mats', 'Hummels', 'CB', 15],
        [100014, 'Romain', 'Canuti', 'LB', 16],
        [100015, 'Thomas', 'Meunier', 'RB', 24],
        [100016, 'Emre', 'Can', 'CM', 23],
        [100017, 'Mahmoud', 'Dahoud', 'CM', 19],
        [100018, 'Marco', 'Reus', 'LW', 11],
        [100019, 'Jadon', 'Sancho', 'RW', 25],
        [100020, 'Sébastien', 'Haller', 'ST', 9],
    ]],
];

$playerCount = 0;
foreach ($players as $teamData) {
    $teamId = $teamData['comet_team_id'];
    $clubId = $teamData['comet_club_id'];
    foreach ($teamData['players'] as [$id, $first, $last, $pos, $jersey]) {
        CometPlayer::findOrCreateByComet($id, $teamId, $clubId, [
            'first_name' => $first,
            'last_name' => $last,
            'full_name' => "$first $last",
            'position' => $pos,
            'jersey_number' => $jersey,
            'status' => 'active',
        ]);
        $playerCount++;
    }
}
echo "  ✓ $playerCount players created\n";

echo "\n【4】 Creating 5 matches...\n";

$matches = [
    [13901536, 5981, 5982, 654321, 123456, 'Bayern vs Dortmund', null, null],
    [13901537, 5983, 5984, 654322, 654323, 'Leverkusen vs Leipzig', 2, 1],
    [13901538, 5985, 5986, 654324, 654325, 'Wolfsburg vs Frankfurt', 1, 1],
    [13901539, 5987, 5988, 654326, 654327, 'Köln vs Union Berlin', 0, 2],
    [13901540, 5989, 5990, 654328, 654329, 'Gladbach vs Hoffenheim', 3, 0],
];

foreach ($matches as [$id, $homeTeam, $awayTeam, $homeClub, $awayClub, $name, $homeGoals, $awayGoals]) {
    CometMatch::findOrCreateByComet($id, [
        'comet_home_team_id' => $homeTeam,
        'comet_away_team_id' => $awayTeam,
        'comet_home_club_id' => $homeClub,
        'comet_away_club_id' => $awayClub,
        'match_date' => now()->addDays(rand(0, 7)),
        'match_day' => (string)rand(1, 34),
        'location' => 'Stadion ' . rand(1, 10),
        'home_goals' => $homeGoals,
        'away_goals' => $awayGoals,
        'status' => $homeGoals !== null ? 'finished' : 'scheduled',
    ]);
}
echo "  ✓ 5 matches created\n";

echo "\n【5】 Creating match events...\n";

$eventCount = 0;

// Events for finished matches
$finishedMatches = [13901537, 13901538, 13901539, 13901540];
foreach ($finishedMatches as $matchId) {
    // 2-4 goals per match
    for ($i = 0; $i < rand(2, 4); $i++) {
        CometMatchEvent::findOrCreateByComet(1000 + $eventCount++, [
            'comet_match_id' => $matchId,
            'comet_player_id' => 100000 + rand(1, 20),
            'comet_team_id' => 5981 + rand(0, 19),
            'event_type' => 'goal',
            'minute' => rand(1, 90),
            'second' => rand(0, 59),
        ]);
    }

    // 2-6 yellow cards
    for ($i = 0; $i < rand(2, 6); $i++) {
        CometMatchEvent::findOrCreateByComet(2000 + $eventCount++, [
            'comet_match_id' => $matchId,
            'comet_player_id' => 100000 + rand(1, 20),
            'comet_team_id' => 5981 + rand(0, 19),
            'event_type' => 'yellow_card',
            'minute' => rand(1, 90),
        ]);
    }

    // Occasionally a red card
    if (rand(0, 1)) {
        CometMatchEvent::findOrCreateByComet(3000 + $eventCount++, [
            'comet_match_id' => $matchId,
            'comet_player_id' => 100000 + rand(1, 20),
            'comet_team_id' => 5981 + rand(0, 19),
            'event_type' => 'red_card',
            'minute' => rand(30, 90),
        ]);
    }
}
echo "  ✓ $eventCount match events created\n";

echo "\n【6】 Creating player stats...\n";

$statCount = 0;
for ($i = 0; $i < 20; $i++) {
    CometPlayerStat::findOrCreateByComet(4000 + $i, 100000 + rand(1, 20), 5981 + rand(0, 19), [
        'matches_played' => rand(10, 25),
        'goals' => rand(0, 20),
        'assists' => rand(0, 10),
        'yellow_cards' => rand(0, 5),
        'red_cards' => rand(0, 1),
        'average_rating' => round(rand(60, 90) / 10, 1),
        'minutes_played' => rand(500, 2000),
    ]);
    $statCount++;
}
echo "  ✓ $statCount player stats created\n";

echo "\n【7】 Creating match stats...\n";

$matchStatCount = 0;
foreach ($finishedMatches as $i => $matchId) {
    // Home team stats
    CometMatchStat::findOrCreateByComet(5000 + ($i * 2), $matchId, 5981 + ($i * 2), [
        'goals' => rand(0, 3),
        'shots_on_goal' => rand(3, 10),
        'shots_off_goal' => rand(2, 8),
        'possession_percentage' => rand(40, 65),
        'passes' => rand(300, 600),
        'tackles' => rand(5, 15),
        'fouls_committed' => rand(5, 15),
        'corners' => rand(2, 8),
        'pass_accuracy' => round(rand(70, 95), 2),
    ]);

    // Away team stats
    CometMatchStat::findOrCreateByComet(5000 + ($i * 2) + 1, $matchId, 5982 + ($i * 2), [
        'goals' => rand(0, 3),
        'shots_on_goal' => rand(3, 10),
        'shots_off_goal' => rand(2, 8),
        'possession_percentage' => rand(35, 60),
        'passes' => rand(280, 580),
        'tackles' => rand(5, 15),
        'fouls_committed' => rand(5, 15),
        'corners' => rand(1, 7),
        'pass_accuracy' => round(rand(65, 90), 2),
    ]);
    $matchStatCount += 2;
}
echo "  ✓ $matchStatCount match stats created\n";

echo "\n【8】 Final Database Summary:\n";
echo "  • Clubs: " . CometClub::count() . "\n";
echo "  • Teams: " . CometTeam::count() . "\n";
echo "  • Players: " . CometPlayer::count() . "\n";
echo "  • Matches: " . CometMatch::count() . "\n";
echo "  • Match Events: " . CometMatchEvent::count() . "\n";
echo "  • Player Stats: " . CometPlayerStat::count() . "\n";
echo "  • Match Stats: " . CometMatchStat::count() . "\n";

echo "\n✅ Extended test data loaded successfully!\n";
echo "📊 Ready for testing Comet API endpoints!\n\n";
