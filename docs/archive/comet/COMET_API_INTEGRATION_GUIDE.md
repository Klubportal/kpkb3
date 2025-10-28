# Comet REST API Integration Guide

**Version**: 1.0  
**Last Updated**: 2024  
**Status**: Production Ready

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Models & Relationships](#models--relationships)
5. [Service Layer](#service-layer)
6. [API Endpoints](#api-endpoints)
7. [Sync Workflow](#sync-workflow)
8. [Player Profiles](#player-profiles)
9. [Error Handling](#error-handling)
10. [Best Practices](#best-practices)

---

## Overview

The Comet REST API Integration provides real-time synchronization with Comet Sports Data API to manage:

- **Clubs**: FIFA ID mapping, extended metadata, sync status
- **Competitions**: Leagues, tournaments, seasons, standings
- **Matches**: Match schedules, results, live status, events
- **Players**: Profiles, statistics, competition-specific data
- **Rankings**: League tables, standings, position tracking
- **Match Events**: Goals, cards, substitutions, detailed match events

### Key Features

✅ Multi-tenant support with automatic data isolation  
✅ Real-time sync with Comet API  
✅ Comprehensive audit logging (comet_syncs table)  
✅ Caching strategy for API efficiency  
✅ Error handling and retry logic  
✅ Player profile enrichment with statistics  
✅ Competition standings and rankings  
✅ Live match tracking  

---

## Architecture

### Components

```
┌─────────────────────────────────────────────────────────┐
│                   HTTP Clients                           │
│    (Web, Mobile, Desktop Applications)                   │
└────────────┬────────────────────────────────────────────┘
             │ HTTPS
┌────────────▼────────────────────────────────────────────┐
│          CometController (Api\CometController)           │
│  - Request validation                                    │
│  - Response formatting                                   │
│  - Authorization checks (auth:sanctum)                   │
└────────────┬────────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────────┐
│       CometApiService (Services\CometApiService)         │
│  - Sync orchestration                                    │
│  - Data transformation                                   │
│  - Caching management                                    │
│  - Audit logging                                         │
└────────────┬────────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────────┐
│              Eloquent Models (Models/*)                  │
│  - Competition, Ranking, GameMatch                       │
│  - Player, PlayerCompetitionStat                         │
│  - MatchEvent, ClubExtended, CometSync                   │
└────────────┬────────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────────┐
│              Database (Multi-tenant)                     │
│  - 9 Comet-related tables                                │
│  - All scoped to tenant_id                               │
│  - Proper foreign keys & indexes                         │
└─────────────────────────────────────────────────────────┘
```

### Data Flow

```
FIFA Club ID
    ↓
CometApiService::syncClubByFifaId()
    ↓
├─→ getClubInfo($fifaId)
│   └─→ Comet API → Club record
│
├─→ getClubCompetitions($fifaId, $season)
│   └─→ Comet API → for each competition:
│       ├─→ syncCompetition()
│       ├─→ syncRankings() → Comet API → league table
│       └─→ syncMatches() → Comet API → matches
│           └─→ syncMatchEvents() → Comet API → events
│
└─→ getTeamPlayers($fifaId)
    └─→ Comet API → Player records → syncClubPlayers()

CometSync audit log tracks every operation
```

---

## Database Schema

### Tables Overview

| Table | Purpose | Records | Key Fields |
|-------|---------|---------|-----------|
| `competitions` | Leagues/tournaments | N | comet_id, name, type, season, status |
| `rankings` | League standings | ~N×20 | position, club_id, points, W/D/L |
| `matches` | Match records | ~N×500 | home/away clubs, score, status, minute |
| `match_events` | Goals, cards, subs | ~N×3000 | event_type, minute, player_id |
| `players` | Player profiles | ~N×500 | name, DOB, position, photo, stats |
| `player_competition_stats` | Per-competition stats | ~N×5000 | competition_id, goals, assists, rating |
| `clubs_extended` | FIFA ID mapping | ~N | fifa_id, comet_id, stadium, coach |
| `comet_syncs` | Audit log | ~N×100 | entity_type, action, status, records |
| `club_competitions` | Club-Competition junction | ~N×100 | club_id, competition_id |

*N = number of clubs per tenant*

### Detailed Schema

#### competitions

```sql
CREATE TABLE competitions (
    id BIGINT PRIMARY KEY,
    tenant_id CHAR(36),           -- Multi-tenancy
    comet_id VARCHAR(255) UNIQUE, -- Comet API ID
    name VARCHAR(255),             -- e.g., "Bundesliga"
    type ENUM('league','cup','group','playoff'), -- Competition type
    season INT,                    -- e.g., 2024
    status ENUM('upcoming','active','completed'),
    country VARCHAR(255),          -- e.g., "Germany"
    league_name VARCHAR(255),      -- League info
    start_date DATE,
    end_date DATE,
    settings JSON,                 -- Flexible metadata
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX (tenant_id, comet_id),
    INDEX (season, status)
);
```

#### rankings

```sql
CREATE TABLE rankings (
    id BIGINT PRIMARY KEY,
    tenant_id CHAR(36),
    competition_id BIGINT,         -- Ref to competition
    club_id BIGINT,                -- Ref to club
    position INT,                  -- Current position (1-20)
    played INT,                    -- Matches played
    won INT, drawn INT, lost INT,  -- Win/Draw/Loss
    goals_for INT,                 -- Goals scored
    goals_against INT,             -- Goals conceded
    goal_difference INT,           -- GF - GA
    points INT,                    -- League points
    form VARCHAR(10),              -- e.g., "WWDLD"
    updated_at TIMESTAMP,
    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    UNIQUE (competition_id, club_id),
    INDEX (position)
);
```

#### matches

```sql
CREATE TABLE matches (
    id BIGINT PRIMARY KEY,
    tenant_id CHAR(36),
    competition_id BIGINT,         -- Ref to competition
    comet_id VARCHAR(255),         -- Comet API ID
    home_club_id BIGINT,           -- Ref to home club
    away_club_id BIGINT,           -- Ref to away club
    home_goals INT DEFAULT 0,      -- Final score
    away_goals INT DEFAULT 0,
    home_goals_ht INT,             -- Half-time score
    away_goals_ht INT,
    status ENUM('scheduled','live','finished','cancelled','postponed'),
    kickoff_time TIMESTAMP,
    minute INT,                    -- Current minute (for live)
    stadium VARCHAR(255),
    attendance INT,
    extra_time BOOLEAN DEFAULT FALSE,
    penalty_shootout BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (competition_id) REFERENCES competitions(id),
    FOREIGN KEY (home_club_id) REFERENCES clubs(id),
    FOREIGN KEY (away_club_id) REFERENCES clubs(id),
    UNIQUE (competition_id, comet_id),
    INDEX (status, kickoff_time),
    INDEX (home_club_id, away_club_id)
);
```

#### match_events

```sql
CREATE TABLE match_events (
    id BIGINT PRIMARY KEY,
    tenant_id CHAR(36),
    match_id BIGINT,               -- Ref to match
    comet_id VARCHAR(255),
    event_type ENUM('goal','own_goal','yellow_card','red_card','substitution'),
    minute INT,                    -- Event minute
    player_id BIGINT,              -- Player involved
    related_player_id BIGINT,      -- For substitution (replaced player)
    details JSON,                  -- Flexible event data
    created_at TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id),
    INDEX (match_id, event_type),
    INDEX (minute)
);
```

#### players

```sql
CREATE TABLE players (
    id BIGINT PRIMARY KEY,
    tenant_id CHAR(36),
    club_id BIGINT,                -- Ref to club
    comet_id VARCHAR(255),         -- Comet API ID
    name VARCHAR(255),
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    date_of_birth DATE,
    nationality VARCHAR(3),        -- ISO country code
    position ENUM(                 -- 16 positions
        'GK',   -- Goalkeeper
        'CB', 'LB', 'RB', 'LWB', 'RWB',  -- Defense
        'CM', 'CAM', 'CDM', 'LM', 'RM',  -- Midfield
        'LW', 'RW', 'ST', 'CF', 'SS'     -- Attack
    ),
    shirt_number INT,
    height_cm INT,
    weight_kg INT,
    foot ENUM('left', 'right', 'both'),
    photo_url TEXT,
    status ENUM(
        'active', 'injured', 'suspended',
        'retired', 'loaned_out'
    ),
    injury_info VARCHAR(255),
    return_date DATE,
    market_value_eur INT,          -- Current market value
    average_rating DECIMAL(3,1),   -- 0.0-10.0
    
    -- Career statistics
    total_matches INT DEFAULT 0,
    total_goals INT DEFAULT 0,
    total_assists INT DEFAULT 0,
    total_yellow_cards INT DEFAULT 0,
    total_red_cards INT DEFAULT 0,
    
    -- Season statistics (2024)
    season_matches INT DEFAULT 0,
    season_goals INT DEFAULT 0,
    season_assists INT DEFAULT 0,
    season_yellow_cards INT DEFAULT 0,
    season_red_cards INT DEFAULT 0,
    
    -- Sync tracking
    is_synced BOOLEAN DEFAULT FALSE,
    last_synced_at TIMESTAMP,
    sync_metadata JSON,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    UNIQUE (tenant_id, club_id, comet_id),
    INDEX (position, status),
    INDEX (shirt_number),
    INDEX (market_value_eur DESC)
);
```

#### player_competition_stats

```sql
CREATE TABLE player_competition_stats (
    id BIGINT PRIMARY KEY,
    tenant_id CHAR(36),
    player_id BIGINT,              -- Ref to player
    competition_id BIGINT,         -- Ref to competition
    matches INT DEFAULT 0,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    average_rating DECIMAL(3,1),
    detailed_stats JSON,           -- Flexible stat tracking
    updated_at TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
    UNIQUE (player_id, competition_id),
    INDEX (goals DESC),
    INDEX (assists DESC)
);
```

#### clubs_extended

```sql
CREATE TABLE clubs_extended (
    id BIGINT PRIMARY KEY,
    tenant_id CHAR(36),
    club_id BIGINT,                -- Ref to club
    fifa_id VARCHAR(255) UNIQUE,   -- FIFA club ID
    comet_id VARCHAR(255),         -- Comet API ID
    code VARCHAR(10),              -- Club code
    founded_year INT,
    stadium_name VARCHAR(255),
    stadium_capacity INT,
    coach_name VARCHAR(255),
    coach_info JSON,               -- Coach details
    country VARCHAR(255),
    league_name VARCHAR(255),
    club_info JSON,
    synced_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    UNIQUE (club_id),
    INDEX (fifa_id)
);
```

#### comet_syncs

```sql
CREATE TABLE comet_syncs (
    id BIGINT PRIMARY KEY,
    tenant_id CHAR(36),
    entity_type VARCHAR(50),       -- 'club', 'competition', 'match', etc.
    action VARCHAR(50),            -- 'sync_start', 'synced', 'failed'
    status VARCHAR(20),            -- 'success', 'failed', 'pending'
    records_affected INT DEFAULT 0,
    sync_data JSON,                -- Operation data
    error_message TEXT,
    created_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX (entity_type, status),
    INDEX (created_at DESC)
);
```

#### club_competitions

```sql
CREATE TABLE club_competitions (
    id BIGINT PRIMARY KEY,
    tenant_id CHAR(36),
    club_id BIGINT,
    competition_id BIGINT,
    joined_at TIMESTAMP,
    created_at TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
    UNIQUE (club_id, competition_id)
);
```

---

## Models & Relationships

### Player Model

**Location**: `app/Models/Player.php`

**Attributes**:

```php
// Identity
- $fillable: ['name', 'first_name', 'last_name', 'comet_id', 'shirt_number']

// Personal
- date_of_birth (DATE)
- nationality (3-char code)
- position (15 types: GK, CB, LB, RB, LWB, RWB, CM, CAM, CDM, LM, RM, LW, RW, ST, CF, SS)
- photo_url (TEXT)
- status (active|injured|suspended|retired|loaned_out)

// Physical
- height_cm (INT)
- weight_kg (INT)
- foot (left|right|both)

// Statistics
- market_value_eur (INT)
- average_rating (DECIMAL 0.0-10.0)
- total_matches, total_goals, total_assists (INT)
- season_matches, season_goals, season_assists (INT)

// Sync
- is_synced (BOOLEAN)
- last_synced_at (TIMESTAMP)
- sync_metadata (JSON)
```

**Relationships**:

```php
// Belongs to
$player->club()                    // Player's club

// Has many
$player->competitionStats()        // Stats per competition
$player->matchEvents()             // Events player participated in
```

**Scopes**:

```php
Player::byPosition('ST')           // Filter by position
Player::byClub($clubId)            // Filter by club
Player::active()                   // Active players only
Player::topScorers()               // Order by goals DESC
Player::injured()                  // Injured players
Player::suspended()                // Suspended players
```

**Computed Attributes**:

```php
$player->age                       // Calculated from DOB
$player->fullName                  // first_name + last_name
$player->positionName              // Localized: "Torwart", "Innenverteidiger"
$player->goalScoringRate           // goals / matches (rounded to 2 decimals)
```

### PlayerCompetitionStat Model

**Location**: `app/Models/PlayerCompetitionStat.php`

**Attributes**:

```php
- competition_id (BIGINT)
- player_id (BIGINT)
- matches (INT)
- goals (INT)
- assists (INT)
- yellow_cards (INT)
- red_cards (INT)
- average_rating (DECIMAL)
- detailed_stats (JSON)
```

**Relationships**:

```php
$stat->player()                    // Related player
$stat->competition()               // Related competition
```

**Scopes**:

```php
PlayerCompetitionStat::byCompetition($compId)
PlayerCompetitionStat::topScorers()
PlayerCompetitionStat::byPlayer($playerId)
```

**Computed Attributes**:

```php
$stat->goalsPerMatch               // goals / matches
$stat->assistsPerMatch             // assists / matches
$stat->cardsPerMatch               // (yellow + red) / matches
```

### Competition Model

**Relationships**:

```php
$competition->rankings()           // League standings
$competition->matches()            // All matches
$competition->playerStats()        // Player stats for this competition
$competition->clubs()              // Participating clubs
```

**Scopes**:

```php
Competition::byType('league')
Competition::active()
Competition::bySeason(2024)
```

### Ranking Model

**Relationships**:

```php
$ranking->competition()            // The competition
$ranking->club()                   // The club
```

**Scopes**:

```php
Ranking::byCompetition($compId)
Ranking::ordered()                 // Order by position
```

### GameMatch Model

**Relationships**:

```php
$match->competition()              // The competition
$match->homeClub()                 // Home team
$match->awayClub()                 // Away team
$match->events()                   // Match events
```

**Scopes**:

```php
GameMatch::live()
GameMatch::finished()
GameMatch::upcoming()
GameMatch::byCompetition($compId)
```

### MatchEvent Model

**Relationships**:

```php
$event->match()                    // Related match
$event->player()                   // Player involved
$event->relatedPlayer()            // For substitutions
```

### ClubExtended Model

**Relationships**:

```php
$extended->club()                  // Related club
```

**Scopes**:

```php
ClubExtended::byFifaId($fifaId)
ClubExtended::synced()
ClubExtended::notSynced()
```

### CometSync Model

**Purpose**: Audit logging for all sync operations

**Attributes**:

```php
- entity_type (VARCHAR)           // 'club', 'competition', 'match', 'player'
- action (VARCHAR)                // 'sync_start', 'synced', 'failed'
- status (VARCHAR)                // 'success', 'failed', 'pending'
- records_affected (INT)
- sync_data (JSON)
- error_message (TEXT)
```

**Static Method**:

```php
CometSync::logSync(
    $tenantId,
    'player',
    'synced',
    42,
    ['club_id' => 1, 'competition_id' => 3]
);
```

---

## Service Layer

### CometApiService

**Location**: `app/Services/CometApiService.php`

**Constructor**:

```php
$service = new CometApiService($tenantId, $apiKey = null);
```

#### Public Methods

##### syncClubByFifaId($fifaId, $season = 2024)

Main orchestration method that syncs everything for a club.

```php
$result = $service->syncClubByFifaId('123456', 2024);

// Returns:
[
    'success' => true,
    'club_id' => 1,
    'club' => Club object,
    'competitions_synced' => 5,
    'players_synced' => 23,
    'timestamp' => Carbon instance
]
```

**Flow**:
1. Logs sync start
2. Gets club info from Comet API
3. Creates/updates Club record
4. Updates ClubExtended with FIFA ID
5. Syncs all competitions (standings + matches + events)
6. Syncs all players
7. Logs sync completion

**Throws**: Exception if club not found or sync fails

---

#### Protected Methods

##### syncCompetition($club, $competitionData, $season)

Syncs a single competition.

```php
$count = $this->syncCompetition($club, $compData, 2024);
// Returns: number of competitions synced
```

##### syncRankings($competition, $competitionId)

Syncs league table/standings.

```php
$count = $this->syncRankings($competition, 1001);
// Updates Ranking table with positions, W/D/L, points
```

##### syncMatches($competition, $competitionId)

Syncs all matches for a competition.

```php
$count = $this->syncMatches($competition, 1001);
// Creates/updates Match records
// Triggers event sync for live/finished matches
```

##### syncMatchEvents($match, $matchId)

Syncs events for a specific match.

```php
$count = $this->syncMatchEvents($match, 5001);
// Creates MatchEvent records (goals, cards, subs)
```

##### syncClubPlayers($club, $fifaId, $playersData)

Syncs all players for a club.

```php
$count = $this->syncClubPlayers($club, '123456', $playerArray);
// Creates/updates Player records with full profile
```

---

#### API Call Methods

##### getClubInfo($fifaId)

Get club basic information.

```php
$clubData = $service->getClubInfo('123456');
// Returns: array with club name, stadium, coach, etc.
// Cached 24 hours
```

##### getClubCompetitions($fifaId, $season = 2024)

Get all competitions a club participates in.

```php
$competitions = $service->getClubCompetitions('123456', 2024);
// Returns: array of competition objects
// Cached 12 hours
```

##### getStandings($competitionId)

Get league table for a competition.

```php
$standings = $service->getStandings(1001);
// Returns: array indexed by position
// Cached 6 hours
```

##### getMatches($competitionId)

Get all matches for a competition.

```php
$matches = $service->getMatches(1001);
// Returns: array of match objects
// Cached 2 hours
```

##### getMatchEvents($matchId)

Get events for a specific match.

```php
$events = $service->getMatchEvents(5001);
// Returns: array of event objects (goals, cards, subs)
// Cached 1 hour
```

##### getTeamPlayers($fifaId)

Get all players from a club.

```php
$players = $service->getTeamPlayers('123456');
// Returns: array of player objects with stats
// Cached 12 hours
```

##### getTopScorers($competitionId, $limit = 10)

Get top scorers for a competition.

```php
$topScorers = $service->getTopScorers(1001, 10);
// Returns: array of player objects sorted by goals DESC
// Cached 6 hours
```

---

### Caching Strategy

All API calls are cached to minimize requests:

| Method | Cache Duration | Rationale |
|--------|---------------|-----------| 
| getClubInfo | 24 hours | Club data changes infrequently |
| getClubCompetitions | 12 hours | Competitions stay same all season |
| getStandings | 6 hours | Updated daily or when matches finish |
| getMatches | 2 hours | Updated frequently (new matches) |
| getMatchEvents | 1 hour | Updated in real-time during matches |
| getTeamPlayers | 12 hours | Squad stays mostly same all season |
| getTopScorers | 6 hours | Updated when matches finish |

**Manual Cache Clear**:

```php
$service->clearCache('club', 123456);      // Clear specific
$service->clearCache();                    // Clear all
```

---

## API Endpoints

**Base URL**: `/api/comet`  
**Authentication**: Required (auth:sanctum)  
**Response Format**: JSON  

### Dashboard

#### GET /api/comet/dashboard

Get overview dashboard with summary data.

**Response**:
```json
{
    "competitions": 5,
    "clubs": 18,
    "players": 342,
    "live_matches": 2,
    "recent_matches": [
        {
            "id": 1,
            "home_club": "Bayern",
            "away_club": "Dortmund",
            "score": "2-1",
            "status": "finished"
        }
    ]
}
```

---

### Club Management

#### POST /api/comet/clubs/{fifaId}/sync

Sync club by FIFA ID (all competitions + players).

**Parameters**:
- `fifaId` (path, required): Club FIFA ID

**Response**:
```json
{
    "message": "Club synced successfully",
    "data": {
        "success": true,
        "club_id": 1,
        "club": { /* Club object */ },
        "competitions_synced": 5,
        "players_synced": 23,
        "timestamp": "2024-01-15T10:30:00Z"
    }
}
```

---

#### GET /api/comet/clubs/{clubId}

Get club information with extended metadata.

**Parameters**:
- `clubId` (path, required): Club ID from database

**Response**:
```json
{
    "club": {
        "id": 1,
        "name": "FC Bayern Munich",
        "country": "Germany"
    },
    "extended_info": {
        "fifa_id": "123456",
        "stadium_name": "Allianz Arena",
        "coach_name": "Thomas Tuchel",
        "synced_at": "2024-01-15T10:30:00Z"
    },
    "competitions": 5,
    "players": 23
}
```

---

#### GET /api/comet/clubs/{clubId}/competitions

Get all competitions for a club.

**Parameters**:
- `clubId` (path, required): Club ID

**Response**:
```json
[
    {
        "competition": { /* Competition object */ },
        "ranking": {
            "position": 1,
            "played": 18,
            "won": 12,
            "drawn": 3,
            "lost": 3,
            "goals_for": 45,
            "goals_against": 18,
            "points": 39
        },
        "position": 1,
        "points": 39
    }
]
```

---

#### GET /api/comet/clubs/{clubId}/players

Get all players for a club.

**Query Parameters**:
- `status` (optional): active|injured|suspended (default: active)
- `position` (optional): Filter by position (GK, CB, ST, etc.)
- `per_page` (optional): Pagination (default: 20)

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "name": "Thomas Müller",
            "position": "ST",
            "shirt_number": 25,
            "date_of_birth": "1988-09-13",
            "nationality": "DE",
            "market_value_eur": 8000000,
            "average_rating": 8.2,
            "total_goals": 234,
            "photo_url": "https://..."
        }
    ],
    "pagination": { /* Standard pagination */ }
}
```

---

#### GET /api/comet/clubs/{clubId}/matches

Get all matches for a club (home + away).

**Query Parameters**:
- `status` (optional): scheduled|live|finished|cancelled
- `per_page` (optional): Pagination (default: 20)

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "home_club_id": 1,
            "away_club_id": 2,
            "home_goals": 2,
            "away_goals": 1,
            "status": "finished",
            "kickoff_time": "2024-01-15T15:30:00Z"
        }
    ],
    "pagination": { /* Pagination */ }
}
```

---

#### GET /api/comet/clubs/{clubId}/live-matches

Get live matches for a club.

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "home_club": { /* Club object */ },
            "away_club": { /* Club object */ },
            "home_goals": 1,
            "away_goals": 0,
            "minute": 34,
            "status": "live",
            "events": [ /* Match events */ ]
        }
    ],
    "count": 1
}
```

---

#### POST /api/comet/clubs/{clubId}/update-from-comet

Update club data from Comet API.

**Response**:
```json
{
    "message": "Club updated from Comet API",
    "data": { /* Sync result */ }
}
```

---

### Competitions

#### GET /api/comet/competitions

List all competitions.

**Query Parameters**:
- `status` (optional): active|upcoming|completed (default: active)
- `per_page` (optional): Default 20

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "comet_id": "1001",
            "name": "Bundesliga",
            "type": "league",
            "season": 2024,
            "status": "active",
            "country": "Germany"
        }
    ],
    "pagination": { /* Pagination */ }
}
```

---

#### GET /api/comet/competitions/{competitionId}

Get specific competition with standings and recent matches.

**Response**:
```json
{
    "data": { /* Competition object */ },
    "standings": [
        {
            "position": 1,
            "club_id": 1,
            "club": { /* Club object */ },
            "played": 18,
            "won": 12,
            "points": 39
        }
    ],
    "recent_matches": [
        { /* Match objects */ }
    ]
}
```

---

#### GET /api/comet/competitions/{competitionId}/standings

Get league table for a competition.

**Response**:
```json
{
    "data": [
        {
            "position": 1,
            "club_id": 1,
            "played": 18,
            "won": 12,
            "drawn": 3,
            "lost": 3,
            "goals_for": 45,
            "goals_against": 18,
            "goal_difference": 27,
            "points": 39,
            "form": "WWWDL",
            "club": { /* Club object */ }
        }
    ],
    "total": 18
}
```

---

#### GET /api/comet/competitions/{competitionId}/matches

Get all matches for a competition.

**Query Parameters**:
- `status` (optional): scheduled|live|finished
- `per_page` (optional): Default 20

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "home_club_id": 1,
            "away_club_id": 2,
            "home_goals": 2,
            "away_goals": 1,
            "status": "finished",
            "kickoff_time": "2024-01-15T15:30:00Z",
            "homeClub": { /* Club */ },
            "awayClub": { /* Club */ }
        }
    ],
    "pagination": { /* Pagination */ }
}
```

---

#### GET /api/comet/competitions/{competitionId}/top-scorers

Get top scorers for a competition.

**Query Parameters**:
- `limit` (optional): How many to return (default: 10)

**Response**:
```json
{
    "data": [
        {
            "player_id": 1,
            "competition_id": 1,
            "player": {
                "id": 1,
                "name": "Robert Lewandowski",
                "position": "ST",
                "photo_url": "https://..."
            },
            "goals": 18,
            "assists": 4,
            "matches": 15,
            "average_rating": 8.7
        }
    ],
    "total": 10
}
```

---

### Matches

#### GET /api/comet/matches/{matchId}

Get specific match with all events.

**Response**:
```json
{
    "match": {
        "id": 1,
        "home_club_id": 1,
        "away_club_id": 2,
        "home_goals": 2,
        "away_goals": 1,
        "status": "finished",
        "kickoff_time": "2024-01-15T15:30:00Z",
        "homeClub": { /* Club */ },
        "awayClub": { /* Club */ }
    },
    "events": {
        "goal": [
            {
                "minute": 12,
                "player_id": 1,
                "player": { /* Player */ }
            }
        ],
        "yellow_card": [
            {
                "minute": 34,
                "player_id": 5,
                "player": { /* Player */ }
            }
        ]
    },
    "summary": {
        "goals": 3,
        "yellow_cards": 4,
        "red_cards": 0
    }
}
```

---

### Players

#### GET /api/comet/players/{playerId}

Get detailed player profile with all statistics.

**Response**:
```json
{
    "player": { /* Full Player object */ },
    "profile": {
        "name": "Robert Lewandowski",
        "age": 35,
        "position": "ST",
        "position_name": "Stürmer",
        "nationality": "PL",
        "shirt_number": 9,
        "photo_url": "https://...",
        "height_cm": 184,
        "weight_kg": 81,
        "foot": "right",
        "market_value_eur": 30000000,
        "average_rating": 8.6
    },
    "career_stats": {
        "total_matches": 456,
        "total_goals": 234,
        "total_assists": 89,
        "total_yellow_cards": 45,
        "total_red_cards": 2,
        "goals_per_match": 0.51
    },
    "season_stats": {
        "matches": 15,
        "goals": 18,
        "assists": 4,
        "yellow_cards": 2,
        "red_cards": 0
    },
    "competition_stats": [
        {
            "competition_id": 1,
            "competition": { /* Competition */ },
            "goals": 18,
            "assists": 4,
            "matches": 15,
            "average_rating": 8.7
        }
    ],
    "recent_events": [
        {
            "id": 1,
            "event_type": "goal",
            "minute": 45,
            "match": { /* Match */ },
            "created_at": "2024-01-15T20:00:00Z"
        }
    ]
}
```

---

## Sync Workflow

### Complete Sync Flow

```
User triggers sync via API
    ↓
