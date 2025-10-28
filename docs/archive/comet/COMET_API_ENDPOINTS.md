# COMET REST API - Vollständige Endpoint-Dokumentation

**Version**: 2.0  
**Datum**: October 23, 2025  
**API Base URL**: https://api-hns.analyticom.de  
**Swagger UI**: https://api-hns.analyticom.de/swagger-ui.html

---

## 📖 Vollständige Endpoint-Referenz

Alle Endpoints der COMET REST API mit detaillierten Parametern, Request/Response Beispielen und Implementierungen.

---

## 1. SYSTEM & THROTTLING ENDPOINTS

### 1.1 Throttling Information
```http
GET /throttling/info
Authorization: Basic base64(username:password)
Content-Type: application/json
```

**Beschreibung**: Rufe aktuelle Rate Limiting Informationen für deinen Account ab.

**Response (200 OK)**:
```json
{
  "standardRate": 100,
  "imageRate": 50,
  "tenant": "admin@example.com",
  "requestsPerSecond": 100,
  "imagesPerSecond": 50,
  "requestWindowMs": 1000
}
```

**Laravel Implementation**:
```php
public function getThrottlingInfo()
{
    return $this->makeRequest('GET', '/throttling/info');
}
```

---

## 2. COMPETITION ENDPOINTS

