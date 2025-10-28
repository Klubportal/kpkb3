# ✅ COMET API - Database Setup Complete (19 Tabellen)

**Datum**: 26. Oktober 2025  
**Status**: ✅ VOLLSTÄNDIG  
**Database**: kp_server (Central)  
**Tabellen**: 19 (9 existierend + 10 neu)

---

## 📊 ÜBERSICHT

### ✅ Phase 1: Existierende Tabellen (9)
1. ✅ `comet_competitions` - Wettbewerbe/Ligen
2. ✅ `comet_clubs_extended` - Vereine
3. ✅ `comet_rankings` - Tabellenstände
4. ✅ `comet_matches` - Spiele
5. ✅ `comet_match_events` - Match-Events (Tore, Karten, etc.)
6. ✅ `comet_players` - Spieler
7. ✅ `comet_player_competition_stats` - Spieler-Statistiken
8. ✅ `comet_club_competitions` - Verein-Wettbewerb Beziehungen
9. ✅ `comet_syncs` - Sync-Protokoll

### 🆕 Phase 2: Neu erstellte Tabellen (10)

#### Match Details (4 Tabellen)
10. ✅ `comet_match_phases` (15 Spalten)
    - Halbzeiten, Verlängerung, Elfmeterschießen
    - 11 Phasen-Typen: FIRST_HALF, SECOND_HALF, FIRST_ET, SECOND_ET, PEN, etc.

11. ✅ `comet_match_players` (24 Spalten)
    - Aufstellungen, Wechsel
    - captain, goalkeeper, starting_lineup Flags
    - Minutenangaben, Statistiken pro Match

12. ✅ `comet_match_officials` (15 Spalten)
    - Schiedsrichter und Assistenten
    - 8 Rollen: REFEREE, ASSISTANT_REFEREE, FOURTH_OFFICIAL, VAR, AVAR, etc.

13. ✅ `comet_match_team_officials` (17 Spalten)
    - Trainer/Betreuer bei Matches
    - 10 Rollen: COACH, ASSISTANT_COACH, GOALKEEPER_COACH, etc.

#### Team Management (3 Tabellen)
14. ✅ `comet_team_officials` (29 Spalten)
    - Team Staff permanent
    - Lizenz-Informationen, Vertragsdaten
    - 13 Rollen inkl. COACH, PHYSICAL_TRAINER, TEAM_DOCTOR, etc.

15. ✅ `comet_facilities` (29 Spalten)
    - Stadien und Sportanlagen
    - GPS-Koordinaten, Kontaktdaten
    - 4 Status: ACTIVE, INACTIVE, UNDER_CONSTRUCTION, CLOSED

16. ✅ `comet_facility_fields` (28 Spalten)
    - Spielfelder in Stadien
    - Platzdimensionen, Bodenbelag
    - 7 Bodentypen: GRASS, ARTIFICIAL, HYBRID, etc.

#### Disziplinar & Statistik (3 Tabellen)
17. ✅ `comet_cases` (28 Spalten)
    - Disziplinarfälle
    - Person oder Organisation als Täter
    - 6 Status: OPEN, PENDING, ACTIVE, CLOSED, APPEALED, WITHDRAWN

18. ✅ `comet_sanctions` (33 Spalten)
    - Strafen und Sperren
    - 11 Sanktionstypen: MATCH_SUSPENSION, FINE, POINTS_DEDUCTION, etc.
    - Finanzielle Strafen, Match-Sperren, Punktabzug

19. ✅ `comet_own_goal_scorers` (24 Spalten)
    - Eigentor-Statistiken
    - Analog zu Top Scorers

---

## 🔗 ENDPOINT MAPPING

### Alle 26 COMET Endpoints abgedeckt:

#### ✅ Competitions
```
GET /api/export/comet/competitions
    → comet_competitions

GET /api/export/comet/competition/{competitionFifaId}/teams
    → comet_clubs_extended

GET /api/export/comet/competition/{competitionFifaId}/ranking
    → comet_rankings

GET /api/export/comet/competition/{competitionFifaId}/topScorers
    → comet_player_competition_stats

GET /api/export/comet/competition/{competitionFifaId}/ownGoalScorers
    → comet_own_goal_scorers ✨ NEU
```

