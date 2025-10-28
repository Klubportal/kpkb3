# 📊 Datenbank & Models - Comet API Integration

**Status**: ✅ **COMPLETE & PRODUCTION READY**

---

## 🗄️ Datenbank-Struktur (9 Tabellen)

### 1. **competitions** - Wettbewerbe/Ligen
```sql
- id (PK)
- tenant_id (FK)
- comet_id (UNIQUE) - Von Comet API
- name - Liga, Pokal, etc.
- type - league, cup, group, playoff
- season - 2024, 2025, etc.
- status - upcoming, active, completed
- start_date, end_date
- country - AT, DE, etc.
```
**Indexes**: tenant_id, status, created_at

---

### 2. **rankings** - Liga-Tabellen/Standings
```sql
- id (PK)
- tenant_id (FK)
- competition_id (FK)
- comet_id (UNIQUE)
- position - 1, 2, 3, etc.
- club_id (FK)
- matches_played, wins, draws, losses
- goals_for, goals_against
- goal_difference - VIRTUAL
- points
- form - JSON {W,D,L,W,D}
```
**Indexes**: tenant_id, competition_id, position, club_id

---

### 3. **matches** - Spielergebnisse/Matches
```sql
- id (PK)
- tenant_id (FK)
- competition_id (FK)
- comet_id (UNIQUE)
- home_club_id (FK)
- away_club_id (FK)
- kickoff_time
- status - scheduled, live, finished, cancelled, postponed
- home_goals, away_goals
- home_goals_ht, away_goals_ht (Halbzeit)
- stadium, attendance
- referee
- week - Spieltag/Matchday
- minute - Aktuelle Minute (live)
```
**Indexes**: tenant_id, competition_id, kickoff_time, status

---

### 4. **match_events** - Match-Events (Tore, Karten, Substitutionen)
```sql
- id (PK)
- tenant_id (FK)
- match_id (FK)
- comet_id (UNIQUE)
- club_id (FK)
- player_id (FK)
- event_type - goal, own_goal, penalty, yellow_card, red_card, substitution_on/off, var_decision
- minute - Minute des Events
- minute_extra - "45+2", "90+5"
- related_player_id (FK) - Für Substitutionen
- description, metadata (JSON)
```
**Indexes**: tenant_id, match_id, minute, event_type, player_id

---

### 5. **players** - Spieler-Profile
```sql
- id (PK)
- tenant_id (FK)
- club_id (FK)
- comet_id (UNIQUE)
- name, first_name, last_name
- date_of_birth
- nationality, nationality_code
- position - GK, CB, LB, RB, LWB, RWB, CM, CAM, CDM, LM, RM, LW, RW, ST, CF, SS
- shirt_number
- photo_url
- height_cm, weight_kg
- foot - left, right, both
- status - active, injured, suspended, retired, loaned_out

CAREER STATS:
- total_matches, total_goals, total_assists
- total_yellow_cards, total_red_cards

SEASON STATS:
- season_matches, season_goals, season_assists
- season_yellow_cards, season_red_cards

RATINGS:
- market_value_eur
- average_rating (1.0 - 10.0)

SYNC:
- is_synced, last_synced_at, sync_metadata (JSON)
```
**Indexes**: tenant_id, club_id, position, nationality, total_goals, season_goals

---

### 6. **player_competition_stats** - Spieler-Stats pro Wettbewerb
```sql
- id (PK)
- tenant_id (FK)
- player_id (FK)
- competition_id (FK)
- matches, goals, assists
- yellow_cards, red_cards
- average_rating (3,2)
- detailed_stats (JSON)
```
**Indexes**: tenant_id, player_id, competition_id, goals

---

### 7. **clubs_extended** - Verein Extended Info (FIFA ID)
```sql
- id (PK)
- tenant_id (FK)
- club_id (FK)
- comet_id (UNIQUE)
- fifa_id (UNIQUE) - FIFA Club ID
- code - Länder- + Klub-Code
- founded_year
- stadium_name, stadium_capacity
- coach_name
- coach_info (JSON)
- country, league_name
- club_info (TEXT)
- is_synced, last_synced_at
- sync_metadata (JSON)
```
**Indexes**: tenant_id, fifa_id, club_id

