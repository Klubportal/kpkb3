# Comet Top Scorers Integration

## Übersicht
Vollständige Integration der Torschützenlisten aus der Comet API für alle Wettbewerbe, an denen NK Prigorje (Team 598) teilnimmt.

## Datenbank

### Tabelle: `comet_top_scorers`
**Connection:** `central` (klubportal_landlord)

#### Schema
```sql
- id (Primary Key)
- competition_fifa_id (Index)
- international_competition_name
- age_category
- age_category_name
- player_fifa_id (Nullable, Index)
- goals
- international_first_name
- international_last_name (Index)
- club
- club_id (Index)
- team_logo
- timestamps

UNIQUE: (competition_fifa_id, player_fifa_id)
INDEX: (competition_fifa_id, goals)
INDEX: (club_id, goals)
```

#### Datenmenge
- **801 Top Scorers** aus 11 Wettbewerben
- **787 Scorers** (98,3%) mit Team-Logos
- **46 verschiedene Clubs**
- **43 Clubs** mit Logos

## Eloquent Model

### `App\Models\Comet\CometTopScorer`

**Fillable Fields:**
```php
'competition_fifa_id', 'international_competition_name', 'age_category',
'age_category_name', 'player_fifa_id', 'goals', 'international_first_name',
'international_last_name', 'club', 'club_id', 'team_logo'
```

**Relationships:**
- `belongsTo(CometCompetition)` via competition_fifa_id
- `belongsTo(CometPlayer)` via player_fifa_id

**Scopes:**
- `topN($limit)` - Top N Torschützen
- `byClub($clubId)` - Filtern nach Club

**Helper Methods:**
- `getFullNameAttribute()` - Voller Spielername

**Casts:**
```php
'competition_fifa_id' => 'integer',
'player_fifa_id' => 'integer',
'goals' => 'integer',
'club_id' => 'integer'
```

## API Integration

### Endpoint
```
GET /competition/{competitionFifaId}/topScorers
```

### Response Structure
```json
{
  "competitionFifaId": 100629221,
  "playerFifaId": 223034,
  "goals": 11,
  "internationalFirstName": "Ivan",
  "internationalLastName": "Galić",
  "popularName": "",
  "club": "NK Mladost - Buzin",
  "clubId": 618
}
```

**Wichtig:** 
- Feld `playerFifaId` kann NULL sein
- Feld `clubId` wird verwendet (nicht `teamFifaId`)
- Feld `club` enthält Klubnamen direkt

## Sync Scripts

### 1. sync_topscorers_nk_prigorje.php
**Zweck:** Synchronisiert Top Scorers von allen 11 NK Prigorje Wettbewerben

**Datenquelle:** `comet_club_competitions` Tabelle

**Funktion:**
- Holt alle Competitions von NK Prigorje (Team 598)
- Für jede Competition: API-Call zu `/competition/{id}/topScorers`
- Speichert ALLE Torschützen (nicht nur NK Prigorje Spieler)
- Markiert NK Prigorje Spieler mit 🔵 in der Ausgabe

**Ergebnis:**
```
✅ 801 Top Scorers synchronisiert
📊 11 Wettbewerbe
🔵 NK Prigorje Spieler markiert
```

**Ausführung:**
```bash
php comet_sync_system/scripts/sync_topscorers_nk_prigorje.php
```

### 2. update_topscorers_logos.php
**Zweck:** Update Team-Logo Pfade in der Datenbank

**Funktion:**
- Scannt `public/images/kp_team_logo_images/` Ordner
- Findet Logos nach Club FIFA ID (z.B. `598.png`, `618.jpg`)
- Bevorzugt PNG > JPG > JPEG > GIF
- Schreibt Pfad in `team_logo` Spalte

**Unterstützte Formate:**
- PNG (bevorzugt)
- JPG/JPEG
- GIF

**Ergebnis:**
```
✅ 787 von 801 Scorers mit Logos
📁 43 von 46 Clubs haben Logos
```

**Ausführung:**
```bash
php comet_sync_system/scripts/update_topscorers_logos.php
```

### 3. download_team_logos.php
**Zweck:** Lädt fehlende Team-Logos von der Comet API

**Funktion:**
- Holt alle einzigartigen `club_id` aus `comet_top_scorers`
- Für jede Club ID: API-Call zu `/club/{clubId}`
- Lädt Logo-Bild herunter und speichert als `{clubId}.{ext}`
- Überspringt bereits vorhandene Logos
- Update `team_logo` Spalte automatisch

**Rate Limiting:** 100ms Verzögerung zwischen Requests

**Logo-Pfad:** `public/images/kp_team_logo_images/{clubId}.{ext}`

**Ausführung:**
```bash
php comet_sync_system/scripts/download_team_logos.php
```

## Verwendung

### Top Scorers einer Competition
```php
use App\Models\Comet\CometTopScorer;

$topScorers = CometTopScorer::where('competition_fifa_id', 100629221)
    ->orderBy('goals', 'desc')
    ->limit(10)
    ->get();

foreach ($topScorers as $scorer) {
    echo "{$scorer->full_name}: {$scorer->goals} Tore ({$scorer->club})\n";
    echo "Logo: {$scorer->team_logo}\n";
}
```

