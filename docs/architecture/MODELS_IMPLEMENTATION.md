# Football CMS - Database Models & Schema Implementation

## Overview

Complete Eloquent ORM models have been created to support the comprehensive football match statistics system. This document details the 6 new models and their relationships.

**Status**: ✅ All Models Created Successfully

---

## 1. GameMatch Model

**File**: `app/Models/GameMatch.php`  
**Table**: `matches`  
**Purpose**: Represents individual football matches with complete match information

### Key Features

**Match Identification**:
- `match_fifa_id` - FIFA unique identifier
- `competition_fifa_id` - Link to competition
- `match_day` - Which day of competition
- `match_type` - Type of match (OFFICIAL, FRIENDLY, etc.)

**Team Information**:
- Home team: `home_team_fifa_id`, `home_team_name`, `home_team_structure_id`
- Away team: `away_team_fifa_id`, `away_team_name`, `away_team_structure_id`

**Results**:
- `home_final_result` - Goals scored by home team
- `away_final_result` - Goals scored by away team
- `status` - Match status (SCHEDULED, ONGOING, COMPLETED, CANCELLED)

**Match Details**:
- Timing: `date_local`, `date_utc`, `timezone`
- `attendance` - Number of spectators
- Venue: `venue_fifa_id`, `venue_name`
- Officials: Referee, assistants, fourth official info

**Sync Tracking**:
- `synced_at` - Last sync from COMET API
- `last_events_sync` - Last time match events were synced

### Important Methods

```php
$match->competition()           // Get competition
$match->matchPlayers()          // Get all players in match
$match->matchEvents()           // Get match events (goals, cards, subs)
$match->homeTeamPlayers()       // Get home team players
$match->awayTeamPlayers()       // Get away team players
$match->goalScorers()           // Get all goals with player info

// Scopes
GameMatch::completed()          // Only completed matches
GameMatch::ongoing()            // Ongoing matches
GameMatch::scheduled()          // Future matches
GameMatch::byTeam($teamId)      // Matches by specific team
GameMatch::homeTeam($teamId)    // Home matches only
GameMatch::awayTeam($teamId)    // Away matches only

// Query Methods
$match->isHomeTeam($teamId)     // Check if team is home
$match->isAwayTeam($teamId)     // Check if team is away
$match->getOpponentTeamId($teamId) // Get opponent's FIFA ID
$match->getTeamResult($teamId)  // WIN/DRAW/LOSS for team
$match->isCompleted()           // Boolean
$match->hasStarted()            // Started or completed?
```

### Example Usage

```php
// Get all goals from a completed match
$match = GameMatch::completed()->first();
$goals = $match->matchEvents()
    ->goals()
    ->ordered()
    ->get();

// Get team's performance
$teamId = 12345;
$teamPlayers = $match->matchPlayers()
    ->byTeam($teamId)
    ->ordered()
    ->get();

$result = $match->getTeamResult($teamId); // WIN, DRAW, or LOSS
```

---

## 2. Ranking Model

**File**: `app/Models/Ranking.php`  
**Table**: `rankings`  
**Purpose**: Team standings and league table positions

### Key Features

**Position Information**:
- `ranking_fifa_id` - FIFA unique identifier
- `competition_fifa_id` - Which competition
- `team_fifa_id` - Which team
- `position` - Current position in table (1, 2, 3...)
- `group` - For grouped competitions (Group A, B, etc.)

**Match Statistics**:
- `matches_played` - Total matches
- `matches_won` - Wins
- `matches_drawn` - Draws
- `matches_lost` - Losses

**Goals**:
- `goals_for` - Total goals scored
- `goals_against` - Total goals conceded
- `goal_difference` - GF - GA

**Points**:
- `points` - Current league points
- `points_deducted` - Deducted points (if any)
- `adjusted_points` - Points after deductions

**Performance Metrics**:
- `win_percentage`, `draw_percentage`, `loss_percentage` - Calculated percentages
- `goals_per_game` - Average GF per match
- `goals_against_per_game` - Average GA per match

### Example Usage

```php
// Get league table
$rankings = Ranking::byCompetition(competitionId)
    ->ordered()
    ->get();

// Check if team is leader
$isLeader = $ranking->isLeader(); // position === 1

// Check relegation zone
$isRelegation = $ranking->isRelegation(); // position >= 15

// Get group standings
$groupA = Ranking::byGroup('A')->ordered()->get();
```

---

## 3. TopScorer Model

**File**: `app/Models/TopScorer.php`  
**Table**: `top_scorers`  
**Purpose**: Goal and assist scoring leaders

