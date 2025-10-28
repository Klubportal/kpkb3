# COMET REST API - Produktions-Endpoint Referenz

**Version**: 1.0  
**Datum**: October 23, 2025  
**API Base URL**: https://api-<tenant>.analyticom.de  
**Bereich**: Datenexport, Datensynchronisation, FIFA Connect Standard

---

## 📋 Inhaltsverzeichnis

1. [API Überblick](#api-überblick)
2. [Authentifizierung & Throttling](#authentifizierung--throttling)
3. [Competitions Endpoints](#competitions-endpoints)
4. [Teams/Clubs Endpoints](#teamsclubs-endpoints)
5. [Players/Personen Endpoints](#playerspersonen-endpoints)
6. [Match Endpoints](#match-endpoints)
7. [Match Events Endpoints](#match-events-endpoints)
8. [Match Officials Endpoints](#match-officials-endpoints)
9. [Match Players Endpoints](#match-players-endpoints)
10. [Case/Disziplinar Endpoints](#casedisziplinar-endpoints)
11. [Images Endpoints](#images-endpoints)
12. [Facilities Endpoints](#facilities-endpoints)
13. [Reports Endpoints](#reports-endpoints)
14. [Response Schemas](#response-schemas)
15. [Error Handling](#error-handling)

---

## 1. API Überblick

### Zweck

Die **COMET REST API - Production** bietet Zugriff auf die replizierte COMET-Datenbank für:

- **Datenexport** - Wettbewerbs-, Team-, Spieler-, Match-Daten exportieren
- **Datensynchronisation** - Lokale Datenbanken mit COMET synchronisieren
- **FIFA Connect Standard** - Alle Daten im FIFA Connect Format
- **Live Updates** - Match Events, Scores, Ergebnisse in Echtzeit
- **Disziplinar-Daten** - Cases, Sanctions, Offender Information

### API Charakteristiken

```
┌──────────────────────────────────────────────────┐
│         COMET REST API Charakteristiken          │
├──────────────────────────────────────────────────┤
│                                                  │
│ Base URL: https://api-<tenant>.analyticom.de   │
│ Format: JSON                                     │
│ Standard: FIFA Connect                           │
│ Auth: HTTP Basic Auth (username:password)        │
│ Throttling: Dynamisch pro Tenant                │
│ Caching: Server-seitig auf replizierter DB      │
│ Response Encoding: UTF-8                         │
│                                                  │
└──────────────────────────────────────────────────┘
```

---

## 2. Authentifizierung & Throttling

### 2.1 Throttling Information Endpoint

```http
GET /api/export/comet/throttling/info
Authorization: Basic base64(username:password)
```

**Beschreibung**: Rufe aktuelle Throttling/Rate-Limit Informationen ab.

**Response (200 OK)**:
```json
{
  "requestsPerSecond": 100,
  "endPointRequestsPerSecond": {
    "/api/export/comet/images": 50,
    "/api/export/comet/competitions": 100,
    "/api/export/comet/matches": 100
  }
}
```

**Bedeutung**:
- `requestsPerSecond`: Standard Rate Limit für alle Endpoints
- `endPointRequestsPerSecond`: Spezifische Limits pro Endpoint
- Bilder-Endpoints haben meist niedrigere Limits wegen Dateigröße

**Implementierung**:
```php
// Laravel
$throttling = Http::withBasicAuth('username', 'password')
    ->get('https://api-dfb.analyticom.de/api/export/comet/throttling/info')
    ->json();

$standardRate = $throttling['requestsPerSecond'];      // 100
$imageRate = $throttling['endPointRequestsPerSecond']['/api/export/comet/images']; // 50
```

---

## 3. Competitions Endpoints

### 3.1 List Competitions

```http
GET /api/export/comet/competitions
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `competitionFifaId` (optional): Filtern nach einzelner Competition
- `competitionFifaIds` (optional): Array von Competition IDs
- `organisationFifaIds` (optional): Array von Organisation IDs
- `active` (optional): true/false/null (default: active für bestimmte Orgs)
- `season` (optional): Saison-Jahr (z.B. 2025)
- `teamFifaId` (optional): Nach Team filtern
- `competitionPhotoEmbedded` (optional): true/false (Fotos einbetten)
- `superiorCompetitionFifaId` (optional): Nach übergeordnetem Wettbewerb
- `ageCategory` (optional): A, SENIORS, U_21, U_19, etc.

**Response (200 OK)**:
```json
[
  {
    "competitionFifaId": 3936145,
    "internationalName": "Copa Bridgestone Libertadores 2025",
    "internationalShortName": "Copa Libertadores 2025",
    "organisationFifaId": 39393,
    "season": 2025,
    "status": "ACTIVE",
    "dateFrom": "2025-01-15T00:00:00",
    "dateTo": "2025-11-30T23:59:59",
    "ageCategory": "SENIORS",
    "teamCharacter": "CLUB",
    "nature": "ROUND_ROBIN",
    "discipline": "FOOTBALL",
    "gender": "MALE",
    "numberOfParticipants": 32,
    "matchType": "OFFICIAL",
    "competitionType": "League",
    "penaltyShootout": true,
    "flyingSubstitutions": false,
    "imageId": 3936909,
    "picture": { /* Picture object */ },
    "localNames": [ /* Local names */ ]
  }
]
```

**Häufige Abfragen**:
```php
// Alle aktiven Wettbewerbe
GET /api/export/comet/competitions?active=true

// Spezifische Competition
GET /api/export/comet/competitions?competitionFifaId=3936145

// Nach Saison
GET /api/export/comet/competitions?season=2025&active=true

// Nach Organisation
GET /api/export/comet/competitions?organisationFifaIds=39393,40004

// Nach Age Category
GET /api/export/comet/competitions?ageCategory=U_21&active=true
```

---

### 3.2 Get Competition Teams

```http
GET /api/export/comet/competition/{competitionFifaId}/teams
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `competitionFifaId` (erforderlich): ID des Wettbewerbs

**Response (200 OK)**:
```json
[
  {
    "teamFifaId": 59577,
    "internationalName": "FC Bayern München",
    "internationalShortName": "FCB",
    "competitionFifaId": 3936145,
    "organisationFifaId": 39393,
    "organisationName": "DFB",
    "organisationShortName": "German Football Association",
    "country": "DE",
    "town": "Munich",
    "region": "Bavaria",
    "status": "ACTIVE",
    "facilityFifaId": 263,
    "localNames": [ /* Local names */ ]
  }
]
```

---

### 3.3 Get Competition Ranking/Standings

```http
GET /api/export/comet/competition/{competitionFifaId}/ranking
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `unofficial` (optional): true/false (für FSHF Tenant)

**Response (200 OK)**:
```json
[
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
  },
  {
    "position": 2,
    "teamFifaId": 59578,
    "team": {
      "internationalName": "Borussia Dortmund",
      "internationalShortName": "BVB",
      "country": "DE"
    },
    "matchesPlayed": 15,
    "wins": 11,
    "draws": 1,
    "losses": 3,
    "goalsFor": 42,
    "goalsAgainst": 15,
    "goalDifference": 27,
    "points": 34,
    "negativePoints": 0,
    "winsAfterPenalties": 0,
    "lossesAfterPenalties": 0
  }
]
```

---

### 3.4 Get Top Scorers

```http
GET /api/export/comet/competition/{competitionFifaId}/topScorers
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
[
  {
    "competitionFifaId": 3936145,
    "playerFifaId": 2467434,
    "internationalFirstName": "Robert",
    "internationalLastName": "Lewandowski",
    "popularName": "Robert",
    "goals": 18,
    "club": "FC Bayern München",
    "clubId": 59577,
    "team": "FC Bayern München",
    "teamId": 59577
  }
]
```

---

### 3.5 Get Own Goal Scorers

```http
GET /api/export/comet/competition/{competitionFifaId}/ownGoalScorers
Authorization: Basic base64(username:password)
```

---

## 4. Teams/Clubs Endpoints

### 4.1 Get Team Players

```http
GET /api/export/comet/team/{teamFifaId}/players
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `status` (erforderlich): ALL, ACTIVE, INACTIVE

**Response (200 OK)**:
```json
[
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
    "playerPosition": "Goalkeeper",
    "rowNumber": 1,
    "homegrown": 1,
    "refNumber1": "NE-001",
    "refNumber2": null,
    "nationalID": "49-12345678",
    "passportNumber": "98765432",
    "localPersonNames": [ /* Local names */ ]
  }
]
```

---

### 4.2 Get Team Officials

```http
GET /api/export/comet/team/{teamFifaId}/teamOfficials
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `status` (erforderlich): ALL, ACTIVE, INACTIVE

**Response (200 OK)**:
```json
[
  {
    "role": "COACH",
    "cometRoleName": "label.headCoach",
    "cometRoleNameKey": "label.headCoach",
    "personFifaId": 1787804,
    "teamId": 57359,
    "status": "ACTIVE",
    "person": { /* PersonExport object */ }
  }
]
```

---

## 5. Players/Personen Endpoints

### 5.1 Get Player Details

```http
GET /api/export/comet/player/{playerFifaId}
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
{
  "person": {
    "personFifaId": 240607,
    "internationalFirstName": "Manuel",
    "internationalLastName": "Neuer",
    "gender": "MALE",
    "nationality": "DE",
    "nationalityFIFA": "GER",
    "dateOfBirth": "1986-03-27T00:00:00",
    "countryOfBirth": "DE",
    "placeOfBirth": "Gelsenkirchen",
    "playerPosition": "Goalkeeper",
    "homegrown": 1,
    "refNumber1": "NE-001",
    "nationalID": "49-12345678",
    "passportNumber": "98765432",
    "localPersonNames": [ /* Local names in different languages */ ]
  },
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
  "competitionList": [ /* Competitions player participates in */ ]
}
```

---

## 6. Match Endpoints

### 6.1 Get All Competition Matches

```http
GET /api/export/comet/competition/{competitionFifaId}/matches
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `teamFifaId` (optional): Filtern nach Team
- `matchDay` (optional): Spieltag
- `dateTimeLocal` (optional): Match-Datum (dd.MM.yyyy)
- `status` (optional): RUNNING, TO_SCHEDULE, OFFICIALISED, PLAYED, SCHEDULED, CANCELLED, POSTPONED
- `dateFrom` (optional): Von-Datum (dd.MM.yyyy)
- `dateTo` (optional): Bis-Datum (dd.MM.yyyy)
- `currentMatchDayOnly` (optional): true/false

**Response (200 OK)**:
```json
[
  {
    "matchFifaId": 7763137,
    "competitionFifaId": 3936145,
    "facilityFifaId": 55504,
    "matchDay": 11,
    "matchDayDesc": "SEMIFINALS",
    "matchOrderNumber": 1,
    "status": "PLAYED",
    "statusDescription": "Match completed",
    "dateTimeLocal": "2025-10-25T15:30:00",
    "dateTimeUTC": "2025-10-25T14:30:00",
    "attendance": 75000,
    "leg": "HOME",
    "homeFinalResult": 3,
    "awayFinalResult": 1,
    "matchPhases": [ /* Phase details */ ],
    "matchTeams": [ /* Home and Away teams */ ],
    "matchOfficials": [ /* Referee and assistants */ ],
    "facility": { /* Stadium info */ },
    "matchSummary": "Bayern dominated the match...",
    "lastUpdateDateTime": "2025-10-25T17:45:00"
  }
]
```

---

### 6.2 Get Single Match Details

```http
GET /api/export/comet/match/{matchFifaId}
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
Siehe oben - identische Struktur wie einzelnes Match aus Liste.

---

### 6.3 Get Match Last Update DateTime

```http
GET /api/export/comet/match/{matchFifaId}/lastUpdateDateTime
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
{
  "matchFifaId": 7763137,
  "lastUpdateDateTime": "2025-10-25T17:45:00"
}
```

**Verwendung**: Prüfe periodisch, ob Match aktualisiert wurde, bevor du komplette Match-Daten abrufst.

---

## 7. Match Events Endpoints

### 7.1 Get All Match Events

```http
GET /api/export/comet/match/{matchFifaId}/events
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `eventType` (optional): GOAL, YELLOW, RED, SUBSTITUTION, etc.

**Response (200 OK)**:
```json
[
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
  },
  {
    "id": 368228,
    "matchPhase": "FIRST_HALF",
    "minute": 34,
    "second": 15,
    "stoppageTime": 0,
    "eventType": "YELLOW",
    "eventDetailType": null,
    "playerFifaId": 240704,
    "matchTeam": "AWAY",
    "personName": "Dan-Axel Zagadou",
    "shirtNumber": 6
  },
  {
    "id": 368229,
    "matchPhase": "FIRST_HALF",
    "minute": 45,
    "second": 0,
    "stoppageTime": 0,
    "eventType": "SUBSTITUTION",
    "playerFifaId": 240705,
    "playerFifaId2": 240706,
    "matchTeam": "AWAY",
    "personName": "Out: Marco Reus",
    "personName2": "In: Karim Adeyemi"
  }
]
```

**Event Types**:
- `GOAL` - Tor
- `YELLOW` - Gelbe Karte
- `RED` - Rote Karte
- `SECOND_YELLOW` - Zweite Gelbe Karte
- `SUBSTITUTION` - Spielerwechsel
- `PENALTY` - Elfmeter
- `PENALTY_FAILED_MISS` - Elfmeter verschossen
- `PENALTY_FAILED_SAVE` - Elfmeter gehalten
- `OWN_GOAL` - Eigentor
- `RED_CARD` - Rote Karte
- `EXPULSION` - Ausschluss
- `ACCUMULATED_FOUL` - Angesammelte Fouls
- `TIME_OUT` - Auszeit

---

### 7.2 Get Latest Match Events (Last N Seconds)

```http
GET /api/export/comet/match/{matchFifaId}/latest/events
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `seconds` (erforderlich): Events der letzten N Sekunden

**Response (200 OK)**:
```json
[
  {
    "id": 368245,
    "matchPhase": "SECOND_HALF",
    "minute": 67,
    "second": 45,
    "eventType": "GOAL",
    "playerFifaId": 240702,
    "matchTeam": "HOME",
    "personName": "Benjamin Pavard",
    "shirtNumber": 17
  }
]
```

**Besonderheit**: Gelöschte Events werden mit NULL-Payload zurückgegeben:
```json
{
  "id": 368246,
  "matchPhase": null,
  "minute": null,
  "second": null,
  "stoppageTime": null,
  "eventType": null,
  "eventDetailType": null,
  "playerFifaId": null,
  "matchTeam": null,
  "personName": null
}
```

---

## 8. Match Officials Endpoints

### 8.1 Get Match Officials

```http
GET /api/export/comet/match/{matchFifaId}/officials
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
[
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
  },
  {
    "personFifaId": 987655,
    "personName": "Marco Häcker (GER)",
    "role": "ASSISTANT_REFEREE",
    "roleDescription": "Assistant Referee",
    "cometRoleName": "Assistant Referee",
    "cometRoleNameKey": "label.assistantReferee",
    "matchFifaId": 7763137
  }
]
```

---

### 8.2 Get Match Team Officials

```http
GET /api/export/comet/match/{matchFifaId}/teamOfficials
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
[
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
        "roleDescription": "Head Coach",
        "cometRoleName": "Head Coach",
        "cometRoleNameKey": "label.headCoach",
        "personFifaId": 1787804,
        "personName": "Thomas Tuchel",
        "person": { /* PersonExport */ },
        "matchTeamId": 7641389,
        "matchFifaId": 7763137,
        "teamId": 59577
      }
    ]
  },
  {
    "teamFifaId": 59578,
    "teamNature": "AWAY",
    "organisation": {
      "internationalName": "Borussia Dortmund",
      "internationalShortName": "BVB"
    },
    "officials": [ /* BVB officials */ ]
  }
]
```

---

## 9. Match Players Endpoints

### 9.1 Get Match Players (Both Teams)

```http
GET /api/export/comet/match/{matchFifaId}/players
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
[
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
        "matchEvents": [ /* Events involving this player */ ]
      }
    ]
  }
]
```

---

### 9.2 Get Single Match Player

```http
GET /api/export/comet/match/{matchFifaId}/players/{personFifaId}
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
Siehe oben - einzelner Player Objekt.

---

## 10. Case/Disziplinar Endpoints

### ⚠️ Require ROLE_DISCIPLINARY_WEB

### 10.1 Get Competition Cases

```http
GET /api/export/comet/competition/{competitionFifaId}/cases
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
[
  {
    "caseFifaId": 5419390,
    "description": "Player received red card for violent conduct",
    "caseDate": "2025-10-25T17:45:00",
    "offenderNature": "PERSON",
    "offenderPersonNature": "PLAYER",
    "status": "ACTIVE",
    "competitionFifaId": 3936145,
    "matchFifaId": 7763137,
    "matchEventFifaId": 370507,
    "offenderPersonFifaId": 1803409,
    "organisationFifaId": 39393
  }
]
```

---

### 10.2 Get Case Details

```http
GET /api/export/comet/case/{caseFifaId}
Authorization: Basic base64(username:password)
```

---

### 10.3 Get Case Sanctions

```http
GET /api/export/comet/case/{caseFifaId}/sanctions
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `status` (erforderlich): all, active

**Response (200 OK)**:
```json
[
  {
    "id": 367446,
    "currency": "EUR",
    "dateFrom": "2025-10-28T00:00:00",
    "dateTo": "2025-11-11T23:59:59",
    "measure": "MATCHES",
    "personSanctionNature": "MATCH_SUSPENSION",
    "status": "ACTIVE",
    "value": 3,
    "valueServed": 1,
    "caseFifaId": 5419390
  }
]
```

---

### 10.4 Get Person Offender Cases

```http
GET /api/export/comet/case/person/{offenderPersonFifaId}
Authorization: Basic base64(username:password)
```

---

### 10.5 Get Organisation Offender Cases

```http
GET /api/export/comet/case/organisation/{offenderOrganisationFifaId}
Authorization: Basic base64(username:password)
```

---

### 10.6 Get Match Cases

```http
GET /api/export/comet/match/{matchFifaId}/cases
Authorization: Basic base64(username:password)
```

---

### 10.7 Get Sanction Details

```http
GET /api/export/comet/sanction/{sanctionId}
Authorization: Basic base64(username:password)
```

---

## 11. Images Endpoints

### 11.1 Get Images (Base64 Encoded)

```http
GET /api/export/comet/images/{entity}/{fifaId}
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `entity`: person, competition, organization
- `fifaId`: FIFA ID der Entity

**Response (200 OK)**:
```json
{
  "contentType": "image/jpeg",
  "pictureLink": "/Person/1789311_1362020456723",
  "value": "/9j/4AAQSkZJRgABAgAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsL..."
}
```

**Verwendung**:
```javascript
// Base64 zu Bild konvertieren
const imageData = 'data:image/jpeg;base64,' + response.value;
document.getElementById('playerPhoto').src = imageData;
```

**Entities**:
- `person` - Spieler Fotos (playerFifaId)
- `competition` - Wettbewerbs Logo
- `organization` - Club/Verband Logo

---

### 11.2 Check Image Updated

```http
GET /api/export/comet/images/update/{entity}/{fifaId}
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `date` (erforderlich): Letztes Update-Datum (dd.MM.yyyy)

**Response (200 OK)**:
```json
true
```

**Bedeutung**: `true` = Bild wurde aktualisiert seit angegebenem Datum.

---

## 12. Facilities Endpoints

### 12.1 Get All Facilities

```http
GET /api/export/comet/facilities
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `facilityFifaId` (optional): Filtern nach Facility ID

**Response (200 OK)**:
```json
[
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
    "phone": "+49 89 308 9600",
    "fax": "+49 89 308 96100",
    "fields": [
      {
        "facilityFifaId": 39933,
        "capacity": 75024,
        "groundNature": "GRASS",
        "length": 105,
        "width": 68,
        "latitude": "48.2188",
        "longitude": "11.6217"
      }
    ],
    "localNames": [ /* Local names */ ]
  }
]
```

---

## 13. Reports Endpoints

### 13.1 Execute Ad-Hoc Report

```http
GET /api/export/comet/adHocReport/{reportId}
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `reportId` (erforderlich): Report ID

**Query Parameter**:
- `paramString` (optional): Report Parameter (komplexe String)

**Response (200 OK)**:
```json
[
  {
    "playerName": "Robert Lewandowski",
    "club": "FC Bayern München",
    "goals": 18,
    "assists": 5,
    "appearances": 15,
    "minutes": 1350
  }
]
```

---

## 14. Response Schemas

### PersonExport Schema

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
  "localPersonNames": [
    {
      "personFifaId": 240607,
      "firstName": "Manuel",
      "lastName": "Neuer",
      "popularName": "Manuel",
      "language": "GER",
      "birthName": null,
      "title": null
    }
  ]
}
```

### Match Schema (Vollständig)

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
  "matchSummary": "Bayern Munich dominated...",
  "matchPhases": [ /* Array of MatchPhase */ ],
  "matchTeams": [ /* Array of MatchTeam with officials */ ],
  "matchOfficials": [ /* Referees and assistants */ ],
  "facility": { /* Facility details */ }
}
```

### Competition Schema

```json
{
  "competitionFifaId": 3936145,
  "ageCategory": "SENIORS",
  "ageCategoryName": "label.category.seniors",
  "dateFrom": "2025-01-15T00:00:00",
  "dateTo": "2025-11-30T23:59:59",
  "discipline": "FOOTBALL",
  "gender": "MALE",
  "internationalName": "Copa Bridgestone Libertadores 2025",
  "internationalShortName": "Copa Libertadores 2025",
  "imageId": 3936909,
  "multiplier": 2,
  "nature": "ROUND_ROBIN",
  "numberOfParticipants": 32,
  "orderNumber": 2,
  "organisationFifaId": 39393,
  "season": 2025,
  "status": "ACTIVE",
  "teamCharacter": "CLUB",
  "superiorCompetitionFifaId": 3936029,
  "matchType": "OFFICIAL",
  "flyingSubstitutions": false,
  "penaltyShootout": true,
  "competitionType": "League",
  "competitionTypeId": 1,
  "picture": { /* Picture object */ },
  "localNames": [ /* Local names */ ],
  "rankingNotes": "-2 points to XYZ for..."
}
```

---

## 15. Error Handling

### HTTP Status Codes

| Code | Bedeutung | Grund |
|------|-----------|-------|
| 200 | OK | Request erfolgreich |
| 400 | Bad Request | Ungültige Parameter |
| 401 | Unauthorized | Authentifizierung erforderlich |
| 403 | Forbidden | Keine Berechtigung |
| 404 | Not Found | Ressource existiert nicht |
| 500 | Server Error | Interner Fehler |
| 502 | Bad Gateway | Gateway Fehler |

### Error Response Format

```json
{
  "messages": [
    {
      "message": "Invalid competition id",
      "severity": "error",
      "type": 1,
      "additionalInfo": {
        "parameter": "competitionFifaId",
        "expectedFormat": "integer"
      }
    }
  ]
}
```

### Häufige Fehler

```
401 Unauthorized:
- Credentials ungültig
- Authentication Header fehlt
- Username/Password falsch

403 Forbidden:
- Keine ROLE_DISCIPLINARY_WEB für Disziplinar-Endpoints
- Tenant nicht berechtigt

404 Not Found:
- Competition/Match/Team/Player nicht vorhanden
- Falscher Tenant

400 Bad Request:
- Parameter fehlt (z.B. status bei Team Players)
- Falsches Datumsformat
- Ungültige Enum-Werte
```

---

## Implementierungs-Beispiel: Laravel

```php
// app/Services/CometExportService.php

class CometExportService {
    private $baseUrl = 'https://api-dfb.analyticom.de';
    private $username;
    private $password;

    public function __construct() {
        $this->username = config('comet.username');
        $this->password = config('comet.password');
    }

    /**
     * Get competitions
     */
    public function getCompetitions($filters = []) {
        return $this->makeRequest('GET', '/api/export/comet/competitions', $filters);
    }

    /**
     * Get competition matches
     */
    public function getCompetitionMatches($competitionId, $filters = []) {
        $filters['competitionFifaId'] = $competitionId;
        return $this->makeRequest('GET', "/api/export/comet/competition/{$competitionId}/matches", $filters);
    }

    /**
     * Get match details with all related data
     */
    public function getMatchFull($matchId) {
        return [
            'match' => $this->makeRequest('GET', "/api/export/comet/match/{$matchId}"),
            'officials' => $this->makeRequest('GET', "/api/export/comet/match/{$matchId}/officials"),
            'teamOfficials' => $this->makeRequest('GET', "/api/export/comet/match/{$matchId}/teamOfficials"),
            'players' => $this->makeRequest('GET', "/api/export/comet/match/{$matchId}/players"),
            'events' => $this->makeRequest('GET', "/api/export/comet/match/{$matchId}/events"),
            'phases' => $this->makeRequest('GET', "/api/export/comet/match/{$matchId}/phases"),
        ];
    }

    /**
     * Get live updates since last check
     */
    public function getLatestEvents($matchId, $seconds = 60) {
        return $this->makeRequest('GET', "/api/export/comet/match/{$matchId}/latest/events", [
            'seconds' => $seconds
        ]);
    }

    /**
     * Make HTTP request with auth
     */
    private function makeRequest($method, $endpoint, $params = []) {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->{strtolower($method)}($this->baseUrl . $endpoint, $params);

            if ($response->failed()) {
                \Log::error('COMET Export Error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('COMET Export Exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
```

---

**Letzte Aktualisierung**: October 23, 2025  
**Version**: 1.0  
**Status**: ✅ Production Ready  
**Base URL**: https://api-<tenant>.analyticom.de  
**Authentifizierung**: HTTP Basic Auth (Username:Password)
