# COMET SYNC SYSTEM - NK Prigorje
## Funktionierende Dateien für Competition-Synchronisation

### 📁 Struktur

```
comet_sync_system/
├── config/              # Konfigurationsdateien
├── migrations/          # Datenbank-Migrationen
├── models/             # Eloquent Models
├── services/           # API Service
├── scripts/            # Sync-Scripts
└── README.md           # Diese Datei
```

---

## ✅ FUNKTIONIERT PERFEKT

### Team FIFA ID: **598** (NICHT 618!)
- **11 Competitions** für Saison 2025/2026
- **254 Spieler**
- **1508 Matches** (alle Spiele der 11 Competitions)
- **137 Rankings** (alle Teams der 11 Competitions)
- **801 Top Scorers** (alle Torschützen der 11 Competitions)
- **787 Team-Logos** (98,3% Coverage)

---

## 📋 Dateien Übersicht

### 1. Migrations (`migrations/`)

#### Zentrale Datenbank Migrationen:
- `2025_10_26_120000_create_comet_competitions_table.php` - Competition Tabelle
- `2025_10_26_120001_create_comet_clubs_extended_table.php` - Erweiterte Club-Daten
- `2025_10_26_120002_create_comet_players_table.php` - Spieler
- `2025_10_26_120003_create_comet_matches_table.php` - Spiele
- `2025_10_26_120004_create_comet_match_events_table.php` - Spiel-Events
- `2025_10_26_120005_create_comet_rankings_table.php` - Tabellen/Rankings
- `2025_10_26_120006_create_comet_player_competition_stats_table.php` - Spieler-Statistiken
- `2025_10_26_120007_create_comet_club_competitions_table.php` - **WICHTIG!** Verknüpfung Club↔Competition
- `2025_10_26_120008_create_comet_syncs_table.php` - Sync-Protokoll
- `2025_10_26_120009_create_comet_match_phases_table.php` - Spielphasen
- `2025_10_26_120010_create_comet_match_players_table.php` - Spieler im Match
- `2025_10_26_120011_create_comet_match_officials_table.php` - Schiedsrichter
- `2025_10_26_120012_create_comet_match_team_officials_table.php` - Team Officials
- `2025_10_26_120013_create_comet_team_officials_table.php` - Team Officials Stammdaten
- `2025_10_26_120014_create_comet_facilities_table.php` - Stadien/Plätze
- `2025_10_26_120015_create_comet_facility_fields_table.php` - Spielfelder
- `2025_10_26_120016_create_comet_cases_table.php` - Fälle
- `2025_10_26_120017_create_comet_sanctions_table.php` - Strafen
- `2025_10_26_120018_create_comet_own_goal_scorers_table.php` - Eigentore

#### Zusätzliche Migrationen (erweiterte Felder):
- `2025_10_26_160001_add_api_fields_to_comet_competitions.php`
- `2025_10_26_160002_update_comet_players_fields.php`
- `2025_10_26_160003_add_api_fields_to_comet_matches.php`
- `2025_10_26_160004_create_comet_matches_simple_table.php` - Vereinfachte Matches
- `2025_10_26_160005_add_api_fields_to_comet_clubs_extended.php`
- `2025_10_26_162000_create_comet_rankings_final_table.php` - Production Rankings
- `2025_10_26_173000_create_comet_top_scorers_final_table.php` - **Production Top Scorers**

### 2. Models (`models/`)

- `CometCompetition.php` - Competition Model mit Relationships
- `CometPlayer.php` - Spieler Model
- `CometMatch.php` - Spiel Model
- `CometMatchSimple.php` - Vereinfachte Matches
- `CometRanking.php` - **Rankings/Tabellen Model**
- `CometTopScorer.php` - **Torschützen Model**
- `CometClubExtended.php` - Club Model
- `CometClubCompetition.php` - **WICHTIG!** Verknüpfung Model

### 3. Services (`services/`)

- `CometApiService.php` - Kompletter API Service mit allen Endpoints

### 4. Scripts (`scripts/`)

#### Competition & Player Sync:
- `sync_nk_prigorje_comet_data.php` - Competition & Player Sync
- `sync_competitions.php` - Competitions synchronisieren
- `sync_players.php` - Spieler synchronisieren

#### Match Sync:
- `sync_matches_nk_prigorje.php` - Alle Matches der 11 Competitions
- `sync_matches.php` - Einzelne Match Details

#### Rankings Sync:
- `sync_rankings_nk_prigorje.php` - Rankings/Tabellen aller 11 Competitions

#### Top Scorers Sync:
- `sync_topscorers_nk_prigorje.php` - **Top Scorers aller 11 Competitions**
- `update_topscorers_logos.php` - **Team-Logos aus Ordner zuweisen**
- `download_team_logos.php` - **Logos von API herunterladen**

---

## 🚀 Installation

### 1. Konfiguration in `.env`

```env
COMET_API_URL=https://api-hns.analyticom.de/api/export/comet
COMET_USERNAME=nkprigorje
COMET_PASSWORD=3c6nR$dS
```

### 2. Config in `config/services.php`

```php
'comet' => [
    'api_url' => env('COMET_API_URL', 'https://api-hns.analyticom.de/api/export/comet'),
    'username' => env('COMET_USERNAME'),
    'password' => env('COMET_PASSWORD'),
],
```

### 3. Migrationen ausführen

