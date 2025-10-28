# Multi-Tenancy Isolation Analyse

## 🔍 Aktuelle Situation (PROBLEMATISCH!)

### Datenbank-Struktur:
```
Central DB (kpkb3):
- tenants (Tenant-Verwaltung)
- users (Central Admins)
- comet_* Tabellen (14 Tabellen) ✅ KORREKT

Tenant DBs (tenant_nknapijed, tenant_nkprigorjem):
- comet_* Tabellen (14 Tabellen) ❌ FALSCH!
- template_settings ✅ KORREKT
- users (Tenant-Users) ✅ KORREKT
- ... weitere Tenant-Tabellen
```

## 🚨 PROBLEM: Comet-Daten in Tenant-DBs

**Gefunden:**
- Tenant-Datenbanken haben eigene `comet_*` Tabellen
- Comet-Migrations existieren in `database/migrations/tenant/`
- Diese wurden bei `php artisan tenants:migrate` ausgeführt

**Soll-Zustand:**
- Comet-Daten NUR in Central DB (kpkb3)
- Tenant-DBs greifen via Models mit `connection = 'central'` darauf zu
- Comet-Migrations NUR in `database/migrations/` (nicht tenant/)

## ✅ Was KORREKT ist:

### 1. Comet Models haben Central Connection:
```php
// app/Models/Comet/CometMatch.php
protected $connection = 'central'; ✅
```

### 2. Tenancy Config ist korrekt:
```php
// config/tenancy.php
'database' => [
    'central_connection' => 'central',
    'prefix' => 'tenant_',
]
```

### 3. Jeder Tenant hat eigene DB:
```
tenant_nknapijed    ✅
tenant_nkprigorjem  ✅
```

## ❌ Was FALSCH ist:

### 1. Comet-Migrations in tenant/ Ordner:
```
database/migrations/tenant/2025_01_01_000004_create_comet_club_competitions_table.php
database/migrations/tenant/2025_01_01_000005_create_comet_club_representatives_table.php
database/migrations/tenant/2025_01_01_000006_create_comet_clubs_extended_table.php
database/migrations/tenant/2025_01_01_000007_create_comet_coaches_table.php
database/migrations/tenant/2025_01_01_000008_create_comet_match_events_table.php
database/migrations/tenant/2025_01_01_000009_create_comet_match_officials_table.php
database/migrations/tenant/2025_01_01_000010_create_comet_match_phases_table.php
database/migrations/tenant/2025_01_01_000011_create_comet_match_players_table.php
database/migrations/tenant/2025_01_01_000012_create_comet_match_team_officials_table.php
database/migrations/tenant/2025_01_01_000013_create_comet_matches_table.php
database/migrations/tenant/2025_01_01_000014_create_comet_own_goal_scorers_table.php
database/migrations/tenant/2025_01_01_000016_create_comet_rankings_table.php
database/migrations/tenant/2025_01_01_000017_create_comet_team_officials_table.php
database/migrations/tenant/2025_01_01_000018_create_comet_top_scorers_table.php
```
**→ Diese 14 Migrations müssen GELÖSCHT werden!**

### 2. Comet-Tabellen in Tenant-DBs existieren:
```sql
-- tenant_nknapijed hat:
comet_club_competitions
comet_club_representatives
comet_clubs_extended
comet_coaches
comet_match_events
comet_match_officials
comet_match_phases
comet_match_players
comet_match_team_officials
comet_matches
comet_own_goal_scorers
comet_rankings
comet_team_officials
comet_top_scorers
```
**→ Diese Tabellen müssen aus Tenant-DBs ENTFERNT werden!**

## 🔧 Lösung: 3-Schritt-Bereinigung

### Schritt 1: Comet-Tabellen aus Tenant-DBs löschen
```sql
-- Für jede Tenant-DB:
DROP TABLE IF EXISTS comet_club_competitions;
DROP TABLE IF EXISTS comet_club_representatives;
DROP TABLE IF EXISTS comet_clubs_extended;
DROP TABLE IF EXISTS comet_coaches;
DROP TABLE IF EXISTS comet_match_events;
DROP TABLE IF EXISTS comet_match_officials;
DROP TABLE IF EXISTS comet_match_phases;
DROP TABLE IF EXISTS comet_match_players;
DROP TABLE IF EXISTS comet_match_team_officials;
DROP TABLE IF EXISTS comet_matches;
DROP TABLE IF EXISTS comet_own_goal_scorers;
DROP TABLE IF EXISTS comet_rankings;
DROP TABLE IF EXISTS comet_team_officials;
DROP TABLE IF EXISTS comet_top_scorers;
```

