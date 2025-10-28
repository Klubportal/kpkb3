# Football CMS - Complete Implementation Guide

## Current Status

✅ **Database Schema**: 7 migration tables (competitions, matches, rankings, top_scorers, match_events, match_players, player_statistics)

✅ **Eloquent Models**: 6 models with relationships and query builders

✅ **Service Layer**: 4 production-ready services for API communication and data aggregation

⏳ **Controllers & Routes**: Next step

---

## How It Works: Complete Data Flow

### 1. Data Synchronization Flow

```
COMET API
    │
    └─> CometApiService
        │
        ├─> getCompetitionsByOrganisation()
        ├─> getMatches()
        ├─> getMatchEvents()
        ├─> getMatchPlayerStatistics()
        ├─> getRankings()
        └─> getTopScorers()
                │
                └─> SyncService
                    │
                    ├─> syncCompetition()
                    ├─> syncCompetitionMatches()
                    ├─> syncMatch()
                    ├─> syncMatchEvents()
                    └─> syncMatchPlayerStatistics()
                            │
                            └─> Database Tables
                                ├─ competitions
                                ├─ matches
                                ├─ match_events
                                ├─ match_players
                                ├─ rankings
                                ├─ top_scorers
                                └─ player_statistics
```

### 2. Statistics Calculation Flow

```
After match completion:

Match Status = "COMPLETED"
        │
        └─> SyncService::syncMatch()
            │
            └─> MatchPlayer records created (40+ fields per player)
                │
                └─> StatisticsCalculator::calculateMatchStatistics()
                    │
                    └─> PlayerStatistic updated/created (60+ aggregated fields)
                            │
                            └─> Season stats ready for viewing
```

### 3. Ranking Calculation Flow

```
After any match completion:

GameMatch status = "COMPLETED"
        │
        └─> RankingCalculator::updateRankingsAfterMatch()
            │
            └─> For each team: Calculate
                ├─ Points (3 for win, 1 for draw, 0 for loss)
                ├─ Goals for/against
                ├─ Goal difference
                ├─ Win/draw/loss percentages
                ├─ Goals per game
                └─ Goals against per game
                    │
                    └─> Sort by: Points → Goal Diff → Goals For
                        │
                        └─> Update Ranking table with positions
```

---

## Quick Start Guide

### Step 1: Update Configuration

**File**: `.env`

```bash
# Add COMET API credentials (optional - will use defaults from kp_api.php)
COMET_API_BASE_URL=https://api-hns.analyticom.de
COMET_API_USERNAME=nkprigorje
COMET_API_PASSWORD=3c6nR$dS
COMET_API_RATE_LIMIT=1000
```

### Step 2: Run Database Migrations

```bash
# Migrate for each tenant
php artisan migrate --path=database/migrations/tenant
```

### Step 3: Run Initial Sync

```bash
# Sync all competitions for an organisation (use actual organisation FIFA ID)
php artisan sync:comet 12345

# Sync specific season
php artisan sync:comet 12345 --season=2024

# Sync specific competition
php artisan sync:comet 12345 --competition-id=67890
```

### Step 4: Query the Data

```php
// Get all completed matches
$matches = GameMatch::completed()->get();

// Get league table
$table = Ranking::byCompetition($competitionId)->ordered()->get();

// Get player season statistics
$player = PlayerStatistic::byPlayer($playerFifaId)
    ->bySeason(2024)
    ->first();

// Get top scorers
$scorers = TopScorer::byCompetition($competitionId)
    ->ordered()
    ->limit(10)
    ->get();
```

---

## Real-World Usage Examples

### Example 1: Display Match Results with Player Stats

```php
<?php

// Get a completed match
$match = GameMatch::completed()
    ->where('home_team_fifa_id', $teamId)
    ->first();

if (!$match) {
    return response()->json(['message' => 'Match not found']);
}

// Build response
$response = [
    'match' => [
        'id' => $match->match_fifa_id,
        'teams' => "{$match->home_team_name} vs {$match->away_team_name}",
        'result' => "{$match->home_final_result}-{$match->away_final_result}",
        'attendance' => $match->attendance,
        'date' => $match->date_local->format('d.m.Y H:i'),
    ],
    'home_players' => [],
    'away_players' => [],
];

// Get home team players
foreach ($match->homeTeamPlayers()->played()->ordered()->get() as $player) {
    $response['home_players'][] = [
        'name' => $player->player_name,
        'position' => $player->getDisplayPosition(),
        'shirt_number' => $player->shirt_number,
        'minutes' => $player->minutes_played,
        'goals' => $player->goals,
        'assists' => $player->assists,
        'yellow_cards' => $player->yellow_cards,
        'red_cards' => $player->red_cards,
        'passes_accuracy' => $player->pass_accuracy . '%',
        'rating' => $player->rating,
    ];
}

// Get away team players (same structure)
foreach ($match->awayTeamPlayers()->played()->ordered()->get() as $player) {
    // ... same as above
}

// Get match events timeline
$response['events'] = [];
foreach ($match->matchEvents()->ordered()->get() as $event) {
    $response['events'][] = [
        'minute' => $event->minute,
        'type' => $event->getDisplayEventType(),
        'player' => $event->player_name,
        'team' => $event->team_type,
    ];
}

return response()->json($response);
```