### Key Features

**Player Information**:
- `top_scorer_fifa_id` - FIFA unique identifier
- `player_fifa_id` - Which player
- `player_name`, `player_nationality`, `player_position`

**Team & Club**:
- `team_fifa_id`, `team_name`
- `club_fifa_id`, `club_name`

**Scoring Statistics**:
- `rank` - Position in scorers list (1, 2, 3...)
- `goals` - Total goals including own goals/penalties
- `net_goals` - Clean goals scored
- `assists` - Total assists
- `is_top_scorer` - Boolean flag for top scorer

### Example Usage

```php
// Get top scorers in competition
$scorers = TopScorer::byCompetition(competitionId)
    ->ordered()
    ->get();

// Get leading scorer
$leader = TopScorer::topScorers()
    ->isLeadingScorer() // rank === 1
    ->first();

// Compare goals vs net goals
$goalDiff = $scorer->getGoalDifferenceAttribute();
```

---

## 4. MatchEvent Model

**File**: `app/Models/MatchEvent.php`  
**Table**: `match_events`  
**Purpose**: Individual match events (goals, cards, substitutions, etc.)

### Key Features

**Event Identification**:
- `match_event_fifa_id` - FIFA unique identifier
- `match_fifa_id` - Which match
- `competition_fifa_id` - Which competition
- `event_type` - Type of event (see below)

**Event Types**:
```
GOAL - Regular goal
OWN_GOAL - Own goal
PENALTY - Penalty (converted goal)
YELLOW - Yellow card
RED - Direct red card
SECOND_YELLOW - Second yellow (red card)
SUBSTITUTION - Player substitution
```

**Player Information**:
- `player_fifa_id` - Primary player (scorer, carded player, player subbed out)
- `player_name`, `player_position`
- `second_player_fifa_id` - Secondary player (player coming on in substitution)
- `second_player_name`

**Match Details**:
- `team_type` - HOME or AWAY
- `minute` - Minute of match (0-90+)
- `second` - Exact second (0-59)
- `stoppage_time` - Stoppage time indicator ('+45' etc.)
- `match_phase` - FIRST_HALF, SECOND_HALF, FIRST_ET, SECOND_ET, PEN

**Ordering**:
- `order_id` - Order of events in match

### Important Methods

```php
// Scopes
MatchEvent::goals()             // Only goals
MatchEvent::yellowCards()       // Yellow cards
MatchEvent::redCards()          // Red cards
MatchEvent::substitutions()     // Substitutions only
MatchEvent::penalties()         // Penalty goals
MatchEvent::ownGoals()          // Own goals
MatchEvent::homeTeam()          // Home team events
MatchEvent::awayTeam()          // Away team events
MatchEvent::ordered()           // Chronological order

// Query Methods
$event->isGoal()                // Is it a goal?
$event->isCard()                // Is it a card?
$event->isSubstitution()        // Is it a substitution?
$event->getDisplayEventType()   // Human-readable type
```

### Example Usage

```php
// Get all goals from match
$goals = $match->matchEvents()
    ->goals()
    ->ordered()
    ->get();

// Get yellow and red cards
$cards = $match->matchEvents()
    ->withCards()
    ->get();

// Get substitutions chronologically
$subs = $match->matchEvents()
    ->substitutions()
    ->ordered()
    ->get();
    // Player going off: $sub->player_fifa_id
    // Player going on: $sub->second_player_fifa_id
```

---

## 5. MatchPlayer Model

**File**: `app/Models/MatchPlayer.php`  
**Table**: `match_players`  
**Purpose**: Per-match individual player statistics (40+ fields)

### Key Features

**Player Identification**:
- `match_player_fifa_id` - FIFA unique identifier
- `player_fifa_id`, `player_name`
- `player_position`, `player_nationality`, `player_date_of_birth`
- `shirt_number`

**Squad Status**:
- `captain` - Is captain?
- `goalkeeper` - Is goalkeeper?
- `starting_lineup` - Started match?
- `played` - Appeared in match?

**Performance Data** (40+ fields):

**Minutes & Appearances**:
- `minutes_played` - Total minutes
- `appearance_order` - Which number on bench?
- `substituted_in_minute` - When came on?
- `substituted_out_minute` - When came off?
- `substitution_reason` - WHY substituted?

**Goals & Assists**:
- `goals` - Total goals
- `assists` - Total assists
- `penalty_goals` - Goals from penalties

**Disciplinary**:
- `yellow_cards` - Yellow cards received
- `red_cards` - Red cards
- `second_yellow_red_card` - Second yellow = red?
- `fouls_committed` - Fouls committed
- `fouls_suffered` - Fouls against player