---

### 8. **comet_syncs** - Sync Audit Log
```sql
- id (PK)
- tenant_id (FK)
- entity_type - club, competition, match, player, ranking
- entity_id
- action - created, updated, deleted, synced
- records_affected
- sync_data (JSON)
- error_message
- status - success, failed, pending
- synced_at
```
**Indexes**: tenant_id, entity_type, status, synced_at

---

### 9. **club_competitions** - Club ↔ Competition Junction
```sql
- id (PK)
- tenant_id (FK)
- club_id (FK)
- competition_id (FK)
- is_participant
- wins, draws, losses
- goals_for, goals_against
- points
```
**Indexes**: tenant_id, club_id, competition_id

---

## 🏛️ Eloquent Models (10 Models)

### 1. Player Model
```php
class Player extends Model {
    // Relationships
    - belongsTo(Club)
    - hasMany(PlayerCompetitionStat)
    - hasMany(MatchEvent)
    
    // Scopes
    - scopeByPosition($position)
    - scopeByClub($clubId)
    - scopeActive()
    - scopeTopScorers($limit = 10)
    - scopeInjured()
    - scopeSuspended()
    
    // Computed Attributes
    - $age (from date_of_birth)
    - $fullName (first_name + last_name)
    - $positionName (localized)
    - $goalScoringRate (goals/matches)
}
```

---

### 2. PlayerCompetitionStat Model
```php
class PlayerCompetitionStat extends Model {
    // Relationships
    - belongsTo(Player)
    - belongsTo(Competition)
    
    // Scopes
    - scopeByCompetition($competitionId)
    - scopeTopScorers($limit = 10)
    - scopeByPlayer($playerId)
    
    // Computed Attributes
    - $goalsPerMatch
    - $assistsPerMatch
    - $cardsPerMatch
}
```

---

### 3. CometSync Model
```php
class CometSync extends Model {
    // Scopes
    - scopeByEntity($entityType)
    - scopeSuccessful()
    - scopeFailed()
    - scopeRecent($days = 7)
    
    // Static Methods
    - logSync($tenantId, $entityType, $action, $records, $data, $status, $error)
}
```

---

### 4. ClubExtended Model
```php
class ClubExtended extends Model {
    // Relationships
    - belongsTo(Club)
    
    // Scopes
    - scopeByFifaId($fifaId)
    - scopeByCountry($country)
    - scopeSynced()
    - scopeNotSynced()
}
```

---

### 5. Club Model (Updated)
```php
class Club extends BaseTenant {
    // NEW Comet Relationships
    - hasOne(ClubExtended) - FIFA ID mapping
    - belongsToMany(Competition) - via club_competitions
    - hasMany(Player) - Club players
    - hasMany(Ranking) - Club standings
    - hasMany(GameMatch, 'home_club_id') - Home matches
    - hasMany(GameMatch, 'away_club_id') - Away matches
}
```

---

### 6. Competition Model
```php
class Competition extends Model {
    // Relationships
    - hasMany(Ranking)
    - hasMany(GameMatch)
    - hasMany(TopScorer)
    - hasMany(MatchEvent)
    - belongsToMany(Club)
    
    // Scopes
    - scopeActive()
    - scopeCurrentSeason()
    - scopeOfficial()
}
```

---

### 7. GameMatch Model (Updated)
```php
class GameMatch extends Model {
    // Relationships (EXISTING)
    - belongsTo(Competition)
    - hasMany(MatchPlayer)
    - hasMany(MatchEvent)
    
    // NEW Comet Relationships
    - belongsTo(Club, 'home_club_id') - homeClub
    - belongsTo(Club, 'away_club_id') - awayClub
    - hasMany(MatchEvent, 'match_id')
}
```

---