POST /api/comet/clubs/123456/sync
    ↓
CometController::syncClubByFifaId($fifaId)
    ↓
CometApiService::syncClubByFifaId(123456)
    ↓
├─→ CometSync::logSync('sync_start')
│
├─→ getClubInfo(123456) [API call]
│   └─→ Create/Update Club record
│   └─→ Create/Update ClubExtended with FIFA ID
│
├─→ getClubCompetitions(123456, 2024) [API call]
│   └─→ For each competition:
│       ├─→ syncCompetition()
│       │   └─→ Create/Update Competition
│       │   └─→ Attach club to competition
│       │
│       ├─→ syncRankings() [API: getStandings()]
│       │   └─→ Create/Update Ranking records
│       │
│       ├─→ syncMatches() [API: getMatches()]
│       │   ├─→ Create/Update Match records
│       │   └─→ For live/finished:
│       │       └─→ syncMatchEvents() [API: getMatchEvents()]
│       │           └─→ Create/Update MatchEvent records
│
├─→ getTeamPlayers(123456) [API call]
│   └─→ syncClubPlayers()
│       └─→ For each player:
│           └─→ Create/Update Player record
│
└─→ CometSync::logSync('synced', counts)

Return result with sync counts
    ↓
Response to client
```

---

### Sync Timing

- **Full Sync**: ~10-30 seconds (depending on club's competitions/players)
- **Partial Update**: ~2-5 seconds (standings + recent matches)
- **Cache Hit**: <100ms (if data cached)

### Audit Logging

Every sync operation is logged to `comet_syncs` table:

```php
CometSync::logSync(
    tenantId: $tenantId,           // Auto-scoped
    entity_type: 'club',           // What was synced
    action: 'synced',              // What happened
    records_affected: 23,          // How many records
    sync_data: [                   // Context data
        'fifa_id' => 123456,
        'competitions_synced' => 5,
        'players_synced' => 23
    ]
);
```

**Query sync history**:

```php
// Recent syncs
CometSync::recent(days: 7)->get();