**Goalkeeper Stats**:
- `saves` - Saves made
- `goals_conceded` - Goals conceded
- `goalkeeper_punches` - Punches made

**Attacking Stats**:
- `shots_on_target` - Shots on target
- `shots_off_target` - Shots off target
- `shot_accuracy` - Accuracy percentage
- `cross_attempts`, `cross_completed`, `cross_accuracy`
- `dribbles_attempted`, `dribbles_completed`, `dribble_success_rate`

**Defensive Stats**:
- `tackles`, `tackles_won`
- `interceptions`
- `clearances`, `blocks`

**Passing Stats**:
- `passes_completed`, `passes_attempted`
- `pass_accuracy` - Percentage
- `long_passes_attempted`, `long_passes_completed`

**Duels**:
- `duels_won`, `duels_lost`
- `headers_won`

**Match Result**:
- `match_result` - WIN/DRAW/LOSS for team
- `goals_team_scored`
- `goals_team_conceded`

**Performance**:
- `rating` - Match rating
- `performance_rating`

### Important Methods

```php
// Status checks
$player->isStarter()                // Starting lineup?
$player->isSubstitute()             // Came off bench?
$player->isGoalkeeper()             // GK?
$player->isCaptain()                // Captain?
$player->hasSubstitution()          // Subbed in/out?
$player->hasYellowCard()            // Got yellow?
$player->hasRedCard()               // Got red?
$player->hasSecondYellow()          // Two yellows?

// Scopes
MatchPlayer::homeTeam()             // Home team players
MatchPlayer::awayTeam()             // Away team players
MatchPlayer::starting()             // Starters only
MatchPlayer::substitutes()          // Subs only
MatchPlayer::played()               // Players who played
MatchPlayer::goalscorers()          // Goals > 0
MatchPlayer::withCards()            // Got yellow or red
MatchPlayer::goalkeepers()          // GK only
MatchPlayer::byPosition('FWD')      // By position

// Aggregations
$player->getPlayerStats()           // Key stats array
$player->getDisplayPosition()       // "Forward", "Defender"
$player->getDisplayMatchResult()    // "✓ Win", "= Draw", "✗ Loss"
```

### Example Usage - Complete Match Stats

```php
// After match finished, get all club players' stats
$match = GameMatch::completed()->first();
$clubPlayers = $match->matchPlayers()
    ->homeTeam()
    ->played()
    ->ordered()
    ->get();

foreach ($clubPlayers as $player) {
    // Wer gespielt hat? (Who played)
    echo $player->player_name . "\n";
    
    // Wer hat tor geschossen? (Who scored)
    if ($player->goals > 0) {
        echo "  ⚽ Goals: {$player->goals}\n";
    }
    
    // Wer hat rote karte? (Who got red card)
    if ($player->hasRedCard()) {
        echo "  🔴 Red Card\n";
    } elseif ($player->hasYellowCard()) {
        echo "  🟡 Yellow Cards: {$player->yellow_cards}\n";
    }
    
    // Wieviel minuten? (Minutes played)
    echo "  ⏱️ Minutes: {$player->minutes_played}\n";
    
    // Soviel statistik wie möglich (Maximum stats)
    echo "  Assists: {$player->assists}\n";
    echo "  Passes: {$player->passes_completed}/{$player->passes_attempted}\n";
    echo "  Accuracy: {$player->pass_accuracy}%\n";
    echo "  Tackles: {$player->tackles}\n";
    echo "  Shots: {$player->shots_on_target}/{$player->shots_off_target}\n";
    echo "  Rating: {$player->rating}/10\n";
}
```

---

## 6. PlayerStatistic Model

**File**: `app/Models/PlayerStatistic.php`  
**Table**: `player_statistics`  
**Purpose**: Season aggregation of player statistics (60+ fields)

### Key Features

**Identification**:
- `player_fifa_id` - Which player
- `team_fifa_id` - Which team
- `competition_fifa_id` - Which competition
- `season` - Season year (e.g., 2024)

**Player Info**:
- `player_name`, `player_position`
- `player_nationality`, `player_date_of_birth`, `player_height`, `player_weight`
- `team_name`, `club_name`

### Season Statistics (60+ fields)

**Appearances & Minutes**:
- `appearances` - Matches played
- `starts` - Starting appearances
- `substitutions_on` - Times subbed in
- `substitutions_off` - Times subbed out
- `minutes_played` - Total minutes
- `average_minutes_per_game` - Calculated average