### Example 2: Display League Table with Detailed Stats

```php
<?php

$competitionId = 67890;
$standings = Ranking::byCompetition($competitionId)
    ->ordered()
    ->get();

// Build league table
$table = [];
foreach ($standings as $team) {
    $table[] = [
        'position' => $team->position,
        'team' => $team->team_name,
        'matches' => $team->matches_played,
        'wins' => $team->matches_won,
        'draws' => $team->matches_drawn,
        'losses' => $team->matches_lost,
        'goals_for' => $team->goals_for,
        'goals_against' => $team->goals_against,
        'goal_difference' => $team->goal_difference,
        'points' => $team->points,
        'form' => "{$team->win_percentage}% W",
    ];
}

return response()->json(['standings' => $table]);
```

### Example 3: Display Player Season Statistics

```php
<?php

$player = PlayerStatistic::byPlayer($playerFifaId)
    ->bySeason(2024)
    ->first();

if (!$player) {
    return response()->json(['message' => 'Player not found']);
}

$stats = [
    'personal' => [
        'name' => $player->player_name,
        'position' => $player->getDisplayPosition(),
        'team' => $player->team_name,
        'season' => $player->getSeasonDisplay(),
    ],
    'appearances' => [
        'total' => $player->appearances,
        'starts' => $player->starts,
        'substitutions_on' => $player->substitutions_on,
        'substitutions_off' => $player->substitutions_off,
        'minutes_played' => $player->minutes_played,
        'avg_minutes_per_game' => $player->average_minutes_per_game,
    ],
    'goals_assists' => [
        'goals' => $player->goals,
        'assists' => $player->assists,
        'goals_per_game' => $player->goals_per_game,
        'assists_per_game' => $player->assists_per_game,
        'penalties' => $player->penalty_goals,
    ],
    'discipline' => [
        'yellow_cards' => $player->yellow_cards,
        'red_cards' => $player->red_cards,
        'total_cards' => $player->total_cards,
        'fouls_committed' => $player->fouls_committed,
    ],
    'passing' => [
        'completed' => $player->passes_completed,
        'attempted' => $player->passes_attempted,
        'accuracy' => $player->pass_accuracy . '%',
    ],
    'defensive' => [
        'tackles' => $player->tackles,
        'interceptions' => $player->interceptions,
        'clearances' => $player->clearances,
        'blocks' => $player->blocks,
    ],
    'attacking' => [
        'shots_on_target' => $player->shots_on_target,
        'shots_off_target' => $player->shots_off_target,
        'shot_accuracy' => $player->shot_accuracy . '%',
        'dribbles_completed' => $player->dribbles_completed,
        'dribble_success' => $player->dribble_success_rate . '%',
    ],
    'performance' => [
        'average_rating' => $player->avg_rating,
        'best_rating' => $player->best_performance_rating,
        'worst_rating' => $player->worst_performance_rating,
        'man_of_match' => $player->man_of_match,
    ],
];

// Goalkeeper specific stats
if ($player->isGoalkeeper()) {
    $stats['goalkeeper'] = [
        'appearances' => $player->appearances,
        'clean_sheets' => $player->clean_sheets,
        'goals_conceded' => $player->goals_conceded,
        'saves' => $player->saves,
        'save_percentage' => $player->save_percentage . '%',
    ];
}

return response()->json(['statistics' => $stats]);
```

### Example 4: Get Top Performers in Competition

```php
<?php

$competitionId = 67890;
$season = 2024;

$calculator = app(\App\Services\StatisticsCalculator::class);
$topPerformers = $calculator->getTopPerformers(
    competitionFifaId: $competitionId,
    season: $season,
    limit: 10
);

$response = [
    'top_scorers' => $topPerformers['top_scorers']->map(fn($p) => [
        'rank' => 1,
        'name' => $p->player_name,
        'team' => $p->team_name,
        'goals' => $p->goals,
        'matches' => $p->appearances,
        'goals_per_game' => $p->goals_per_game,
    ])->values(),

    'top_assists' => $topPerformers['top_assists']->map(fn($p) => [
        'rank' => 1,
        'name' => $p->player_name,
        'team' => $p->team_name,
        'assists' => $p->assists,
        'matches' => $p->appearances,
    ])->values(),

    'best_rated' => $topPerformers['top_rated']->map(fn($p) => [
        'rank' => 1,
        'name' => $p->player_name,
        'team' => $p->team_name,
        'rating' => $p->avg_rating,
        'matches' => $p->appearances,
    ])->values(),

    'most_appearances' => $topPerformers['most_appearances']->map(fn($p) => [
        'rank' => 1,
        'name' => $p->player_name,
        'team' => $p->team_name,
        'matches' => $p->appearances,
        'minutes' => $p->minutes_played,
    ])->values(),
];

return response()->json($response);
```