// Failed syncs
CometSync::failed()->get();

// By entity
CometSync::byEntity('player')->get();

// Get errors
CometSync::whereNotNull('error_message')->get();
```

---

## Player Profiles

### Display Player Profile

**Complete player data for frontend**:

```php
GET /api/comet/players/1
```

Returns comprehensive player object with:

- **Identity**: Name, DOB, nationality, shirt number
- **Physical**: Height, weight, foot preference
- **Position**: 16 positions with localized names
- **Status**: Active, injured, suspended, retired, loaned
- **Statistics**: Career totals, season stats, per-competition stats
- **Value**: Market value in EUR, average rating
- **Media**: Photo URL for display
- **Relationships**: Club, competition stats, match events

---

### Player Statistics Display

#### Season Statistics

```json
{
    "season_matches": 15,
    "season_goals": 18,
    "season_assists": 4,
    "season_yellow_cards": 2,
    "season_red_cards": 0
}
```

#### Per-Competition Statistics

```json
{
    "competition_id": 1,
    "matches": 15,
    "goals": 18,
    "assists": 4,
    "yellow_cards": 2,
    "red_cards": 0,
    "average_rating": 8.7
}
```

#### Computed Attributes

```php
$player->age                       // From DOB
$player->fullName                  // first + last
$player->positionName              // "Stürmer" (German)
$player->goalScoringRate           // 0.51 (goals/matches)
```

---

### Player Search & Filtering

```php
// By position
Player::byPosition('ST')->paginate(20);

