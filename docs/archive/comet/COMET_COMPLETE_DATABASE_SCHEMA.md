# 🗄️ COMET API - Complete Database Schema

**Version**: 2.0  
**Datum**: 26. Oktober 2025  
**Basis**: FIFA Connect COMET REST API  
**Multi-Tenancy**: Central Database (kp_server)

---

## 📊 ÜBERSICHT

### Existierende Tabellen (9)
✅ comet_competitions  
✅ comet_clubs_extended  
✅ comet_rankings  
✅ comet_matches  
✅ comet_match_events  
✅ comet_players  
✅ comet_player_competition_stats  
✅ comet_club_competitions  
✅ comet_syncs  

### Fehlende Tabellen für neue Endpoints (10)
❌ comet_match_phases (Match Phasen: Halbzeiten, Verlängerung)  
❌ comet_match_players (Match Aufstellungen)  
❌ comet_match_officials (Schiedsrichter)  
❌ comet_match_team_officials (Trainer bei Matches)  
❌ comet_team_officials (Team Staff: Trainer, Physios, etc.)  
❌ comet_facilities (Stadien)  
❌ comet_facility_fields (Spielfelder in Stadien)  
❌ comet_cases (Disziplinar-Fälle)  
❌ comet_sanctions (Strafen/Sperren)  
❌ comet_own_goal_scorers (Eigentore Statistik)  

---

## 📋 ENDPOINT → TABELLEN MAPPING

### 1. Team Officials Endpoints
```
GET /api/export/comet/competition/{competitionFifaId}/{teamFifaId}/teamOfficials
GET /api/export/comet/team/{teamFifaId}/teamOfficials
```
**Benötigte Tabelle**: `comet_team_officials`

**Datenstruktur**:
```json
{
  "role": "COACH",
  "cometRoleName": "label.headCoach",
  "cometRoleNameKey": "label.headCoach",
  "personFifaId": 1787804,
  "teamId": 57359,
  "status": "ACTIVE",
  "person": {
    "personFifaId": 1787804,
    "internationalFirstName": "Thomas",
    "internationalLastName": "Tuchel"
  }
}
```

---

### 2. Match Phases Endpoint
```
GET /api/export/comet/match/{matchFifaId}/phases
```
**Benötigte Tabelle**: `comet_match_phases`

**Datenstruktur**:
```json
{
  "matchFifaId": 102860260,
  "phase": "FIRST_HALF",
  "homeScore": 2,
  "awayScore": 1,
  "regularTime": 45,
  "stoppageTime": 2,
  "startDateTime": "2025-10-23T19:15:40",
  "endDateTime": "2025-10-23T20:00:40"
}
```

**Phasen-Typen**:
- FIRST_HALF (1. Halbzeit)
- SECOND_HALF (2. Halbzeit)
- FIRST_ET (1. Verlängerung)
- SECOND_ET (2. Verlängerung)
- PEN (Elfmeterschießen)

---

### 3. Match Players Endpoint
```
GET /api/export/comet/match/{matchFifaId}/players
GET /api/export/comet/match/{matchFifaId}/players/{personFifaId}
```
**Benötigte Tabelle**: `comet_match_players`

**Datenstruktur**:
```json
{
  "shirtNumber": 10,
  "captain": 1,
  "goalkeeper": 0,
  "startingLineup": 1,
  "played": 1,
  "position": "Midfielder",
  "teamFifaId": 618,
  "matchFifaId": 102860260,
  "personFifaId": 223034,
  "personName": "Ivan Galić",
  "matchEvents": [...]
}
```

---

### 4. Match Officials Endpoint
```
GET /api/export/comet/match/{matchFifaId}/officials
```
**Benötigte Tabelle**: `comet_match_officials`

**Datenstruktur**:
```json
{
  "personFifaId": 987654,
  "personName": "Felix Brych (GER)",
  "role": "REFEREE",
  "roleDescription": "Referee",
  "cometRoleName": "Referee",
  "cometRoleNameKey": "label.referee",
  "matchFifaId": 7763137
}
```

**Rollen**:
- REFEREE (Hauptschiedsrichter)
- ASSISTANT_REFEREE (Linienrichter)
- FOURTH_OFFICIAL (4. Offizieller)
- VAR (Video Assistant Referee)
- AVAR (Assistant VAR)

---

### 5. Match Team Officials Endpoint
```
GET /api/export/comet/match/{matchFifaId}/teamOfficials
```
**Benötigte Tabelle**: `comet_match_team_officials`

**Datenstruktur**:
```json
{
  "role": "COACH",
  "roleDescription": "Head Coach",
  "personFifaId": 1787804,
  "personName": "Thomas Tuchel",
  "matchFifaId": 7763137,
  "teamFifaId": 59577,
  "matchTeam": "HOME"
}
```

