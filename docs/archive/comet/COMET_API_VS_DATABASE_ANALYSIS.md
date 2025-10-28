# COMET API vs DATENBANK - KOMPLETTE ANALYSE

**Datum**: 26. Oktober 2025  
**Club**: NK Prigorje (FIFA ID: 598)  
**API Base**: https://api-hns.analyticom.de/api/export/comet/  
**Season Filter**: 2025, 2026, 25/26

---

## 📊 ÜBERSICHT API ENDPUNKTE vs DATENBANK

| # | API Endpoint | DB Tabelle | Status | Fehlende Felder |
|---|--------------|------------|--------|-----------------|
| 1 | `/competitions` | `comet_competitions` | ⚠️ Teilweise | club_fifa_id, active, organisationFifaId |
| 2 | `/competition/{id}/teams` | `comet_clubs_extended` | ⚠️ Teilweise | status, facilityFifaId, localNames |
| 3 | `/competition/{id}/ranking` | `comet_rankings` | ✅ Gut | - |
| 4 | `/competition/{id}/matches` | `comet_matches` | ⚠️ Teilweise | matchFifaId, matchType, nature |
| 5 | `/match/{id}` | `comet_matches` | ⚠️ Teilweise | matchPhases, matchOfficials, attendance |
| 6 | `/match/{id}/events` | `comet_match_events` | ⚠️ Teilweise | eventType details, stoppageTime |
| 7 | `/match/{id}/players` | `comet_match_players` | ⚠️ Teilweise | startingLineup, captain, goalkeeper |
| 8 | `/match/{id}/phases` | `comet_match_phases` | ✅ Gut | - |
| 9 | `/team/{id}/players` | `comet_players` | ⚠️ Teilweise | personFifaId, localNames, placeOfBirth |
| 10 | `/team/{id}/teamOfficials` | `comet_team_officials` | ⚠️ Teilweise | role details, cometRoleName |
| 11 | `/competition/{id}/topScorers` | - | ❌ Fehlt | Keine eigene Tabelle |
| 12 | `/competition/{id}/ownGoalScorers` | `comet_own_goal_scorers` | ✅ Gut | - |
| 13 | `/competition/{id}/cases` | `comet_cases` | ⚠️ Teilweise | offenderNature, caseDate |
| 14 | `/case/{id}/sanctions` | `comet_sanctions` | ⚠️ Teilweise | sanction details |
| 15 | `/facilities/{id}` | `comet_facilities` | ⚠️ Teilweise | address, capacity details |

---

## 1️⃣ COMPETITIONS ENDPOINT

### API Endpoint
```http
GET /api/export/comet/competitions?active=true&organisationFifaIds=598
```

### API Response Felder
```json
{
  "competitionFifaId": 100629221,          // int64
  "internationalName": "1. HNL",
  "internationalShortName": "1. HNL",
  "organisationFifaId": 598,                // HNS Croatia
  "season": 2025,                           // int
  "status": "ACTIVE",                       // ACTIVE/INACTIVE
  "dateFrom": "2025-07-01T00:00:00",
  "dateTo": "2026-05-31T23:59:59",
  "ageCategory": "SENIORS",                 // A, SENIORS, U_21, U_19, etc.
  "teamCharacter": "CLUB",                  // CLUB/NATIONAL
  "nature": "ROUND_ROBIN",                  // ROUND_ROBIN, KNOCK_OUT, etc.
  "discipline": "FOOTBALL",
  "gender": "MALE",                         // MALE/FEMALE
  "numberOfParticipants": 10,
  "matchType": "OFFICIAL",                  // OFFICIAL/FRIENDLY
  "competitionType": "League",
  "penaltyShootout": true,
  "flyingSubstitutions": false,
  "imageId": 123456,
  "localNames": [
    {
      "language": "hr",
      "name": "Prva HNL",
      "shortName": "1. HNL"
    }
  ]
}
```