// By club
Player::byClub($clubId)->paginate(20);

// Active only
Player::active()->paginate(20);

// Top scorers
Player::topScorers()->limit(10)->get();

// Injured players
Player::injured()->get();

// Suspended
Player::suspended()->get();

// All combined
Player::byClub($clubId)
    ->byPosition('ST')
    ->active()
    ->orderByDesc('season_goals')
    ->paginate(20);
```

---

## Error Handling

### HTTP Error Responses

#### 400 Bad Request

```json
{
    "message": "Club does not have FIFA ID assigned",
    "error": "Details..."
}
```

#### 404 Not Found

```json
{
    "message": "Resource not found",
    "error": "Club/competition/player not found"
}
```

#### 500 Server Error

```json
{
    "message": "Sync failed",
    "error": "Internal error message"
}
```

---

### Service Layer Error Handling

**Retry Logic**:
- Max 3 retries for failed API requests
- 1 second delay between retries
- Exponential backoff for 429 (rate limit)

**Error Logging**:
- All errors logged to `comet_syncs` table
- Error messages stored for audit trail
- Exceptions thrown up to controller

**Graceful Degradation**:
- If competition sync fails, continues with next
- If player sync fails, continues
- Partial failures don't block entire sync

---

### Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| "Club not found" | Invalid FIFA ID | Verify FIFA ID is correct format |
| Sync timeout | Too many players | Break into smaller syncs, use queue jobs |
| Duplicate entries | Race condition | Use updateOrCreate, not create |
| Stale data | Cache not cleared | Clear cache after manual updates |
| 429 Too Many Requests | Rate limit hit | Increase cache times, add delay |

---

## Best Practices

### 1. Cache Management

```php
// Always use cache to minimize API calls
$standings = $service->getStandings($compId);  // Cached 6h