**Goals & Assists**:
- `goals` - Total goals
- `assists` - Total assists
- `goals_per_game` - Average
- `assists_per_game` - Average
- `goal_assist_ratio` - Goals to assists ratio
- `penalty_goals` - Goals from penalties
- `penalty_attempts`, `penalty_success_rate`

**Disciplinary Record**:
- `yellow_cards` - Total yellows
- `red_cards` - Total reds
- `second_yellows` - Number of second yellows
- `total_cards` - All cards
- `cards_per_game` - Average
- `fouls_committed` - Fouls per season
- `fouls_per_game` - Average fouls
- `suspensions_served` - Suspensions completed
- `suspensions_pending` - Upcoming suspensions

**Defensive Stats**:
- `tackles`, `tackles_per_game`
- `interceptions`
- `clearances`, `blocks`

**Attacking Stats**:
- `shots_on_target`, `shots_off_target`, `total_shots`
- `shot_accuracy` - Percentage
- `shots_per_game` - Average
- `pass_attempts`, `passes_completed`
- `pass_accuracy` - Percentage
- `passes_per_game` - Average
- `cross_attempts`, `cross_completed`, `cross_accuracy`
- `long_passes_attempted`, `long_passes_completed`, `long_pass_accuracy`
- `dribbles_attempted`, `dribbles_completed`, `dribble_success_rate`

**Duels & Headers**:
- `duels_won`, `duels_lost`, `duels_per_game`
- `duel_success_rate` - Win percentage
- `headers_won`

**Goalkeeper Stats** (if GK):
- `saves`
- `goals_conceded`
- `clean_sheets` - Matches without conceding
- `save_percentage`
- `goals_conceded_per_game`
- `goalkeeper_punches`, `goalkeeper_throws`

**Match Results**:
- `wins` - Matches team won
- `draws` - Drawn matches
- `losses` - Lost matches
- `win_percentage`, `draw_percentage`, `loss_percentage`

**Ratings & Honors**:
- `avg_rating` - Average match rating
- `best_performance_rating` - Best match rating
- `worst_performance_rating` - Worst match rating
- `man_of_match` - Times named man of match

### Important Methods

```php
// Status checks
$stats->isGoalkeeper()              // Is GK?
$stats->isRegular()                 // 10+ appearances?
$stats->isTopScorer()               // Has goals?

// Scopes - Filter by multiple criteria
PlayerStatistic::byPlayer($id)      // Single player
PlayerStatistic::byTeam($id)        // Team players
PlayerStatistic::byCompetition($id) // Competition players
PlayerStatistic::bySeason(2024)     // Season filter
PlayerStatistic::byPosition('FWD')  // Position filter

// Position filters
PlayerStatistic::goalkeepers()      // All GK
PlayerStatistic::defenders()        // All DEF
PlayerStatistic::midfielders()      // All MID
PlayerStatistic::forwards()         // All FWD

// Performance filters
PlayerStatistic::regular()          // 10+ appearances
PlayerStatistic::topScorers()       // Top 10 goal scorers
PlayerStatistic::assists()          // Top 10 assist leaders
PlayerStatistic::topRated()         // Top 10 ratings
PlayerStatistic::mostCapped()       // Most appearances

// Data aggregation
$stats->getCareerStats()            // Key stats array
$stats->getPerformanceMetrics()     // Performance ratios
$stats->getSuspensionStatus()       // Card/suspension info
$stats->getGoalkeeperStats()        // GK-specific stats
$stats->getDisplayPosition()        // Human-readable position
```

### Example Usage - Complete Season Statistics

```php
// Get all player statistics for competition
$players = PlayerStatistic::byCompetition($competitionId)
    ->bySeason(2024)
    ->mostCapped()  // Sort by appearances
    ->get();

foreach ($players as $player) {
    echo "{$player->player_name} ({$player->team_name})\n";
    
    // Grundlegende Statistiken (Basic stats)
    echo "Appearances: {$player->appearances}\n";
    echo "Minutes: {$player->minutes_played}\n";
    echo "Average per game: {$player->average_minutes_per_game}\n";
    
    // Offensive
    echo "Goals: {$player->goals} ({$player->goals_per_game}/game)\n";
    echo "Assists: {$player->assists} ({$player->assists_per_game}/game)\n";
    
    // Disciplinary
    echo "Cards: {$player->total_cards} ({$player->yellow_cards}Y {$player->red_cards}R)\n";
    
    // Advanced
    echo "Pass Accuracy: {$player->pass_accuracy}%\n";
    echo "Shot Accuracy: {$player->shot_accuracy}%\n";
    echo "Duel Success: {$player->duel_success_rate}%\n";
    
    // Performance
    echo "Rating: {$player->avg_rating}/10\n";
    echo "Performance Range: {$player->worst_performance_rating} - {$player->best_performance_rating}\n";
    echo "Man of Match: {$player->man_of_match}x\n";
}
```