### Unsere DB Struktur (comet_competitions)
```sql
CREATE TABLE comet_competitions (
  id BIGINT PRIMARY KEY,
  comet_id BIGINT UNIQUE,              -- ✅ = competitionFifaId
  name VARCHAR,                         -- ✅ = internationalName
  slug VARCHAR UNIQUE,                  -- ⚠️ Generiert, nicht in API
  description TEXT,                     -- ⚠️ Nicht in API
  country VARCHAR(3),                   -- ⚠️ Nicht direkt (von organisationFifaId)
  logo_url VARCHAR,                     -- ⚠️ Aus imageId generieren
  type ENUM,                            -- ✅ = competitionType (aber anders)
  season VARCHAR(20),                   -- ⚠️ API hat INT, wir VARCHAR
  status ENUM,                          -- ⚠️ API: ACTIVE/INACTIVE, wir: upcoming/active/finished/cancelled
  start_date DATE,                      -- ✅ = dateFrom
  end_date DATE,                        -- ✅ = dateTo
  settings JSON,                        -- ⚠️ Könnten hier viel mehr speichern
  created_at, updated_at
);
```

### ❌ FEHLENDE FELDER in DB
- `organisationFifaId` (WICHTIG! - zum Filtern)
- `ageCategory` (SENIORS, U_21, U_19, etc.)
- `teamCharacter` (CLUB/NATIONAL)
- `nature` (ROUND_ROBIN, KNOCK_OUT)
- `discipline` (FOOTBALL, FUTSAL)
- `gender` (MALE/FEMALE)
- `numberOfParticipants`
- `matchType` (OFFICIAL/FRIENDLY)
- `penaltyShootout` (boolean)
- `flyingSubstitutions` (boolean)
- `imageId`
- `localNames` (JSON array)

### ✅ MAPPING FÜR NK PRIGORJE
```php
// Filter für API Call
$params = [
    'active' => true,
    'organisationFifaIds' => 598,  // HNS Croatia
    'season' => 2025,
];

// Zu speichernde Felder
DB::table('comet_competitions')->updateOrInsert([
    'comet_id' => $data['competitionFifaId'],
], [
    'name' => $data['internationalName'],
    'slug' => Str::slug($data['internationalName']),
    'country' => 'HRV',  // Von organisationFifaId lookup
    'type' => strtolower($data['competitionType']),  // League -> league
    'season' => $data['season'],  // 2025 -> "2025"
    'status' => $data['status'] === 'ACTIVE' ? 'active' : 'finished',
    'start_date' => $data['dateFrom'],
    'end_date' => $data['dateTo'],
    'settings' => json_encode([
        'organisation_fifa_id' => $data['organisationFifaId'],
        'age_category' => $data['ageCategory'],
        'team_character' => $data['teamCharacter'],
        'nature' => $data['nature'],
        'gender' => $data['gender'],
        'participants' => $data['numberOfParticipants'],
        'match_type' => $data['matchType'],
        'penalty_shootout' => $data['penaltyShootout'],
        'flying_substitutions' => $data['flyingSubstitutions'],
        'image_id' => $data['imageId'],
        'local_names' => $data['localNames'],
    ]),
]);
```

---

## 2️⃣ COMPETITION TEAMS ENDPOINT

### API Endpoint
```http
GET /api/export/comet/competition/{competitionFifaId}/teams
```

### API Response Felder
```json
{
  "teamFifaId": 618,                    // int64
  "internationalName": "NK Prigorje",
  "internationalShortName": "Prigorje",
  "competitionFifaId": 100629221,
  "organisationFifaId": 598,            // Parent organization
  "organisationName": "HNS",
  "organisationShortName": "Croatian FA",
  "country": "HR",
  "town": "Markuševec",
  "region": "Zagreb",
  "status": "ACTIVE",                   // ACTIVE/INACTIVE
  "facilityFifaId": 39933,              // Stadium
  "localNames": [
    {
      "language": "hr",
      "name": "NK Prigorje Markuševec",
      "shortName": "Prigorje"
    }
  ]
}
```