// Clear cache only when necessary
$service->clearCache('competition', $compId);

// Never cache sensitive, rapidly-changing data
// (use live endpoints instead)
```

---

### 2. Async Syncs

For large clubs with many competitions/players, use queued jobs:

```php
// Dispatch as job instead of sync
SyncClubFromCometJob::dispatch($clubId, $fifaId)
    ->onConnection('redis')
    ->onQueue('high');
```

---

### 3. Data Validation

```php
// Always validate FIFA ID format before sync
if (!preg_match('/^\d{4,6}$/', $fifaId)) {
    throw new Exception("Invalid FIFA ID format");
}
```

---

### 4. Multi-tenant Isolation

```php
// All queries are automatically scoped by tenant
$club = Club::where('tenant_id', auth()->user()->tenant_id)
    ->find($clubId);

// Service layer gets tenantId from constructor
$service = new CometApiService(auth()->user()->tenant_id);
```

---

### 5. Audit Trail

```php
// Always check sync history
$syncs = CometSync::where('tenant_id', $tenantId)
    ->orderByDesc('created_at')
    ->limit(50)
    ->get();

// Monitor failures
$failures = CometSync::failed()
    ->where('entity_type', 'club')
    ->get();
```

---

### 6. Performance Optimization

```php
// Use eager loading to avoid N+1 queries
$clubs = Club::with(['extended', 'competitions', 'players'])
    ->paginate(20);