#### ✅ Matches
```
GET /api/export/comet/competition/{competitionFifaId}/matches
GET /api/export/comet/match/{matchFifaId}
    → comet_matches

GET /api/export/comet/match/{matchFifaId}/phases
    → comet_match_phases ✨ NEU

GET /api/export/comet/match/{matchFifaId}/events
GET /api/export/comet/match/{matchFifaId}/latest/events
    → comet_match_events

GET /api/export/comet/match/{matchFifaId}/players
GET /api/export/comet/match/{matchFifaId}/players/{personFifaId}
    → comet_match_players ✨ NEU

GET /api/export/comet/match/{matchFifaId}/officials
    → comet_match_officials ✨ NEU

GET /api/export/comet/match/{matchFifaId}/teamOfficials
    → comet_match_team_officials ✨ NEU

GET /api/export/comet/match/{matchFifaId}/cases
    → comet_cases ✨ NEU

GET /api/export/comet/match/{matchFifaId}/lastUpdateDateTime
    → Cache/API-Aufruf (keine Tabelle nötig)
```

#### ✅ Teams & Players
```
GET /api/export/comet/team/{teamFifaId}/players
GET /api/export/comet/player/{playerFifaId}
    → comet_players

GET /api/export/comet/team/{teamFifaId}/teamOfficials
GET /api/export/comet/competition/{competitionFifaId}/{teamFifaId}/teamOfficials
    → comet_team_officials ✨ NEU
```

#### ✅ Facilities
```
GET /api/export/comet/facilities
    → comet_facilities ✨ NEU
    → comet_facility_fields ✨ NEU
```

#### ✅ Disciplinary
```
GET /api/export/comet/competition/{competitionFifaId}/cases
GET /api/export/comet/case/{caseFifaId}
    → comet_cases ✨ NEU

GET /api/export/comet/case/{caseFifaId}/sanctions
    → comet_sanctions ✨ NEU
```

#### ✅ Images
```
GET /api/export/comet/images/{entity}/{fifaId}
GET /api/export/comet/images/update/{entity}/{fifaId}
    → Speichern in: logo_url, photo_url, image_url Felder
```

#### ✅ System
```
GET /api/export/comet/throttling/info
    → Cache (keine Tabelle nötig)
```

---

## 📊 DATENBANK STATISTIK

| Kategorie | Tabellen | Spalten (Total) | Indexes |
|-----------|----------|-----------------|---------|
| **Competitions** | 3 | ~60 | 15+ |
| **Matches** | 5 | ~115 | 35+ |
| **Players** | 2 | ~50 | 12+ |
| **Teams** | 3 | ~65 | 18+ |
| **Facilities** | 2 | ~57 | 12+ |
| **Disciplinary** | 2 | ~61 | 20+ |
| **Statistics** | 1 | ~24 | 8+ |
| **System** | 1 | ~15 | 5+ |
| **TOTAL** | **19** | **~450** | **125+** |

---

## 🎯 RELATIONSHIP MAPPING

### comet_matches (Kern-Tabelle)
```php
// Match hat viele Details
hasMany → comet_match_phases
hasMany → comet_match_events
hasMany → comet_match_players
hasMany → comet_match_officials
hasMany → comet_match_team_officials
hasMany → comet_cases

// Match gehört zu
belongsTo → comet_competitions
belongsTo → comet_facilities
```

### comet_players
```php
hasMany → comet_match_players
hasMany → comet_match_events
hasMany → comet_player_competition_stats
hasMany → comet_own_goal_scorers
hasMany → comet_cases (via offender)
belongsTo → comet_clubs_extended
```

### comet_clubs_extended
```php
hasMany → comet_team_officials
hasMany → comet_players
hasMany → comet_facilities
hasMany → comet_matches (as home/away)
hasMany → comet_rankings
```

### comet_competitions
```php
hasMany → comet_matches
hasMany → comet_rankings
hasMany → comet_player_competition_stats
hasMany → comet_own_goal_scorers
hasMany → comet_cases
hasMany → comet_club_competitions
```

### comet_facilities
```php
hasMany → comet_facility_fields
hasMany → comet_matches
belongsTo → comet_clubs_extended (owner)
```