### Unsere DB Struktur (comet_clubs_extended)
```sql
CREATE TABLE comet_clubs_extended (
  id BIGINT PRIMARY KEY,
  club_fifa_id BIGINT UNIQUE,          -- ✅ = teamFifaId
  name VARCHAR,                         -- ✅ = internationalName
  short_name VARCHAR,                   -- ✅ = internationalShortName
  logo_url VARCHAR,                     -- ⚠️ Nicht in diesem Endpoint
  founded INT,                          -- ⚠️ Nicht in diesem Endpoint
  colors VARCHAR,                       -- ⚠️ Nicht in diesem Endpoint
  stadium VARCHAR,                      -- ⚠️ Nur facilityFifaId
  city VARCHAR,                         -- ✅ = town
  country VARCHAR(3),                   -- ✅ = country
  website VARCHAR,                      -- ⚠️ Nicht in diesem Endpoint
  created_at, updated_at
);
```

### ❌ FEHLENDE FELDER in DB
- `organisationFifaId` (WICHTIG!)
- `organisationName`
- `region`
- `status` (ACTIVE/INACTIVE)
- `facilityFifaId` (FK zu facilities)
- `localNames` (JSON)

### ✅ MAPPING
```php
// Nur Teams von NK Prigorje (organisationFifaId = 598)
$teams = collect($response)->where('organisationFifaId', 598);

foreach ($teams as $team) {
    DB::table('comet_clubs_extended')->updateOrInsert([
        'club_fifa_id' => $team['teamFifaId'],
    ], [
        'name' => $team['internationalName'],
        'short_name' => $team['internationalShortName'],
        'city' => $team['town'],
        'country' => $team['country'],
        'stadium' => null,  // Wird von facilityFifaId geholt
        // In settings JSON speichern:
        'settings' => json_encode([
            'organisation_fifa_id' => $team['organisationFifaId'],
            'region' => $team['region'],
            'status' => $team['status'],
            'facility_fifa_id' => $team['facilityFifaId'],
            'local_names' => $team['localNames'],
        ]),
    ]);
}
```

---

## 3️⃣ PLAYERS ENDPOINT

### API Endpoint
```http
GET /api/export/comet/team/{teamFifaId}/players?status=ALL
```

### API Response Felder
```json
{
  "personFifaId": 223034,               // int64 - UNIQUE Player ID
  "internationalFirstName": "Marko",
  "internationalLastName": "Kovačić",
  "popularName": "Marko",
  "gender": "MALE",
  "nationality": "HR",
  "dateOfBirth": "1995-05-15",
  "placeOfBirth": "Zagreb",
  "countryOfBirth": "HR",
  "playerPosition": "Midfielder",        // Goalkeeper, Defender, Midfielder, Forward
  "status": "ACTIVE",                    // ACTIVE/INACTIVE
  "localPersonNames": [
    {
      "language": "hr",
      "firstName": "Marko",
      "lastName": "Kovačić",
      "popularName": "Marko"
    }
  ]
}
```

### Unsere DB Struktur (comet_players)
```sql
CREATE TABLE comet_players (
  id BIGINT PRIMARY KEY,
  club_fifa_id BIGINT,                 -- ✅ Von team holen
  comet_id BIGINT,                     -- ⚠️ WAS IST DAS? Sollte personFifaId sein!
  name VARCHAR,                         -- ⚠️ Kombiniert, API hat getrennt
  first_name VARCHAR,                   -- ✅ = internationalFirstName
  last_name VARCHAR,                    -- ✅ = internationalLastName
  date_of_birth DATE,                   -- ✅ = dateOfBirth
  nationality VARCHAR,                  -- ✅ = nationality
  nationality_code VARCHAR(3),          -- ✅ = nationality
  position ENUM,                        -- ✅ = playerPosition (mapping nötig)
  shirt_number INT,                     -- ⚠️ Nicht in diesem Endpoint (nur in competition context)
  photo_url VARCHAR,                    -- ⚠️ Nicht in diesem Endpoint
  height_cm INT,                        -- ⚠️ Nicht in diesem Endpoint
  weight_kg INT,                        -- ⚠️ Nicht in diesem Endpoint
  foot ENUM,                            -- ⚠️ Nicht in diesem Endpoint
  status ENUM,                          -- ✅ = status
  // ... Statistiken nicht in diesem Endpoint
  created_at, updated_at
);
```

### ❌ FEHLENDE/FALSCHE FELDER
- `comet_id` sollte `person_fifa_id` heißen!
- `placeOfBirth` fehlt
- `countryOfBirth` fehlt
- `gender` fehlt
- `popularName` fehlt
- `localPersonNames` fehlt