// Index frequently queried fields
// Competitions: (season, status)
// Players: (position, status)
// Matches: (status, kickoff_time)

// Use pagination for large result sets
Player::byClub($clubId)->paginate(20);  // Not get() for 500+ records
```

---

### 7. Season Management

```php
// Always specify season for consistency
$syncs = $service->syncClubByFifaId($fifaId, 2024);

// Query by season
Competition::bySeason(2024)->get();
```

---

### 8. Live Match Tracking

```php
// Get live matches (updated every minute)
GameMatch::where('status', 'live')->get();

// Events refresh every 10 seconds
MatchEvent::where('match_id', $matchId)->latest()->get();

// Clear cache during matches
$service->clearCache('match_events', $matchId);
```

---

### 9. Rate Limiting

```php
// Comet API: ~1000 requests/hour
// Implement own rate limiting

// Cache all endpoints to minimize requests:
// - getClubInfo: 24h cache
// - getStandings: 6h cache  
// - getMatches: 2h cache
// - getMatchEvents: 1h cache (or 10min for live)

// Monitor rate limit status
$status = $service->getRateLimitStatus();
if ($status['calls_remaining'] < 100) {
    Log::warning("Rate limit approaching");
}
```

---

### 10. Testing

```php
// Mock Comet API responses in tests
Http::fake([
    'https://api.soccer.sportdata.de/v2.0/*' => Http::response([...], 200)
]);

