# Football CMS - Service Layer Implementation

## Overview

Complete Service Layer for handling data synchronization, statistics calculation, and ranking management. These services orchestrate the entire data pipeline from COMET API to database.

**Status**: ✅ 4 Service Classes Created

---

## 1. CometApiService

**File**: `app/Services/CometApiService.php`  
**Purpose**: Handles all HTTP communication with COMET REST API

### Features

**Authentication**:
- HTTP Basic Auth with credentials from `kp_api.config`
- Username: `nkprigorje`
- Password: `3c6nR$dS`
- Base URL: `https://api-hns.analyticom.de`

**Automatic Caching**:
```
- Competitions: 24 hours
- Rankings: 6 hours
- Matches: 2 hours
- Match Events: 10 minutes
- Player Stats: 6 hours
- Teams: 7 days
```

**Error Handling**:
- Retry logic with exponential backoff (3 attempts)
- Rate limit detection (HTTP 429)
- 404 handling (returns null)
- 500+ server errors trigger retries
- Comprehensive logging

### Key Methods

```php
// Competitions
getCompetitionsByOrganisation(int $organisationFifaId, int $season = null)
getCompetition(int $competitionFifaId)

// Matches
getMatches(int $competitionFifaId, array $filters = [])
getMatch(int $matchFifaId)
getMatchEvents(int $matchFifaId)
getMatchStatistics(int $matchFifaId)
getMatchPlayerStatistics(int $matchFifaId)

// Rankings & Scores
getRankings(int $competitionFifaId)
getTopScorers(int $competitionFifaId)

// Teams & Players
getTeam(int $teamFifaId)
getTeamSquad(int $teamFifaId)
getTeamStatistics(int $teamFifaId, int $competitionFifaId = null)
getPlayer(int $playerFifaId)
getPlayerStatistics(int $playerFifaId, int $competitionFifaId = null, int $season = null)

// Cache Management
clearCache(string $type, int $id = null)
getRateLimitStatus(): array
```

### Example Usage

```php
$cometApi = new CometApiService();

// Get all competitions for organisation
$competitions = $cometApi->getCompetitionsByOrganisation(organisationId: 12345, season: 2024);

// Get matches (cached for 2 hours)
$matches = $cometApi->getMatches(competitionFifaId: 67890);

// Get match events with goals and cards
$events = $cometApi->getMatchEvents(matchFifaId: 111111);

// Get player statistics
$playerStats = $cometApi->getPlayerStatistics(
    playerFifaId: 222222,
    competitionFifaId: 67890,
    season: 2024
);

// Check rate limit status
$status = $cometApi->getRateLimitStatus();
echo "Calls remaining: " . $status['calls_remaining'];
```

---

## 2. StatisticsCalculator

**File**: `app/Services/StatisticsCalculator.php`  
**Purpose**: Aggregates per-match player data into season-long statistics

### Key Methods

#### Calculate Season Statistics

```php
// Calculate all statistics for a competition
calculateCompetitionStatistics(int $competitionFifaId, int $teamFifaId = null, int $season = null)

// Aggregate for one specific player
aggregatePlayerStatistics(
    int $playerFifaId,
    int $teamFifaId,
    int $competitionFifaId,
    int $season
)
```

### Statistics Calculated

**Aggregations** (Sum):
- Appearances, starts, substitutions
- Minutes played
- Goals, assists, penalty goals
- Cards (yellow, red)
- Fouls, tackles, interceptions, clearances
- Shots (on/off target), passes
- Crosses, dribbles, duels, headers
- Saves, goals conceded (GK specific)

**Percentages** (Calculated):
- Shot accuracy: `(shots_on_target / total_shots) * 100`
- Pass accuracy: `(passes_completed / passes_attempted) * 100`
- Cross accuracy: `(crosses_completed / crosses_attempted) * 100`
- Dribble success rate: `(dribbles_completed / dribbles_attempted) * 100`
- Duel success rate: `(duels_won / total_duels) * 100`
- Win/Draw/Loss percentage: `(matches / appearances) * 100`
- Save percentage (GK): `(saves / (saves + goals_conceded)) * 100`