---

### 6. Facilities Endpoint
```
GET /api/export/comet/facilities
GET /api/export/comet/facilities?facilityFifaId=39933
```
**Benötigte Tabellen**: 
- `comet_facilities` (Stadien)
- `comet_facility_fields` (Spielfelder)

**Facility Datenstruktur**:
```json
{
  "facilityFifaId": 39933,
  "status": "ACTIVE",
  "internationalName": "Allianz Arena",
  "internationalShortName": "Allianz Arena",
  "organisationFifaId": 39393,
  "town": "Munich",
  "address": "Werner-Heisenberg-Allee 25",
  "webAddress": "https://www.fcbayern.com",
  "email": "info@fcbayern.com",
  "phone": "+49 89 308 9600"
}
```

**Field Datenstruktur**:
```json
{
  "facilityFifaId": 39933,
  "orderNumber": 1,
  "discipline": "FOOTBALL",
  "capacity": 75024,
  "groundNature": "GRASS",
  "length": 105,
  "width": 68,
  "latitude": "48.2188",
  "longitude": "11.6217"
}
```

---

### 7. Cases & Sanctions Endpoints
```
GET /api/export/comet/competition/{competitionFifaId}/cases
GET /api/export/comet/match/{matchFifaId}/cases
GET /api/export/comet/case/{caseFifaId}
GET /api/export/comet/case/{caseFifaId}/sanctions
```
**Benötigte Tabellen**: 
- `comet_cases` (Disziplinar-Fälle)
- `comet_sanctions` (Strafen/Sperren)

**Case Datenstruktur**:
```json
{
  "caseFifaId": 5419390,
  "description": "Unsportliches Verhalten",
  "caseDate": "2025-10-23",
  "status": "ACTIVE",
  "offenderNature": "PERSON",
  "offenderPersonFifaId": 223034,
  "matchFifaId": 102860260,
  "competitionFifaId": 100629221
}
```

**Sanction Datenstruktur**:
```json
{
  "sanctionId": 367446,
  "caseFifaId": 5419390,
  "sanctionType": "MATCH_SUSPENSION",
  "numberOfMatches": 2,
  "startDate": "2025-10-26",
  "endDate": "2025-11-09",
  "status": "ACTIVE"
}
```

---

### 8. Own Goal Scorers Endpoint
```
GET /api/export/comet/competition/{competitionFifaId}/ownGoalScorers
```
**Benötigte Tabelle**: `comet_own_goal_scorers`

**Datenstruktur** (identisch zu topScorers):
```json
{
  "playerFifaId": 223034,
  "goals": 2,
  "club": "NK Prigorje",
  "clubId": 598,
  "team": "NK Prigorje Markuševec",
  "teamid": 618,
  "internationalFirstName": "Ivan",
  "internationalLastName": "Galić"
}
```

---

### 9. Images Endpoints
```
GET /api/export/comet/images/{entity}/{fifaId}
GET /api/export/comet/images/update/{entity}/{fifaId}
```
**Verwendung**: Bilder werden als Base64 zurückgegeben und können:
1. Direkt in existierenden Tabellen gespeichert werden (logo_url, photo_url)
2. Im Filesystem gespeichert werden (storage/app/public/comet-images/)

**Entities**:
- person (Spieler-Fotos → comet_players.photo_url)
- competition (Liga-Logos → comet_competitions.logo_url)
- organization (Club-Logos → comet_clubs_extended.logo_url)
- facility (Stadion-Bilder → comet_facilities.image_url)

---

### 10. Throttling Info Endpoint
```
GET /api/export/comet/throttling/info
```
**Verwendung**: Cache in Laravel, keine DB-Tabelle nötig

```php
Cache::remember('comet.throttling', 3600, function() {
    return Http::withBasicAuth(config('comet.user'), config('comet.pass'))
        ->get('https://api-hns.analyticom.de/api/export/comet/throttling/info')
        ->json();
});
```

---

## 🎯 PRIORITÄTEN

### Phase 1: Match Details (Höchste Priorität)
1. ✅ **comet_match_phases** - Halbzeit-Ergebnisse
2. ✅ **comet_match_players** - Aufstellungen
3. ✅ **comet_match_officials** - Schiedsrichter
4. ✅ **comet_match_team_officials** - Trainer/Betreuer bei Match

→ **Warum**: Für vollständige Live-Match-Darstellung essentiell

---

### Phase 2: Team Management
5. ✅ **comet_team_officials** - Trainer, Staff
6. ✅ **comet_facilities** - Stadien
7. ✅ **comet_facility_fields** - Spielfelder