// Test sync workflow
$service->syncClubByFifaId('123456');
$this->assertDatabaseHas('clubs_extended', ['fifa_id' => '123456']);
$this->assertDatabaseHas('players', ['club_id' => $clubId]);

// Test error handling
$service->syncClubByFifaId('invalid');  // Should throw
```

---

## Configuration

### Config File: `config/services.php`

```php
'comet' => [
    'api_key' => env('COMET_API_KEY'),
    'base_url' => env('COMET_API_URL', 'https://api.soccer.sportdata.de/v2.0/'),
    'cache_ttl' => env('COMET_CACHE_TTL', 3600),  // seconds
    'max_retries' => env('COMET_MAX_RETRIES', 3),
    'retry_delay' => env('COMET_RETRY_DELAY', 1000),  // ms
],
```

### Environment Variables

```env
COMET_API_KEY=your_api_key_here
COMET_API_URL=https://api.soccer.sportdata.de/v2.0/
COMET_CACHE_TTL=3600
COMET_MAX_RETRIES=3
COMET_RETRY_DELAY=1000
```

---

## Examples

### Example 1: Sync Club by FIFA ID

```php
// Controller
$service = new CometApiService(auth()->user()->tenant_id);
$result = $service->syncClubByFifaId('173');  // Bayern Munich FIFA ID