---

## Database Relationships

### Relationship Diagram

```
Competition (1)
    ├── GameMatch (many) → matches
    ├── Ranking (many) → rankings
    ├── TopScorer (many) → top_scorers
    └── MatchEvent (many) → match_events

GameMatch (1)
    ├── Competition (1) ← belongsTo
    ├── MatchPlayer (many) → players in match
    │   ├── homeTeamPlayers() → filter HOME
    │   ├── awayTeamPlayers() → filter AWAY
    │   └── goalScorers() → goals > 0
    └── MatchEvent (many) → all events

MatchPlayer (1)
    ├── GameMatch (1) ← belongsTo
    ├── Competition (1) ← belongsTo
    └── MatchEvent (many) ← via player_fifa_id

MatchEvent (1)
    ├── GameMatch (1) ← belongsTo
    ├── Competition (1) ← belongsTo
    ├── MatchPlayer (1) → primary player
    └── MatchPlayer (1) → second player (subs)

PlayerStatistic (1)
    ├── Competition (1) ← belongsTo
    └── MatchPlayer (many) ← via player_fifa_id
```

---

## Usage Patterns

### Pattern 1: Match Results with Complete Stats

```php
$match = GameMatch::completed()
    ->where('home_team_fifa_id', $teamId)
    ->first();

// Get match details
echo "Match: {$match->home_team_name} vs {$match->away_team_name}\n";
echo "Result: {$match->home_final_result}-{$match->away_final_result}\n";
echo "Attendance: {$match->attendance}\n";

// Get all players who played with stats
$players = $match->matchPlayers()
    ->where('team_type', 'HOME')
    ->played()
    ->get();

foreach ($players as $player) {
    $stats = $player->getPlayerStats();
    // Display: goals, assists, rating, etc.
}
```

### Pattern 2: Player Season Performance

```php
$player = PlayerStatistic::byPlayer($playerFifaId)
    ->bySeason(2024)
    ->first();

// Aggregated season data
$metrics = $player->getPerformanceMetrics();

if ($player->isGoalkeeper()) {
    $gkStats = $player->getGoalkeeperStats();
} else {
    // Outfield player stats
}
```

### Pattern 3: Match Events Timeline

```php
$match = GameMatch::find($matchId);

// Chronological event timeline
$events = $match->matchEvents()->ordered()->get();

foreach ($events as $event) {
    echo "{$event->minute}' - {$event->getDisplayEventType()}: {$event->player_name}\n";
    
    if ($event->isSubstitution()) {
        echo "   Out: {$event->player_name}\n";
        echo "   In: {$event->second_player_name}\n";
    }
}
```

### Pattern 4: Competition Statistics

```php
$competition = Competition::find($competitionId);

// League table
$table = $competition->rankings()
    ->ordered()
    ->get();

// Top scorers
$scorers = $competition->topScorers()
    ->ordered()
    ->get();

// Top rated players
$topPlayers = PlayerStatistic::byCompetition($competitionId)
    ->topRated()
    ->get();
```

---

## Data Synchronization

All models include a `synced_at` timestamp field:

```php
// Check if data is fresh
$match = GameMatch::find($id);
if ($match->synced_at->isToday()) {
    // Data was synced today
}

// Find stale data
$staleMatches = GameMatch::where('synced_at', '<', now()->subDay())
    ->get();
```

---

## Summary

**6 New Eloquent Models Created**:

| Model | Table | Purpose | Key Fields |
|-------|-------|---------|-----------|
| **GameMatch** | matches | Match information | 30+ fields |
| **Ranking** | rankings | League table | 20+ fields |
| **TopScorer** | top_scorers | Goal/assist leaders | 15+ fields |
| **MatchEvent** | match_events | Match events | Goals, cards, subs |
| **MatchPlayer** | match_players | Per-match stats | **40+ fields** |
| **PlayerStatistic** | player_statistics | Season aggregation | **60+ fields** |

**Total Statistics Captured Per Player**: 
- **Per Match**: 40+ fields covering goals, assists, passes, tackles, cards, ratings
- **Per Season**: 60+ fields with aggregations, percentages, and performance metrics

This comprehensive schema enables full match analysis and player evaluation for your football CMS!
