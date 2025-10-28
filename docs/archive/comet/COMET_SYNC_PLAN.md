# COMET TABELLEN - SYNCHRONISATIONS-PLAN

**Datum:** 28. Oktober 2025  
**Status:** In Planung  
**Priorität:** HOCH

---

## 📋 INHALTSVERZEICHNIS

1. [Problemstellung](#problemstellung)
2. [Ist-Zustand](#ist-zustand)
3. [Soll-Zustand](#soll-zustand)
4. [Migrations-Strategie](#migrations-strategie)
5. [Implementierungs-Plan](#implementierungs-plan)
6. [Rollback-Strategie](#rollback-strategie)
7. [Testing-Plan](#testing-plan)
8. [Checklisten](#checklisten)

---

## 🎯 PROBLEMSTELLUNG

### Kern-Problem
Die Comet-Tabellen in der Central DB (kpkb3) und den 6 Tenant-Datenbanken haben **unterschiedliche Strukturen**.

### Auswirkungen
- ❌ Sync-Fehler beim Importieren von COMET-Daten
- ❌ Inkonsistente Datenstrukturen über Tenants hinweg
- ❌ Schwierigkeiten bei neuen Features
- ❌ Wartungsprobleme

### Zahlen
- **585+ Probleme** identifiziert
- **27 fehlende Tabellen** (über alle Tenants)
- **435+ fehlende Spalten**
- **120+ unterschiedliche Definitionen**

---

## 📊 IST-ZUSTAND

### Central DB (kpkb3)
```
Total: 18 comet_* Tabellen
```

**Tabellen-Liste:**
1. `comet_club_competitions`
2. `comet_club_representatives`
3. `comet_clubs_extended`
4. `comet_coaches`
5. `comet_match_events`
6. `comet_match_officials`
7. `comet_match_phases`
8. `comet_match_players`
9. `comet_match_team_officials`
10. `comet_matches`
11. `comet_own_goal_scorers` ⚠️ wird nicht verwendet
12. `comet_player_competition_stats`
13. `comet_players`
14. `comet_rankings`
15. `comet_sanctions` ⚠️ wird nicht verwendet
16. `comet_syncs` ⚠️ wird nicht verwendet
17. `comet_team_officials`
18. `comet_top_scorers`

### Tenant-DBs

#### Produktiv-Tenants

**nknapijed** (tenant_nknapijed)
- ✅ Beste Übereinstimmung: 9/18 Tabellen identisch
- ❌ 3 fehlende Tabellen
- ❌ 81 fehlende Spalten
- ❌ 16 unterschiedliche Definitionen

**nkprigorjem** (tenant_nkprigorjem)
- ⚠️ Schlechteste Übereinstimmung: 1/18 Tabellen identisch
- ❌ 3 fehlende Tabellen
- ❌ 23 fehlende Spalten
- ❌ 39 unterschiedliche Definitionen

#### Test-Tenants

**testautosettings** (tenant_testautosettings)
- ❌ 6 fehlende Tabellen
- ❌ 81 fehlende Spalten
- ❌ 16 unterschiedliche Definitionen

**testclub** (tenant_testclub)
- ❌ 6 fehlende Tabellen
- ❌ 88 fehlende Spalten
- ❌ 17 unterschiedliche Definitionen

**testcometsync** (tenant_testcometsync)
- ❌ 6 fehlende Tabellen
- ❌ 81 fehlende Spalten
- ❌ 16 unterschiedliche Definitionen

**testneuerclub1761599717** (tenant_testneuerclub1761599717)
- ❌ 6 fehlende Tabellen
- ❌ 81 fehlende Spalten
- ❌ 16 unterschiedliche Definitionen

### Migrations-Dateien

**Aktueller Zustand:**
```
database/migrations/          - 5 comet-Migrations (alt, veraltet)
database/migrations/comet/    - 18 comet-Migrations (neu, aktuell)
database/migrations/tenant/   - 3 comet-Migrations (gemischt)
comet_sync_system/migrations/ - 4 comet-Migrations (veraltet)
```

**Problem:** Mehrere Migrations-Quellen führen zu Inkonsistenzen

---

## 🎯 SOLL-ZUSTAND

### Ziel-Architektur

```
┌─────────────────────────────────────────┐
│         CENTRAL DB (kpkb3)              │
│  - KEINE comet_* Tabellen               │
│  - Nur Tenant-Management                │
│  - Settings                             │
└─────────────────────────────────────────┘
                    │
        ┌───────────┴───────────┐
        │                       │
┌───────▼─────────┐    ┌────────▼────────┐
│  TENANT DB 1    │    │  TENANT DB 2    │
│  ───────────    │    │  ───────────    │
│  18 comet_*     │    │  18 comet_*     │
│  Tabellen       │    │  Tabellen       │
│  (identisch)    │    │  (identisch)    │
└─────────────────┘    └─────────────────┘
```

### Migrations-Struktur

**EINZIGE Quelle:**
```
database/migrations/comet/
├── 2025_01_01_000000_001_create_comet_clubs_extended_table.php
├── 2025_01_01_000000_002_create_comet_players_table.php
├── 2025_01_01_000000_003_create_comet_matches_table.php
├── ... (15 weitere)
└── 2025_01_01_000000_018_create_comet_top_scorers_table.php
```

**Entfernen:**
- `database/migrations/*comet*.php` (Hauptordner)
- `database/migrations/tenant/*comet*.php`
- `comet_sync_system/migrations/*` (gesamter Ordner)

### Tabellen-Status

#### Behalten (15 Tabellen)
1. ✅ `comet_club_competitions`
2. ✅ `comet_club_representatives`
3. ✅ `comet_clubs_extended`
4. ✅ `comet_coaches`
5. ✅ `comet_match_events`
6. ✅ `comet_match_officials`
7. ✅ `comet_match_phases`
8. ✅ `comet_match_players`
9. ✅ `comet_match_team_officials`
10. ✅ `comet_matches`
11. ✅ `comet_player_competition_stats`
12. ✅ `comet_players`
13. ✅ `comet_rankings`
14. ✅ `comet_team_officials`
15. ✅ `comet_top_scorers`

#### Entfernen (3 Tabellen)
1. ❌ `comet_own_goal_scorers` - bereits aus allen Tenants gelöscht
2. ❌ `comet_sanctions` - bereits aus allen Tenants gelöscht
3. ❌ `comet_syncs` - bereits aus allen Tenants gelöscht

**Grund für Entfernung:**
- Werden in der Applikation nicht verwendet
- Keine Daten vorhanden
- Reduziert Komplexität

---

## 🔧 MIGRATIONS-STRATEGIE

### Ansatz nach Tenant-Typ

#### PRODUKTIV-TENANTS (nknapijed, nkprigorjem)
**Strategie:** Inkrementelle Migration (Daten behalten)

**Schritte:**
1. ✅ **Backup erstellen**
   ```bash
   mysqldump -uroot tenant_nknapijed > backup_nknapijed_$(date +%Y%m%d_%H%M%S).sql
   ```

2. ✅ **Dry-Run ausführen**
   ```bash
   php sync_tenant_comet_tables.php --tenant=nknapijed --dry-run
   ```

3. ✅ **Fehlende Tabellen erstellen**
   - `comet_club_representatives`
   - `comet_coaches`
   - `comet_top_scorers`

4. ✅ **Fehlende Spalten hinzufügen**
   - `comet_players`: +23 Spalten (Kontakte, Medizin, etc.)
   - `comet_matches`: +18 Spalten (FIFA IDs, Team-Details)
   - `comet_clubs_extended`: +10 Spalten (Organisation, Logo, etc.)
   - `comet_rankings`: +7 Spalten (Competition Info)
   - Weitere Tabellen nach Bedarf

5. ✅ **Datentypen korrigieren**
   - Spalten mit unterschiedlichen Typen anpassen
   - NULL-Eigenschaften harmonisieren
   - Indizes angleichen

6. ✅ **Struktur-Vergleich**
   ```bash
   php compare_comet_tables.php
   ```

7. ✅ **Tests durchführen**
   - Comet-Sync testen
   - Daten-Integrität prüfen
   - Frontend-Anzeige testen

#### TEST-TENANTS (4 Stück)
**Strategie:** Neu erstellen (Daten verwerfen)

**Schritte:**
1. ✅ **Tenant-DBs droppen**
   ```sql
   DROP DATABASE IF EXISTS tenant_testautosettings;
   DROP DATABASE IF EXISTS tenant_testclub;
   DROP DATABASE IF EXISTS tenant_testcometsync;
   DROP DATABASE IF EXISTS tenant_testneuerclub1761599717;
   ```

2. ✅ **Tenants aus Central entfernen**
   ```sql
   DELETE FROM kpkb3.tenants WHERE id IN (
     'testautosettings', 
     'testclub', 
     'testcometsync', 
     'testneuerclub1761599717'
   );
   DELETE FROM kpkb3.domains WHERE tenant_id IN (...);
   ```

3. ✅ **Neu registrieren**
   ```bash
   php artisan tenants:create testclub
   # Verwendet automatisch aktuelle Migrations
   ```

4. ✅ **Struktur verifizieren**
   ```bash
   php compare_comet_tables.php
   ```

**Vorteil:**
- ✅ Garantiert 100% korrekte Struktur
- ✅ Keine Legacy-Probleme
- ✅ Schneller als Migration

---

## 📝 IMPLEMENTIERUNGS-PLAN

### Phase 1: Vorbereitung (1-2 Stunden)

#### 1.1 Migrations konsolidieren
```bash
# Alle comet-Migrations in database/migrations/comet/ sammeln
# Sicherstellen dass alle 15 Tabellen vorhanden sind
# Nummerierung prüfen: 001 bis 015
```

**Checkliste:**
- [ ] Alle Migrations in `database/migrations/comet/` vorhanden
- [ ] Korrekte Nummerierung (001-015)
- [ ] Alle Spalten lt. Central DB enthalten
- [ ] Keine Duplikate

#### 1.2 Alte Migrations entfernen
```bash
# Backup erstellen
cp -r database/migrations database/migrations_backup_$(date +%Y%m%d)

# Löschen
rm database/migrations/*comet*.php
rm database/migrations/tenant/*comet*.php
rm -rf comet_sync_system/migrations/
```

**Checkliste:**
- [ ] Backup erstellt
- [ ] Alte Dateien aus Hauptordner entfernt
- [ ] Alte Dateien aus tenant-Ordner entfernt
- [ ] comet_sync_system/migrations/ entfernt

#### 1.3 Central DB aufräumen
```sql
-- Entferne ungenutzte Tabellen aus Central DB
DROP TABLE IF EXISTS kpkb3.comet_own_goal_scorers;
DROP TABLE IF EXISTS kpkb3.comet_sanctions;
DROP TABLE IF EXISTS kpkb3.comet_syncs;
```

**Checkliste:**
- [ ] 3 Tabellen aus Central DB entfernt
- [ ] migrations-Tabelle bereinigt (Einträge für gelöschte Tabellen entfernen)

#### 1.4 Scripts erstellen
```bash
# sync_tenant_comet_tables.php
# reset_test_tenants.php
# verify_comet_structure.php
```

**Checkliste:**
- [ ] sync_tenant_comet_tables.php erstellt und getestet
- [ ] reset_test_tenants.php erstellt und getestet
- [ ] verify_comet_structure.php erstellt und getestet

---

### Phase 2: Test-Tenants neu erstellen (30 Min)

```bash
# Script ausführen
php reset_test_tenants.php
```

**Was passiert:**
1. Backup aller Test-Tenant-DBs
2. DBs droppen
3. Einträge aus Central DB löschen
4. Tenants neu erstellen
5. Struktur verifizieren

**Checkliste:**
- [ ] testautosettings neu erstellt
- [ ] testclub neu erstellt
- [ ] testcometsync neu erstellt
- [ ] testneuerclub1761599717 neu erstellt
- [ ] Struktur-Vergleich: 15/15 Tabellen identisch
- [ ] Keine Fehler in Logs

---

### Phase 3: Produktiv-Tenants migrieren (2-3 Stunden)

#### 3.1 nknapijed

**Dry-Run:**
```bash
php sync_tenant_comet_tables.php --tenant=nknapijed --dry-run > migration_nknapijed_plan.txt
```

**Review:**
- [ ] Plan geprüft
- [ ] Keine unerwarteten Änderungen
- [ ] Backup erstellt

**Ausführen:**
```bash
php sync_tenant_comet_tables.php --tenant=nknapijed
```

**Verifizieren:**
```bash
php verify_comet_structure.php --tenant=nknapijed
```

**Checkliste:**
- [ ] Backup erstellt
- [ ] Migration erfolgreich
- [ ] Struktur-Vergleich: 15/15 Tabellen identisch
- [ ] Comet-Sync getestet
- [ ] Frontend getestet
- [ ] Keine Fehler in Logs

#### 3.2 nkprigorjem

**Gleiche Schritte wie 3.1**

**Checkliste:**
- [ ] Backup erstellt
- [ ] Migration erfolgreich
- [ ] Struktur-Vergleich: 15/15 Tabellen identisch
- [ ] Comet-Sync getestet
- [ ] Frontend getestet
- [ ] Keine Fehler in Logs

---

### Phase 4: Validierung & Monitoring (1 Stunde)

#### 4.1 Finale Struktur-Prüfung
```bash
php verify_comet_structure.php --all
```

**Erwartetes Ergebnis:**
```
✅ ALLE 6 TENANTS: 15/15 Tabellen identisch
✅ KEINE Unterschiede
✅ KEINE fehlenden Spalten
✅ KEINE unterschiedlichen Definitionen
```

#### 4.2 Artisan Command erstellen
```bash
php artisan make:command CometStructureCheck
```

**Features:**
- Täglicher automatischer Check
- Warnung bei Abweichungen
- Slack/Email Benachrichtigung

**Checkliste:**
- [ ] Command erstellt
- [ ] In scheduler eingetragen
- [ ] Tests geschrieben
- [ ] Benachrichtigungen konfiguriert

---

## 🔙 ROLLBACK-STRATEGIE

### Wenn etwas schief geht

#### Produktiv-Tenants (nknapijed, nkprigorjem)

**Option 1: Backup wiederherstellen**
```bash
# DB droppen
mysql -uroot -e "DROP DATABASE tenant_nknapijed"

# Backup einspielen
mysql -uroot -e "CREATE DATABASE tenant_nknapijed"
mysql -uroot tenant_nknapijed < backup_nknapijed_20251028_HHMMSS.sql
```

**Option 2: Einzelne Tabellen/Spalten entfernen**
```sql
-- Wenn nur eine Tabelle Probleme macht
DROP TABLE tenant_nknapijed.comet_coaches;

-- Wenn nur eine Spalte Probleme macht
ALTER TABLE tenant_nknapijed.comet_players 
DROP COLUMN email;
```

**Checkliste:**
- [ ] Problem identifiziert
- [ ] Rollback-Methode gewählt
- [ ] Backup vorhanden
- [ ] Rollback durchgeführt
- [ ] System funktioniert wieder
- [ ] Fehler dokumentiert

#### Test-Tenants

**Einfach neu erstellen:**
```bash
php reset_test_tenants.php
```

---

## 🧪 TESTING-PLAN

### Vor der Migration

**Checkliste:**
- [ ] Alle Scripts erstellt und lokal getestet
- [ ] Backup-Strategie definiert
- [ ] Rollback-Plan erstellt
- [ ] Test-Tenant als Guinea-Pig benutzt

### Während der Migration

**Für jeden Tenant:**
- [ ] Backup erstellt
- [ ] Dry-Run durchgeführt und geprüft
- [ ] Migration ausgeführt
- [ ] Struktur verifiziert
- [ ] Logs geprüft

### Nach der Migration

**Funktionale Tests:**
1. **Comet-Sync Test**
   ```bash
   php artisan comet:sync --tenant=nknapijed --club-fifa-id=396
   ```
   - [ ] Daten werden importiert
   - [ ] Keine SQL-Fehler
   - [ ] Alle Tabellen befüllt

2. **Frontend Test**
   - [ ] Spieler-Liste anzeigen
   - [ ] Match-Details anzeigen
   - [ ] Tabelle anzeigen
   - [ ] Top-Scorer anzeigen
   - [ ] Team-Details anzeigen

3. **Backend Test (Filament)**
   - [ ] Comet Players Resource öffnen
   - [ ] Filter funktionieren
   - [ ] Details-Seite öffnen
   - [ ] Export funktioniert

4. **Performance Test**
   - [ ] Sync-Zeit messen
   - [ ] Query-Performance prüfen
   - [ ] Keine N+1 Queries

---

## ✅ CHECKLISTEN

### Pre-Migration Checklist

**Vorbereitung:**
- [ ] Migrations konsolidiert in `database/migrations/comet/`
- [ ] Alte Migrations entfernt
- [ ] Central DB aufgeräumt (3 Tabellen gelöscht)
- [ ] Scripts erstellt: sync, reset, verify
- [ ] Scripts lokal getestet
- [ ] Backup-Strategie definiert
- [ ] Rollback-Plan erstellt
- [ ] Team informiert
- [ ] Wartungsfenster geplant (falls nötig)

**Technisch:**
- [ ] Git commit: "Prepare comet table sync"
- [ ] Alle Änderungen committed
- [ ] Branch erstellt: feature/comet-sync
- [ ] Tests geschrieben für Scripts

---

### Migration Checklist - Test-Tenants

- [ ] testautosettings
  - [ ] Backup erstellt
  - [ ] Neu erstellt
  - [ ] Struktur: 15/15 ✅
  - [ ] Getestet

- [ ] testclub
  - [ ] Backup erstellt
  - [ ] Neu erstellt
  - [ ] Struktur: 15/15 ✅
  - [ ] Getestet

- [ ] testcometsync
  - [ ] Backup erstellt
  - [ ] Neu erstellt
  - [ ] Struktur: 15/15 ✅
  - [ ] Getestet

- [ ] testneuerclub1761599717
  - [ ] Backup erstellt
  - [ ] Neu erstellt
  - [ ] Struktur: 15/15 ✅
  - [ ] Getestet

---

### Migration Checklist - Produktiv-Tenants

**nknapijed:**
- [ ] **VOR Migration**
  - [ ] Backup erstellt
  - [ ] Dry-Run durchgeführt
  - [ ] Plan geprüft
  - [ ] Team informiert

- [ ] **WÄHREND Migration**
  - [ ] Migration gestartet
  - [ ] Logs überwacht
  - [ ] Keine Fehler

- [ ] **NACH Migration**
  - [ ] Struktur: 15/15 ✅
  - [ ] Comet-Sync getestet
  - [ ] Frontend getestet
  - [ ] Backend getestet
  - [ ] Performance OK

**nkprigorjem:**
- [ ] **VOR Migration**
  - [ ] Backup erstellt
  - [ ] Dry-Run durchgeführt
  - [ ] Plan geprüft
  - [ ] Team informiert

- [ ] **WÄHREND Migration**
  - [ ] Migration gestartet
  - [ ] Logs überwacht
  - [ ] Keine Fehler

- [ ] **NACH Migration**
  - [ ] Struktur: 15/15 ✅
  - [ ] Comet-Sync getestet
  - [ ] Frontend getestet
  - [ ] Backend getestet
  - [ ] Performance OK

---

### Post-Migration Checklist

**Validierung:**
- [ ] Alle 6 Tenants: Struktur 15/15 identisch
- [ ] Keine Fehler in Logs
- [ ] Alle funktionalen Tests bestanden
- [ ] Performance Tests bestanden

**Cleanup:**
- [ ] Alte Backups archiviert
- [ ] Temporäre Scripts entfernt
- [ ] Git commit: "Complete comet table sync"
- [ ] Branch merged

**Monitoring:**
- [ ] Artisan Command `comet:structure-check` eingerichtet
- [ ] Scheduler konfiguriert (täglich)
- [ ] Benachrichtigungen getestet
- [ ] Dokumentation aktualisiert

**Team:**
- [ ] Erfolgreiche Migration kommuniziert
- [ ] Dokumentation geteilt
- [ ] Lessons Learned festgehalten

---

## 📅 ZEITPLAN

### Geschätzte Dauer: 4-6 Stunden

**Phase 1: Vorbereitung** - 1-2h
- Scripts erstellen: 1h
- Migrations konsolidieren: 30min
- Testing: 30min

**Phase 2: Test-Tenants** - 30min
- Neu erstellen: 15min
- Verifizieren: 15min

**Phase 3: Produktiv-Tenants** - 2-3h
- nknapijed: 1-1.5h
- nkprigorjem: 1-1.5h

**Phase 4: Validierung** - 1h
- Struktur-Check: 15min
- Funktionale Tests: 30min
- Monitoring setup: 15min

---

## 📞 ANSPRECHPARTNER

**Bei Problemen kontaktieren:**
- Entwickler: [Name]
- DevOps: [Name]
- Datenbank-Admin: [Name]

**Eskalation bei kritischen Fehlern:**
1. Migration stoppen
2. Rollback durchführen
3. Team Lead informieren
4. Problem analysieren
5. Neuen Plan erstellen

---

## 📚 REFERENZEN

**Dateien:**
- `comet_structure_report.txt` - Detaillierter Struktur-Report
- `COMET_STRUCTURE_ANALYSIS.md` - Analyse-Bericht
- `database/migrations/comet/` - Migrations-Ordner

**Scripts:**
- `sync_tenant_comet_tables.php` - Tenant Sync Script
- `reset_test_tenants.php` - Test-Tenants neu erstellen
- `verify_comet_structure.php` - Struktur-Vergleich
- `compare_comet_tables.php` - Ursprüngliches Analyse-Script

**Commands:**
- `php artisan comet:structure-check` - Automatischer Check
- `php artisan tenants:create {name}` - Neuen Tenant erstellen
- `php artisan comet:sync` - Comet-Daten synchronisieren

---

**Stand:** 28. Oktober 2025  
**Version:** 1.0  
**Autor:** GitHub Copilot  
**Status:** 📋 Bereit zur Implementierung