// Result:
[
    'success' => true,
    'club_id' => 1,
    'competitions_synced' => 5,
    'players_synced' => 23
]

// Club now has:
// - All 5 competitions with standings
// - All matches for those competitions
// - All 23 players with profiles
// - Complete audit trail in comet_syncs table
```

---

### Example 2: Display Top Scorers

```php
// Get top 10 scorers for Bundesliga
$topScorers = PlayerCompetitionStat::where('competition_id', $bundesligaId)
    ->orderByDesc('goals')
    ->limit(10)
    ->with('player')
    ->get();

// Display
foreach ($topScorers as $stat) {
    echo $stat->player->name . ': ' . $stat->goals . ' goals';
    // Robert Lewandowski: 18 goals
}
```

---

### Example 3: Get Club's Current Standings

```php
// Club's position in each competition
$club = Club::findOrFail($clubId);
$standings = Ranking::whereIn(
    'competition_id',
    $club->competitions()->pluck('id')
)->where('club_id', $clubId)
->with('competition')
->get();

// Display
foreach ($standings as $ranking) {
    echo $ranking->competition->name . ': #' . $ranking->position;
    // Bundesliga: #1
    // DFB-Pokal: #2
}
```

---

### Example 4: Live Match Updates

```php
// Get all live matches
$liveMatches = GameMatch::where('status', 'live')
    ->with('homeClub', 'awayClub', 'events')
    ->get();

// For real-time updates, poll this endpoint:
GET /api/comet/clubs/{clubId}/live-matches

// Updates included:
// - Current score
// - Current minute
// - Recent events (goals, cards)
```

---

### Example 5: Player Search

```php
// Find all strikers in a club
$strikers = Player::byClub($clubId)
    ->byPosition('ST')
    ->active()
    ->orderByDesc('market_value_eur')
    ->paginate(10);

// Display
foreach ($strikers as $player) {
    echo $player->name . ' - €' . number_format($player->market_value_eur);
}
```

---

## Troubleshooting

### Sync Not Working

1. Check API key configuration
2. Verify FIFA ID format
3. Check rate limit status
4. Look at comet_syncs table for errors
5. Verify network connectivity to api.soccer.sportdata.de

### Data Appearing Twice

1. Check for race conditions in queue jobs
2. Ensure updateOrCreate is used, not create
3. Clear cache if manually updated

### Slow Performance

1. Check database indexes
2. Use eager loading with relationships
3. Enable query caching
4. Break large syncs into queue jobs
5. Adjust cache TTL (increase for better performance)

### Missing Players

1. Verify Comet API returns all players
2. Check player status filters
3. Confirm sync completed successfully
4. Check comet_syncs error messages

---

## References

- **Comet API**: https://api.soccer.sportdata.de/v2.0/
- **Laravel Documentation**: https://laravel.com/docs
- **Tenancy for Laravel**: https://tenancyforlaravel.com/docs
- **Eloquent ORM**: https://laravel.com/docs/eloquent

---

**Last Updated**: January 2024  
**Maintainer**: Development Team  
**Status**: Production Ready ✅
