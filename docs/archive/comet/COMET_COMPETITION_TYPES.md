# COMET - Competition Type & Competition Management

**Version**: 1.0  
**Datum**: October 23, 2025  
**Bereich**: System Administration & Competition Setup

---

## 📋 Inhaltsverzeichnis

1. [Competition Types - Grundlagen](#competition-types---grundlagen)
2. [Neue Competition Type erstellen](#neue-competition-type-erstellen)
3. [Competition Type Parameter](#competition-type-parameter)
4. [Neue Competition hinzufügen](#neue-competition-hinzufügen)
5. [Unoffizielle Matches](#unoffizielle-matches)
6. [Best Practices](#best-practices)
7. [Laravel Integration](#laravel-integration)

---

## 1. Competition Types - Grundlagen

### Was sind Competition Types?

**Competition Types** sind vordefinierte Konfigurationsvorlagen, die verwendet werden, um neue Wettbewerbe im System zu erstellen. Sie gruppieren Wettbewerbe mit denselben Einstellungen und gehören zum selben Rang.

### Zweck der Competition Types

✅ **Einheitliche Konfiguration** - Alle Wettbewerbe desselben Typs haben gleiche Einstellungen  
✅ **Effizienz** - Keine wiederholte Konfiguration für ähnliche Wettbewerbe  
✅ **Konsistenz** - Gleiche Regeln und Richtlinien für verwandte Wettbewerbe  
✅ **Verwaltbarkeit** - Zentrale Verwaltung von Wettbewerbseigenschaften  

### Wann man neue Competition Types benötigt

| Szenario | Competition Type |
|----------|-----------------|
| Deutsche Bundesliga | Bundesliga |
| Pokal-Turniere | DFB-Pokal, Regional-Pokal |
| Internationale Spiele | Freundschaftsspiele, Qualifikation |
| Jugendwettbewerbe | U21, U19, U17, U15 |
| Veteranen-Liga | Veteranen |
| Futsal/Beachsoccer | Futsal-Liga, Beachsoccer |

---

## 2. Neue Competition Type erstellen

### Schritt 1: Navigation zur Competition Type Verwaltung

```
COMET Admin Dashboard
└── Menü (Linke Navigationsbalk)
    └── Competitions
        └── New Competition Type (Oder: Competition Types)
```

### Schritt 2: Erforderliche Felder ausfüllen

Folgende Felder sind **MANDATORY** (erforderlich):

#### 2.1 Organization (Verband)
```
Auswahlfeld: Wähle die zuständige Organisation
Beispiele:
- DFB (Deutscher Fußball-Bund)
- UEFA (Europäischer Verband)
- FIFA (Weltverband)
- Landesverbände (Bayerischer FV, etc.)
```

#### 2.2 Name
```
Textfeld: Interne Bezeichnung (ohne Leerzeichen empfohlen)
Beispiele:
- BUNDESLIGA_2025
- DFB_POKAL
- REGIONAL_LIGA
- U21_CHAMPIONSHIP
```

#### 2.3 Display Name
```
Textfeld: Anzeigetext (für Benutzeroberfläche)
Beispiele:
- "Bundesliga 2025/2026"
- "DFB-Pokal 2025/2026"
- "Regionalliga Süd"
- "U21 Europameisterschaft"
```

#### 2.4 Team Type
```
Auswahlfeld (Dropdown):
Optionen:
- CLUB - Für Club-Wettbewerbe (Bundesliga, DFB-Pokal)
- NATIONAL_TEAM - Für Nationalteams (Länderspiele)
- REGIONAL_TEAM - Für Regional-Teams (Landesverbände)
```

**Auswahl-Matrix:**
| Wettbewerb | Team Type |
|-----------|-----------|
| Bundesliga | CLUB |
| Pokal | CLUB |
| Länderspiele | NATIONAL_TEAM |
| Qualifikation | CLUB oder NATIONAL_TEAM |
| Freundschaftsspiele | CLUB |

#### 2.5 Discipline (Spielart)
```
Auswahlfeld (Dropdown):
Optionen:
- FOOTBALL - 11er Fußball (Standard)
- FUTSAL - Hallenfußball
- BEACH_SOCCER - Beachsoccer
```

#### 2.6 Gender (Geschlecht)
```
Auswahlfeld (Dropdown):
Optionen:
- MALE - Herren
- FEMALE - Damen
- MIXED - Gemischte Teams
```

#### 2.7 Age Category (Altersgruppe)
```
Auswahlfeld (Dropdown):
Optionen (je nach Konfiguration):
- SENIORS - Erwachsene (Standard)
- U21 - Spieler unter 21 Jahren
- U19 - Spieler unter 19 Jahren
- U17 - Spieler unter 17 Jahren
- U15 - Spieler unter 15 Jahren
- U13 - Spieler unter 13 Jahren
- VETERANS - Veteranen (über 30)
- BEGINNERS - Anfänger
- YOUTH - Jugend (allgemein)
```

#### 2.8 Match Type (Spiel-Typ)
```
Auswahlfeld (Dropdown):
Optionen:
- OFFICIAL - Offizielle Spiele (Liga, Pokal, International)
- FRIENDLY - Freundschaftsspiele
- UNOFFICIAL - Unoffizielle Spiele (siehe Abschnitt 5)
```

**Unterschiede:**
| Match Type | Verwendung | Wertung |
|-----------|-----------|--------|
| OFFICIAL | Liga, Pokal, Qualifikation | Zählt für Ligatabelle |
| FRIENDLY | Testspiele, Vorbereitung | Zählt nicht |
| UNOFFICIAL | Clubs vs. National Teams | Keine FIFA-Anerkennung |

#### 2.9 Rank (Rangierung)
```
Textfeld oder Auswahlfeld:
Bedeutung: Hierarchische Einstufung des Wettbewerbs

Beispiele (von oben nach unten):
1 - International/Champions League
2 - Top-Liga (Bundesliga)
3 - Pokal
4 - Zweite Liga
5 - Regionalliga
10 - Veteranen-Liga
```

### Schritt 3: Konfiguration speichern

```
Button: 💾 SAVE

Bestätigung: "Competition Type successfully created"
Neue Competition Type steht nun zur Verfügung
```

---

## 3. Competition Type Parameter

### Vollständige Parameterliste

```
┌─────────────────────────────────────────────┐
│       COMPETITION TYPE CONFIGURATION        │
├─────────────────────────────────────────────┤
│                                             │
│ Organization:        [▼ DFB]               │
│ Name:               [BUNDESLIGA_2025    ]   │
│ Display Name:       [Bundesliga 2025/26]   │
│ Team Type:          [▼ CLUB]                │
│ Discipline:         [▼ FOOTBALL]            │
│ Gender:             [▼ MALE]                │
│ Age Category:       [▼ SENIORS]             │
│ Match Type:         [▼ OFFICIAL]            │
│ Rank:               [2                 ]   │
│                                             │
│ Additional Settings:                        │
│ ├─ Number of Teams:  [18]                  │
│ ├─ Matches per Team: [34]                  │
│ ├─ Home & Away:      [✓]                   │
│ ├─ Playoff Format:   [None/KO/Group]       │
│ ├─ Season Duration:  [08/2025 - 05/2026]  │
│ └─ Tier Level:       [1 - Highest]         │
│                                             │
│ [💾 SAVE]  [Cancel]                        │
└─────────────────────────────────────────────┘
```

### Beispiel-Konfigurationen

#### Beispiel 1: Bundesliga
```
Organization:      DFB
Name:              BUNDESLIGA_2025
Display Name:      Bundesliga 2025/2026
Team Type:         CLUB
Discipline:        FOOTBALL
Gender:            MALE
Age Category:      SENIORS
Match Type:        OFFICIAL
Rank:              2
Home & Away:       ✓ Aktiviert
Number of Teams:   18
Matches per Team:  34
```

#### Beispiel 2: DFB-Pokal
```
Organization:      DFB
Name:              DFB_POKAL_2025
Display Name:      DFB-Pokal 2025/2026
Team Type:         CLUB
Discipline:        FOOTBALL
Gender:            MALE
Age Category:      SENIORS
Match Type:        OFFICIAL
Rank:              3
Format:            Knockout (KO)
Number of Teams:   64
```

#### Beispiel 3: Länderspiele
```
Organization:      UEFA
Name:              INTERNATIONALS_2025
Display Name:      Internationale Freundschaftsspiele
Team Type:         NATIONAL_TEAM
Discipline:        FOOTBALL
Gender:            MALE
Age Category:      SENIORS
Match Type:        FRIENDLY
Rank:              1
```

#### Beispiel 4: U21 Europameisterschaft
```
Organization:      UEFA
Name:              U21_EUROCHA_2025
Display Name:      U21 Europameisterschaft 2025
Team Type:         NATIONAL_TEAM
Discipline:        FOOTBALL
Gender:            MALE
Age Category:      U21
Match Type:        OFFICIAL
Rank:              2
Format:            Group + Knockout
```

#### Beispiel 5: Unoffizielle Matches
```
Organization:      DFB
Name:              FRIENDLY_CLUB_VS_NATIONAL
Display Name:      Clubs vs. National Teams
Team Type:         MIXED (CLUB & NATIONAL_TEAM)
Discipline:        FOOTBALL
Gender:            MALE
Age Category:      SENIORS
Match Type:        UNOFFICIAL
Rank:              5
Registration:      INVITED_TEAMS
```

---

## 4. Neue Competition hinzufügen

### Option 1: Direkt auf Competition Type Screen

```
Auf dem Competition Type Creation Screen:
Nach dem Speichern der Competition Type:
1. Oben auf "New competition" klicken
2. Competition Type wird automatisch vorausgewählt
3. Spezifische Konkurrenz-Details eingeben
```

### Option 2: Vom Hauptmenü

```
COMET Admin Dashboard
└── Menü (Linke Navigationsbalk)
    └── Competitions
        └── New Competition
```

### Competition Details ausfüllen

```
┌────────────────────────────────────────────┐
│       CREATE NEW COMPETITION               │
├────────────────────────────────────────────┤
│                                            │
│ Competition Type: [▼ Bundesliga 2025]      │
│ Season:           [2025/2026           ]   │
│ Start Date:       [15.08.2025          ]   │
│ End Date:         [30.05.2026          ]   │
│ Number of Teams:  [18                  ]   │
│ Number of Rounds: [34                  ]   │
│ Match Format:     [▼ Home & Away]          │
│ Registration:     [▼ Manual/Auto]          │
│                                            │
│ Participating Teams:                       │
│ ├─ FC Bayern München                       │
│ ├─ Borussia Dortmund                       │
│ ├─ RB Leipzig                              │
│ └─ ... (weitere 15 Teams)                 │
│                                            │
│ [💾 SAVE]  [Cancel]                       │
└────────────────────────────────────────────┘
```

---

## 5. Unoffizielle Matches

### Wann verwendet man Unoffizielle Matches?

Unoffizielle Matches sind Spiele zwischen:
- **Clubs** ↔️ **National Teams** (z.B. FCB vs. DFB-Team)
- Diese Spiele sind **nicht FIFA-anerkannt** für offizielle Rekorde
- Bieten aber spannende Testmöglichkeiten

### Beispiele für Unoffizielle Matches

```
Offizielle Spiele:        Unoffizielle Spiele:
- Bundesliga              - FC Bayern vs. U-21 National Team
- DFB-Pokal               - RB Leipzig vs. Nationalmannschaft
- Internationale          - Borussia Dortmund vs. Frauen-National
- Qualifikationen         - Club Testmatch
```

### Schritt-für-Schritt: Unoffizielle Wettbewerb erstellen

#### Schritt 1: Competition Type mit Unofficial Match Type

```
1. Navigiere zu: Competitions → New Competition Type
2. Fülle die Felder aus:

Organization:      [▼ DFB]
Name:              [CLUB_VS_NATIONAL]
Display Name:      [Clubs vs. National Teams]
Team Type:         [▼ CLUB]
Discipline:        [▼ FOOTBALL]
Gender:            [▼ MALE]
Age Category:      [▼ SENIORS]
Match Type:        [▼ UNOFFICIAL]  ← WICHTIG!
Rank:              [5]

3. [💾 SAVE] drücken
```

#### Schritt 2: Neue Competition erstellen

```
1. Hauptmenü: Competitions → New Competition
2. Wähle die neu erstellte "Clubs vs. National Teams" Type
3. Spezifische Details:

Season:            [2025/2026]
Start Date:        [01.01.2025]
End Date:          [31.12.2025]
Registration:      [▼ INVITED_TEAMS]  ← WICHTIG!

4. [💾 SAVE]
```

#### Schritt 3: Registration Parameter setzen

```
Im Competition Settings Screen:

Competition → Settings → Registration
Auswahlfeld: [▼ INVITED_TEAMS]

Dies erlaubt die manuelle Auswahl von Clubs
statt automatischer Registrierung
```

#### Schritt 4: Matches Tab - Neue Matches hinzufügen

```
1. Im Competition Screen auf "Matches" Tab klicken
2. Abschnitt "Arrange Matches" erweitern

┌────────────────────────────────────┐
│ Arrange Matches                    │
├────────────────────────────────────┤
│                                    │
│ [+ Add new Match]                  │
│                                    │
└────────────────────────────────────┘
```

#### Schritt 5: Home Team (Club) auswählen

```
Nach "Add new Match" klick:

Feld: Home Team
      [🔍]  [Suchfeld eingeben]

Beispiel: FC Bayern München eingeben
Ergebnis: FC Bayern München (CLUB)
          wählen
```

#### Schritt 6: Away Team (National Team) auswählen

```
Feld: Away Team
      [🔍]  [Suchfeld eingeben]

Beispiel: "U-21 National Team" eingeben
Ergebnis: DFB U-21 National Team
          wählen
```

#### Schritt 7: Match-Details konfigurieren

```
Match Details:

Date:        [25.05.2025          ]
Time:        [19:30               ]
Location:    [Allianz Arena        ]
Referee:     [▼ Felix Brych        ]
Status:      [▼ SCHEDULED]

Match Type:  [UNOFFICIAL]  (automatisch vorbelegt)
```

#### Schritt 8: Änderungen speichern

```
Button: [💾 SAVE]

Bestätigung: "Match successfully created"
Das unoffizielle Match ist jetzt im System
```

### Ergebnis nach Erstellung

```
Competition Overview:
├─ Competition Name: "Clubs vs. National Teams"
├─ Season: 2025/2026
├─ Match Type: UNOFFICIAL
├─ Registration: INVITED_TEAMS
│
└─ Scheduled Matches:
   ├─ FC Bayern München (Home) vs. U-21 National Team (Away)
   │  Date: 25.05.2025 19:30 - Allianz Arena
   │  Status: SCHEDULED
   │
   ├─ Borussia Dortmund (Home) vs. Women National Team (Away)
   │  Date: 27.05.2025 19:00 - Signal Iduna Park
   │  Status: SCHEDULED
   │
   └─ RB Leipzig (Home) vs. U-20 National Team (Away)
      Date: 29.05.2025 20:00 - Red Bull Arena
      Status: SCHEDULED
```

---

## 6. Best Practices

### ✅ Best Practices für Competition Type Management

1. **Aussagekräftige Namen verwenden**
   ```
   ✅ GUT:  BUNDESLIGA_2025, U21_EUROPAMEISTERSCHAFT
   ❌ FALSCH: COMPETITION_1, LEAGUE_TYPE
   ```

2. **Hierarchie durch Rank bewahren**
   ```
   Rank 1: Internationale Top-Wettbewerbe
   Rank 2: Nationale Ligen
   Rank 3: Pokal-Wettbewerbe
   Rank 4-5: Unterklassen, Testspiele
   ```

3. **Konsistent bleiben**
   ```
   ✅ Alle Bundesliga-Seasons verwenden gleiche Type
   ✅ Alle Pokal-Wettbewerbe verwenden gleiche Type
   ❌ Nicht für jedes Jahr neue Type erstellen
   ```

4. **Discipline richtig zuordnen**
   ```
   FOOTBALL:     11er Fußball (Standard)
   FUTSAL:       5er Hallenfußball
   BEACH_SOCCER: Beach-Variante
   ```

5. **Age Category präzise wählen**
   ```
   ✅ U21, U19, U17 für präzise Altersgruppen
   ❌ Nicht "YOUTH" verwenden wenn U19 passt
   ```

6. **Registration Parameter korrekt setzen**
   ```
   ✅ INVITED_TEAMS: Für Unoffizielle Matches
   ✅ AUTO: Für automatische Registrierung
   ✅ MANUAL: Für manuelle Verwaltung
   ```

### ❌ Häufige Fehler vermeiden

1. **Falscher Match Type**
   ```
   ❌ OFFICIAL für Freundschaftsspiele
   ✅ FRIENDLY für Testspiele
   ```

2. **Verwirrte Team Types**
   ```
   ❌ NATIONAL_TEAM für Clubs wählen
   ✅ CLUB für Club-Wettbewerbe
   ✅ NATIONAL_TEAM für Länderspiele
   ```

3. **Unoffizielle Matches falsch konfigurieren**
   ```
   ❌ Match Type = OFFICIAL + Club vs. National Team
   ✅ Match Type = UNOFFICIAL + INVITED_TEAMS Registration
   ```

4. **Keine Dokumentation**
   ```
   ❌ Competition Type ohne beschreibende Namen
   ✅ Display Name sollte selbsterklärend sein
   ```

---

## 7. Laravel Integration

### 7.1 Database Schema für Competition Types

```php
// database/migrations/create_competition_types_table.php

Schema::create('competition_types', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('organization_id');
    $table->string('name')->unique();
    $table->string('display_name');
    $table->enum('team_type', ['CLUB', 'NATIONAL_TEAM', 'REGIONAL_TEAM']);
    $table->enum('discipline', ['FOOTBALL', 'FUTSAL', 'BEACH_SOCCER']);
    $table->enum('gender', ['MALE', 'FEMALE', 'MIXED']);
    $table->enum('age_category', ['SENIORS', 'U21', 'U19', 'U17', 'U15', 'U13', 'VETERANS', 'BEGINNERS', 'YOUTH']);
    $table->enum('match_type', ['OFFICIAL', 'FRIENDLY', 'UNOFFICIAL']);
    $table->integer('rank');
    $table->boolean('home_and_away')->default(true);
    $table->integer('number_of_teams')->nullable();
    $table->integer('matches_per_team')->nullable();
    $table->enum('format', ['ROUND_ROBIN', 'KNOCKOUT', 'GROUP_KNOCKOUT'])->default('ROUND_ROBIN');
    $table->timestamps();
    
    $table->foreign('organization_id')->references('id')->on('organizations');
    $table->index('rank');
});
```

### 7.2 Model für Competition Type

```php
// app/Models/CompetitionType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CompetitionType extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'display_name',
        'team_type',
        'discipline',
        'gender',
        'age_category',
        'match_type',
        'rank',
        'home_and_away',
        'number_of_teams',
        'matches_per_team',
        'format',
    ];

    // Constants
    const TEAM_TYPES = ['CLUB', 'NATIONAL_TEAM', 'REGIONAL_TEAM'];
    const DISCIPLINES = ['FOOTBALL', 'FUTSAL', 'BEACH_SOCCER'];
    const GENDERS = ['MALE', 'FEMALE', 'MIXED'];
    const AGE_CATEGORIES = ['SENIORS', 'U21', 'U19', 'U17', 'U15', 'U13', 'VETERANS', 'BEGINNERS', 'YOUTH'];
    const MATCH_TYPES = ['OFFICIAL', 'FRIENDLY', 'UNOFFICIAL'];
    const FORMATS = ['ROUND_ROBIN', 'KNOCKOUT', 'GROUP_KNOCKOUT'];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function competitions()
    {
        return $this->hasMany(Competition::class);
    }

    // Scopes
    public function scopeOfficial($query)
    {
        return $query->where('match_type', 'OFFICIAL');
    }

    public function scopeFriendly($query)
    {
        return $query->where('match_type', 'FRIENDLY');
    }

    public function scopeUnofficial($query)
    {
        return $query->where('match_type', 'UNOFFICIAL');
    }

    public function scopeByTeamType($query, $teamType)
    {
        return $query->where('team_type', $teamType);
    }

    public function scopeByAgeCategory($query, $ageCategory)
    {
        return $query->where('age_category', $ageCategory);
    }

    public function scopeByDiscipline($query, $discipline)
    {
        return $query->where('discipline', $discipline);
    }

    public function scopeHighestRanked($query)
    {
        return $query->orderBy('rank')->limit(10);
    }

    // Accessors
    public function isOfficial(): bool
    {
        return $this->match_type === 'OFFICIAL';
    }

    public function isFriendly(): bool
    {
        return $this->match_type === 'FRIENDLY';
    }

    public function isUnofficial(): bool
    {
        return $this->match_type === 'UNOFFICIAL';
    }

    public function requiresInvitedTeams(): bool
    {
        return $this->match_type === 'UNOFFICIAL';
    }
}
```

### 7.3 Filament Resource für Competition Type

```php
// app/Filament/Resources/CompetitionTypeResource.php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use App\Models\CompetitionType;

class CompetitionTypeResource extends Resource
{
    protected static ?string $model = CompetitionType::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Competition Management';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->required()
                            ->label('Organization'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('BUNDESLIGA_2025')
                            ->hint('Internal name without spaces'),

                        Forms\Components\TextInput::make('display_name')
                            ->required()
                            ->placeholder('Bundesliga 2025/2026')
                            ->hint('Display name for users'),
                    ])->columns(2),

                Forms\Components\Section::make('Competition Settings')
                    ->schema([
                        Forms\Components\Select::make('team_type')
                            ->options([
                                'CLUB' => 'Club',
                                'NATIONAL_TEAM' => 'National Team',
                                'REGIONAL_TEAM' => 'Regional Team',
                            ])
                            ->required(),

                        Forms\Components\Select::make('discipline')
                            ->options([
                                'FOOTBALL' => 'Football (11v11)',
                                'FUTSAL' => 'Futsal (5v5)',
                                'BEACH_SOCCER' => 'Beach Soccer',
                            ])
                            ->required()
                            ->default('FOOTBALL'),

                        Forms\Components\Select::make('gender')
                            ->options([
                                'MALE' => 'Male',
                                'FEMALE' => 'Female',
                                'MIXED' => 'Mixed',
                            ])
                            ->required(),

                        Forms\Components\Select::make('age_category')
                            ->options([
                                'SENIORS' => 'Seniors',
                                'U21' => 'U21',
                                'U19' => 'U19',
                                'U17' => 'U17',
                                'U15' => 'U15',
                                'U13' => 'U13',
                                'VETERANS' => 'Veterans',
                                'BEGINNERS' => 'Beginners',
                                'YOUTH' => 'Youth',
                            ])
                            ->required()
                            ->default('SENIORS'),

                        Forms\Components\Select::make('match_type')
                            ->options([
                                'OFFICIAL' => 'Official',
                                'FRIENDLY' => 'Friendly',
                                'UNOFFICIAL' => 'Unofficial',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('rank')
                            ->options(array_combine(range(1, 20), range(1, 20)))
                            ->required()
                            ->helperText('1 = Highest, 20 = Lowest'),
                    ])->columns(2),

                Forms\Components\Section::make('Format Settings')
                    ->schema([
                        Forms\Components\Toggle::make('home_and_away')
                            ->default(true)
                            ->label('Home & Away Format'),

                        Forms\Components\TextInput::make('number_of_teams')
                            ->numeric()
                            ->label('Number of Teams')
                            ->helperText('Total teams in competition'),

                        Forms\Components\TextInput::make('matches_per_team')
                            ->numeric()
                            ->label('Matches per Team'),

                        Forms\Components\Select::make('format')
                            ->options([
                                'ROUND_ROBIN' => 'Round Robin',
                                'KNOCKOUT' => 'Knockout',
                                'GROUP_KNOCKOUT' => 'Group + Knockout',
                            ])
                            ->default('ROUND_ROBIN'),
                    ])->columns(2),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('display_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('team_type')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'CLUB' => 'blue',
                        'NATIONAL_TEAM' => 'red',
                        'REGIONAL_TEAM' => 'yellow',
                    }),

                Tables\Columns\TextColumn::make('match_type')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'OFFICIAL' => 'green',
                        'FRIENDLY' => 'yellow',
                        'UNOFFICIAL' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('age_category')
                    ->label('Age Category'),

                Tables\Columns\TextColumn::make('rank')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('match_type')
                    ->options([
                        'OFFICIAL' => 'Official',
                        'FRIENDLY' => 'Friendly',
                        'UNOFFICIAL' => 'Unofficial',
                    ]),

                Tables\Filters\SelectFilter::make('team_type')
                    ->options([
                        'CLUB' => 'Club',
                        'NATIONAL_TEAM' => 'National Team',
                        'REGIONAL_TEAM' => 'Regional Team',
                    ]),
            ]);
    }
}
```

### 7.4 Service für Competition Type Management

```php
// app/Services/CompetitionTypeService.php

namespace App\Services;

use App\Models\CompetitionType;
use Illuminate\Database\Eloquent\Collection;

class CompetitionTypeService
{
    /**
     * Erstelle neue Competition Type
     */
    public function create(array $data): CompetitionType
    {
        return CompetitionType::create($data);
    }

    /**
     * Hole alle verfügbaren Competition Types
     */
    public function getAllTypes(): Collection
    {
        return CompetitionType::orderBy('rank')->get();
    }

    /**
     * Hole Official Competition Types
     */
    public function getOfficialTypes(): Collection
    {
        return CompetitionType::official()->get();
    }

    /**
     * Hole Friendly Competition Types
     */
    public function getFriendlyTypes(): Collection
    {
        return CompetitionType::friendly()->get();
    }

    /**
     * Hole Unofficial Competition Types (für Club vs. National)
     */
    public function getUnofficialTypes(): Collection
    {
        return CompetitionType::unofficial()->get();
    }

    /**
     * Filtriere nach Team Type
     */
    public function getByTeamType(string $teamType): Collection
    {
        return CompetitionType::byTeamType($teamType)->get();
    }

    /**
     * Filtriere nach Age Category
     */
    public function getByAgeCategory(string $ageCategory): Collection
    {
        return CompetitionType::byAgeCategory($ageCategory)->get();
    }

    /**
     * Hole Top-ranked Types
     */
    public function getHighestRanked(int $limit = 5): Collection
    {
        return CompetitionType::highestRanked($limit)->get();
    }

    /**
     * Prüfe ob Type Invited Teams benötigt
     */
    public function requiresInvitedTeams(CompetitionType $type): bool
    {
        return $type->isUnofficial();
    }

    /**
     * Validiere Kombinationen
     */
    public function validateCombination(array $data): bool
    {
        // Unoffizielle Matches sollten nicht OFFICIAL sein
        if ($data['match_type'] === 'UNOFFICIAL' && 
            $data['team_type'] !== 'MIXED') {
            return false;
        }

        return true;
    }
}
```

### 7.5 API Endpoint für Competition Types

```php
// routes/api.php

Route::apiResource('competition-types', CompetitionTypeController::class);

// app/Http/Controllers/Api/CompetitionTypeController.php

namespace App\Http\Controllers\Api;

use App\Models\CompetitionType;
use App\Services\CompetitionTypeService;
use Illuminate\Http\Request;

class CompetitionTypeController
{
    public function __construct(private CompetitionTypeService $service) {}

    /**
     * GET /api/competition-types
     */
    public function index(Request $request)
    {
        $types = CompetitionType::query();

        if ($request->has('team_type')) {
            $types->byTeamType($request->team_type);
        }

        if ($request->has('match_type')) {
            $types->where('match_type', $request->match_type);
        }

        if ($request->has('age_category')) {
            $types->byAgeCategory($request->age_category);
        }

        return $types->orderBy('rank')->paginate();
    }

    /**
     * POST /api/competition-types
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => 'required|uuid|exists:organizations,id',
            'name' => 'required|string|unique:competition_types',
            'display_name' => 'required|string',
            'team_type' => 'required|in:' . implode(',', CompetitionType::TEAM_TYPES),
            'discipline' => 'required|in:' . implode(',', CompetitionType::DISCIPLINES),
            'gender' => 'required|in:' . implode(',', CompetitionType::GENDERS),
            'age_category' => 'required|in:' . implode(',', CompetitionType::AGE_CATEGORIES),
            'match_type' => 'required|in:' . implode(',', CompetitionType::MATCH_TYPES),
            'rank' => 'required|integer|min:1|max:20',
        ]);

        return $this->service->create($validated);
    }

    /**
     * GET /api/competition-types/{id}
     */
    public function show(CompetitionType $competitionType)
    {
        return $competitionType;
    }
}
```

---

## Checkliste: Competition Type Setup

```
✓ Organisation ausgewählt
✓ Name eindeutig und aussagekräftig
✓ Display Name benutzerfreundlich
✓ Team Type korrekt (CLUB/NATIONAL_TEAM/REGIONAL_TEAM)
✓ Discipline passend (FOOTBALL/FUTSAL/BEACH_SOCCER)
✓ Gender passend (MALE/FEMALE/MIXED)
✓ Age Category spezifisch
✓ Match Type passend (OFFICIAL/FRIENDLY/UNOFFICIAL)
✓ Rank hierarchisch korrekt (1-20)
✓ Zusätzliche Settings konfiguriert
✓ Formation bestätigt
✓ Änderungen gespeichert
✓ Neue Wettbewerbe hinzufügen bereit
```

---

**Letzte Aktualisierung**: October 23, 2025  
**Version**: 1.0  
**Status**: ✅ Production Ready  
**Zielgruppe**: System Administrators, Competition Managers, Developers