→ **Warum**: Team-Seiten und Stadion-Infos

---

### Phase 3: Disziplinar & Statistik
8. ✅ **comet_cases** - Disziplinar-Fälle
9. ✅ **comet_sanctions** - Sperren/Strafen
10. ✅ **comet_own_goal_scorers** - Eigentor-Statistik

→ **Warum**: Erweiterte Statistiken und Spielerverfügbarkeit

---

## 📐 RELATIONSHIPS

### comet_matches
```
hasMany → comet_match_phases
hasMany → comet_match_events (existiert bereits)
hasMany → comet_match_players
hasMany → comet_match_officials
hasMany → comet_match_team_officials
hasMany → comet_cases
belongsTo → comet_facilities (via facilityFifaId)
```

### comet_players
```
hasMany → comet_match_players
hasMany → comet_match_events (via player_fifa_id)
hasMany → comet_own_goal_scorers
hasMany → comet_cases (via offenderPersonFifaId)
```

### comet_clubs_extended
```
hasMany → comet_team_officials (via teamFifaId)
hasMany → comet_facilities (via organisationFifaId)
```

### comet_competitions
```
hasMany → comet_cases
hasMany → comet_own_goal_scorers
```

### comet_cases
```
belongsTo → comet_matches
belongsTo → comet_competitions
belongsTo → comet_players (polymorphic offender)
hasMany → comet_sanctions
```

### comet_facilities
```
hasMany → comet_facility_fields
belongsTo → comet_clubs_extended (via organisationFifaId)
hasMany → comet_matches
```

---

## 🔧 MIGRATION NAMING CONVENTION

```
2025_10_26_120009_create_comet_match_phases_table.php
2025_10_26_120010_create_comet_match_players_table.php
2025_10_26_120011_create_comet_match_officials_table.php
2025_10_26_120012_create_comet_match_team_officials_table.php
2025_10_26_120013_create_comet_team_officials_table.php
2025_10_26_120014_create_comet_facilities_table.php
2025_10_26_120015_create_comet_facility_fields_table.php
2025_10_26_120016_create_comet_cases_table.php
2025_10_26_120017_create_comet_sanctions_table.php
2025_10_26_120018_create_comet_own_goal_scorers_table.php
```

Alle Migrationen verwenden:
```php
Schema::connection('central')->create('table_name', function (Blueprint $table) {
    // ...
});
```

---

## 📊 FIELD TYPES REFERENZ

### FIFA IDs
```php
$table->bigInteger('comet_id')->unique()->comment('FIFA ID');
$table->bigInteger('person_fifa_id')->comment('FIFA Person ID');
$table->bigInteger('match_fifa_id')->comment('FIFA Match ID');
$table->bigInteger('competition_fifa_id')->comment('FIFA Competition ID');
```

### Enums
```php
$table->enum('phase', ['FIRST_HALF', 'SECOND_HALF', 'FIRST_ET', 'SECOND_ET', 'PEN']);
$table->enum('role', ['REFEREE', 'ASSISTANT_REFEREE', 'FOURTH_OFFICIAL', 'VAR', 'AVAR']);
$table->enum('status', ['ACTIVE', 'INACTIVE', 'SUSPENDED']);
$table->enum('ground_nature', ['GRASS', 'ARTIFICIAL', 'CLAY', 'SAND', 'MIXED']);
```

### Boolean Flags (0/1)
```php
$table->boolean('captain')->default(false);
$table->boolean('goalkeeper')->default(false);
$table->boolean('starting_lineup')->default(false);
$table->boolean('played')->default(false);
```

### Timestamps
```php
$table->dateTime('start_date_time')->nullable();
$table->dateTime('end_date_time')->nullable();
$table->timestamp('last_synced_at')->nullable();
$table->timestamps(); // created_at, updated_at
```

### Indexes
```php
$table->index('match_fifa_id');
$table->index('competition_fifa_id');
$table->index(['match_fifa_id', 'phase']); // Composite
$table->unique(['match_fifa_id', 'person_fifa_id']); // Unique constraint
```

---

## 🚀 NÄCHSTE SCHRITTE

1. ✅ Erstelle alle 10 Migrationen
2. ✅ Erstelle entsprechende Eloquent Models
3. ✅ Erweitere CometApiService um neue Endpoint-Methoden
4. ✅ Teste Sync für jeden Endpoint einzeln
5. ✅ Implementiere Batch-Sync für komplette Match-Details
6. ✅ Dokumentiere API-Response → DB-Mapping

---

**Status**: 📝 Dokumentation Complete  
**Nächster Schritt**: Migrationen erstellen