### ✅ MAPPING
```php
// Für NK Prigorje Team (teamFifaId = 618)
$response = Http::get("...team/618/players?status=ACTIVE");

foreach ($response->json() as $player) {
    DB::table('comet_players')->updateOrInsert([
        'comet_id' => $player['personFifaId'],  // ⚠️ Umbenennen zu person_fifa_id
    ], [
        'club_fifa_id' => 598,  // NK Prigorje organisation
        'first_name' => $player['internationalFirstName'],
        'last_name' => $player['internationalLastName'],
        'name' => $player['internationalFirstName'] . ' ' . $player['internationalLastName'],
        'date_of_birth' => $player['dateOfBirth'],
        'nationality' => $player['nationality'],
        'nationality_code' => $player['nationality'],
        'position' => mapPosition($player['playerPosition']),
        'status' => strtolower($player['status']),
        // In JSON speichern:
        'sync_metadata' => json_encode([
            'person_fifa_id' => $player['personFifaId'],
            'popular_name' => $player['popularName'],
            'gender' => $player['gender'],
            'place_of_birth' => $player['placeOfBirth'],
            'country_of_birth' => $player['countryOfBirth'],
            'local_names' => $player['localPersonNames'],
        ]),
    ]);
}

function mapPosition($apiPosition) {
    return match($apiPosition) {
        'Goalkeeper' => 'goalkeeper',
        'Defender' => 'defender',
        'Midfielder' => 'midfielder',
        'Forward' => 'forward',
        default => 'unknown',
    };
}
```

---

## 4️⃣ MATCHES ENDPOINT

### API Endpoint
```http
GET /api/export/comet/competition/{competitionFifaId}/matches
GET /api/export/comet/match/{matchFifaId}
```

### API Response Felder
```json
{
  "matchFifaId": 102860260,             // int64 - UNIQUE Match ID
  "competitionFifaId": 100629221,
  "dateTimeLocal": "2025-10-26T18:00:00",
  "matchDay": 10,                       // Round number
  "matchType": "OFFICIAL",
  "nature": "HOME_AND_AWAY",
  "status": "PLAYED",                   // SCHEDULED/PLAYED/POSTPONED/CANCELLED
  "attendance": 1500,
  "facilityFifaId": 39933,
  "matchTeams": [
    {
      "teamNature": "HOME",
      "teamFifaId": 618,
      "internationalName": "NK Prigorje"
    },
    {
      "teamNature": "AWAY",
      "teamFifaId": 620,
      "internationalName": "NK Zagreb"
    }
  ],
  "matchPhases": [
    {
      "phase": "FIRST_HALF",
      "homeScore": 1,
      "awayScore": 0,
      "regularTime": 45,
      "stoppageTime": 2
    },
    {
      "phase": "SECOND_HALF",
      "homeScore": 1,
      "awayScore": 1,
      "regularTime": 45,
      "stoppageTime": 3
    }
  ],
  "matchOfficials": [
    {
      "personFifaId": 445566,
      "role": "REFEREE",
      "internationalFirstName": "Ivan",
      "internationalLastName": "Bebek"
    }
  ]
}
```

### Unsere DB Struktur (comet_matches)
```sql
CREATE TABLE comet_matches (
  id BIGINT PRIMARY KEY,
  competition_id BIGINT FK,             -- ⚠️ Sollte auch competitionFifaId haben!
  comet_id BIGINT UNIQUE,               -- ✅ = matchFifaId
  home_club_fifa_id BIGINT,             -- ✅ Von matchTeams[HOME]
  away_club_fifa_id BIGINT,             -- ✅ Von matchTeams[AWAY]
  kickoff_time DATETIME,                -- ✅ = dateTimeLocal
  status ENUM,                          -- ✅ = status (mapping)
  home_goals INT,                       -- ✅ Von matchPhases berechnen
  away_goals INT,                       -- ✅ Von matchPhases berechnen
  home_goals_ht INT,                    -- ✅ Von FIRST_HALF phase
  away_goals_ht INT,                    -- ✅ Von FIRST_HALF phase
  stadium VARCHAR,                      -- ⚠️ Von facilityFifaId lookup
  attendance INT,                       -- ✅ = attendance
  referee VARCHAR,                      -- ⚠️ Von matchOfficials[REFEREE]
  round VARCHAR,                        -- ✅ = matchDay
  week INT,                             -- ⚠️ Nicht in API
  minute INT,                           -- ⚠️ Nur für LIVE matches
  extra_time JSON,                      -- ✅ matchPhases EXTRA_TIME/PENALTIES
  created_at, updated_at
);
```