**Per-Game Averages**:
- Minutes per game
- Goals per game
- Assists per game
- Cards per game
- Fouls per game
- Tackles per game
- Shots per game
- Passes per game
- Duels per game
- Goals conceded per game (GK)

**Special Calculations**:
- `goal_assist_ratio`: `goals / assists`
- `clean_sheets` (GK): Matches where `goals_conceded = 0`
- `man_of_match`: Count of matches with rating >= 8.5
- `total_cards`: `yellow_cards + red_cards`

### Example Usage

```php
$calculator = new StatisticsCalculator();

// After a match is finished
$calculator->calculateMatchStatistics(matchFifaId: 111111);

// Calculate season stats for all players in competition
$calculator->calculateCompetitionStatistics(
    competitionFifaId: 67890,
    season: 2024
);

// Get top performers in competition
$topPerformers = $calculator->getTopPerformers(
    competitionFifaId: 67890,
    season: 2024,
    limit: 10
);

echo "Top Scorers:\n";
foreach ($topPerformers['top_scorers'] as $player) {
    echo "{$player->player_name}: {$player->goals} goals\n";
}

echo "Best Rated:\n";
foreach ($topPerformers['top_rated'] as $player) {
    echo "{$player->player_name}: {$player->avg_rating}/10\n";
}

echo "Cleanest Sheets (Goalkeepers):\n";
foreach ($topPerformers['cleanest_sheets'] as $gk) {
    echo "{$gk->player_name}: {$gk->clean_sheets} clean sheets\n";
}
```

---

## 3. RankingCalculator

**File**: `app/Services/RankingCalculator.php`  
**Purpose**: Computes league standings and team positions

### Ranking Algorithm

**Sorting Criteria** (Priority Order):
1. **Points** (Wins × 3 + Draws × 1)
2. **Goal Difference** (`goals_for - goals_against`)
3. **Goals For** (Total goals scored)
4. **Head-to-Head** (If needed)

**Points System**:
```
- Win = 3 points
- Draw = 1 point
- Loss = 0 points
```

### Key Methods

```php
// Full ranking calculation
calculateCompetitionRankings(int $competitionFifaId)

// Calculate standings up to specific match day
calculateMatchDayRankings(int $competitionFifaId, int $matchDay)

// Update rankings after single match
updateRankingsAfterMatch(int $matchFifaId)

// Recalculate entire season (expensive operation)
recalculateSeasonStatistics(int $competitionFifaId, int $season)

// Query methods
getStandings(int $competitionFifaId): Collection
getTeamPosition(int $competitionFifaId, int $teamFifaId)
getPromotionRelegationZone(int $competitionFifaId, int $promotionPlaces = 2, int $relegationPlaces = 3)
getChaseGroup(int $competitionFifaId, int $pointsMargin = 6)
getFormTable(int $competitionFifaId, int $lastMatches = 5)
```

### Calculated Fields per Team

**Raw Statistics**:
- `matches_played` - Total matches
- `matches_won` - Victories
- `matches_drawn` - Draws
- `matches_lost` - Defeats
- `goals_for` - Goals scored
- `goals_against` - Goals conceded
- `points` - Total league points

**Derived Statistics**:
- `goal_difference` = `goals_for - goals_against`
- `win_percentage` = `(wins / matches_played) * 100`
- `draw_percentage` = `(draws / matches_played) * 100`
- `loss_percentage` = `(losses / matches_played) * 100`
- `goals_per_game` = `goals_for / matches_played`
- `goals_against_per_game` = `goals_against / matches_played`

### Example Usage