### 2.1 Get All Competitions
```http
GET /competitions
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `country` (optional): Filterung nach Land
- `season` (optional): Filterung nach Saison (z.B. "2025/2026")
- `type` (optional): LEAGUE, CUP, FRIENDLY, TOURNAMENT
- `page` (optional): Pagination (Standard: 1)
- `limit` (optional): Items pro Seite (Standard: 50)

**Response (200 OK)**:
```json
{
  "data": [
    {
      "fifaId": "123456",
      "name": "Bundesliga 2025",
      "shortName": "BL",
      "country": "Germany",
      "countryCode": "DE",
      "season": "2025/2026",
      "type": "LEAGUE",
      "startDate": "2025-08-15",
      "endDate": "2026-05-30",
      "active": true,
      "numberOfMatches": 306,
      "numberOfClubs": 18,
      "numberOfPlayers": 540
    },
    {
      "fifaId": "123457",
      "name": "DFB-Pokal 2025",
      "shortName": "DFB",
      "country": "Germany",
      "countryCode": "DE",
      "season": "2025/2026",
      "type": "CUP",
      "startDate": "2025-08-08",
      "endDate": "2026-05-23",
      "active": true,
      "numberOfMatches": 64,
      "numberOfClubs": 64,
      "numberOfPlayers": 1200
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 50,
    "total": 5,
    "pages": 1
  }
}
```

**Laravel Implementation**:
```php
public function getAllCompetitions($country = null, $season = null, $page = 1)
{
    $params = [
        'page' => $page,
        'limit' => 50,
    ];
    
    if ($country) $params['country'] = $country;
    if ($season) $params['season'] = $season;
    
    return $this->makeRequest('GET', '/competitions', $params);
}
```

---

### 2.2 Get Competition by FIFA ID
```http
GET /competition/{competitionFifaId}
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `competitionFifaId` (erforderlich): FIFA ID des Wettbewerbs

**Response (200 OK)**:
```json
{
  "fifaId": "123456",
  "name": "Bundesliga 2025",
  "shortName": "BL",
  "country": "Germany",
  "countryCode": "DE",
  "season": "2025/2026",
  "type": "LEAGUE",
  "startDate": "2025-08-15",
  "endDate": "2026-05-30",
  "active": true,
  "numberOfMatches": 306,
  "numberOfClubs": 18,
  "numberOfPlayers": 540,
  "logo": "https://cdn.analyticom.de/competitions/123456/logo.png",
  "description": "Höchste Spielklasse im deutschen Fußball"
}
```

**Error (404 Not Found)**:
```json
{
  "error": {
    "code": "COMPETITION_NOT_FOUND",
    "message": "Competition with FIFA ID 999999 not found"
  }
}
```

**Laravel Implementation**:
```php
public function getCompetition($fifaId)
{
    return $this->makeRequest('GET', "/competition/{$fifaId}");
}
```

---

## 3. CLUB ENDPOINTS

### 3.1 Get All Clubs
```http
GET /clubs
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `competition` (optional): FIFA ID des Wettbewerbs
- `country` (optional): Ländercode (z.B. "DE")
- `search` (optional): Suchtext für Clubname
- `page` (optional): Pagination (Standard: 1)
- `limit` (optional): Items pro Seite (Standard: 50)

**Response (200 OK)**:
```json
{
  "data": [
    {
      "fifaId": "654321",
      "name": "FC Bayern München",
      "shortName": "FCB",
      "countryCode": "DE",
      "city": "Munich",
      "founded": 1900,
      "stadium": "Allianz Arena",
      "stadiumCapacity": 75024,
      "officialWebsite": "https://www.fcbayern.com",
      "colors": {
        "primary": "#DC143C",
        "secondary": "#FFFFFF"
      },
      "logo": "https://cdn.analyticom.de/clubs/654321/logo.png",
      "badge": "https://cdn.analyticom.de/clubs/654321/badge.png",
      "totalPlayers": 32,
      "nationalTeamPlayers": 8
    },
    {
      "fifaId": "654322",
      "name": "Borussia Dortmund",
      "shortName": "BVB",
      "countryCode": "DE",
      "city": "Dortmund",
      "founded": 1909,
      "stadium": "Signal Iduna Park",
      "stadiumCapacity": 81365,
      "officialWebsite": "https://www.bvb.de",
      "colors": {
        "primary": "#FFD700",
        "secondary": "#000000"
      },
      "logo": "https://cdn.analyticom.de/clubs/654322/logo.png",
      "badge": "https://cdn.analyticom.de/clubs/654322/badge.png",
      "totalPlayers": 28,
      "nationalTeamPlayers": 5
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 50,
    "total": 18,
    "pages": 1
  }
}
```

**Laravel Implementation**:
```php
public function getAllClubs($competition = null, $country = null, $search = null, $page = 1)
{
    $params = [
        'page' => $page,
        'limit' => 50,
    ];
    
    if ($competition) $params['competition'] = $competition;
    if ($country) $params['country'] = $country;
    if ($search) $params['search'] = $search;
    
    return $this->makeRequest('GET', '/clubs', $params);
}
```

---

### 3.2 Get Club by FIFA ID
```http
GET /club/{clubFifaId}
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `clubFifaId` (erforderlich): FIFA ID des Clubs

**Response (200 OK)**:
```json
{
  "fifaId": "654321",
  "name": "FC Bayern München",
  "shortName": "FCB",
  "countryCode": "DE",
  "city": "Munich",
  "founded": 1900,
  "stadium": "Allianz Arena",
  "stadiumCapacity": 75024,
  "stadiumPhone": "+49 89 / 308 9600",
  "stadiumEmail": "info@fcbayern.com",
  "officialWebsite": "https://www.fcbayern.com",
  "chairman": "Uli Hoeneß",
  "manager": "Thomas Tuchel",
  "colors": {
    "primary": "#DC143C",
    "secondary": "#FFFFFF"
  },
  "logo": "https://cdn.analyticom.de/clubs/654321/logo.png",
  "badge": "https://cdn.analyticom.de/clubs/654321/badge.png",
  "crest": "https://cdn.analyticom.de/clubs/654321/crest.png",
  "totalPlayers": 32,
  "nationalTeamPlayers": 8,
  "history": {
    "founded": 1900,
    "championships": 35,
    "cupTitles": 20,
    "europeanTitles": 6
  }
}
```

**Laravel Implementation**:
```php
public function getClub($fifaId)
{
    return $this->makeRequest('GET', "/club/{$fifaId}");
}
```

---

## 4. PLAYER ENDPOINTS

### 4.1 Get All Players
```http
GET /players
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `club` (optional): Club FIFA ID
- `competition` (optional): Wettbewerbs FIFA ID
- `country` (optional): Spieler-Nationalität
- `position` (optional): Position (GK, LB, CB, RB, DM, CM, LM, RM, AM, ST)
- `search` (optional): Spieler-Name
- `active` (optional): true/false (aktive Spieler)
- `page` (optional): Pagination (Standard: 1)
- `limit` (optional): Items pro Seite (Standard: 50)

**Response (200 OK)**:
```json
{
  "data": [
    {
      "fifaId": "240607",
      "firstName": "Manuel",
      "lastName": "Neuer",
      "fullName": "Manuel Neuer",
      "dateOfBirth": "1986-03-27",
      "birthPlace": "Gelsenkirchen",
      "nationality": "Germany",
      "nationalityCode": "DE",
      "position": "GK",
      "shirtNumber": 1,
      "height": 193,
      "weight": 84,
      "preferredFoot": "RIGHT",
      "international": {
        "caps": 127,
        "goals": 0
      },
      "club": {
        "fifaId": "654321",
        "name": "FC Bayern München",
        "shirtNumber": 1
      },
      "contract": {
        "startDate": "2021-04-01",
        "endDate": "2026-06-30",
        "years": 5,
        "status": "ACTIVE"
      },
      "statistics": {
        "appearances": 450,
        "goals": 0,
        "assists": 0,
        "yellowCards": 12,
        "redCards": 0
      },
      "photo": "https://cdn.analyticom.de/players/240607/photo.jpg",
      "active": true
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 50,
    "total": 540,
    "pages": 11
  }
}
```

**Laravel Implementation**:
```php
public function getAllPlayers($club = null, $competition = null, $position = null, $page = 1)
{
    $params = [
        'page' => $page,
        'limit' => 50,
    ];
    
    if ($club) $params['club'] = $club;
    if ($competition) $params['competition'] = $competition;
    if ($position) $params['position'] = $position;
    
    return $this->makeRequest('GET', '/players', $params);
}
```

---

### 4.2 Get Player by FIFA ID
```http
GET /player/{playerFifaId}
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `playerFifaId` (erforderlich): FIFA ID des Spielers

**Response (200 OK)**:
```json
{
  "fifaId": "240607",
  "firstName": "Manuel",
  "lastName": "Neuer",
  "fullName": "Manuel Neuer",
  "dateOfBirth": "1986-03-27",
  "birthPlace": "Gelsenkirchen",
  "nationality": "Germany",
  "nationalityCode": "DE",
  "position": "GK",
  "shirtNumber": 1,
  "height": 193,
  "weight": 84,
  "preferredFoot": "RIGHT",
  "marketValue": "€15,000,000",
  "international": {
    "caps": 127,
    "goals": 0,
    "debutDate": "2009-06-03"
  },
  "club": {
    "fifaId": "654321",
    "name": "FC Bayern München",
    "joinDate": "2011-07-01",
    "shirtNumber": 1,
    "appearances": 450
  },
  "contract": {
    "startDate": "2021-04-01",
    "endDate": "2026-06-30",
    "years": 5,
    "status": "ACTIVE",
    "salaryPerYear": "€25,000,000"
  },
  "statistics": {
    "appearances": 450,
    "goals": 0,
    "assists": 0,
    "yellowCards": 12,
    "redCards": 0,
    "cleanSheets": 189,
    "minutesPlayed": 40500
  },
  "awards": [
    {
      "award": "The Best FIFA Men's Goalkeeper",
      "year": 2020
    }
  ],
  "photo": "https://cdn.analyticom.de/players/240607/photo.jpg",
  "socialMedia": {
    "instagram": "@manuneuer",
    "twitter": "@ManuNeuer"
  },
  "active": true
}
```

**Laravel Implementation**:
```php
public function getPlayer($fifaId)
{
    return $this->makeRequest('GET', "/player/{$fifaId}");
}
```

---

## 5. MATCH ENDPOINTS

### 5.1 Get All Matches
```http
GET /matches
Authorization: Basic base64(username:password)
```

**Query Parameter** (mindestens eines erforderlich):
- `competition` (erforderlich ODER zeitraum): Wettbewerbs FIFA ID
- `fromDate` (optional): Start-Datum (YYYY-MM-DD)
- `toDate` (optional): End-Datum (YYYY-MM-DD)
- `club` (optional): Club FIFA ID (nur Spiele dieses Clubs)
- `status` (optional): SCHEDULED, LIVE, FINISHED, CANCELLED, POSTPONED, ABANDONED
- `round` (optional): Spieltag/Runde
- `page` (optional): Pagination (Standard: 1)
- `limit` (optional): Items pro Seite (Standard: 50)

**Response (200 OK)**:
```json
{
  "data": [
    {
      "fifaId": "13901536",
      "competition": {
        "fifaId": "123456",
        "name": "Bundesliga 2025",
        "round": "Matchday 1"
      },
      "homeClub": {
        "fifaId": "654321",
        "name": "FC Bayern München",
        "shortName": "FCB",
        "logo": "https://cdn.analyticom.de/clubs/654321/logo.png"
      },
      "awayClub": {
        "fifaId": "654322",
        "name": "Borussia Dortmund",
        "shortName": "BVB",
        "logo": "https://cdn.analyticom.de/clubs/654322/logo.png"
      },
      "matchDate": "2025-10-25T15:30:00Z",
      "status": "SCHEDULED",
      "stadium": {
        "name": "Allianz Arena",
        "city": "Munich",
        "capacity": 75024
      },
      "referee": {
        "fifaId": "987654",
        "firstName": "Felix",
        "lastName": "Brych",
        "nationality": "Germany"
      },
      "assistantReferees": [
        {
          "fifaId": "987655",
          "firstName": "Marco",
          "lastName": "Häcker"
        }
      ],
      "score": {
        "homeTeam": null,
        "awayTeam": null,
        "homeTeamHT": null,
        "awayTeamHT": null
      },
      "extraTime": false,
      "penalties": false,
      "attendance": null
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 50,
    "total": 306,
    "pages": 7
  }
}
```

**Laravel Implementation**:
```php
public function getAllMatches($competition = null, $fromDate = null, $toDate = null, $status = null, $page = 1)
{
    $params = [
        'page' => $page,
        'limit' => 50,
    ];
    
    if ($competition) $params['competition'] = $competition;
    if ($fromDate) $params['fromDate'] = $fromDate;
    if ($toDate) $params['toDate'] = $toDate;
    if ($status) $params['status'] = $status;
    
    return $this->makeRequest('GET', '/matches', $params);
}
```

---

### 5.2 Get Match by FIFA ID
```http
GET /match/{matchFifaId}
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `matchFifaId` (erforderlich): FIFA ID des Spiels

**Response (200 OK)**:
```json
{
  "fifaId": "13901536",
  "competition": {
    "fifaId": "123456",
    "name": "Bundesliga 2025",
    "round": "Matchday 1"
  },
  "homeClub": {
    "fifaId": "654321",
    "name": "FC Bayern München",
    "shortName": "FCB",
    "logo": "https://cdn.analyticom.de/clubs/654321/logo.png"
  },
  "awayClub": {
    "fifaId": "654322",
    "name": "Borussia Dortmund",
    "shortName": "BVB",
    "logo": "https://cdn.analyticom.de/clubs/654322/logo.png"
  },
  "matchDate": "2025-10-25T15:30:00Z",
  "status": "FINISHED",
  "stadium": {
    "name": "Allianz Arena",
    "city": "Munich",
    "capacity": 75024
  },
  "referee": {
    "fifaId": "987654",
    "firstName": "Felix",
    "lastName": "Brych",
    "nationality": "Germany"
  },
  "assistantReferees": [
    {
      "fifaId": "987655",
      "firstName": "Marco",
      "lastName": "Häcker"
    },
    {
      "fifaId": "987656",
      "firstName": "Matthias",
      "lastName": "Jöllenbeck"
    }
  ],
  "videoAssistant": {
    "fifaId": "987657",
    "firstName": "Christian",
    "lastName": "Dingert"
  },
  "score": {
    "homeTeam": 3,
    "awayTeam": 1,
    "homeTeamHT": 2,
    "awayTeamHT": 0
  },
  "scorers": [
    {
      "playerFifaId": "240701",
      "playerName": "Serge Gnabry",
      "team": "HOME",
      "minute": 12,
      "penalty": false,
      "ownGoal": false
    },
    {
      "playerFifaId": "240702",
      "playerName": "Benjamin Pavard",
      "team": "HOME",
      "minute": 34,
      "penalty": false,
      "ownGoal": false
    },
    {
      "playerFifaId": "240703",
      "playerName": "Sadio Mané",
      "team": "HOME",
      "minute": 67,
      "penalty": false,
      "ownGoal": false
    },
    {
      "playerFifaId": "240704",
      "playerName": "Marco Reus",
      "team": "AWAY",
      "minute": 88,
      "penalty": false,
      "ownGoal": false
    }
  ],
  "extraTime": false,
  "penalties": false,
  "attendance": 75000,
  "lastUpdateDateTime": "2025-10-25T17:45:23Z"
}
```

**Laravel Implementation**:
```php
public function getMatch($fifaId)
{
    return $this->makeRequest('GET', "/match/{$fifaId}");
}
```

---

### 5.3 Get Match Last Update DateTime
```http
GET /match/{matchFifaId}/lastUpdateDateTime
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `matchFifaId` (erforderlich): FIFA ID des Spiels

**Response (200 OK)**:
```json
{
  "matchFifaId": "13901536",
  "lastUpdateDateTime": "2025-10-25T17:45:23Z",
  "status": "FINISHED",
  "dataVersion": 2,
  "changesSince": "2025-10-25T17:40:00Z"
}
```

**Verwendungsfall**: Überprüfe periodisch, ob eine Aktualisierung erforderlich ist, anstatt ständig alle Match-Daten zu laden.

**Laravel Implementation**:
```php
public function getMatchLastUpdate($fifaId)
{
    $response = $this->makeRequest('GET', "/match/{$fifaId}/lastUpdateDateTime");
    
    $match = Match::where('comet_fifa_id', $fifaId)->first();
    
    if ($match && $match->last_comet_update) {
        $remoteUpdate = strtotime($response['lastUpdateDateTime']);
        $localUpdate = strtotime($match->last_comet_update);
        
        if ($remoteUpdate > $localUpdate) {
            $this->syncMatchDetails($fifaId);
        }
    }
    
    return $response;
}
```

---

### 5.4 Get Live Match Events
```http
GET /match/{matchFifaId}/latest/events
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `matchFifaId` (erforderlich - Path): FIFA Match ID
- `seconds` (erforderlich - Query): Events der letzten N Sekunden

**Response (200 OK)**:
```json
{
  "matchFifaId": "13901536",
  "events": [
    {
      "id": 13901536,
      "matchPhase": "FIRST_HALF",
      "minute": 12,
      "second": 30,
      "stoppageTime": null,
      "eventType": "GOAL",
      "eventDetailType": null,
      "playerFifaId": 240701,
      "playerFifaId2": null,
      "matchTeam": "HOME",
      "penaltyOrder": null,
      "personName": "Serge Gnabry",
      "localPersonName": null,
      "personName2": null,
      "localPersonName2": null,
      "timestamp": "2025-10-25T15:42:30Z"
    },
    {
      "id": 13901537,
      "matchPhase": "FIRST_HALF",
      "minute": 34,
      "second": 15,
      "stoppageTime": null,
      "eventType": "YELLOW_CARD",
      "eventDetailType": null,
      "playerFifaId": 240704,
      "playerFifaId2": null,
      "matchTeam": "AWAY",
      "penaltyOrder": null,
      "personName": "Dan-Axel Zagadou",
      "localPersonName": null,
      "personName2": null,
      "localPersonName2": null,
      "timestamp": "2025-10-25T15:59:15Z"
    },
    {
      "id": 13901538,
      "matchPhase": "FIRST_HALF",
      "minute": 38,
      "second": 45,
      "stoppageTime": null,
      "eventType": "SUBSTITUTION",
      "eventDetailType": null,
      "playerFifaId": 240705,
      "playerFifaId2": 240706,
      "matchTeam": "AWAY",
      "penaltyOrder": null,
      "personName": "Out: Marco Reus",
      "localPersonName": null,
      "personName2": "In: Karim Adeyemi",
      "localPersonName2": null,
      "timestamp": "2025-10-25T16:03:45Z"
    }
  ],
  "eventCount": 3,
  "querySeconds": 120,
  "queryTimestamp": "2025-10-25T16:05:00Z"
}
```

**Event Types**:
- `GOAL` - Tor
- `YELLOW_CARD` - Gelbe Karte
- `RED_CARD` - Rote Karte
- `SUBSTITUTION` - Spielerwechsel
- `PENALTY_GOAL` - Elfmeter Tor
- `OWN_GOAL` - Eigentor
- `VAR_REVIEW` - VAR Überprüfung
- `INJURY` - Verletzung
- `CORNER` - Eckstoß
- `FREE_KICK` - Freistoß
- `OFFSIDEA` - Abseits

**Deleted Event (NULL-Payload)**:
```json
{
  "id": 13901587,
  "matchPhase": null,
  "minute": null,
  "second": null,
  "stoppageTime": null,
  "eventType": null,
  "eventDetailType": null,
  "playerFifaId": null,
  "playerFifaId2": null,
  "matchTeam": null,
  "penaltyOrder": null,
  "personName": null,
  "localPersonName": null,
  "personName2": null,
  "localPersonName2": null
}
```

**Laravel Implementation**:
```php
public function getLiveEvents($matchFifaId, $secondsSince = 60)
{
    $response = $this->makeRequest('GET', "/match/{$matchFifaId}/latest/events", [
        'seconds' => $secondsSince
    ]);
    
    if (!$response->successful()) {
        return [];
    }
    
    $events = $response->json()['events'] ?? [];
    
    // Filtere nur aktive Events (nicht gelöschte)
    $activeEvents = array_filter($events, function($event) {
        return $event['eventType'] !== null;
    });
    
    // Speichere Events in DB
    foreach ($activeEvents as $event) {
        MatchEvent::updateOrCreate(
            ['comet_event_id' => $event['id']],
            [
                'match_comet_id' => $matchFifaId,
                'event_type' => $event['eventType'],
                'minute' => $event['minute'],
                'player_comet_id' => $event['playerFifaId'],
                'team' => $event['matchTeam'],
                'timestamp' => $event['timestamp'],
            ]
        );
    }
    
    return $activeEvents;
}
```

---

## 6. DISCIPLINARY ENDPOINTS

### ⚠️ Nur mit ROLE_DISCIPLINARY_WEB verfügbar

### 6.1 Get Competition Cases
```http
GET /competition/{competitionFifaId}/cases
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `competitionFifaId` (erforderlich): FIFA Competition ID

**Query Parameter**:
- `status` (optional): OPEN, CLOSED, APPEALED
- `page` (optional): Pagination (Standard: 1)

**Response (200 OK)**:
```json
{
  "data": [
    {
      "caseFifaId": "CASE-123456",
      "competition": "Bundesliga 2025",
      "offenderType": "PLAYER",
      "offenderPersonFifaId": "240704",
      "offenderName": "Marco Reus",
      "offenderClub": "Borussia Dortmund",
      "incident": "Violent conduct",
      "match": {
        "fifaId": "13901536",
        "homeClub": "FC Bayern München",
        "awayClub": "Borussia Dortmund",
        "date": "2025-10-25"
      },
      "reportedDate": "2025-10-25",
      "status": "OPEN",
      "severity": "HIGH"
    }
  ]
}
```

---

### 6.2 Get Match Cases
```http
GET /match/{matchFifaId}/cases
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
{
  "data": [
    {
      "caseFifaId": "CASE-123456",
      "offenderType": "PLAYER",
      "offenderPersonFifaId": "240704",
      "offenderName": "Marco Reus",
      "incident": "Violent conduct",
      "reportedDate": "2025-10-25",
      "status": "OPEN",
      "severity": "HIGH"
    }
  ]
}
```

---

### 6.3 Get Case by FIFA ID
```http
GET /case/{caseFifaId}
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
{
  "caseFifaId": "CASE-123456",
  "competition": "Bundesliga 2025",
  "offenderType": "PLAYER",
  "offenderPersonFifaId": "240704",
  "offenderName": "Marco Reus",
  "offenderClub": "Borussia Dortmund",
  "incident": "Violent conduct",
  "description": "Player received red card for violent conduct in 87th minute",
  "match": {
    "fifaId": "13901536",
    "homeClub": "FC Bayern München",
    "awayClub": "Borussia Dortmund",
    "date": "2025-10-25"
  },
  "reportedDate": "2025-10-25",
  "hearingDate": "2025-10-28",
  "status": "OPEN",
  "severity": "HIGH",
  "sanctions": [
    {
      "sanctionId": "SANC-123456",
      "type": "SUSPENSION",
      "duration": "3 matches",
      "fineAmount": "€10,000"
    }
  ]
}
```

---

### 6.4 Get Case Sanctions
```http
GET /case/{caseFifaId}/sanctions
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
{
  "data": [
    {
      "sanctionId": "SANC-123456",
      "type": "SUSPENSION",
      "duration": "3 matches",
      "startDate": "2025-10-28",
      "endDate": "2025-11-11",
      "fineAmount": "€10,000",
      "imposed": "2025-10-27",
      "status": "ACTIVE"
    }
  ]
}
```

---

### 6.5 Get Person Cases
```http
GET /case/person/{offenderPersonFifaId}
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
{
  "data": [
    {
      "caseFifaId": "CASE-123456",
      "offenderName": "Marco Reus",
      "incident": "Violent conduct",
      "date": "2025-10-25",
      "status": "CLOSED",
      "sanctions": 1
    }
  ]
}
```

---

### 6.6 Get Organisation Cases
```http
GET /case/organisation/{offenderOrganisationFifaId}
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
{
  "data": [
    {
      "caseFifaId": "CASE-789012",
      "offenderClub": "Borussia Dortmund",
      "incident": "Improper conduct of team",
      "date": "2025-10-25",
      "status": "CLOSED",
      "fineAmount": "€50,000"
    }
  ]
}
```

---

### 6.7 Get Sanction by ID
```http
GET /sanction/{sanctionId}
Authorization: Basic base64(username:password)
```

**Response (200 OK)**:
```json
{
  "sanctionId": "SANC-123456",
  "case": {
    "caseFifaId": "CASE-123456",
    "offenderName": "Marco Reus"
  },
  "type": "SUSPENSION",
  "duration": "3 matches",
  "startDate": "2025-10-28",
  "endDate": "2025-11-11",
  "fineAmount": "€10,000",
  "imposed": "2025-10-27",
  "status": "ACTIVE",
  "appeal": null
}
```

---

## 7. AD-HOC REPORTS ENDPOINTS

### 7.1 Generate Ad-Hoc Report
```http
GET /adHocReport/{reportId}
Authorization: Basic base64(username:password)
```

**Path Parameter**:
- `reportId` (erforderlich): Report-Identifier aus COMET

**Query Parameter**:
- `parameters` (erforderlich): Komplexe Parameter (vom COMET generiert)

**Beispiele für Reports**:

```
Spielerverträge nach Startdatum:
/adHocReport/CONTRACT_BY_DATE?parameters=competition:123&fromDate:2025-01-01&toDate:2025-12-31

Registrierungen pro Club:
/adHocReport/REGISTRATIONS_BY_CLUB?parameters=competition:123&season:2025

Fair Play Report:
/adHocReport/FAIR_PLAY?parameters=competition:123&includeCards:true&includeSuspensions:true

Torschützen-Tabelle:
/adHocReport/TOP_SCORERS?parameters=competition:123&limit:20

Spieler-Statistiken:
/adHocReport/PLAYER_STATS?parameters=competition:123&club:654321&season:2025
```

**Response (200 OK)**:
```json
{
  "reportId": "CONTRACT_BY_DATE",
  "executedAt": "2025-10-23T16:30:00Z",
  "totalRecords": 150,
  "columns": [
    "PlayerName",
    "Club",
    "ContractStart",
    "ContractEnd",
    "Years",
    "Status"
  ],
  "data": [
    {
      "PlayerName": "Manuel Neuer",
      "Club": "FC Bayern München",
      "ContractStart": "2021-04-01",
      "ContractEnd": "2026-06-30",
      "Years": 5,
      "Status": "ACTIVE"
    },
    {
      "PlayerName": "Serge Gnabry",
      "Club": "FC Bayern München",
      "ContractStart": "2017-11-01",
      "ContractEnd": "2027-06-30",
      "Years": 9,
      "Status": "ACTIVE"
    }
  ]
}
```

**Laravel Implementation**:
```php
public function generateReport($reportId, $parameters)
{
    $url = "/adHocReport/{$reportId}?parameters=" . urlencode($parameters);
    return $this->makeRequest('GET', $url);
}

// Verwendung:
$params = 'competition:123&season:2025&club:654321';
$report = $this->generateReport('PLAYER_STATS', $params);
```

---

## 8. BINARY/IMAGE ENDPOINTS

### 8.1 Get Club Logo
```http
GET /club/{clubFifaId}/logo
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `size` (optional): SMALL (64x64), MEDIUM (256x256), LARGE (512x512), ORIGINAL
- `format` (optional): PNG, JPG, WEBP

**Response**: Binary Image File

---

### 8.2 Get Player Photo
```http
GET /player/{playerFifaId}/photo
Authorization: Basic base64(username:password)
```

**Query Parameter**:
- `size` (optional): SMALL (100x100), MEDIUM (250x250), LARGE (500x500), ORIGINAL
- `format` (optional): PNG, JPG, WEBP

**Response**: Binary Image File

---

### 8.3 Get Club Badge
```http
GET /club/{clubFifaId}/badge
Authorization: Basic base64(username:password)
```

**Response**: Binary Badge Image

---

## 9. ERROR HANDLING & STATUS CODES

### HTTP Status Codes

| Code | Bedeutung | Beispiel |
|------|-----------|----------|
| 200 | OK | Erfolgreiches Request |
| 400 | Bad Request | Fehlende/ungültige Parameter |
| 401 | Unauthorized | Authentifizierung erforderlich |
| 403 | Forbidden | Keine Berechtigung (falsche Rolle) |
| 404 | Not Found | Ressource nicht vorhanden |
| 429 | Too Many Requests | Rate Limit überschritten |
| 500 | Internal Server Error | Server-Fehler |
| 503 | Service Unavailable | Service nicht erreichbar |

### Error Response Format

```json
{
  "error": {
    "code": "INVALID_PARAMETERS",
    "message": "Invalid competition id",
    "timestamp": "2025-10-23T16:30:00Z",
    "details": {
      "parameter": "competitionFifaId",
      "value": "invalid_value",
      "expected": "Valid FIFA ID"
    }
  }
}
```

### Rate Limiting Response

```http
HTTP/1.1 429 Too Many Requests
Retry-After: 5
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1698076205
```

```json
{
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Please retry after 5 seconds.",
    "retryAfter": 5,
    "resetTime": "2025-10-23T16:30:05Z"
  }
}
```

---

## 10. AUTHENTICATION TROUBLESHOOTING

### Problem: 401 Unauthorized

**Lösungen**:
1. Credentials prüfen (Username/Password korrekt?)
2. Base64 Encoding richtig? (`base64_encode('username:password')`)
3. Header richtig? (`Authorization: Basic ...`)

### Problem: 403 Forbidden

**Lösungen**:
1. User-Rolle prüfen (benötigte Rolle für Endpoint?)
2. Account aktiv?
3. Subscription Status prüfen

### Problem: 429 Too Many Requests

**Lösungen**:
1. Throttling/Rate Limits implementieren
2. `/throttling/info` aufrufen für aktuelle Limits
3. Exponential Backoff implementieren
4. Caching verwenden

---

## 11. BEST PRACTICES FÜR ENDPOINT NUTZUNG

### ✅ Richtig

```php
// 1. Caching für wiederholte Requests
$clubs = Cache::remember("comet_clubs_{$competition}", 24*60, function() use ($competition) {
    return $this->makeRequest('GET', '/clubs', ['competition' => $competition]);
});

// 2. Batch Requests wo möglich
foreach ($playerIds as $id) {
    Cache::remember("comet_player_{$id}", 12*60, function() use ($id) {
        return $this->getPlayer($id);
    });
}

// 3. Nur nötige Felder anfordern
$matches = $this->getAllMatches($competition, null, null, 'FINISHED');

// 4. Pagination für große Datenmengen
for ($page = 1; $page <= $totalPages; $page++) {
    $players = $this->getAllPlayers(null, null, null, $page);
    // Process...
}

// 5. Live Events mit passenden Intervallen prüfen
Schedule::command('comet:check-live-events')
    ->everyMinute()
    ->between('14:00', '18:00');
```

### ❌ Vermeiden

```php
// FALSCH: Direkte API-Calls ohne Caching
foreach ($request->clubs as $clubId) {
    $club = $this->getClub($clubId);  // Zu viele Requests!
    view()->with('club', $club);
}

// FALSCH: Zu häufige Polling
Schedule::command('comet:sync-all')->everySecond();  // Rate Limit!

// FALSCH: Credentials hardcoden
$auth = 'Basic ' . base64_encode('admin@example.com:password123');

// FALSCH: Keine Fehlerbehandlung
$response = $this->makeRequest('GET', '/endpoint');
$data = $response->json();  // Was wenn Request fehlschlägt?
```

---

## 12. HÄUFIG GENUTZTE ENDPUNKTE - QUICK REFERENCE

| Use Case | Endpoint | Cache TTL |
|----------|----------|-----------|
| Alle Clubs in Wettbewerb | `GET /clubs?competition={id}` | 24h |
| Spiel-Details | `GET /match/{matchId}` | 1h |
| Live-Events | `GET /match/{matchId}/latest/events?seconds=60` | 1m |
| Match-Update prüfen | `GET /match/{matchId}/lastUpdateDateTime` | 5m |
| Spieler eines Clubs | `GET /players?club={id}` | 12h |
| Spieler-Details | `GET /player/{playerId}` | 24h |
| Spiele zeitraum | `GET /matches?competition={id}&fromDate=...&toDate=...` | 1h |

---

**Letzte Aktualisierung**: October 23, 2025  
**API Version**: 2.0  
**Status**: ✅ Production Ready  
**Swagger UI**: https://api-hns.analyticom.de/swagger-ui.html