### ❌ FEHLENDE FELDER
- `competition_fifa_id` (für direkte API queries)
- `match_type` (OFFICIAL/FRIENDLY)
- `nature` (HOME_AND_AWAY, NEUTRAL, etc.)
- `facility_fifa_id`
- `match_day` (API matchDay)

### ✅ MAPPING
```php
foreach ($competitions as $comp) {
    $response = Http::get("...competition/{$comp->comet_id}/matches");
    
    foreach ($response->json() as $match) {
        // Nur Matches mit NK Prigorje
        $nkTeam = collect($match['matchTeams'])->first(fn($t) => $t['teamFifaId'] == 618);
        if (!$nkTeam) continue;
        
        $homeTeam = collect($match['matchTeams'])->first(fn($t) => $t['teamNature'] == 'HOME');
        $awayTeam = collect($match['matchTeams'])->first(fn($t) => $t['teamNature'] == 'AWAY');
        
        // Finale Scores berechnen
        $finalPhase = collect($match['matchPhases'])->last();
        $htPhase = collect($match['matchPhases'])->first(fn($p) => $p['phase'] == 'FIRST_HALF');
        
        DB::table('comet_matches')->updateOrInsert([
            'comet_id' => $match['matchFifaId'],
        ], [
            'competition_id' => $comp->id,  // Unsere internal ID
            'home_club_fifa_id' => $homeTeam['teamFifaId'],
            'away_club_fifa_id' => $awayTeam['teamFifaId'],
            'kickoff_time' => $match['dateTimeLocal'],
            'status' => mapMatchStatus($match['status']),
            'home_goals' => $finalPhase['homeScore'] ?? null,
            'away_goals' => $finalPhase['awayScore'] ?? null,
            'home_goals_ht' => $htPhase['homeScore'] ?? null,
            'away_goals_ht' => $htPhase['awayScore'] ?? null,
            'attendance' => $match['attendance'],
            'round' => "Matchday {$match['matchDay']}",
            // In extra_time JSON:
            'extra_time' => json_encode([
                'competition_fifa_id' => $match['competitionFifaId'],
                'match_type' => $match['matchType'],
                'nature' => $match['nature'],
                'facility_fifa_id' => $match['facilityFifaId'],
                'match_day' => $match['matchDay'],
                'all_phases' => $match['matchPhases'],
            ]),
        ]);
    }
}
```

---

## 🎯 PRIORITÄT FÜR NK PRIGORJE (598)

### Phase 1: Competitions (WICHTIG!)
```http
GET /competitions?active=true&organisationFifaIds=598&season=2025
```
**Speichern in**: `comet_competitions`  
**Filter**: Nur aktive 2025/2026 Season

### Phase 2: Teams
```http
GET /competition/{competitionFifaId}/teams
```
**Für jede Competition aus Phase 1**  
**Speichern in**: `comet_clubs_extended`  
**Filter**: Nur teamFifaId = 618 (NK Prigorje)

### Phase 3: Players
```http
GET /team/618/players?status=ACTIVE
```
**Speichern in**: `comet_players`  
**Nur aktive Spieler**

### Phase 4: Matches
```http
GET /competition/{competitionFifaId}/matches
```
**Für jede Competition**  
**Speichern in**: `comet_matches`  
**Filter**: Nur Matches mit teamFifaId = 618

### Phase 5: Match Details (für jedes Match)
```http
GET /match/{matchFifaId}/events
GET /match/{matchFifaId}/players
GET /match/{matchFifaId}/phases
```
**Speichern in**:
- `comet_match_events`
- `comet_match_players`
- `comet_match_phases`

