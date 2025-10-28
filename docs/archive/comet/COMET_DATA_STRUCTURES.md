# COMET Data Structures - Vollständige Referenz

**Version**: 1.0  
**Datum**: October 23, 2025  
**Standard**: FIFA Connect  
**Bereich**: API Response Schemas, Datentypen, Strukturen  

---

## 📋 Inhaltsverzeichnis

1. [Error Handling Structures](#error-handling-structures)
2. [Person Structures](#person-structures)
3. [Team Officer Structures](#team-officer-structures)
4. [Competition Structures](#competition-structures)
5. [Facility Structures](#facility-structures)
6. [Match Structures](#match-structures)
7. [Match Event Structures](#match-event-structures)
8. [Match Official Structures](#match-official-structures)
9. [Match Player Structures](#match-player-structures)
10. [Case & Disciplinary Structures](#case--disciplinary-structures)
11. [Statistics Structures](#statistics-structures)
12. [Organisation Structures](#organisation-structures)

---

## 1. Error Handling Structures

### ErrorMessage Schema

**Beschreibung**: Einzelne Fehlermeldung mit Severity und zusätzlichen Informationen.

```json
{
  "message": "Invalid competition id provided",
  "severity": "error",
  "type": 1,
  "additionalInfo": {
    "parameter": "competitionFifaId",
    "expectedFormat": "integer",
    "receivedValue": "invalid_string"
  }
}
```

**Felder**:

| Feld | Typ | Erforderlich | Beschreibung |
|------|-----|-------------|-------------|
| message | STRING | ✅ | Aussagekräftige Fehlermeldung |
| severity | ENUM | ✅ | info, warn, error |
| type | BIGINT | ❌ | Fehler-Code (1=Refresh, 2=Deactivated, 100=Duplicate) |
| additionalInfo | OBJECT | ❌ | Zusätzliche Debug-Informationen |

**Severity Werte**:
```
"info"   - Informativ (z.B. Rate Limit Warning)
"warn"   - Warnung (z.B. Daten könnten veraltet sein)
"error"  - Fehler (Request fehlgeschlagen)
```

**Beispiele**:
```json
// Info
{
  "message": "Rate limit approaching: 950/1000 requests used",
  "severity": "info"
}

// Warning
{
  "message": "Competition data not updated in 24 hours",
  "severity": "warn",
  "type": 3
}

// Error
{
  "message": "Authentication failed",
  "severity": "error",
  "type": 401
}
```

---

### ExceptionViewModel Schema

**Beschreibung**: Container für mehrere Fehlermeldungen in Error-Responses (4xx Codes).

```json
{
  "messages": [
    {
      "message": "Invalid competition id",
      "severity": "error",
      "type": 1
    },
    {
      "message": "Authentication token expired",
      "severity": "error",
      "type": 401
    }
  ],
  "message": "One or more errors occurred"
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| messages | ARRAY[ErrorMessage] | Array von einzelnen Fehlern |
| message | STRING | Zusammenfassung (veraltet, messages verwenden) |

**HTTP Response Beispiel**:
```http
HTTP/1.1 400 Bad Request
Content-Type: application/json

{
  "messages": [
    {
      "message": "Parameter 'status' is required",
      "severity": "error"
    }
  ]
}
```

**Handling in Laravel**:
```php
try {
    $response = Http::withBasicAuth($user, $pass)
        ->get('https://api-dfb.analyticom.de/api/export/comet/competitions');
    
    if ($response->failed()) {
        $errors = $response->json('messages', []);
        foreach ($errors as $error) {
            Log::error('COMET API Error', $error);
        }
    }
} catch (\Exception $e) {
    Log::error('COMET Connection Error', ['error' => $e->getMessage()]);
}
```

---

### ThrottlingInfo Schema

**Beschreibung**: Aktuelle Rate-Limit Informationen für API-Zugriff.

```json
{
  "requestsPerSecond": 100,
  "endPointRequestsPerSecond": {
    "/api/export/comet/images": 50,
    "/api/export/comet/competitions": 100,
    "/api/export/comet/match/*/events": 200
  }
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| requestsPerSecond | DOUBLE | Standard Rate Limit (Requests pro Sekunde) |
| endPointRequestsPerSecond | OBJECT | Spezifische Limits pro Endpoint |

**Interpretation**:
```
requestsPerSecond: 100
  → Max. 100 Requests pro Sekunde über alle Endpoints
  → Pro Minute: 6.000 Requests

endPointRequestsPerSecond["/api/export/comet/images"]: 50
  → Images Endpoint: max. 50 Req/s (Bilder sind groß)
  → Andere Endpoints: bis zu 100 Req/s
```

**Implementierung in Laravel**:
```php
class CometThrottleService {
    private $throttling;
    private $requestsThisSecond = 0;
    private $lastSecond = 0;

    public function __construct() {
        $this->throttling = Cache::remember('comet.throttling', 3600, function() {
            return Http::withBasicAuth(config('comet.user'), config('comet.pass'))
                ->get('https://api-dfb.analyticom.de/api/export/comet/throttling/info')
                ->json();
        });
    }

    public function canMakeRequest($endpoint): bool {
        $now = microtime(true);
        $second = intval($now);
        
        // Reset pro Sekunde
        if ($second !== $this->lastSecond) {
            $this->requestsThisSecond = 0;
            $this->lastSecond = $second;
        }

        $limit = $this->throttling['endPointRequestsPerSecond'][$endpoint] 
            ?? $this->throttling['requestsPerSecond'];
        
        return ++$this->requestsThisSecond <= $limit;
    }
}
```

---

## 2. Person Structures

### LocalPersonName Schema

**Beschreibung**: Mehrsprachiger Name einer Person.

```json
{
  "personFifaId": 240607,
  "firstName": "Manuel",
  "lastName": "Neuer",
  "popularName": "Manuel",
  "language": "GER",
  "birthName": null,
  "title": null
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| personFifaId | BIGINT | FIFA Person ID |
| firstName | STRING | Vorname in Lokalsprache |
| lastName | STRING | Nachname in Lokalsprache |
| popularName | STRING | Künstlername/Spitzname |
| language | STRING | ISO 639-1 Code (GER, SPA, FRA, etc.) |
| birthName | STRING | Geburtsname (wenn anders) |
| title | STRING | Titel (Prof, Dr., etc.) |

**Beispiele**:
```json
[
  {
    "personFifaId": 240607,
    "firstName": "Manuel",
    "lastName": "Neuer",
    "language": "GER",
    "popularName": "Manuel"
  },
  {
    "personFifaId": 240607,
    "firstName": "Manuel",
    "lastName": "Neur",
    "language": "ENG",
    "popularName": "Manuel"
  }
]
```

---

### PersonExport Schema (FULL)

**Beschreibung**: Vollständige Person/Spieler Informationen aus COMET.

```json
{
  "personFifaId": 240607,
  "internationalFirstName": "Manuel",
  "internationalLastName": "Neuer",
  "gender": "MALE",
  "nationality": "DE",
  "nationalityFIFA": "GER",
  "dateOfBirth": "1986-03-27T00:00:00",
  "countryOfBirth": "DE",
  "countryOfBirthFIFA": "GER",
  "regionOfBirth": "North Rhine-Westphalia",
  "placeOfBirth": "Gelsenkirchen",
  "place": "Munich",
  "playerPosition": "Goalkeeper",
  "rowNumber": 1,
  "homegrown": 1,
  "refNumber1": "NE-001",
  "refNumber2": null,
  "nationalID": "49-12345678",
  "passportNumber": "98765432",
  "national_team": "Germany",
  "localPersonNames": [
    {
      "personFifaId": 240607,
      "firstName": "Manuel",
      "lastName": "Neuer",
      "language": "GER"
    }
  ]
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| personFifaId | BIGINT | Eindeutige FIFA Person ID |
| internationalFirstName | STRING | Englischer Vorname |
| internationalLastName | STRING | Englischer Nachname |
| gender | ENUM | MALE, FEMALE, OTHER |
| nationality | STRING | ISO 3166-1 alpha-2 (DE, ES, FR) |
| nationalityFIFA | STRING | FIFA Code (GER, ESP, FRA) |
| dateOfBirth | DATETIME | Geburtsdatum |
| countryOfBirth | STRING | Geburt Land ISO Code |
| countryOfBirthFIFA | STRING | Geburt Land FIFA Code |
| regionOfBirth | STRING | Geburt Region/Bundesland |
| placeOfBirth | STRING | Geburt Stadt/Ort |
| place | STRING | Aktueller Wohnort |
| playerPosition | STRING | Position (Goalkeeper, Defender, etc.) |
| rowNumber | INTEGER | Trikotnummer (Standard) |
| homegrown | INTEGER | 0/1 Homegrown Status |
| refNumber1 | STRING | Referenznummer 1 (z.B. DFB-ID) |
| refNumber2 | STRING | Referenznummer 2 |
| nationalID | STRING | Nationale ID / Personalausweis |
| passportNumber | STRING | Reisepass |
| national_team | STRING | Nationalteam Name |
| localPersonNames | ARRAY[LocalPersonName] | Namen in verschiedenen Sprachen |

**Mapping zu Laravel**:
```php
// app/Models/Player.php
class Player extends Model {
    protected $fillable = [
        'person_fifa_id',
        'international_first_name',
        'international_last_name',
        'gender',
        'nationality',
        'nationality_fifa',
        'date_of_birth',
        'country_of_birth',
        'region_of_birth',
        'place_of_birth',
        'player_position',
        'row_number',
        'homegrown',
        'ref_number1',
        'ref_number2',
        'national_id',
        'passport_number',
        'national_team'
    ];

    public function localNames() {
        return $this->hasMany(PlayerLocalName::class, 'person_fifa_id', 'person_fifa_id');
    }

    public function getFullNameAttribute() {
        return "{$this->international_first_name} {$this->international_last_name}";
    }

    public function getLocalizedNameAttribute($language = 'GER') {
        $localName = $this->localNames()->where('language', $language)->first();
        return $localName?->full_name ?? $this->full_name;
    }
}
```

---

### PersonBasic Schema

**Beschreibung**: Vereinfachte Person Information (ohne localNames in Array).

Identisch mit PersonExport, aber `localPersonNames` enthält einzelne LocalPersonNameBasic Objekte statt Array.

---

## 3. Team Officer Structures

### TeamOfficialRegistration Schema

**Beschreibung**: Team Official (Trainer, Assistent, etc.) bei einem Team.

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
    "internationalLastName": "Tuchel",
    ...
  }
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| role | ENUM | COACH, ASSISTANT_COACH, GOALKEEPER_COACH, PHYSICAL_TRAINER, TEAM_DOCTOR, PHYSIOTHERAPIST, DIRECTOR |
| cometRoleName | STRING | Label-Schlüssel (z.B. "label.headCoach") |
| cometRoleNameKey | STRING | Übersetzungsschlüssel |
| personFifaId | BIGINT | FIFA Person ID |
| teamId | BIGINT | Team/Club FIFA ID |
| status | ENUM | ACTIVE, INACTIVE, SUSPENDED |
| person | OBJECT | PersonExport Details |

**Role Enums**:
```
COACH                  - Trainer / Headcoach
ASSISTANT_COACH        - Assistenztrainer
GOALKEEPER_COACH       - Torwart-Trainer
PHYSICAL_TRAINER       - Athletik-Trainer
TEAM_DOCTOR            - Team-Arzt
PHYSIOTHERAPIST        - Physiotherapeut
DIRECTOR               - Geschäftsführer / Manager
```

---

## 4. Competition Structures

### LocalName Schema

**Beschreibung**: Mehrsprachige Bezeichnung für Competition, Facility, Organisation.

```json
{
  "name": "Bundesliga",
  "shortName": "BL",
  "placeName": "Germany",
  "regionName": "Bavaria",
  "language": "GER",
  "organisationFifaId": 39393,
  "competitionFifaId": 3936145,
  "facilityFifaId": null
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| name | STRING | Vollständiger Name in Lokalsprache |
| shortName | STRING | Kurzbezeichnung |
| placeName | STRING | Ort / Region Name |
| regionName | STRING | Regionales Gebiet |
| language | STRING | ISO 639-1 Code |
| organisationFifaId | BIGINT | Organisation (wenn applicable) |
| competitionFifaId | BIGINT | Competition (wenn applicable) |
| facilityFifaId | BIGINT | Facility/Stadion (wenn applicable) |

---

### Picture Schema

**Beschreibung**: Bild/Grafik Daten (Base64 encoded).

```json
{
  "contentType": "image/jpeg",
  "pictureLink": "/Competition/3936145_1625097200000",
  "value": "/9j/4AAQSkZJRgABAgAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5..."
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| contentType | STRING | MIME-Type (image/jpeg, image/png, etc.) |
| pictureLink | STRING | Pfad zum Bild auf Server |
| value | STRING | Base64-codiertes Bild |

**Verwendung**:
```php
// Base64 zu Image konvertieren
if ($picture && !empty($picture['value'])) {
    $imageSrc = 'data:' . $picture['contentType'] . ';base64,' . $picture['value'];
    
    // Speichern auf Filesystem
    $filename = uniqid() . '.' . explode('/', $picture['contentType'])[1];
    Storage::disk('public')->put("images/{$filename}", base64_decode($picture['value']));
}
```

---

## 5. Facility Structures

### Field Schema

**Beschreibung**: Ein Spielfeld innerhalb eines Stadions.

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

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| facilityFifaId | BIGINT | Referenz zum Stadion |
| orderNumber | INTEGER | Feldnummer |
| discipline | STRING | FOOTBALL, FUTSAL, BEACH_SOCCER |
| capacity | INTEGER | Zuschauer-Kapazität |
| groundNature | ENUM | GRASS, ARTIFICIAL, CLAY, SAND, MIXED |
| length | FLOAT | Länge in Meter |
| width | FLOAT | Breite in Meter |
| latitude | STRING | Geografische Breite |
| longitude | STRING | Geografische Länge |

---

### Facility Schema

**Beschreibung**: Stadion oder Sportanlage.

```json
{
  "facilityFifaId": 39933,
  "status": "ACTIVE",
  "internationalName": "Allianz Arena",
  "internationalShortName": "Allianz Arena",
  "organisationFifaId": 39393,
  "parentFacilityFifaId": null,
  "town": "Munich",
  "address": "Werner-Heisenberg-Allee 25",
  "webAddress": "https://www.fcbayern.com",
  "email": "info@fcbayern.com",
  "phone": "+49 89 308 9600",
  "fax": "+49 89 308 96100",
  "fields": [
    {
      "facilityFifaId": 39933,
      "orderNumber": 1,
      "capacity": 75024,
      "groundNature": "GRASS",
      "length": 105,
      "width": 68
    }
  ],
  "localNames": [
    {
      "name": "Allianz Arena",
      "language": "GER"
    }
  ]
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| facilityFifaId | BIGINT | Eindeutige FIFA Facility ID |
| status | ENUM | ACTIVE, INACTIVE |
| internationalName | STRING | Englischer Name |
| internationalShortName | STRING | Kurzbezeichnung |
| organisationFifaId | BIGINT | Besitzer Organisation |
| parentFacilityFifaId | BIGINT | Übergeordnete Facility (z.B. Complex) |
| town | STRING | Stadt |
| address | STRING | Straße und Hausnummer |
| webAddress | STRING | Website URL |
| email | STRING | Kontakt Email |
| phone | STRING | Telefonnummer |
| fax | STRING | Fax Nummer |
| fields | ARRAY[Field] | Alle Spielfelder |
| localNames | ARRAY[LocalName] | Mehrsprachige Namen |

---

## 6. Match Structures

### MatchPhase Schema

**Beschreibung**: Eine Phase eines Matches (1. Halbzeit, 2. Halbzeit, Verlängerung, Elfmeterserie).

```json
{
  "awayScore": 0,
  "homeScore": 1,
  "startDateTime": "2025-10-25T15:30:00",
  "endDateTime": "2025-10-25T17:45:00",
  "regularTime": 45,
  "stoppageTime": 2,
  "phaseLength": 47,
  "phase": "FIRST_HALF",
  "matchFifaId": 7763137,
  "startDateTimeUTC": "2025-10-25T14:30:00",
  "endDateTimeUTC": "2025-10-25T16:45:00"
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| homeScore | INTEGER | Tore Heimteam in dieser Phase |
| awayScore | INTEGER | Tore Gastteam in dieser Phase |
| startDateTime | DATETIME | Start der Phase (lokale Zeit) |
| endDateTime | DATETIME | Ende der Phase (lokale Zeit) |
| regularTime | INTEGER | Reguläre Spielzeit in Minuten |
| stoppageTime | INTEGER | Nachspielzeit in Minuten |
| phaseLength | INTEGER | Gesamtdauer (regularTime + stoppageTime) |
| phase | ENUM | Phasen-Typ |
| matchFifaId | BIGINT | Match Referenz |
| startDateTimeUTC | DATETIME | Start UTC |
| endDateTimeUTC | DATETIME | Ende UTC |

**Phase Enums**:
```
FIRST_HALF       - 1. Halbzeit
SECOND_HALF      - 2. Halbzeit
FIRST_ET         - 1. Verlängerung
SECOND_ET        - 2. Verlängerung
PEN              - Elfmeterserie
BEFORE_THE_MATCH - Vor Match
DURING_THE_BREAK - Halbzeitpause
AFTER_THE_MATCH  - Nach Match
PER_1, PER_2, PER_3 - Futsal Perioden
```

---

### Organisation Schema

**Beschreibung**: Club, Verband oder Organisation.

```json
{
  "internationalName": "FC Bayern München",
  "internationalShortName": "FCB",
  "country": "DE",
  "town": "Munich",
  "status": "ACTIVE",
  "refNumber1": "DFB-0001",
  "refNumber2": null,
  "localNames": [
    {
      "name": "Fußball-Club Bayern München",
      "language": "GER"
    }
  ]
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| internationalName | STRING | Englischer Name |
| internationalShortName | STRING | Kurzbezeichnung |
| country | STRING | ISO 3166-1 alpha-2 Code |
| town | STRING | Sitz-Stadt |
| status | ENUM | ACTIVE, INACTIVE |
| refNumber1 | STRING | Referenznummer 1 |
| refNumber2 | STRING | Referenznummer 2 |
| localNames | ARRAY[LocalName] | Mehrsprachige Namen |

---

### MatchTeam Schema

**Beschreibung**: Ein Team in einem Match (Home oder Away).

```json
{
  "teamNature": "HOME",
  "organisationFifaId": 39393,
  "teamFifaId": 59577,
  "matchFifaId": 7763137,
  "team": {
    "internationalName": "FC Bayern München",
    "internationalShortName": "FCB",
    "country": "DE"
  },
  "teamEvents": [
    {
      "id": 368227,
      "eventType": "GOAL",
      "minute": 12,
      "playerFifaId": 240701
    }
  ]
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| teamNature | ENUM | HOME, AWAY |
| teamFifaId | BIGINT | Team FIFA ID |
| organisationFifaId | BIGINT | Club/Organisation FIFA ID |
| matchFifaId | BIGINT | Match ID |
| team | OBJECT | Organisation Details |
| teamEvents | ARRAY[MatchEvent] | Events dieses Teams |

---

### Match Schema (FULL)

**Beschreibung**: Vollständige Match Information mit allen Details.

```json
{
  "matchFifaId": 7763137,
  "competitionFifaId": 3936145,
  "facilityFifaId": 55504,
  "attendance": 75000,
  "dateTimeLocal": "2025-10-25T15:30:00",
  "dateTimeUTC": "2025-10-25T14:30:00",
  "matchDay": 11,
  "matchDayDesc": "SEMIFINALS",
  "matchOrderNumber": 1,
  "status": "PLAYED",
  "statusDescription": "Match completed successfully",
  "resultSupplement": null,
  "resultSupplementHome": 0,
  "resultSupplementAway": 0,
  "homeFinalResult": 3,
  "awayFinalResult": 1,
  "leg": "HOME",
  "lastUpdateDateTime": "2025-10-25T17:45:00",
  "matchSummary": "Bayern Munich dominated the match with 65% possession...",
  "matchPhases": [ /* Array of MatchPhase */ ],
  "matchTeams": [ /* Home and Away teams */ ],
  "matchOfficials": [ /* Referees */ ],
  "facility": { /* Stadium info */ }
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| matchFifaId | BIGINT | Eindeutige FIFA Match ID |
| competitionFifaId | BIGINT | Competition Referenz |
| facilityFifaId | BIGINT | Stadion Referenz |
| attendance | INTEGER | Zuschauerzahl |
| dateTimeLocal | DATETIME | Match Zeit (lokale Zeitzone) |
| dateTimeUTC | DATETIME | Match Zeit (UTC) |
| matchDay | INTEGER | Spieltag / Matchday |
| matchDayDesc | STRING | Spieltag Bezeichnung |
| matchOrderNumber | INTEGER | Nummer des Matches am Spieltag |
| status | ENUM | RUNNING, TO_SCHEDULE, OFFICIALISED, PLAYED, SCHEDULED, CANCELLED, POSTPONED |
| statusDescription | STRING | Begründung für Status (z.B. Absage-Grund) |
| resultSupplement | STRING | Zusatz (z.B. "Walkover") |
| resultSupplementHome | INTEGER | Zusatz Tore Heimteam |
| resultSupplementAway | INTEGER | Zusatz Tore Gastteam |
| homeFinalResult | INTEGER | Finale Tore Heimteam |
| awayFinalResult | INTEGER | Finale Tore Gastteam |
| leg | ENUM | HOME, AWAY (bei 2-Spiele-Systemen) |
| lastUpdateDateTime | DATETIME | Letzte Aktualisierung |
| matchSummary | STRING | Zusammenfassung des Matches |
| matchPhases | ARRAY[MatchPhase] | Alle Phasen |
| matchTeams | ARRAY[MatchTeam] | Home und Away Teams |
| matchOfficials | ARRAY[MatchOfficial] | Schiedsrichter |
| facility | OBJECT | Stadion Details |

**Status Enums**:
```
RUNNING           - Läuft gerade
TO_SCHEDULE       - Muss noch angesetzt werden
SCHEDULED         - Angesetzt / geplant
OFFICIALISED      - Official results confirmed
PLAYED            - Beendet/Gespielt
CANCELLED         - Abgesagt
POSTPONED         - Verschoben
```

---

## 7. Match Event Structures

### MatchEvent Schema

**Beschreibung**: Ereignis während eines Matches (Tor, Karte, Wechsel, etc.).

```json
{
  "id": 368227,
  "matchPhase": "FIRST_HALF",
  "minute": 12,
  "second": 30,
  "stoppageTime": 0,
  "eventType": "GOAL",
  "eventDetailType": null,
  "playerFifaId": 240701,
  "playerFifaId2": null,
  "teamOfficialFifaId": null,
  "matchTeam": "HOME",
  "penaltyOrder": null,
  "personName": "Serge Gnabry",
  "localPersonName": null,
  "personName2": null,
  "localPersonName2": null,
  "shirtNumber": 7
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| id | BIGINT | Event ID |
| matchPhase | ENUM | FIRST_HALF, SECOND_HALF, etc. |
| minute | INTEGER | Minute des Matches |
| second | INTEGER | Sekunde innerhalb der Minute |
| stoppageTime | INTEGER | Nachspielzeit Minute |
| eventType | ENUM | GOAL, YELLOW, RED, SUBSTITUTION, etc. |
| eventDetailType | ENUM | Detail-Typ (z.B. DENY_GOAL_BY_OFFENCE) |
| playerFifaId | BIGINT | Spieler 1 (Schütze, Erhalt Karte, etc.) |
| playerFifaId2 | BIGINT | Spieler 2 (bei Wechsel: Austausch) |
| teamOfficialFifaId | BIGINT | Official ID (wenn applicable) |
| matchTeam | ENUM | HOME, AWAY |
| penaltyOrder | INTEGER | Nummer in Elfmeterserie |
| personName | STRING | Name Spieler 1 |
| localPersonName | STRING | Lokaler Name Spieler 1 |
| personName2 | STRING | Name Spieler 2 |
| localPersonName2 | STRING | Lokaler Name Spieler 2 |
| shirtNumber | INTEGER | Trikotnummer |

**Event Types**:
```
GOAL                     - Tor
YELLOW                   - Gelbe Karte
RED                      - Rote Karte
SECOND_YELLOW            - Zweite Gelbe (= Rot)
SUBSTITUTION             - Spielerwechsel
PENALTY                  - Elfmeter
PENALTY_FAILED_MISS      - Elfmeter verschossen
PENALTY_FAILED_SAVE      - Elfmeter gehalten
OWN_GOAL                 - Eigentor
EXPULSION                - Ausschluss
ACCUMULATED_FOUL         - Angesammelte Fouls
OFFICIAL_YELLOW          - Official erhalten Karte
TIME_OUT                 - Auszeit (Futsal)
```

---

## 8. Match Official Structures

### MatchOfficial Schema

**Beschreibung**: Schiedsrichter oder Assistent.

```json
{
  "personFifaId": 987654,
  "personName": "Felix Brych (GER)",
  "localPersonName": null,
  "role": "REFEREE",
  "roleDescription": "Referee",
  "cometRoleName": "Referee",
  "cometRoleNameKey": "label.referee",
  "person": { /* PersonExport */ },
  "matchFifaId": 7763137
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| personFifaId | BIGINT | Official FIFA ID |
| personName | STRING | Voller Name |
| localPersonName | STRING | Lokalisierter Name |
| role | ENUM | REFEREE, ASSISTANT_REFEREE, FOURTH_OFFICIAL, VAR, AVAR |
| roleDescription | STRING | Rollen-Beschreibung |
| cometRoleName | STRING | Label-Schlüssel |
| cometRoleNameKey | STRING | Übersetzungsschlüssel |
| person | OBJECT | PersonExport Details |
| matchFifaId | BIGINT | Match ID |

---

### MatchTeamOfficials Schema

**Beschreibung**: Alle Officials (Trainer, etc.) eines Teams in einem Match.

```json
{
  "teamFifaId": 59577,
  "teamNature": "HOME",
  "organisation": {
    "internationalName": "FC Bayern München",
    "internationalShortName": "FCB"
  },
  "officials": [
    {
      "role": "COACH",
      "personFifaId": 1787804,
      "personName": "Thomas Tuchel",
      "person": { /* PersonExport */ }
    }
  ]
}
```

---

### TeamOfficial Schema

**Beschreibung**: Ein einzelner Official/Trainer.

```json
{
  "role": "COACH",
  "roleDescription": "Head Coach",
  "cometRoleName": "Head Coach",
  "cometRoleNameKey": "label.headCoach",
  "personFifaId": 1787804,
  "personName": "Thomas Tuchel",
  "localPersonName": null,
  "person": { /* PersonExport */ },
  "matchTeamId": 7641389,
  "matchFifaId": 7763137,
  "teamId": 59577,
  "matchEvents": [ /* Karten, etc. */ ]
}
```

---

## 9. Match Player Structures

### MatchPlayer Schema

**Beschreibung**: Ein Spieler in der Match-Aufstellung.

```json
{
  "shirtNumber": 1,
  "captain": 1,
  "goalkeeper": 1,
  "startingLineup": 1,
  "played": 1,
  "teamFifaId": 59577,
  "matchFifaId": 7763137,
  "personFifaId": 240607,
  "personName": "Manuel Neuer",
  "localPersonName": null,
  "person": { /* PersonExport */ },
  "matchEvents": [ /* Tore, Karten, etc. */ ]
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| shirtNumber | INTEGER | Trikotnummer |
| captain | BOOLEAN | 0/1 Kapitän? |
| goalkeeper | BOOLEAN | 0/1 Torwart? |
| startingLineup | BOOLEAN | 0/1 In Startaufstellung? |
| played | BOOLEAN | 0/1 Hat gespielt? |
| teamFifaId | BIGINT | Team FIFA ID |
| matchFifaId | BIGINT | Match FIFA ID |
| personFifaId | BIGINT | Spieler FIFA ID |
| personName | STRING | Voller Name |
| localPersonName | STRING | Lokalisierter Name |
| person | OBJECT | PersonExport Details |
| matchEvents | ARRAY[MatchEvent] | Alle Events dieses Spielers |

---

### MatchTeamPlayers Schema

**Beschreibung**: Alle Spieler eines Teams in einem Match.

```json
{
  "teamFifaId": 59577,
  "teamNature": "HOME",
  "organisation": {
    "internationalName": "FC Bayern München",
    "internationalShortName": "FCB"
  },
  "players": [
    {
      "shirtNumber": 1,
      "personFifaId": 240607,
      "personName": "Manuel Neuer",
      ...
    }
  ]
}
```

---

## 10. Case & Disciplinary Structures

### Case Schema

**Beschreibung**: Disziplinarfall / Sanktionfall.

```json
{
  "caseFifaId": 5419390,
  "description": "Player received red card for violent conduct",
  "caseDate": "2025-10-25T17:45:00",
  "offenderNature": "PERSON",
  "offenderPersonNature": "PLAYER",
  "matchOfficialNature": "REFEREE",
  "teamOfficialNature": "COACH",
  "organisationOfficialNature": "OTHER",
  "status": "ACTIVE",
  "competitionFifaId": 3936145,
  "matchFifaId": 7763137,
  "matchEventFifaId": 370507,
  "offenderPersonFifaId": 1803409,
  "offenderOrganisationFifaId": 39393,
  "organisationFifaId": 40004
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| caseFifaId | BIGINT | Case ID |
| description | STRING | Fallbeschreibung |
| caseDate | DATETIME | Datum des Falles |
| offenderNature | ENUM | PERSON, ORGANISATION |
| offenderPersonNature | ENUM | PLAYER, OFFICIAL, COACH, etc. |
| status | ENUM | ACTIVE, CLOSED, APPEALED |
| competitionFifaId | BIGINT | Zugehörige Competition |
| matchFifaId | BIGINT | Zugehöriges Match |
| offenderPersonFifaId | BIGINT | Betroffene Person |
| offenderOrganisationFifaId | BIGINT | Betroffene Organisation |
| organisationFifaId | BIGINT | Zuständige Organisation |

---

### Sanction Schema

**Beschreibung**: Strafe/Sanktion aus einem Case.

```json
{
  "id": 367446,
  "currency": "EUR",
  "dateFrom": "2025-10-28T00:00:00",
  "dateTo": "2025-11-11T23:59:59",
  "measure": "MATCHES",
  "organisationSanctionNature": "DEDUCTION_OF_POINTS",
  "personSanctionNature": "MATCH_SUSPENSION",
  "status": "ACTIVE",
  "value": 3,
  "valueServed": 1,
  "caseFifaId": 5419390
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| id | BIGINT | Sanction ID |
| currency | STRING | Währung (EUR, USD, etc.) |
| dateFrom | DATETIME | Gültig von |
| dateTo | DATETIME | Gültig bis |
| measure | ENUM | MATCHES, DAYS, AMOUNT_MONEY |
| personSanctionNature | ENUM | MATCH_SUSPENSION, BAN, FINE |
| organisationSanctionNature | ENUM | DEDUCTION_OF_POINTS, FINE, STADIUM_BAN |
| status | ENUM | ACTIVE, SERVED, CANCELLED |
| value | FLOAT | Strafwert (z.B. 3 Spiele) |
| valueServed | FLOAT | Bereits verbüßt |
| caseFifaId | BIGINT | Case Referenz |

---

## 11. Statistics Structures

### TopScorer Schema

**Beschreibung**: Torschützenliste.

```json
{
  "competitionFifaId": 3936145,
  "playerFifaId": 2467434,
  "goals": 18,
  "internationalFirstName": "Robert",
  "internationalLastName": "Lewandowski",
  "popularName": "Robert",
  "club": "FC Bayern München",
  "clubId": 59577,
  "team": "FC Bayern München",
  "teamId": 59577
}
```

---

### OwnGoalScorer Schema

**Beschreibung**: Eigentor-Statistik.

```json
{
  "competitionFifaId": 3936145,
  "playerFifaId": 2467434,
  "goals": 2,
  "internationalFirstName": "Player",
  "internationalLastName": "Name",
  "popularName": "Player",
  "club": "FC Bayern München",
  "clubId": 59577
}
```

---

### Ranking Schema

**Beschreibung**: Tabelle/Ranking Position.

```json
{
  "position": 1,
  "teamFifaId": 59577,
  "team": {
    "internationalName": "FC Bayern München",
    "internationalShortName": "FCB",
    "country": "DE"
  },
  "matchesPlayed": 15,
  "wins": 12,
  "draws": 2,
  "losses": 1,
  "goalsFor": 45,
  "goalsAgainst": 12,
  "goalDifference": 33,
  "points": 38,
  "negativePoints": 0,
  "winsAfterPenalties": 0,
  "lossesAfterPenalties": 0
}
```

**Felder**:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| position | INTEGER | Tabellen-Position |
| teamFifaId | BIGINT | Team FIFA ID |
| team | OBJECT | Organisation Details |
| matchesPlayed | INTEGER | Gespielte Matches |
| wins | INTEGER | Siege |
| draws | INTEGER | Unentschieden |
| losses | INTEGER | Niederlagen |
| goalsFor | INTEGER | Geschossene Tore |
| goalsAgainst | INTEGER | Erhaltene Tore |
| goalDifference | INTEGER | Torquotient |
| points | INTEGER | Punkte |
| negativePoints | INTEGER | Punkte-Abzüge |
| winsAfterPenalties | INTEGER | Siege nach Elfmetern |
| lossesAfterPenalties | INTEGER | Niederlagen nach Elfmetern |

---

## 12. Organisation Structures

### CompetitionTeam Schema

**Beschreibung**: Team/Club der in einer Competition teilnimmt.

```json
{
  "teamFifaId": 59577,
  "internationalName": "FC Bayern München",
  "internationalShortName": "FCB",
  "competitionFifaId": 3936145,
  "organisationFifaId": 39393,
  "organisationName": "DFB",
  "organisationShortName": "German Football Association",
  "postalCode": "80992",
  "country": "DE",
  "region": "Bavaria",
  "town": "Munich",
  "status": "ACTIVE",
  "organisationNature": "CLUB",
  "facilityFifaId": 39933,
  "localNames": [ /* LocalName */ ]
}
```

---

### CompetitionTeamOfficial Schema

**Beschreibung**: Official bei einem Team in einer Competition.

```json
{
  "teamOfficialsPersonFifaId": 1803565,
  "role": "COACH",
  "cometRoleName": "label.headCoach",
  "cometRoleNameKey": "label.headCoach",
  "person": { /* PersonExport */ }
}
```

---

### CompetitionTeamOfficialList Schema

**Beschreibung**: Alle Officials eines Teams in einer Competition.

```json
{
  "teamFifaId": 39776,
  "organisation": { /* Organisation */ },
  "teamOfficials": [ /* Array of CompetitionTeamOfficial */ ]
}
```

---

### CompetitionTeamPlayer Schema

**Beschreibung**: Spieler eines Teams in einer Competition.

```json
{
  "playersPersonFifaId": 1806847,
  "shirtNumber": 10,
  "status": "ACTIVE",
  "person": { /* PersonExport */ }
}
```

---

### CompetitionTeamPlayerList Schema

**Beschreibung**: Alle Spieler eines Teams in einer Competition.

```json
{
  "teamFifaId": 39776,
  "teamName": "ATLETICO VENEZUELA CLUB DE FUTBOL",
  "organisation": { /* Organisation */ },
  "players": [ /* Array of CompetitionTeamPlayer */ ]
}
```

---

### Player Schema

**Beschreibung**: Spieler mit allen Registrierungen und Competitions.

```json
{
  "person": { /* PersonExport */ },
  "playerRegistrationList": [
    {
      "personFifaId": 240607,
      "status": "ACTIVE",
      "organisationFifaId": 39393,
      "registrationValidFrom": "2011-07-01T00:00:00",
      "registrationValidTo": "2026-06-30T23:59:59",
      "level": "INTERNATIONAL",
      "discipline": "FOOTBALL",
      "registrationNature": "CLUB"
    }
  ],
  "competitionList": [ /* Competitions where player participates */ ]
}
```

**Registration Levels**:
```
INTERNATIONAL   - Internationales Niveau
NATIONAL        - Nationales Niveau
REGIONAL        - Regionales Niveau
CLUB            - Club-Niveau
```

---

## Zusammenfassung: Alle Schemas

| Schema | Felder | Zweck |
|--------|--------|-------|
| ErrorMessage | 4 | Einzelne Fehlermeldung |
| ExceptionViewModel | 2 | Fehler-Container |
| ThrottlingInfo | 2 | Rate-Limit Info |
| LocalPersonName | 7 | Mehrsprachige Namen |
| PersonExport | 20 | Vollständige Person |
| PersonBasic | 20 | Vereinfachte Person |
| TeamOfficialRegistration | 7 | Team-Official |
| LocalName | 8 | Lokalisierte Bezeichnung |
| Picture | 3 | Bild/Grafik |
| Field | 9 | Spielfeld |
| Facility | 13 | Stadion |
| MatchPhase | 10 | Match Phase |
| Organisation | 8 | Club/Verband |
| MatchTeam | 6 | Team im Match |
| Match | 20+ | Vollständiges Match |
| MatchEvent | 15 | Match Ereignis |
| MatchOfficial | 8 | Schiedsrichter |
| MatchTeamOfficials | 4 | Team-Officials im Match |
| TeamOfficial | 11 | Ein Official |
| MatchPlayer | 11 | Spieler im Match |
| MatchTeamPlayers | 4 | Alle Spieler im Match |
| Case | 13 | Disziplinarfall |
| Sanction | 9 | Strafe |
| TopScorer | 9 | Torschütze |
| OwnGoalScorer | 8 | Eigentor |
| Ranking | 13 | Tabellen-Position |
| CompetitionTeam | 15 | Team in Competition |
| CompetitionTeamOfficial | 5 | Official in Competition |
| CompetitionTeamOfficialList | 3 | Alle Officials |
| CompetitionTeamPlayer | 4 | Spieler in Competition |
| CompetitionTeamPlayerList | 4 | Alle Spieler |
| Player | 3 | Spieler mit Registrierungen |

---

**Letzte Aktualisierung**: October 23, 2025  
**Version**: 1.0  
**Status**: ✅ Production Ready  
**Alle 32+ Schemas dokumentiert**: ✅