```php
$rankingCalc = new RankingCalculator();

// Calculate full standings
$rankingCalc->calculateCompetitionRankings(competitionFifaId: 67890);

// Get current table
$standings = $rankingCalc->getStandings(competitionFifaId: 67890);
foreach ($standings as $i => $team) {
    echo "{$team->position}. {$team->team_name} - {$team->points}pts\n";
}

// Get promotion/relegation zones
$zones = $rankingCalc->getPromotionRelegationZone(
    competitionFifaId: 67890,
    promotionPlaces: 2,    // Top 2 are promoted
    relegationPlaces: 3    // Bottom 3 are relegated
);

echo "PROMOTION (Top 2):\n";
foreach ($zones['promotion'] as $team) {
    echo "  {$team->team_name}\n";
}

echo "SAFE ZONE:\n";
foreach ($zones['safe'] as $team) {
    echo "  {$team->team_name}\n";
}

echo "RELEGATION ZONE (Bottom 3):\n";
foreach ($zones['relegation'] as $team) {
    echo "  {$team->team_name}\n";
}

// Get teams chasing leader
$chaseGroup = $rankingCalc->getChaseGroup(
    competitionFifaId: 67890,
    pointsMargin: 6  // Within 6 points of leader
);

// Get form table (last 5 matches)
$form = $rankingCalc->getFormTable(
    competitionFifaId: 67890,
    lastMatches: 5
);
```

---

## 4. SyncService

**File**: `app/Services/SyncService.php`  
**Purpose**: Orchestrates entire data synchronization pipeline

### Sync Workflow

```
┌─────────────────────────────────────┐
│  Organisation Competitions Sync     │
│  syncOrganisationCompetitions()     │
└──────────────┬──────────────────────┘
               │
      ┌────────┴────────┐
      ▼                 ▼
  Competition     Competition
   Sync            Sync
      │                 │
  ┌───┴─────────────────┴───┐
  │  syncCompetition()      │
  └───┬──────────────┬──────┬┬─┐
      │              │      ││ │
   ┌──▼──┐      ┌───▼──┐   ││ │
   │Match│      │Rank- │   ││ │
   │Sync │      │ings  │   ││ │
   └──┬──┘      └───┬──┘   ││ │
      │             │      ││ │
   ┌──▼─────────┐   │      ││ │
   │ Match      │   │      ││ │
   │ Events &   │   │      ││ │
   │ Statistics │   │      ││ │
   └────────────┘   │      ││ │
                    │      ││ │
              ┌─────┴──┬───┴┴─┤
              ▼        ▼      ▼
           Rankings   Stats   Top
           Updated  Calculated Scorers
```

### Key Methods

```php
// Full sync for organisation
syncOrganisationCompetitions(int $organisationFifaId, int $season = null)

// Sync single competition
syncCompetition(array $competitionData): int

// Sync all matches in competition
syncCompetitionMatches(int $competitionFifaId)

// Sync single match with events
syncMatch(array $matchData, int $competitionFifaId = null): int
syncMatchEvents(int $matchFifaId)
syncMatchPlayerStatistics(int $matchFifaId)

// Sync standings
syncCompetitionRankings(int $competitionFifaId)

// Sync goal leaders
syncCompetitionTopScorers(int $competitionFifaId)

// Check sync status
getSyncStatus(int $competitionFifaId): array
```

### Example Usage - Full Sync

```php
$syncService = app(SyncService::class);

// Sync all competitions for an organisation
$competitionIds = $syncService->syncOrganisationCompetitions(
    organisationFifaId: 12345,
    season: 2024
);

echo "Synced " . count($competitionIds) . " competitions\n";

// Check sync status for a competition
$status = $syncService->getSyncStatus(competitionFifaId: 67890);

echo "Competition: {$status['competition_name']}\n";
echo "Last synced: {$status['last_synced']}\n";
echo "Matches: {$status['completed_matches']}/{$status['total_matches']} completed\n";
echo "Rankings updated: " . ($status['rankings_synced'] ? 'Yes' : 'No') . "\n";
echo "Top scorers updated: " . ($status['top_scorers_synced'] ? 'Yes' : 'No') . "\n";
```

