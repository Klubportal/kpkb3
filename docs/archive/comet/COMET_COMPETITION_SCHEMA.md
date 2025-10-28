# COMET Competition Schema - Detaillierte Referenz

**Version**: 1.0  
**Datum**: October 23, 2025  
**Standard**: FIFA Connect  
**Bereich**: Competition Object Definition  

---

## 📋 Inhaltsverzeichnis

1. [Competition Object Übersicht](#competition-object-übersicht)
2. [Feld-Referenz (A-Z)](#feld-referenz-a-z)
3. [Feld-Kategorien](#feld-kategorien)
4. [Gültige Werte & Enums](#gültige-werte--enums)
5. [Praktische Beispiele](#praktische-beispiele)
6. [Datenbank Schema](#datenbank-schema)
7. [Laravel Model](#laravel-model)
8. [Häufige Abfragen](#häufige-abfragen)

---

## 1. Competition Object Übersicht

Das **Competition Object** in COMET definiert einen Wettbewerb (Liga, Pokal, Turnier, etc.) mit all seinen Metadaten, Konfigurationen und Verwaltungsinformationen.

```
┌─────────────────────────────────────────────────────────┐
│                   COMPETITION OBJECT                    │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Identifikatoren:                                       │
│    • competitionFifaId (PK)                             │
│    • organisationFifaId (FK)                            │
│    • superiorCompetitionFifaId (FK)                     │
│                                                         │
│  Kern-Informationen:                                    │
│    • internationalName / internationalShortName        │
│    • localNames (Mehrsprachig)                          │
│    • internationalDescription                           │
│                                                         │
│  Klassifikation:                                        │
│    • ageCategory (SENIORS, U_21, U_19, etc.)           │
│    • discipline (FOOTBALL)                              │
│    • gender (MALE, FEMALE, MIXED)                       │
│    • teamCharacter (CLUB, NATIONAL)                     │
│    • matchType (OFFICIAL, FRIENDLY, etc.)               │
│                                                         │
│  Zeitliche Informationen:                               │
│    • dateFrom / dateTo                                  │
│    • season (z.B. 2025)                                 │
│                                                         │
│  Struktur & Format:                                     │
│    • nature (ROUND_ROBIN, KNOCKOUT, etc.)              │
│    • multiplier (Punkte-Multiplikator)                  │
│    • numberOfParticipants                               │
│    • orderNumber (Sortierung)                           │
│                                                         │
│  Regelwerk:                                             │
│    • flyingSubstitutions (Wechsel ohne Aus)            │
│    • penaltyShootout (Elfmeterschießen möglich)        │
│    • competitionType (z.B. "League", "Cup")            │
│                                                         │
│  Assets & Links:                                        │
│    • imageId / picture (Logo/Banner)                    │
│    • rankingNotes (Notizen zur Tabelle)                │
│                                                         │
│  Status:                                                │
│    • status (ACTIVE, INACTIVE)                          │
│    • ageCategoryName (Label-Key)                        │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 2. Feld-Referenz (A-Z)

### ageCategory
**Typ**: STRING  
**Enum**: SENIORS, U_21, U_19, U_18, U_17, U_16, U_15, U_14, U_13, U_12, U_11, U_10, OTHER, A

**Beschreibung**: Altersklassifikation des Wettbewerbs.

**Beispiele**:
```
SENIORS     - Erwachsene/Profis
U_21        - U-21 Meisterschaft
U_19        - U-19 Meisterschaft
A           - Ohne Altersbeschränkung
OTHER       - Sonstige
```

**Verwendung**:
```php
if ($competition->ageCategory === 'SENIORS') {
    // Erwachsenen-Liga
}
```

---

### ageCategoryName
**Typ**: STRING  
**Format**: Label-Key für Internationalisierung

**Beschreibung**: Übersetzungsschlüssel für UI-Darstellung.

**Beispiele**:
```
label.category.seniors
label.category.u_21
label.category.u_19
label.category.absolute
```

---

### dateFrom
**Typ**: DATETIME (ISO 8601)  
**Format**: yyyy-MM-dd'T'HH:mm:ss

**Beschreibung**: Startdatum des Wettbewerbs.

**Beispiel**:
```
2025-01-15T00:00:00
```

**Verwendung**:
```php
$startDate = Carbon::parse($competition->dateFrom);
$daysSinceStart = $startDate->diffInDays(now());
```

---

### dateTo
**Typ**: DATETIME (ISO 8601)  
**Format**: yyyy-MM-dd'T'HH:mm:ss

**Beschreibung**: Enddatum des Wettbewerbs.

**Beispiel**:
```
2025-11-30T23:59:59
```

---

### discipline
**Typ**: STRING  
**Enum**: FOOTBALL, FUTSAL, BEACH_SOCCER

**Beschreibung**: Sport-Art.

**Beispiel**:
```
FOOTBALL    - 11er Fußball
FUTSAL      - Hallenfußball
BEACH_SOCCER - Beachsoccer
```

---

### gender
**Typ**: STRING  
**Enum**: MALE, FEMALE, MIXED

**Beschreibung**: Geschlecht der Teilnehmer.

**Beispiele**:
```
MALE        - Herren
FEMALE      - Damen
MIXED       - Gemischt
```

---

### internationalName
**Typ**: STRING (Max 255)  
**Format**: Englischer Name

**Beschreibung**: Internationaler Wettbewerbsname (Englisch).

**Beispiele**:
```
"Copa Bridgestone Libertadores 2025"
"German Bundesliga 2024/2025"
"UEFA Champions League 2024/2025"
"DFB-Pokal 2024/2025"
"U-21 European Championship 2025"
```

**Verwendung**:
```php
// Fallback für mehrsprachige Systeme
$name = $competition->localNames->first(fn($n) => $n->language === 'DE')->name 
    ?? $competition->internationalName;
```

---

### internationalShortName
**Typ**: STRING (Max 50)  
**Format**: Abkürzung

**Beschreibung**: Internationale Kurz-Bezeichnung.

**Beispiele**:
```
"Copa Libertadores 2025"
"Bundesliga"
"Champions League"
"DFB-Pokal"
"U21-EURO 2025"
```

---

### imageId
**Typ**: BIGINT  
**FK Reference**: Picture/Image table

**Beschreibung**: Referenz zur Wettbewerbs-Logo oder Banner-Grafik.

**Verwendung**:
```php
// Abrufen via COMET Images API
GET /api/export/comet/images/competition/{competitionFifaId}
```

---

### multiplier
**Typ**: INTEGER  
**Range**: 1-10

**Beschreibung**: Punkte-Multiplikator für diese Competition.

**Beispiele**:
```
1   - Standard (3 Punkte für Sieg, 1 für Remis)
2   - Doppelte Punkte
```

**Verwendung**:
```php
$points = match($match->result) {
    'HOME_WIN' => 3 * $competition->multiplier,
    'DRAW' => 1 * $competition->multiplier,
    'AWAY_WIN' => 0
};
```

---

### nature
**Typ**: STRING  
**Enum**: ROUND_ROBIN, KNOCKOUT, GROUP_STAGE, SWISS_SYSTEM, OTHER

**Beschreibung**: Wettbewerbsformat/Struktur.

**Beispiele**:
```
ROUND_ROBIN     - Jeder gegen Jeden (Gruppen)
KNOCKOUT        - K.O.-System (Elfmeterschießen möglich)
GROUP_STAGE     - Gruppenphase + Knockout
SWISS_SYSTEM    - Swiss-System (z.B. Chess)
OTHER           - Sonstiges Format
```

**Logik**:
```php
if ($competition->nature === 'KNOCKOUT') {
    // Showdown: Elfmeter möglich
    $enablePenalties = true;
} elseif ($competition->nature === 'ROUND_ROBIN') {
    // Ligaformat: Tabelle wichtiger
    $showRanking = true;
}
```

---

### numberOfParticipants
**Typ**: INTEGER  
**Range**: 1-unlimited

**Beschreibung**: Erwartete oder aktuelle Anzahl teilnehmender Teams.

**Beispiele**:
```
4   - Kleine Gruppe
16  - Standard-Turnier
32  - Große Liga
256 - Landesweiter Wettbewerb
```

**Verwendung**:
```php
$percentage = ($teamsRegistered / $competition->numberOfParticipants) * 100;
echo "Registration: {$percentage}% complete";
```

---

### orderNumber
**Typ**: INTEGER  
**Range**: 1-1000

**Beschreibung**: Sortierreihenfolge in Listen.

**Verwendung**:
```php
Competition::orderBy('orderNumber')->get();  // Korrekte Reihenfolge
```

---

### organisationFifaId
**Typ**: BIGINT  
**FK Reference**: Organisation table

**Beschreibung**: Veranstalter-Organisation (Verband, Club, etc.).

**Beispiele**:
```
39393   - DFB (German Football Association)
39394   - Bayern Munich
40004   - UEFA
```

**Hierarchie**:
```
FIFA (Top)
  └─ Continent (e.g., UEFA)
      └─ Country (e.g., DFB)
          └─ Regional (e.g., Bavarian FA)
              └─ Club (e.g., Bayern Munich)
```

---

### season
**Typ**: INTEGER  
**Format**: yyyy oder yyyy/yyyy

**Beschreibung**: Saison des Wettbewerbs.

**Beispiele**:
```
2025            - Kalender-Saison
2024/2025 → 2024 - Sportliche Saison (gespeichert als Start-Jahr)
```

**Verwendung**:
```php
if ($competition->season === now()->year) {
    $badge = 'CURRENT_SEASON';
}
```

---

### status
**Typ**: STRING  
**Enum**: ACTIVE, INACTIVE, ARCHIVED, PENDING, CANCELLED

**Beschreibung**: Lebenszyklus-Status des Wettbewerbs.

**Beispiele**:
```
ACTIVE      - Läuft aktuell oder geplant
INACTIVE    - Pausiert/Aufgelöst
ARCHIVED    - Historisch abgeschlossen
PENDING     - Wartet auf Freigabe
CANCELLED   - Abgesagt
```

---

### teamCharacter
**Typ**: STRING  
**Enum**: CLUB, NATIONAL, COMBINED, SCHOOL, OTHER

**Beschreibung**: Charakterisierung der teilnehmenden Teams.

**Beispiele**:
```
CLUB        - Klub-Wettbewerb (Liga, Pokal)
NATIONAL    - Nationalteam-Turnier
COMBINED    - Gemischt (z.B. klub + national)
SCHOOL      - Schulen/Universitäten
OTHER       - Sonstiges
```

**Logik**:
```php
if ($competition->teamCharacter === 'NATIONAL') {
    // Nur Nationalteams erlaubt
    $allowClubParticipation = false;
}
```

---

### superiorCompetitionFifaId
**Typ**: BIGINT (Nullable)  
**FK Reference**: Competition table

**Beschreibung**: Referenz zur übergeordneten Competition (z.B. Gruppe gehört zu Liga).

**Beispiele**:
```
Copa Libertadores 2025 (Super)
  └─ Grupo 1 (superiorCompetitionFifaId = Copa ID)
  └─ Grupo 2 (superiorCompetitionFifaId = Copa ID)
  └─ Grupo 3 (superiorCompetitionFifaId = Copa ID)

Champions League Group Stage (Super)
  └─ Group A
  └─ Group B
  └─ etc.
```

**Hierarchie-Abfrage**:
```php
// Alle Unter-Competitionen
$subCompetitions = Competition::where('superiorCompetitionFifaId', 
    $competition->competitionFifaId)->get();

// Übergeordnete Competition
$parent = Competition::find($competition->superiorCompetitionFifaId);
```

---

### picture
**Typ**: OBJECT (Picture/Image)  
**Nested Schema**:
```json
{
  "contentType": "image/jpeg",
  "pictureLink": "/Competition/3936145_1625097200000",
  "value": "base64_encoded_image_data"
}
```

**Beschreibung**: Eingebettetes Bild-Objekt (wenn `competitionPhotoEmbedded=true`).

**Verwendung**:
```php
if (!empty($competition->picture)) {
    $imageSrc = 'data:' . $competition->picture['contentType'] . 
                ';base64,' . $competition->picture['value'];
}
```

---

### flyingSubstitutions
**Typ**: BOOLEAN

**Beschreibung**: Dürfen Spieler eingewechselt werden, ohne dass der herausgenommene Spieler das Feld verlässt (im Futsal/Hockey)?

**Beispiele**:
```
true    - Ja, Spieler können beliebig aus/ein (Futsal)
false   - Nein, klassische Wechsel-Regel
```

---

### penaltyShootout
**Typ**: BOOLEAN

**Beschreibung**: Ist Elfmeterschießen bei Unentschieden erlaubt?

**Beispiele**:
```
true    - Ja, K.O.-Spiele mit Elfmetern
false   - Nein, oder nicht applicable
```

---

### matchType
**Typ**: STRING  
**Enum**: OFFICIAL, FRIENDLY, QUALIFYING, TEST_MATCH, UNOFFICIAL

**Beschreibung**: Offizialitäts-Status der Spiele.

**Beispiele**:
```
OFFICIAL       - Offizielle Meisterschafts-Spiele
FRIENDLY       - Freundschaftsspiele
QUALIFYING     - Qualifikations-Spiele
TEST_MATCH     - Testkampf
UNOFFICIAL     - Inoffizielle/Freundschafts-Matches
```

**Verwendung**:
```php
if ($competition->matchType === 'OFFICIAL') {
    $countForRanking = true;  // Zählt zur Tabelle
} else {
    $countForRanking = false; // Nur informativ
}
```

---

### localNames
**Typ**: ARRAY OF OBJECTS  
**Nested Schema**:
```json
[
  {
    "name": "Liga Profesional de Fútbol",
    "shortName": "LPF",
    "language": "SPA",
    "competitionFifaId": 3936145,
    "organisationFifaId": 39393,
    "placeName": "Argentina",
    "regionName": "Buenos Aires"
  }
]
```

**Beschreibung**: Mehrsprachige Bezeichnungen des Wettbewerbs.

**Verwendung**:
```php
// Lokalisierte Namen abrufen
$germanName = $competition->localNames
    ->firstWhere('language', 'GER')?->name 
    ?? $competition->internationalName;

$spanishName = $competition->localNames
    ->firstWhere('language', 'SPA')?->name;
```

---

### rankingNotes
**Typ**: STRING (LONGTEXT)  
**Format**: Rich Text oder Plain Text

**Beschreibung**: Anmerkungen zur Tabelle/Rangliste (Punktabzüge, Besonderheiten, etc.).

**Beispiele**:
```
"-2 points to Bayern Munich for ... reasons"
"Record incomplete: 3 matches still to be played"
"Ranking frozen as of 2025-10-15"
"Special rules apply: see rule book § 5.2"
```

**Verwendung**:
```php
// In der UI anzeigen
@if($competition->rankingNotes)
    <div class="alert alert-info">
        {{ $competition->rankingNotes }}
    </div>
@endif
```

---

### competitionType
**Typ**: STRING  
**Enum / Free Text**: League, Cup, Tournament, Qualifying, Friendly, Championship, etc.

**Beschreibung**: Kategorisierung des Wettbewerbs-Typs.

**Beispiele**:
```
League          - Ligawettbewerb
Cup             - Pokalwettbewerb
Tournament      - Turnier
Championship    - Meisterschaft
Qualifying      - Qualifikation
Friendly        - Freundschaften
PlayOff         - Playoff-Serie
Super Cup       - Super Cup
```

**Verwendung**:
```php
$competitionIcon = match($competition->competitionType) {
    'League' => '🏆',
    'Cup' => '🥇',
    'Tournament' => '🎯',
    'Championship' => '👑',
    default => '⚽'
};
```

---

### competitionTypeId
**Typ**: BIGINT (Nullable)  
**FK Reference**: CompetitionType table

**Beschreibung**: Referenz zur standardisierten Competition-Typ Definition.

**Verwendung**:
```php
// Über Foreign Key zur CompetitionType-Tabelle
$competitionTypeConfig = $competition->competitionType();
```

---

## 3. Feld-Kategorien

### 🔑 Primär-Identifikatoren
```
competitionFifaId       - Eindeutige FIFA ID
organisationFifaId      - Veranstalter
superiorCompetitionFifaId - Hierarchische Verbindung
```

### 📝 Kern-Informationen
```
internationalName       - Englischer Name
internationalShortName  - Kurzbezeichnung
localNames              - Mehrsprachige Namen
competitionType         - Kategorisierung
competitionTypeId       - Typ-Referenz
```

### 🎯 Klassifikation
```
ageCategory             - Altersklasse
ageCategoryName         - Label-Key
discipline              - Sport-Art
gender                  - Geschlecht
teamCharacter           - Team-Typ
matchType               - Offizialitäts-Status
```

### 📅 Zeitliche Information
```
dateFrom                - Start-Datum
dateTo                  - End-Datum
season                  - Saison-Jahr
```

### 🏗️ Struktur & Format
```
nature                  - Wettbewerbs-Format
numberOfParticipants    - Teilnehmer-Anzahl
orderNumber             - Sortierreihenfolge
multiplier              - Punkte-Multiplikator
```

### ⚙️ Regelwerk
```
flyingSubstitutions     - Beliebige Wechsel?
penaltyShootout         - Elfmeter möglich?
rankingNotes            - Tabellen-Notizen
```

### 🎨 Assets & Links
```
imageId                 - Bild-Referenz
picture                 - Eingebettete Grafik
```

### ✅ Status
```
status                  - Aktiv/Inaktiv
```

---

## 4. Gültige Werte & Enums

### ageCategory
```
SENIORS
U_21
U_19
U_18
U_17
U_16
U_15
U_14
U_13
U_12
U_11
U_10
A (no restriction)
OTHER
```

### discipline
```
FOOTBALL
FUTSAL
BEACH_SOCCER
```

### gender
```
MALE
FEMALE
MIXED
```

### nature
```
ROUND_ROBIN
KNOCKOUT
GROUP_STAGE
SWISS_SYSTEM
OTHER
```

### teamCharacter
```
CLUB
NATIONAL
COMBINED
SCHOOL
OTHER
```

### matchType
```
OFFICIAL
FRIENDLY
QUALIFYING
TEST_MATCH
UNOFFICIAL
```

### status
```
ACTIVE
INACTIVE
ARCHIVED
PENDING
CANCELLED
```

---

## 5. Praktische Beispiele

### Beispiel 1: Bundesliga 2024/2025

```json
{
  "competitionFifaId": 3936145,
  "internationalName": "German Bundesliga 2024/2025",
  "internationalShortName": "Bundesliga",
  "organisationFifaId": 39393,
  "season": 2024,
  "dateFrom": "2024-08-16T00:00:00",
  "dateTo": "2025-05-17T23:59:59",
  "ageCategory": "SENIORS",
  "ageCategoryName": "label.category.seniors",
  "discipline": "FOOTBALL",
  "gender": "MALE",
  "teamCharacter": "CLUB",
  "nature": "ROUND_ROBIN",
  "matchType": "OFFICIAL",
  "numberOfParticipants": 18,
  "orderNumber": 1,
  "multiplier": 1,
  "status": "ACTIVE",
  "flyingSubstitutions": false,
  "penaltyShootout": false,
  "competitionType": "League",
  "competitionTypeId": 1,
  "superiorCompetitionFifaId": null,
  "imageId": 3936909,
  "picture": { "contentType": "image/png", "pictureLink": "...", "value": "..." },
  "rankingNotes": "Official DFB League - 34 matchdays",
  "localNames": [
    { "language": "GER", "name": "Bundesliga", "shortName": "BL" },
    { "language": "FRA", "name": "Bundesliga", "shortName": "BL" }
  ]
}
```

### Beispiel 2: DFB-Pokal (Knockout)

```json
{
  "competitionFifaId": 3936200,
  "internationalName": "DFB-Pokal 2024/2025",
  "internationalShortName": "DFB-Pokal",
  "organisationFifaId": 39393,
  "season": 2024,
  "dateFrom": "2024-08-16T00:00:00",
  "dateTo": "2025-05-24T23:59:59",
  "ageCategory": "SENIORS",
  "discipline": "FOOTBALL",
  "gender": "MALE",
  "teamCharacter": "CLUB",
  "nature": "KNOCKOUT",
  "matchType": "OFFICIAL",
  "numberOfParticipants": 64,
  "orderNumber": 2,
  "multiplier": 2,
  "status": "ACTIVE",
  "flyingSubstitutions": false,
  "penaltyShootout": true,
  "competitionType": "Cup",
  "competitionTypeId": 2,
  "superiorCompetitionFifaId": null,
  "rankingNotes": "K.O.-System with 2-leg semi-finals"
}
```

### Beispiel 3: U-21 EM 2025 (Turnier mit Gruppen)

```json
{
  "competitionFifaId": 3936300,
  "internationalName": "UEFA European U-21 Championship 2025",
  "internationalShortName": "U21-EURO 2025",
  "organisationFifaId": 40004,
  "season": 2025,
  "dateFrom": "2025-06-01T00:00:00",
  "dateTo": "2025-07-05T23:59:59",
  "ageCategory": "U_21",
  "ageCategoryName": "label.category.u_21",
  "discipline": "FOOTBALL",
  "gender": "MALE",
  "teamCharacter": "NATIONAL",
  "nature": "GROUP_STAGE",
  "matchType": "OFFICIAL",
  "numberOfParticipants": 16,
  "orderNumber": 1,
  "multiplier": 1,
  "status": "ACTIVE",
  "flyingSubstitutions": false,
  "penaltyShootout": true,
  "competitionType": "Championship",
  "competitionTypeId": 3,
  "superiorCompetitionFifaId": null,
  "rankingNotes": "4 groups of 4 teams + knockout stages"
}
```

### Beispiel 4: U-21 EM 2025 - Gruppe A (Sub-Competition)

```json
{
  "competitionFifaId": 3936301,
  "internationalName": "UEFA U-21 EURO 2025 - Group A",
  "internationalShortName": "U21-EURO Group A",
  "organisationFifaId": 40004,
  "season": 2025,
  "dateFrom": "2025-06-01T00:00:00",
  "dateTo": "2025-06-10T23:59:59",
  "ageCategory": "U_21",
  "discipline": "FOOTBALL",
  "gender": "MALE",
  "teamCharacter": "NATIONAL",
  "nature": "ROUND_ROBIN",
  "matchType": "OFFICIAL",
  "numberOfParticipants": 4,
  "orderNumber": 1,
  "multiplier": 1,
  "status": "ACTIVE",
  "flyingSubstitutions": false,
  "penaltyShootout": false,
  "competitionType": "Championship",
  "competitionTypeId": 3,
  "superiorCompetitionFifaId": 3936300,
  "rankingNotes": "Group stage: each team plays 3 matches"
}
```

### Beispiel 5: Futsal Liga (Mit Flying Substitutions)

```json
{
  "competitionFifaId": 3936400,
  "internationalName": "German Futsal Bundesliga 2024/2025",
  "internationalShortName": "Futsal BL",
  "organisationFifaId": 39393,
  "season": 2024,
  "dateFrom": "2024-09-15T00:00:00",
  "dateTo": "2025-04-06T23:59:59",
  "ageCategory": "SENIORS",
  "discipline": "FUTSAL",
  "gender": "MALE",
  "teamCharacter": "CLUB",
  "nature": "ROUND_ROBIN",
  "matchType": "OFFICIAL",
  "numberOfParticipants": 10,
  "orderNumber": 1,
  "multiplier": 1,
  "status": "ACTIVE",
  "flyingSubstitutions": true,
  "penaltyShootout": true,
  "competitionType": "League",
  "competitionTypeId": 1
}
```

---

## 6. Datenbank Schema

### migrations/central/create_competitions_table.php

```php
Schema::create('competitions', function (Blueprint $table) {
    $table->bigInteger('competition_fifa_id')->primary();
    $table->bigInteger('organisation_fifa_id')->index();
    $table->bigInteger('superior_competition_fifa_id')->nullable()->index();
    $table->bigInteger('competition_type_id')->nullable();
    
    // Kern-Informationen
    $table->string('international_name', 255);
    $table->string('international_short_name', 50);
    $table->string('competition_type', 100)->nullable();
    
    // Klassifikation
    $table->enum('age_category', [
        'SENIORS', 'U_21', 'U_19', 'U_18', 'U_17', 'U_16', 'U_15',
        'U_14', 'U_13', 'U_12', 'U_11', 'U_10', 'A', 'OTHER'
    ])->default('SENIORS');
    $table->string('age_category_name', 100);
    $table->enum('discipline', ['FOOTBALL', 'FUTSAL', 'BEACH_SOCCER'])->default('FOOTBALL');
    $table->enum('gender', ['MALE', 'FEMALE', 'MIXED'])->default('MALE');
    $table->enum('team_character', ['CLUB', 'NATIONAL', 'COMBINED', 'SCHOOL', 'OTHER'])->default('CLUB');
    $table->enum('match_type', ['OFFICIAL', 'FRIENDLY', 'QUALIFYING', 'TEST_MATCH', 'UNOFFICIAL'])->default('OFFICIAL');
    
    // Zeitliche Information
    $table->dateTime('date_from');
    $table->dateTime('date_to');
    $table->year('season');
    
    // Struktur & Format
    $table->enum('nature', ['ROUND_ROBIN', 'KNOCKOUT', 'GROUP_STAGE', 'SWISS_SYSTEM', 'OTHER'])->default('ROUND_ROBIN');
    $table->integer('number_of_participants')->default(0);
    $table->integer('order_number')->default(1);
    $table->tinyInteger('multiplier')->default(1);
    
    // Regelwerk
    $table->boolean('flying_substitutions')->default(false);
    $table->boolean('penalty_shootout')->default(false);
    $table->longText('ranking_notes')->nullable();
    
    // Assets
    $table->bigInteger('image_id')->nullable();
    
    // Status
    $table->enum('status', ['ACTIVE', 'INACTIVE', 'ARCHIVED', 'PENDING', 'CANCELLED'])->default('ACTIVE');
    
    // Audit
    $table->timestamps();
    $table->timestamp('synced_at')->nullable();
    $table->softDeletes();
    
    // Indizes
    $table->index('organisation_fifa_id');
    $table->index('season');
    $table->index('status');
    $table->index(['organisation_fifa_id', 'season']);
});
```

---

## 7. Laravel Model

### app/Models/Competition.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competition extends Model
{
    use SoftDeletes;

    protected $table = 'competitions';
    protected $primaryKey = 'competition_fifa_id';
    public $incrementing = false;
    protected $keyType = 'bigint';

    protected $fillable = [
        'competition_fifa_id',
        'organisation_fifa_id',
        'superior_competition_fifa_id',
        'competition_type_id',
        'international_name',
        'international_short_name',
        'competition_type',
        'age_category',
        'age_category_name',
        'discipline',
        'gender',
        'team_character',
        'match_type',
        'date_from',
        'date_to',
        'season',
        'nature',
        'number_of_participants',
        'order_number',
        'multiplier',
        'flying_substitutions',
        'penalty_shootout',
        'ranking_notes',
        'image_id',
        'status',
    ];

    protected $casts = [
        'date_from' => 'datetime',
        'date_to' => 'datetime',
        'flying_substitutions' => 'boolean',
        'penalty_shootout' => 'boolean',
        'multiplier' => 'integer',
        'number_of_participants' => 'integer',
        'season' => 'integer',
        'synced_at' => 'datetime',
    ];

    // ============================================
    // Relationships
    // ============================================

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_fifa_id', 'organisation_fifa_id');
    }

    public function superiorCompetition()
    {
        return $this->belongsTo(Competition::class, 'superior_competition_fifa_id', 'competition_fifa_id');
    }

    public function subCompetitions()
    {
        return $this->hasMany(Competition::class, 'superior_competition_fifa_id', 'competition_fifa_id');
    }

    public function teams()
    {
        return $this->hasMany(CompetitionTeam::class, 'competition_fifa_id', 'competition_fifa_id');
    }

    public function matches()
    {
        return $this->hasMany(Match::class, 'competition_fifa_id', 'competition_fifa_id');
    }

    public function rankings()
    {
        return $this->hasMany(Ranking::class, 'competition_fifa_id', 'competition_fifa_id');
    }

    // ============================================
    // Scopes
    // ============================================

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeByOrganisation($query, $organisationId)
    {
        return $query->where('organisation_fifa_id', $organisationId);
    }

    public function scopeBySeason($query, $season)
    {
        return $query->where('season', $season);
    }

    public function scopeByAgeCategory($query, $ageCategory)
    {
        return $query->where('age_category', $ageCategory);
    }

    public function scopeByDiscipline($query, $discipline)
    {
        return $query->where('discipline', $discipline);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeKnockout($query)
    {
        return $query->where('nature', 'KNOCKOUT');
    }

    public function scopeRoundRobin($query)
    {
        return $query->where('nature', 'ROUND_ROBIN');
    }

    public function scopeOfficial($query)
    {
        return $query->where('match_type', 'OFFICIAL');
    }

    public function scopeWithPenaltyShootout($query)
    {
        return $query->where('penalty_shootout', true);
    }

    // ============================================
    // Accessors / Mutators
    // ============================================

    public function getIsOngoingAttribute(): bool
    {
        return now()->between($this->date_from, $this->date_to);
    }

    public function getIsUpcomingAttribute(): bool
    {
        return now()->before($this->date_from);
    }

    public function getIsCompletedAttribute(): bool
    {
        return now()->after($this->date_to);
    }

    public function getIsCurrentSeasonAttribute(): bool
    {
        return $this->season === now()->year;
    }

    // ============================================
    // Methods
    // ============================================

    public function getDisplayName(?string $language = null): string
    {
        if ($language && method_exists($this, 'localNames')) {
            $localName = $this->localNames()
                ->where('language', $language)
                ->first();
            
            if ($localName) {
                return $localName->name;
            }
        }

        return $this->international_name;
    }

    public function getHierarchyPath(): string
    {
        $path = [$this->international_short_name];
        
        $parent = $this->superiorCompetition;
        while ($parent) {
            array_unshift($path, $parent->international_short_name);
            $parent = $parent->superiorCompetition;
        }
        
        return implode(' → ', $path);
    }

    public function calculateTeamPercentage(): int
    {
        $registered = $this->teams()->count();
        return (int) (($registered / $this->number_of_participants) * 100);
    }
}
```

---

## 8. Häufige Abfragen

### Abfrage 1: Alle aktiven Bundesligen

```php
$leagues = Competition::active()
    ->where('nature', 'ROUND_ROBIN')
    ->where('match_type', 'OFFICIAL')
    ->where('gender', 'MALE')
    ->whereNull('superior_competition_fifa_id')
    ->orderBy('season', 'desc')
    ->orderBy('order_number')
    ->get();
```

### Abfrage 2: Aktuelle Saison - Alle Wettbewerbe einer Organisation

```php
$currentSeason = now()->year;

$competitions = Competition::where('organisation_fifa_id', 39393)
    ->where('season', $currentSeason)
    ->active()
    ->orderBy('order_number')
    ->get();
```

### Abfrage 3: Turniere mit Gruppen (Group Stage + Knockouts)

```php
$tournaments = Competition::where('nature', 'GROUP_STAGE')
    ->orWhere('nature', 'KNOCKOUT')
    ->where('team_character', 'NATIONAL')
    ->active()
    ->get();

// Mit Gruppen
$groups = $tournaments->flatMap(function($tournament) {
    return $tournament->subCompetitions;
});
```

### Abfrage 4: U-21 Meisterschaften

```php
$u21Championships = Competition::where('age_category', 'U_21')
    ->where('match_type', 'OFFICIAL')
    ->orderBy('date_from', 'desc')
    ->get();
```

### Abfrage 5: Wettbewerbe mit Elfmeterschießen

```php
$withPenalties = Competition::where('penalty_shootout', true)
    ->where('nature', 'KNOCKOUT')
    ->active()
    ->get();
```

### Abfrage 6: Futsal Liga

```php
$futsal = Competition::where('discipline', 'FUTSAL')
    ->where('nature', 'ROUND_ROBIN')
    ->active()
    ->get();
```

### Abfrage 7: Laufende Wettbewerbe

```php
$ongoing = Competition::where('date_from', '<=', now())
    ->where('date_to', '>=', now())
    ->active()
    ->orderBy('order_number')
    ->get();
```

### Abfrage 8: Hierarchie einer Competition

```php
$competition = Competition::find(3936301); // U21-EURO Group A

// Übergeordnete Competition
$parentCompetition = $competition->superiorCompetition;

// Alle Gruppen (Schwester-Competitionen)
$sisters = Competition::where('superior_competition_fifa_id', 
    $competition->superior_competition_fifa_id)
    ->get();
```

---

## Zusammenfassung

| Feld | Typ | Erforderlich | Beispiel |
|------|-----|-------------|---------|
| competitionFifaId | BIGINT | ✅ | 3936145 |
| internationalName | STRING | ✅ | "Bundesliga" |
| organisationFifaId | BIGINT | ✅ | 39393 |
| season | INTEGER | ✅ | 2025 |
| dateFrom | DATETIME | ✅ | 2025-01-15T00:00:00 |
| dateTo | DATETIME | ✅ | 2025-11-30T23:59:59 |
| status | ENUM | ✅ | ACTIVE |
| ageCategory | ENUM | ✅ | SENIORS |
| discipline | ENUM | ✅ | FOOTBALL |
| gender | ENUM | ✅ | MALE |
| teamCharacter | ENUM | ✅ | CLUB |
| nature | ENUM | ✅ | ROUND_ROBIN |
| matchType | ENUM | ✅ | OFFICIAL |
| internationalShortName | STRING | ✅ | "BL" |
| numberOfParticipants | INTEGER | ✅ | 18 |
| orderNumber | INTEGER | ✅ | 1 |
| multiplier | INTEGER | ✅ | 1 |
| ageCategoryName | STRING | ✅ | "label.category.seniors" |
| flyingSubstitutions | BOOLEAN | ✅ | false |
| penaltyShootout | BOOLEAN | ✅ | true |
| competitionType | STRING | ✅ | "League" |
| superiorCompetitionFifaId | BIGINT | ❌ | null |
| imageId | BIGINT | ❌ | 3936909 |
| picture | OBJECT | ❌ | {...} |
| localNames | ARRAY | ❌ | [...] |
| rankingNotes | STRING | ❌ | "..." |
| competitionTypeId | BIGINT | ❌ | 1 |

---

**Letzte Aktualisierung**: October 23, 2025  
**Version**: 1.0  
**Status**: ✅ Production Ready  
**Schema Version**: FIFA Connect 2025