```bash
# Alle Comet-Migrationen
php artisan migrate --path=database/migrations --step

# Oder einzelne Migrationen
php artisan migrate --path=database/migrations/2025_10_26_120007_create_comet_club_competitions_table.php
```

### 4. Sync ausführen

```bash
# Competitions & Players
php comet_sync_system/scripts/sync_nk_prigorje_comet_data.php

# Matches (alle Spiele der 11 Competitions)
php comet_sync_system/scripts/sync_matches_nk_prigorje.php

# Rankings/Tabellen
php comet_sync_system/scripts/sync_rankings_nk_prigorje.php

# Top Scorers (Torschützenlisten)
php comet_sync_system/scripts/sync_topscorers_nk_prigorje.php

# Team-Logos zuweisen
php comet_sync_system/scripts/update_topscorers_logos.php
```

### 5. Kompletter Initial Sync

```bash
# 1. Migrationen
php artisan migrate

# 2. Competitions & Players
php comet_sync_system/scripts/sync_nk_prigorje_comet_data.php

# 3. Matches
php comet_sync_system/scripts/sync_matches_nk_prigorje.php

# 4. Rankings
php comet_sync_system/scripts/sync_rankings_nk_prigorje.php

# 5. Top Scorers
php comet_sync_system/scripts/sync_topscorers_nk_prigorje.php

# 6. Logos
php comet_sync_system/scripts/update_topscorers_logos.php
```

---

## 🔑 WICHTIGE ERKENNTNISSE

### Team FIFA ID vs. Club FIFA ID

- ❌ **Team FIFA ID 618** → 19 Competitions (ZU VIELE!)
- ✅ **Team FIFA ID 598** → 11 Competitions (KORREKT!)

### Die 11 Competitions:

1. PRVA ZAGREBAČKA LIGA - SENIORI 25/26 (Senioren)
2. **1. ZNL JUNIORI 25/26** ⚽ (JUNIOREN)
3. 1. ZNL KADETI 25/26 (U15)
4. 2. ZNL PIONIRI 25/26 (U13)
5. 2. ZNL MLAĐI PIONIRI 25/26 (U11)
6. 2. "B1"ZNL LIMAĆI grupa "A" 25/26 (U9)
7. 2. "B2"ZNL LIMAĆI grupa "A" 25/26 (U9)
8. 2. "B1"ZNL ZAGIĆI grupa "A" 25/26 (U7)
9. 2. "B2"ZNL ZAGIĆI grupa "A" 25/26 (U7)
10. 57. prvenstvo veterana ZNS, 1. liga skupina B (Veteranen)
11. KUP ZNS-a - SENIORI 25/26 (Pokal)

---

## 📊 Datenbank Schema

### `comet_club_competitions` (WICHTIG!)

Diese Tabelle verknüpft Club FIFA ID 598 mit den Competitions:

```sql
CREATE TABLE comet_club_competitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    competitionFifaId INT(11) NULLABLE INDEX,
    ageCategory VARCHAR(100) NOT NULL,
    ageCategoryName VARCHAR(100) NOT NULL,
    internationalName VARCHAR(255) NOT NULL,
    season SMALLINT(6) NOT NULL,
    status VARCHAR(50) NOT NULL,
    flag_played_matches INT(11) NULLABLE,
    flag_scheduled_matches INT(11) NULLABLE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**WICHTIG:** Diese Struktur entspricht deiner Produktions-Datenbank!

---

## 🔄 Sync-Ablauf

Der Sync-Script (`sync_nk_prigorje_comet_data.php`) macht folgendes:

1. **Competitions holen** (teamFifaId = 598, active = true)
2. **In `comet_competitions` speichern**
3. **In `comet_club_competitions` verknüpfen** ← WICHTIG!
4. **Teams holen** (für jede Competition)
5. **Spieler holen** (Team FIFA ID 598)
6. **Matches holen** (für jede Competition)
7. **Top Scorers holen** (für jede Competition)

---

## 🎯 API Endpoints

Der `CometApiService` nutzt:

```
GET /competitions?teamFifaId=598&active=true
GET /competition/{id}/teams
GET /team/{id}/players
GET /competition/{id}/matches
GET /competition/{id}/topScorers
```

---

## ✅ Test-Scripts

Im Hauptverzeichnis sind noch Test-Scripts:

- `test_club_598_api.php` - Testet verschiedene API-Parameter
- `verify_club_598_competitions.php` - Verifiziert die 11 Competitions
- `analyze_11_competitions.php` - Analysiert die Competition-Struktur

---

## 📝 Nächste Schritte

1. **Tenant-Sync**: Daten von Central → Tenant DB synchronisieren
2. **Filament Resources**: Admin-Panels für Competition-Management
3. **Scheduler**: Automatische Syncs einrichten
4. **Webhooks**: Real-time Updates von Comet API

---

## 🐛 Bekannte Probleme

- **Match Sync**: Foreign Key Constraints bei anderen Clubs (werden übersprungen)
- **Matches**: Nur Matches mit NK Prigorje werden gespeichert
- **Organisation IDs**: 10 (Zagrebački NS), 178182 (HNS Središte Centar)

---

## 📞 Support

Bei Fragen zur Comet API:
- API Dokumentation: https://api-hns.analyticom.de/api/export/comet
- Username: nkprigorje
- Password: 3c6nR$dS

---

**Stand:** 26. Oktober 2025
**Version:** 1.0 - Funktionierend
**Club:** NK Prigorje (FIFA ID 598)