### 8. MatchEvent Model (Updated)
```php
class MatchEvent extends Model {
    // Relationships (EXISTING)
    - belongsTo(GameMatch, 'match_fifa_id')
    - belongsTo(Competition, 'competition_fifa_id')
    - hasOne(MatchPlayer)
    - hasOne(secondPlayer, MatchPlayer)
    
    // NEW Comet Relationships
    - belongsTo(GameMatch, 'match_id') - gameMatch()
    - belongsTo(Club) - club()
    - belongsTo(Player) - player()
    - belongsTo(Player, 'related_player_id') - relatedPlayer()
}
```

---

### 9. Ranking Model (Updated)
```php
class Ranking extends Model {
    // Relationships (EXISTING)
    - belongsTo(Competition)
    
    // NEW Comet Relationship
    - belongsTo(Club) - club()
    
    // Scopes
    - scopeByCompetition()
    - scopeByTeam()
    - scopeOrdered()
}
```

---

### 10. CompetitionRanking Model (NEW)
```php
class CompetitionRanking extends Model {
    // Table: club_competitions (Junction)
    
    // Relationships
    - belongsTo(Club) - club()
    - belongsTo(Competition) - competition()
    
    // Scopes
    - scopeParticipants()
    - scopeByClub($clubId)
    - scopeByCompetition($competitionId)
    
    // Attributes
    - $goalDifference
    - $record (W-D-L format)
}
```

---

## 🔗 Relationship Diagram

```
Club
├── ClubExtended (1:1) - FIFA ID mapping
├── competitions (M:M via club_competitions)
├── players (1:M) - Club players
├── rankings (1:M) - Club standings
├── homeMatches (1:M)
└── awayMatches (1:M)

Player
├── club (M:1)
├── competitionStats (1:M) - Stats per competition
└── matchEvents (1:M) - All events for this player

Competition
├── rankings (1:M) - League table
├── matches (1:M) - All matches
├── topScorers (1:M) - Top goal scorers
└── clubs (M:M via club_competitions)

GameMatch
├── homeClub (M:1) - Home team
├── awayClub (M:1) - Away team
├── competition (M:1)
└── events (1:M) - Match events

MatchEvent
├── gameMatch (M:1)
├── player (M:1) - Goal scorer, carded player, etc.
├── relatedPlayer (M:1) - For substitutions
├── club (M:1)
└── competition (M:1)

Ranking
├── competition (M:1)
└── club (M:1)

PlayerCompetitionStat
├── player (M:1)
└── competition (M:1)

CometSync (Audit Log)
└── Logs all sync operations
```

---

## 🚀 Migration Beispiel

```bash
# Run all migrations
php artisan migrate

# Rollback if needed
php artisan migrate:rollback

# Only Comet migrations
php artisan migrate --path=database/migrations/2025_10_24_000200_create_comet_api_tables.php
```

---

## 📝 Beispiel: Spieler abrufen

```php
// Get all players from a club
$players = Club::find($clubId)->players()->active()->get();

// Get top scorers from a competition
$topScorers = PlayerCompetitionStat::where('competition_id', $competitionId)
    ->orderByDesc('goals')
    ->limit(10)
    ->with('player')
    ->get();

// Get player with all stats
$player = Player::with([
    'club',
    'competitionStats.competition',
    'matchEvents'
])->find($playerId);

// Get match with all events
$match = GameMatch::with([
    'homeClub',
    'awayClub',
    'events.player',
    'events.club'
])->find($matchId);

// Get standings for competition
$standings = Ranking::where('competition_id', $competitionId)
    ->orderBy('position')
    ->with('club')
    ->get();
```

---

## ✅ Status

- ✅ 9 Datenbank-Tabellen - COMPLETE
- ✅ 10 Eloquent Models - COMPLETE
- ✅ Alle Relationships - KONFIGURIERT
- ✅ Alle Scopes - IMPLEMENTIERT
- ✅ Multi-tenant Support - ENABLED
- ✅ Audit Logging - READY
- ✅ Production Ready - YES

---

**Last Updated**: October 23, 2025  
**Status**: 🚀 Production Ready