### comet_cases
```php
hasMany → comet_sanctions
belongsTo → comet_matches
belongsTo → comet_competitions
belongsTo → comet_players (polymorphic offender)
```

---

## 🚀 NÄCHSTE SCHRITTE

### 1. Models erstellen (10 neue)
```bash
php artisan make:model Models/Central/CometMatchPhase
php artisan make:model Models/Central/CometMatchPlayer
php artisan make:model Models/Central/CometMatchOfficial
php artisan make:model Models/Central/CometMatchTeamOfficial
php artisan make:model Models/Central/CometTeamOfficial
php artisan make:model Models/Central/CometFacility
php artisan make:model Models/Central/CometFacilityField
php artisan make:model Models/Central/CometCase
php artisan make:model Models/Central/CometSanction
php artisan make:model Models/Central/CometOwnGoalScorer
```

### 2. Service Layer erweitern
Erweitere `app/Services/CometApiService.php` um:
- `getMatchPhases($matchFifaId)`
- `getMatchPlayers($matchFifaId)`
- `getMatchOfficials($matchFifaId)`
- `getMatchTeamOfficials($matchFifaId)`
- `getTeamOfficials($teamFifaId)`
- `getFacilities($facilityFifaId = null)`
- `getCases($competitionFifaId)`
- `getSanctions($caseFifaId)`
- `getOwnGoalScorers($competitionFifaId)`

### 3. Sync Commands erstellen
```bash
php artisan make:command Comet:SyncMatchDetails
php artisan make:command Comet:SyncTeamOfficials
php artisan make:command Comet:SyncFacilities
php artisan make:command Comet:SyncDisciplinary
```

### 4. Testing
- Unit Tests für Models und Relationships
- Feature Tests für API Endpoints
- Integration Tests für Complete Match Sync

---

## 📝 MIGRATION FILES

Alle neu erstellten Migrationen (26.10.2025):

```
✅ database/migrations/2025_10_26_120009_create_comet_match_phases_table.php
✅ database/migrations/2025_10_26_120010_create_comet_match_players_table.php
✅ database/migrations/2025_10_26_120011_create_comet_match_officials_table.php
✅ database/migrations/2025_10_26_120012_create_comet_match_team_officials_table.php
✅ database/migrations/2025_10_26_120013_create_comet_team_officials_table.php
✅ database/migrations/2025_10_26_120014_create_comet_facilities_table.php
✅ database/migrations/2025_10_26_120015_create_comet_facility_fields_table.php
✅ database/migrations/2025_10_26_120016_create_comet_cases_table.php
✅ database/migrations/2025_10_26_120017_create_comet_sanctions_table.php
✅ database/migrations/2025_10_26_120018_create_comet_own_goal_scorers_table.php
```

Alle ausgeführt in: `kp_server` (Central Database)

---

## ✅ VOLLSTÄNDIGKEIT

| Bereich | Status | Tabellen | Endpoints |
|---------|--------|----------|-----------|
| **Competitions** | ✅ Complete | 3/3 | 5/5 |
| **Matches** | ✅ Complete | 5/5 | 10/10 |
| **Players** | ✅ Complete | 2/2 | 3/3 |
| **Teams** | ✅ Complete | 3/3 | 3/3 |
| **Facilities** | ✅ Complete | 2/2 | 1/1 |
| **Disciplinary** | ✅ Complete | 2/2 | 3/3 |
| **Images** | ✅ Complete | - | 2/2 |
| **System** | ✅ Complete | - | 1/1 |
| **GESAMT** | **✅ 100%** | **19/19** | **26/26** |

---

**Dokumentation erstellt**: 26. Oktober 2025  
**Database Schema**: VOLLSTÄNDIG  
**Migrationen**: AUSGEFÜHRT  
**Bereit für**: Model-Erstellung & Service-Layer-Erweiterung

---

## 📚 Weiterführende Dokumentation

- `COMET_API_COMPLETE_SCHEMA.md` - API Schema & FIFA IDs
- `COMET_API_ENDPOINTS.md` - Endpoint Details & Beispiele
- `COMET_DATA_STRUCTURES.md` - Data Structures Reference
- `COMET_COMPLETE_DATABASE_SCHEMA.md` - Diese Datei