### Phase 6: Rankings
```http
GET /competition/{competitionFifaId}/ranking
```
**Speichern in**: `comet_rankings`

### Phase 7: Top Scorers
```http
GET /competition/{competitionFifaId}/topScorers
```
**Filter**: Nur clubId = 598  
**⚠️ Brauchen eigene Tabelle oder in player_competition_stats**

---

## 🔧 EMPFOHLENE DATENBANK-ÄNDERUNGEN

### 1. comet_competitions - Felder hinzufügen
```sql
ALTER TABLE comet_competitions
  ADD COLUMN organisation_fifa_id BIGINT COMMENT 'Parent organization',
  ADD COLUMN age_category VARCHAR(20) COMMENT 'SENIORS, U_21, etc.',
  ADD COLUMN team_character VARCHAR(20) COMMENT 'CLUB/NATIONAL',
  ADD COLUMN nature VARCHAR(50) COMMENT 'ROUND_ROBIN, KNOCK_OUT',
  ADD COLUMN gender VARCHAR(10) COMMENT 'MALE/FEMALE',
  ADD COLUMN match_type VARCHAR(20) COMMENT 'OFFICIAL/FRIENDLY',
  ADD COLUMN participants INT,
  ADD COLUMN image_id BIGINT,
  ADD COLUMN local_names JSON,
  MODIFY COLUMN season VARCHAR(20);  -- Für "2025/2026" Format
```

### 2. comet_players - comet_id umbenennen
```sql
ALTER TABLE comet_players
  CHANGE COLUMN comet_id person_fifa_id BIGINT COMMENT 'FIFA Person ID';
  ADD COLUMN popular_name VARCHAR,
  ADD COLUMN place_of_birth VARCHAR,
  ADD COLUMN country_of_birth VARCHAR(3),
  ADD COLUMN gender VARCHAR(10),
  ADD COLUMN local_names JSON;
```

### 3. comet_matches - Felder hinzufügen
```sql
ALTER TABLE comet_matches
  ADD COLUMN competition_fifa_id BIGINT COMMENT 'For direct API queries',
  ADD COLUMN match_type VARCHAR(20) COMMENT 'OFFICIAL/FRIENDLY',
  ADD COLUMN nature VARCHAR(50) COMMENT 'HOME_AND_AWAY, NEUTRAL',
  ADD COLUMN facility_fifa_id BIGINT,
  ADD COLUMN match_day INT COMMENT 'Round number from API',
  MODIFY COLUMN comet_id BIGINT COMMENT 'matchFifaId from API';
```

### 4. Neue Tabelle: comet_top_scorers
```sql
CREATE TABLE comet_top_scorers (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  competition_id BIGINT,
  person_fifa_id BIGINT,
  club_fifa_id BIGINT,
  team_fifa_id BIGINT,
  goals INT DEFAULT 0,
  penalties INT DEFAULT 0,
  position INT,
  first_name VARCHAR,
  last_name VARCHAR,
  popular_name VARCHAR,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE(competition_id, person_fifa_id),
  FOREIGN KEY (competition_id) REFERENCES comet_competitions(id),
  INDEX(club_fifa_id),
  INDEX(goals)
);
```

---

## ✅ NÄCHSTE SCHRITTE

1. **Datenbank-Struktur anpassen** (oben genannte ALTER TABLE Befehle)
2. **API Service erstellen** mit korrektem Base URL: `https://api-hns.analyticom.de/api/export/comet/`
3. **Sync-Reihenfolge** einhalten (Competitions -> Teams -> Players -> Matches -> Details)
4. **Filter korrekt anwenden**:
   - `organisationFifaIds=598` für Competitions
   - `teamFifaId=618` für NK Prigorje spezifische Daten
   - `active=true` für aktive Competitions
   - `status=ACTIVE` für aktive Players
   - `season=2025` oder `season=2026`
5. **Mapping-Funktionen** für ENUM-Werte erstellen
6. **Error Handling** für fehlende/optionale Felder
7. **Rate Limiting** beachten (100 req/sec standard, 50 für images)

---

**API Credentials benötigt**:
- Base URL: `https://api-hns.analyticom.de`
- Auth: HTTP Basic Auth
- Username: `?`
- Password: `?`