### Top Scorer eines Clubs
```php
$clubScorers = CometTopScorer::byClub(598)
    ->orderBy('goals', 'desc')
    ->get();
```

### Top N Torschützen
```php
$top10 = CometTopScorer::topN(10)->get();
```

### Mit Relationships
```php
$scorers = CometTopScorer::with(['competition', 'player'])
    ->where('competition_fifa_id', 100629221)
    ->get();

foreach ($scorers as $scorer) {
    echo $scorer->competition->name;
    echo $scorer->player?->full_name;
}
```

## Logo-Dateien

### Struktur
```
public/images/kp_team_logo_images/
├── 598.png    (NK Prigorje)
├── 618.png    (NK Mladost - Buzin)
├── 641.png    (NK Concordia)
├── ...
└── 175044.jpeg
```

### Naming Convention
- Dateiname = Club FIFA ID
- Format: `{clubId}.{ext}`
- Unterstützte Extensions: png, jpg, jpeg, gif

### Verwendung im Frontend
```blade
@if($scorer->team_logo)
    <img src="{{ asset($scorer->team_logo) }}" alt="{{ $scorer->club }}">
@else
    <div class="no-logo">{{ $scorer->club }}</div>
@endif
```

## Daten-Statistiken

### Nach Wettbewerb (Top 5)
1. **PRVA ZAGREBAČKA LIGA - SENIORI 25/26**: 125 Torschützen
2. **1. ZNL JUNIORI 25/26**: 121 Torschützen
3. **KUP ZNS-a - SENIORI 25/26**: 124 Torschützen
4. **2. ZNL PIONIRI 25/26**: 123 Torschützen
5. **DRUGA NL CENTAR PIONIRI 2025/2026**: 123 Torschützen

### Nach Club (Top 5)
1. **NK Prigorje (M)**: 53 Einträge (Club ID 598) 🔵
2. **NK Concordia**: 51 Einträge (Club ID 641)
3. **NK Studentski grad**: 45 Einträge (Club ID 601)
4. **NK Kralj Tomislav**: 39 Einträge (Club ID 175044)
5. **NK Nur**: 38 Einträge (Club ID 610)

### Top Torschützen (Gesamt)
1. **Matija Komušar** (NK Čulinec): 13 Tore
2. **Filip Oreč** (NK Sava): 13 Tore
3. **Relja Hrustić** (NK ZET): 13 Tore
4. **Vito Debeljak** (NK Concordia): 12 Tore
5. **Ivan Galić** (NK Mladost - Buzin): 11 Tore

## Migration

### Neue Installation
```bash
php artisan migrate --path=database/migrations/2025_10_26_173000_create_comet_top_scorers_final_table.php
```

### Rollback
```bash
php artisan migrate:rollback
```

### Fresh Install (mit allen Comet Tabellen)
```bash
php artisan migrate:fresh
php comet_sync_system/scripts/sync_topscorers_nk_prigorje.php
php comet_sync_system/scripts/update_topscorers_logos.php
```

## Wartung

### Regelmäßige Updates
**Empfohlen:** Täglich während der Saison

```bash
# 1. Sync neue Tore
php comet_sync_system/scripts/sync_topscorers_nk_prigorje.php

# 2. Update Logos (nur bei neuen Clubs nötig)
php comet_sync_system/scripts/update_topscorers_logos.php
```

### Daten-Refresh
```bash
# Tabelle leeren
php artisan tinker --execute="DB::connection('central')->table('comet_top_scorers')->truncate();"

# Neu synchronisieren
php comet_sync_system/scripts/sync_topscorers_nk_prigorje.php
php comet_sync_system/scripts/update_topscorers_logos.php
```

## Troubleshooting

### Keine Logos angezeigt
```bash
# Prüfe ob Logos vorhanden
ls public/images/kp_team_logo_images/

# Update Logo-Pfade
php comet_sync_system/scripts/update_topscorers_logos.php

# Lade fehlende Logos
php comet_sync_system/scripts/download_team_logos.php
```

### Player FIFA ID fehlt
Das ist normal - die API liefert nicht immer `playerFifaId`. Die Spalte ist `NULLABLE`.

### Duplikate vermeiden
Die Tabelle hat einen UNIQUE constraint auf `(competition_fifa_id, player_fifa_id)`. Bei erneuter Synchronisation werden bestehende Einträge aktualisiert (updateOrCreate).

## Abhängigkeiten

### Andere Comet Tabellen
- `comet_competitions` - Competition Details
- `comet_club_competitions` - NK Prigorje Competitions
- `comet_players` - Spieler Details (optional)

### Externe Services
- Comet REST API
- Authentifizierung: nkprigorje / 3c6nR$dS

## Changelog

### 2025-10-26 - Initial Release
- ✅ Migration erstellt mit production schema
- ✅ Eloquent Model mit Relationships
- ✅ Sync Script für NK Prigorje (11 Competitions)
- ✅ Logo Update Script
- ✅ Logo Download Script
- ✅ 801 Top Scorers synchronisiert
- ✅ 787 Team-Logos zugewiesen