### Schritt 2: Comet-Migrations aus tenant/ löschen
```bash
# Verschiebe nach _archive
Move-Item database/migrations/tenant/*comet*.php _archive/wrong_tenant_migrations/
```

### Schritt 3: Migration Entries aus Tenant-DBs bereinigen
```sql
-- Aus jeder Tenant migrations Tabelle:
DELETE FROM migrations WHERE migration LIKE '%comet%';
```

## 📋 Richtige Struktur nach Bereinigung:

### Central DB (kpkb3) - NUR hier:
```
✅ tenants
✅ users (Central Admins)
✅ comet_* (14 Tabellen)
✅ sync_logs
✅ settings (global)
```

### Tenant DBs (tenant_*) - Eigene Daten:
```
✅ users (Tenant-Users)
✅ template_settings (pro Tenant)
✅ posts, pages, media
✅ club_players
✅ groups, teams
✅ permissions, roles
❌ KEINE comet_* Tabellen!
```

### Zugriff auf Comet-Daten:
```php
// Im Tenant-Context:
use App\Models\Comet\CometMatch;

// Model hat: protected $connection = 'central';
$matches = CometMatch::where('competition_fifa_id', 123)->get();
// → Liest aus Central DB, nicht aus Tenant DB! ✅
```

## ✅ Isolations-Prüfung nach Bereinigung:

### Test 1: Tenant-Isolation
```php
// In Tenant 1:
$user = User::create(['name' => 'Test1']);

// In Tenant 2:
$users = User::all(); // Sollte Test1 NICHT sehen! ✅
```

### Test 2: Comet-Daten geteilt
```php
// In Tenant 1:
$matches = CometMatch::all(); // Aus Central DB

// In Tenant 2:
$matches = CometMatch::all(); // Gleiche Daten! ✅
```

### Test 3: Template Settings isoliert
```php
// In Tenant 1:
TemplateSetting::create(['key' => 'logo', 'value' => 'logo1.png']);

// In Tenant 2:
TemplateSetting::create(['key' => 'logo', 'value' => 'logo2.png']);
// → Jeder Tenant hat eigene Settings! ✅
```

## 🎯 Soll-Architektur:

```
┌─────────────────────────────────────────┐
│  CENTRAL DATABASE (kpkb3)               │
│  ─────────────────────────────          │
│  • Tenant Management                    │
│  • Central Users/Admins                 │
│  • Comet API Data (SHARED)              │
│    - Matches, Rankings, Top Scorers     │
│    - Competitions, Clubs, Coaches       │
│  • Sync Logs                            │
│  • Global Settings                      │
└─────────────────────────────────────────┘
           ▲              ▲
           │              │
    Read Comet Data   Read Comet Data
           │              │
           │              │
┌──────────┴─────┐  ┌────┴──────────┐
│ TENANT DB 1    │  │ TENANT DB 2   │
│ (tenant_club1) │  │ (tenant_club2)│
├────────────────┤  ├───────────────┤
│ • Users        │  │ • Users       │
│ • Templates    │  │ • Templates   │
│ • Posts/Pages  │  │ • Posts/Pages │
│ • Players      │  │ • Players     │
│ • Groups       │  │ • Groups      │
│ • Media        │  │ • Media       │
│ • Permissions  │  │ • Permissions │
└────────────────┘  └───────────────┘
   ISOLATED            ISOLATED
```

## 📝 Nächste Schritte:

1. **Bereinigungsskript ausführen** (cleanup_tenant_comet_tables.php)
2. **Migrations archivieren** (tenant/comet → _archive)
3. **Isolation testen** (siehe Tests oben)
4. **Dokumentieren** (Architektur-Diagramm erstellen)

## ⚠️ WICHTIG:

Nach der Bereinigung:
- ✅ Tenant-DBs haben KEINE Comet-Tabellen mehr
- ✅ Comet-Models greifen auf Central DB zu
- ✅ Tenant-Daten sind komplett isoliert
- ✅ Jeder Tenant hat eigene Settings/Users/Content
- ✅ Alle Tenants teilen sich Comet API Daten

**Status:** BEREINIGUNG ERFORDERLICH! ⚠️