### Example 5: Display Promotion/Relegation Race

```php
<?php

$competitionId = 67890;
$rankingCalc = app(\App\Services\RankingCalculator::class);

// Get zones
$zones = $rankingCalc->getPromotionRelegationZone(
    competitionFifaId: $competitionId,
    promotionPlaces: 2,
    relegationPlaces: 3
);

$response = [
    'promotion_zone' => [
        'title' => 'Promotion (Top 2)',
        'teams' => $zones['promotion']->map(fn($t) => [
            'position' => $t->position,
            'team' => $t->team_name,
            'points' => $t->points,
            'matches_played' => $t->matches_played,
        ])->values(),
    ],
    'safe_zone' => [
        'title' => 'Safe Zone',
        'count' => $zones['safe']->count(),
        'teams' => $zones['safe']->map(fn($t) => [
            'position' => $t->position,
            'team' => $t->team_name,
            'points' => $t->points,
        ])->values(),
    ],
    'relegation_zone' => [
        'title' => 'Relegation (Bottom 3)',
        'teams' => $zones['relegation']->map(fn($t) => [
            'position' => $t->position,
            'team' => $t->team_name,
            'points' => $t->points,
            'matches_played' => $t->matches_played,
        ])->values(),
    ],
];

return response()->json($response);
```

---

## Information Available After Each Match

### Wer gespielt hat (Who played)?

```php
$players = $match->matchPlayers()->played()->get();
// Access: $player->player_name, $player->starting_lineup
```

### Wer hat Tor geschossen (Who scored)?

```php
$goals = $match->matchEvents()->goals()->get();
// OR
$goalscorers = $match->matchPlayers()->goalscorers()->get();
// Access: $goal->player_name, $player->goals
```

### Wer hat rote Karte (Who got red card)?

```php
$redCards = $match->matchEvents()->redCards()->get();
// OR
$players = $match->matchPlayers()->withRedCards()->get();
// Access: $event->player_name, $player->red_cards
```

### Wieviel Minuten (Minutes played)?

```php
$player->minutes_played
```

### Wieviel Spiel (Match count)?

```php
$stats = PlayerStatistic::byPlayer($playerFifaId)->first();
$stats->appearances  // Total matches
$stats->starts       // Starting appearances
```

### Soviel wie mögliche Statistik (Maximum statistics)?

```php
// Per match (40+ fields):
$matchPlayer->goals
$matchPlayer->assists
$matchPlayer->passes_completed
$matchPlayer->pass_accuracy
$matchPlayer->shots_on_target
$matchPlayer->tackles
$matchPlayer->interceptions
$matchPlayer->yellow_cards
$matchPlayer->red_cards
// ... 30+ more fields

// Per season (60+ fields):
$playerStat->goals_per_game
$playerStat->pass_accuracy
$playerStat->duel_success_rate
$playerStat->win_percentage
$playerStat->average_rating
// ... 50+ more aggregated metrics
```

---

## Scheduled Synchronization

### Set up periodic sync (optional)

**File**: `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // Sync twice daily (morning and evening)
    $schedule->call(function () {
        Artisan::call('sync:comet', ['organisation-id' => 12345]);
    })->twiceDaily(6, 18);

    // Full resync weekly
    $schedule->call(function () {
        Artisan::call('sync:comet', [
            'organisation-id' => 12345,
            '--season' => now()->year
        ]);
    })->weekly()->mondays()->at('02:00');
}
```

---

## Troubleshooting

### No data appears after sync

1. Check logs: `storage/logs/laravel.log`
2. Verify credentials in `.env`
3. Test API connection: `php artisan tinker`
   ```php
   $api = app(\App\Services\CometApiService::class);
   $api->getCompetitionsByOrganisation(12345);
   ```

### Sync is slow

1. Check network connection to COMET API
2. Reduce data scope (sync specific competition instead of whole organisation)
3. Run recalculation in background job

### Stale data

1. Manual sync: `php artisan sync:comet 12345`
2. Clear cache: `php artisan cache:clear`
3. Check last sync time: `php artisan sync:comet 12345 --competition-id=67890`

---

## Next Steps

1. **Create Controllers** - Build REST API endpoints
2. **Create Filament Resources** - Admin dashboard
3. **Create Webhook Listener** - Real-time updates from COMET
4. **Add Tests** - Unit and integration tests
5. **Performance Tuning** - Optimize queries for large datasets
6. **Caching Strategy** - Implement advanced caching