### Example Usage - After Match Completion

```php
// COMET API notifies of match completion
// This is called from event listener or webhook

$syncService = app(SyncService::class);

// Sync the specific match
$syncService->syncMatch($matchData, competitionFifaId: 67890);

// This automatically:
// 1. Saves match details
// 2. Syncs all match events (goals, cards, subs)
// 3. Imports all player statistics from match
// 4. Calculates player season statistics
// 5. Updates league rankings
```

---

## Integration with Models

### Service → Model Flow

```
SyncService
    │
    ├─> Competition::updateOrCreate()
    ├─> GameMatch::updateOrCreate()
    ├─> MatchEvent::create()
    ├─> MatchPlayer::create()
    ├─> TopScorer::create()
    └─> Ranking::create()
        │
        └─> StatisticsCalculator::aggregatePlayerStatistics()
            └─> PlayerStatistic::updateOrCreate()
```

### Querying Synced Data

```php
// Get completed matches with all details
$matches = GameMatch::completed()
    ->with('matchPlayers', 'matchEvents')
    ->get();

foreach ($matches as $match) {
    // Who played?
    $homePlayers = $match->homeTeamPlayers()->played()->get();
    
    // Who scored?
    $goals = $match->goalScorers()->get();
    
    // Who got cards?
    $cards = $match->matchEvents()->whereIn('event_type', ['YELLOW', 'RED'])->get();
}

// Get player season statistics
$playerStats = PlayerStatistic::byPlayer($playerFifaId)
    ->bySeason(2024)
    ->first();

// Get league table
$table = Ranking::byCompetition($competitionId)
    ->ordered()
    ->get();
```

---

## Error Handling

### CometApiService Errors

```php
try {
    $data = $cometApi->getMatches($competitionId);
} catch (Exception $e) {
    Log::error("API Error: " . $e->getMessage());
    // Fallback to cache or skip
}
```

### Sync Errors

```php
try {
    $syncService->syncCompetition($competitionData);
} catch (Exception $e) {
    Log::error("Sync Error: " . $e->getMessage());
    // Report error to admin
    // Retry next scheduled sync
}
```

All errors are logged with full context including:
- Endpoint/method name
- FIFA IDs involved
- Attempt number
- Full error message

---

## Performance Considerations

### Caching Strategy

```
Competitions:      24 hours  (Stable metadata)
Matches:           2 hours   (Results change infrequently)
Match Events:      10 min    (Real-time updates)
Rankings:          6 hours   (Calculated locally)
Player Stats:      6 hours   (Recalculated locally)
Teams:             7 days    (Static data)
Players:           7 days    (Static data)
```

### Bulk Operations

```php
// Recalculate entire season (runs in background job)
$calculator->recalculateSeasonStatistics(
    competitionFifaId: 67890,
    season: 2024
);

// Better to run as:
Artisan::call('sync:season-statistics', [
    'competition_id' => 67890,
    'season' => 2024,
]);
```

### Rate Limiting

- COMET API: 1000 calls/hour limit
- Automatic retry on 429 errors
- Exponential backoff (1 second, 2 seconds, 4 seconds)
- Logging of rate limit status

---

## Deployment Checklist

- [ ] Update `.env` with COMET API credentials
- [ ] Run migrations: `php artisan migrate`
- [ ] Register service classes in container
- [ ] Set up scheduled sync jobs
- [ ] Configure error notifications
- [ ] Test authentication with COMET
- [ ] Verify database connections
- [ ] Monitor first sync for errors

---

## Next Steps

1. **Create Controllers** - API endpoints for accessing synced data
2. **Create Filament Resources** - Admin panel for management
3. **Create Scheduled Jobs** - Automatic periodic sync
4. **Create Event Listeners** - Real-time updates from webhooks
5. **Add Tests** - Unit and integration tests
