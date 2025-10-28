# COMET LIVE API - Real-Time Daten Integration

**Version**: 1.0  
**Datum**: October 23, 2025  
**Bereich**: Real-Time Data Access, Frontend Integration, Live Applications

---

## 📋 Inhaltsverzeichnis

1. [Überblick](#überblick)
2. [Architektur & Infrastruktur](#architektur--infrastruktur)
3. [API Key Management](#api-key-management)
4. [Zugriff auf COMET LIVE API](#zugriff-auf-comet-live-api)
5. [Authentifizierung](#authentifizierung)
6. [Endpunkte Dokumentation](#endpunkte-dokumentation)
7. [Request/Response Format](#requestresponse-format)
8. [Fehlerbehandlung](#fehlerbehandlung)
9. [Performance & Caching](#performance--caching)
10. [Laravel Integration](#laravel-integration)
11. [Best Practices](#best-practices)

---

## 1. Überblick

### Was ist COMET LIVE API?

Die **COMET LIVE API** ist ein spezialisiertes Backend-System, das Echtzeit-Zugriff auf COMET Wettbewerbsdaten ermöglicht. Sie ist ideal für:

- **Live-Anwendungen** (Mobile Apps, Websites)
- **Echtzeit-Updates** (Score-Updates, Match-Events)
- **Große Datenmengen** (Viele gleichzeitige Zugriffe)
- **Third-Party Integration** (Externe Systeme, Websites)

### Unterschied: COMET REST API vs. COMET LIVE API

| Merkmal | REST API | LIVE API |
|---------|----------|----------|
| **Verwendung** | Datensynchronisation | Echtzeit-Zugriff |
| **Infrastruktur** | Integriert in COMET | Separates Backend |
| **Caching** | Manuell konfigurierbar | Automatisch optimiert |
| **Performance** | Abhängig von COMET-Last | Unabhängig, hochperformant |
| **Traffic-Volumen** | Limitiert | Massive Lasten möglich |
| **Authentifizierung** | Username/Password | API Key |
| **Synchronisation** | Erforderlich | Nicht erforderlich |
| **Use Case** | Backend-Systeme | Frontend-Apps |

### Warum COMET LIVE API?

```
┌─────────────────────────────────────────────────────────────┐
│                  COMET LIVE API Vorteile                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ ✅ Keine Datensynchronisation erforderlich                  │
│    └─ Direkte Frontend-zu-API Verbindung möglich           │
│                                                             │
│ ✅ Hochoptimierte Performance                               │
│    └─ Caching & Cache-Eviction Techniken                   │
│    └─ Kann massive Traffic-Volumen verarbeiten             │
│                                                             │
│ ✅ Separate Infrastruktur                                   │
│    └─ Keine Auswirkung auf COMET Performance               │
│    └─ Unabhängige Skalierbarkeit                           │
│                                                             │
│ ✅ Echtzeit-Updates                                         │
│    └─ Live Match Events                                     │
│    └─ Sofortige Score-Updates                              │
│                                                             │
│ ✅ Einfache Integration                                     │
│    └─ Frontend-Developer nur für UI zuständig              │
│    └─ Keine Backend-Entwicklung für Daten erforderlich     │
│                                                             │
│ ✅ Multi-Tenant Support                                     │
│    └─ Mehrere Verbände/Organisationen                       │
│    └─ Automatische Daten-Isolation                          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Architektur & Infrastruktur

### Systemarchitektur

```
┌──────────────────────────────────────────────────────────────┐
│                    COMET SYSTEM                              │
│  (Competition Management, Administration, Data Storage)      │
└────────────────────────────┬─────────────────────────────────┘
                             │
                    Data Synchronisation
                             │
                             ▼
┌──────────────────────────────────────────────────────────────┐
│                  COMET LIVE API BACKEND                      │
│  (Separated Infrastructure, Caching, Cache Eviction)         │
│                                                              │
│  ├─ API Gateway                                             │
│  ├─ Cache Layer (Redis)                                     │
│  ├─ Load Balancer                                           │
│  └─ Database Replicas                                       │
└──────────────────────┬──────────────────────────────────────┘
                       │
        ┌──────────────┼──────────────┐
        │              │              │
        ▼              ▼              ▼
   ┌─────────┐  ┌─────────┐  ┌─────────┐
   │ Frontend│  │  Mobile │  │ Third-  │
   │ Website │  │   Apps  │  │  Party  │
   └─────────┘  └─────────┘  └─────────┘

Jede Anwendung kann direkt zur LIVE API verbinden
Keine lokale Datenbank-Synchronisation erforderlich!
```

### Caching-Strategie

Die LIVE API verwendet fortgeschrittene Caching-Techniken:

```
Datenfluss mit Caching:

1. Request kommt an
   ├─ Prüfe Cache (Redis)
   └─ Cache Hit? → Sofort Response (< 10ms)

2. Cache Miss
   ├─ Query Database Replica
   ├─ Formatiere Response
   ├─ Speichere in Cache
   └─ Sende an Client

3. Cache Eviction
   ├─ Daten ändern sich in COMET
   ├─ Event triggert Cache-Invalidation
   ├─ Nächster Request holt aktuelle Daten
   └─ Client empfängt Update
```

### Multi-Tenant Isolation

```
COMET LIVE API Multi-Tenant Architektur:

┌─────────────────────────────────────────────────────┐
│          COMET LIVE API (Shared Infrastructure)     │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │  Tenant 1    │  │  Tenant 2    │  │Tenant N  │ │
│  │  (DFB)       │  │  (KNVB)      │  │ (FFF)    │ │
│  ├──────────────┤  ├──────────────┤  ├──────────┤ │
│  │ API Key 1    │  │ API Key 2    │  │API Key N │ │
│  │ Org ID 1     │  │ Org ID 2     │  │ Org ID N │ │
│  │ Data Isolated│  │ Data Isolated│  │Isolated  │ │
│  └──────────────┘  └──────────────┘  └──────────┘ │
│                                                     │
└─────────────────────────────────────────────────────┘

Jeder Tenant hat:
- Separate API Keys
- Eigene Daten (Query-Level Isolation)
- Unabhängige Rate Limits
- Separate Authentifizierung
```

---

## 3. API Key Management

### API Key erstellen

Die API Keys werden vom **COMET Superuser** (Administrator) der Organisation erstellt.

**Prozess:**
1. Superuser navigiert zu: Admin → API Keys
2. Klickt auf "New API Key"
3. Wählt Benutzer/Applikation
4. Setzt Permissions
5. Speichert den Key
6. System sendet Email mit:
   - Swagger URL
   - API Key
   - Organization ID
   - Umgebung (Testing/Production)

### API Key Typen

```
┌─────────────────────────────────────────────┐
│         API KEY PERMISSION TYPES            │
├─────────────────────────────────────────────┤
│                                             │
│ 1. UNRESTRICTED (Admin)                     │
│    └─ Zugriff auf alle Daten der Org       │
│                                             │
│ 2. ORGANIZATION_RESTRICTED                  │
│    └─ Linked zu Organization (League)      │
│    └─ Nur Daten dieser Org                 │
│    └─ organizationIdFilter erforderlich    │
│                                             │
│ 3. CLUB_RESTRICTED                          │
│    └─ Linked zu Club (Team)                │
│    └─ Nur Daten dieses Clubs               │
│    └─ teamIdFilter erforderlich            │
│                                             │
│ 4. APPLICATION_SPECIFIC                     │
│    └─ Für dritte Anwendungen               │
│    └─ Begrenzte Endpoints                  │
│    └─ Rate Limits gesetzt                  │
│                                             │
└─────────────────────────────────────────────┘
```

### API Key Best Practices

```
✅ DO's:
- API Key sicher speichern (Environment Variablen)
- Key rotieren regelmäßig
- Separate Keys pro Umgebung (Testing/Production)
- Key-Zugriffe überwachen
- Rate Limits respektieren

❌ DON'Ts:
- API Key in Quellcode hardcoden
- Key in Version Control committen
- Key in Logs loggen
- Key an Dritte weitergeben
- Alte/ungenutzte Keys nicht löschen
```

---

## 4. Zugriff auf COMET LIVE API

### Swagger Documentation URLs

**Demo Environment:**
```
https://api-<tenant>-demo.analyticom.de/swagger-ui.html?urls.primaryName=live
```

**Production Environment:**
```
https://api-<tenant>.analyticom.de/swagger-ui.html?urls.primaryName=live
```

**Ersetze `<tenant>` mit dem COMET Organisationskürzel**

Beispiele:
```
Demo DFB:           https://api-dfb-demo.analyticom.de/swagger-ui.html?urls.primaryName=live
Production DFB:     https://api-dfb.analyticom.de/swagger-ui.html?urls.primaryName=live

Demo KNVB:          https://api-knvb-demo.analyticom.de/swagger-ui.html?urls.primaryName=live
Production KNVB:    https://api-knvb.analyticom.de/swagger-ui.html?urls.primaryName=live

Demo FFF:           https://api-fff-demo.analyticom.de/swagger-ui.html?urls.primaryName=live
Production FFF:     https://api-fff.analyticom.de/swagger-ui.html?urls.primaryName=live
```

### API Documentation Access

**Step 1: Öffne die Swagger URL**
```
Browser öffnen → Swagger URL eingeben
```

**Step 2: Authentifizierung**
```
Swagger fordert auf:
- API_KEY eingeben
- Click "Authorize"
```

**Step 3: Endpoints erkunden**
```
Liste aller verfügbaren Endpoints
Klicke auf einen Endpoint zum Expand
```

**Step 4: Endpoint spezifikation anschauen**
```
Sichtbar:
- Endpoint Beschreibung
- Input Parameter Spezifikation
- Beispiel Server Response
- "Try it out" Button (rot markiert)
```

---

## 5. Authentifizierung

### API Key Header

Alle Requests benötigen den API Key im Header:

```http
GET /api/live/competitions/123456
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json
Accept: application/json
X-API-Version: 1.0
```

### Tenant Parameter

Für Multi-Tenant Umgebungen ist `tenant` immer erforderlich:

```http
GET /api/live/competitions/123456?tenant=dfb
Authorization: Bearer YOUR_API_KEY
```

### Filter Parameter (Conditional)

**Wenn API Key mit Club verlinkt ist:**
```http
GET /api/live/matches?tenant=dfb&teamIdFilter=654321
Authorization: Bearer YOUR_API_KEY
```
- `teamIdFilter` ist **erforderlich**
- Nur Matches dieses Clubs werden zurückgegeben

**Wenn API Key mit Organization verlinkt ist:**
```http
GET /api/live/competitions?tenant=dfb&organizationIdFilter=123456
Authorization: Bearer YOUR_API_KEY
```
- `organizationIdFilter` ist **erforderlich**
- Nur Daten dieser Organisation werden zurückgegeben

---

## 6. Endpunkte Dokumentation

### 6.1 Competitions Endpoints

#### List All Competitions
```http
GET /api/live/competitions
Authorization: Bearer API_KEY
Parameters:
  - tenant (mandatory): Organisationskürzel
  - organizationIdFilter (conditional): Org Filter
  - season (optional): z.B. "2025/2026"
  - page (optional): Pagination
  - limit (optional): Items pro Seite
```

**Response:**
```json
{
  "data": [
    {
      "fifaId": "123456",
      "comet_id": "abc-123-def",
      "name": "Bundesliga 2025",
      "season": "2025/2026",
      "type": "LEAGUE",
      "status": "ACTIVE",
      "startDate": 1692144000,
      "endDate": 1716825600,
      "numberOfMatches": 306,
      "numberOfTeams": 18,
      "lastUpdateTimestamp": 1698076800
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 50,
    "total": 5,
    "totalPages": 1
  }
}
```

#### Get Competition Details
```http
GET /api/live/competitions/{competitionId}
Authorization: Bearer API_KEY
Parameters:
  - competitionId (path): COMET Competition ID
  - tenant (mandatory): Organisationskürzel
```

**Response:**
```json
{
  "fifaId": "123456",
  "comet_id": "abc-123-def",
  "name": "Bundesliga 2025",
  "season": "2025/2026",
  "type": "LEAGUE",
  "status": "ACTIVE",
  "startDate": 1692144000,
  "endDate": 1716825600,
  "numberOfMatches": 306,
  "numberOfTeams": 18,
  "matchesPlayed": 15,
  "matchesRemaining": 291,
  "standings": true,
  "statistics": true,
  "lastUpdateTimestamp": 1698076800
}
```

---

### 6.2 Matches Endpoints

#### List Matches
```http
GET /api/live/matches
Authorization: Bearer API_KEY
Parameters:
  - tenant (mandatory): Organisationskürzel
  - competitionId (required): COMET Competition ID
  - teamIdFilter (conditional): Club Filter
  - organizationIdFilter (conditional): Org Filter
  - status (optional): SCHEDULED, LIVE, FINISHED, CANCELLED
  - fromDate (optional): Timestamp
  - toDate (optional): Timestamp
  - page (optional): Pagination
```

**Response:**
```json
{
  "data": [
    {
      "matchId": "13901536",
      "comet_id": "match-789",
      "competitionId": "123456",
      "homeTeam": {
        "teamId": "654321",
        "name": "FC Bayern München",
        "shortName": "FCB",
        "logo": "https://cdn.analyticom.de/clubs/654321/logo.png"
      },
      "awayTeam": {
        "teamId": "654322",
        "name": "Borussia Dortmund",
        "shortName": "BVB",
        "logo": "https://cdn.analyticom.de/clubs/654322/logo.png"
      },
      "status": "FINISHED",
      "matchDateTime": 1729869000,
      "score": {
        "home": 3,
        "away": 1,
        "homeHT": 2,
        "awayHT": 0
      },
      "venue": {
        "name": "Allianz Arena",
        "city": "Munich"
      },
      "lastUpdateTimestamp": 1729872000
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 50,
    "total": 306,
    "totalPages": 7
  }
}
```

#### Get Match Details
```http
GET /api/live/matches/{matchId}
Authorization: Bearer API_KEY
Parameters:
  - matchId (path): COMET Match ID
  - tenant (mandatory): Organisationskürzel
```

**Response:**
```json
{
  "matchId": "13901536",
  "comet_id": "match-789",
  "competitionId": "123456",
  "homeTeam": {
    "teamId": "654321",
    "name": "FC Bayern München",
    "shortName": "FCB",
    "logo": "https://cdn.analyticom.de/clubs/654321/logo.png",
    "lineup": [
      {
        "playerId": "240607",
        "name": "Manuel Neuer",
        "position": "GK",
        "shirtNumber": 1,
        "captain": true,
        "status": "PLAYING"
      }
    ]
  },
  "awayTeam": {
    "teamId": "654322",
    "name": "Borussia Dortmund",
    "shortName": "BVB",
    "logo": "https://cdn.analyticom.de/clubs/654322/logo.png",
    "lineup": [
      {
        "playerId": "240704",
        "name": "Marco Reus",
        "position": "FW",
        "shirtNumber": 11,
        "captain": false,
        "status": "PLAYING"
      }
    ]
  },
  "status": "LIVE",
  "matchDateTime": 1729869000,
  "currentMinute": 67,
  "matchPhase": "SECOND_HALF",
  "score": {
    "home": 2,
    "away": 1,
    "homeHT": 1,
    "awayHT": 0
  },
  "events": [
    {
      "eventId": "ev-1",
      "eventType": "GOAL",
      "minute": 12,
      "second": 30,
      "team": "HOME",
      "player": {
        "playerId": "240701",
        "name": "Serge Gnabry"
      },
      "timestamp": 1729869780
    }
  ],
  "referee": {
    "refereeId": "987654",
    "name": "Felix Brych",
    "nationality": "Germany"
  },
  "venue": {
    "name": "Allianz Arena",
    "city": "Munich",
    "capacity": 75024
  },
  "lastUpdateTimestamp": 1729872000
}
```

---

### 6.3 Live Events Streaming

#### Get Live Match Events
```http
GET /api/live/matches/{matchId}/events
Authorization: Bearer API_KEY
Parameters:
  - matchId (path): COMET Match ID
  - tenant (mandatory): Organisationskürzel
  - eventType (optional): GOAL, CARD, SUBSTITUTION, etc.
  - page (optional): Pagination
```

**Response:**
```json
{
  "matchId": "13901536",
  "currentStatus": "LIVE",
  "currentMinute": 67,
  "events": [
    {
      "eventId": "ev-1",
      "eventType": "GOAL",
      "minute": 12,
      "second": 30,
      "matchPhase": "FIRST_HALF",
      "team": "HOME",
      "player": {
        "playerId": "240701",
        "name": "Serge Gnabry",
        "shirtNumber": 7
      },
      "assistBy": {
        "playerId": "240702",
        "name": "Benjamin Pavard"
      },
      "timestamp": 1729869780
    },
    {
      "eventId": "ev-2",
      "eventType": "YELLOW_CARD",
      "minute": 34,
      "matchPhase": "FIRST_HALF",
      "team": "AWAY",
      "player": {
        "playerId": "240704",
        "name": "Dan-Axel Zagadou"
      },
      "timestamp": 1729870440
    },
    {
      "eventId": "ev-3",
      "eventType": "SUBSTITUTION",
      "minute": 45,
      "matchPhase": "HALF_TIME",
      "team": "AWAY",
      "playerOut": {
        "playerId": "240705",
        "name": "Emre Can"
      },
      "playerIn": {
        "playerId": "240706",
        "name": "Karim Adeyemi"
      },
      "timestamp": 1729872000
    }
  ],
  "totalEvents": 3,
  "lastUpdateTimestamp": 1729872000
}
```

---

### 6.4 Teams/Clubs Endpoints

#### List Teams
```http
GET /api/live/teams
Authorization: Bearer API_KEY
Parameters:
  - tenant (mandatory): Organisationskürzel
  - competitionId (required): COMET Competition ID
  - organizationIdFilter (conditional): Org Filter
  - teamIdFilter (conditional): Team Filter
```

**Response:**
```json
{
  "data": [
    {
      "teamId": "654321",
      "comet_id": "team-123",
      "name": "FC Bayern München",
      "shortName": "FCB",
      "logo": "https://cdn.analyticom.de/clubs/654321/logo.png",
      "city": "Munich",
      "country": "Germany",
      "founded": 1900,
      "stadium": "Allianz Arena",
      "stadiumCapacity": 75024,
      "coach": "Thomas Tuchel",
      "website": "https://www.fcbayern.com",
      "colors": {
        "primary": "#DC143C",
        "secondary": "#FFFFFF"
      }
    }
  ]
}
```

#### Get Team Details
```http
GET /api/live/teams/{teamId}
Authorization: Bearer API_KEY
Parameters:
  - teamId (path): COMET Team ID
  - tenant (mandatory): Organisationskürzel
  - includeStats (optional): true/false
  - includeRoster (optional): true/false
```

**Response:**
```json
{
  "teamId": "654321",
  "comet_id": "team-123",
  "name": "FC Bayern München",
  "shortName": "FCB",
  "logo": "https://cdn.analyticom.de/clubs/654321/logo.png",
  "city": "Munich",
  "country": "Germany",
  "founded": 1900,
  "stadium": "Allianz Arena",
  "stadiumCapacity": 75024,
  "coach": "Thomas Tuchel",
  "website": "https://www.fcbayern.com",
  "colors": {
    "primary": "#DC143C",
    "secondary": "#FFFFFF"
  },
  "statistics": {
    "matchesPlayed": 15,
    "wins": 12,
    "draws": 2,
    "losses": 1,
    "goalsFor": 45,
    "goalsAgainst": 12,
    "goalDifference": 33,
    "points": 38
  },
  "roster": [
    {
      "playerId": "240607",
      "name": "Manuel Neuer",
      "position": "GK",
      "shirtNumber": 1,
      "dateOfBirth": "1986-03-27",
      "nationality": "Germany"
    }
  ]
}
```

---

### 6.5 Players Endpoints

#### List Players
```http
GET /api/live/players
Authorization: Bearer API_KEY
Parameters:
  - tenant (mandatory): Organisationskürzel
  - teamId (required): COMET Team ID
  - competitionId (optional): COMET Competition ID
  - position (optional): GK, DEF, MID, FWD
```

**Response:**
```json
{
  "data": [
    {
      "playerId": "240607",
      "comet_id": "player-456",
      "firstName": "Manuel",
      "lastName": "Neuer",
      "fullName": "Manuel Neuer",
      "position": "GK",
      "shirtNumber": 1,
      "dateOfBirth": "1986-03-27",
      "nationality": "Germany",
      "height": 193,
      "weight": 84,
      "status": "ACTIVE",
      "statistics": {
        "appearances": 450,
        "cleanSheets": 189,
        "minutesPlayed": 40500
      }
    }
  ]
}
```

#### Get Player Details
```http
GET /api/live/players/{playerId}
Authorization: Bearer API_KEY
Parameters:
  - playerId (path): COMET Player ID
  - tenant (mandatory): Organisationskürzel
  - includeStats (optional): true/false
```

---

### 6.6 Standings/League Tables

#### Get Competition Standings
```http
GET /api/live/competitions/{competitionId}/standings
Authorization: Bearer API_KEY
Parameters:
  - competitionId (path): COMET Competition ID
  - tenant (mandatory): Organisationskürzel
  - groupId (optional): Für Gruppen-Format
```

**Response:**
```json
{
  "competitionId": "123456",
  "season": "2025/2026",
  "lastUpdateTimestamp": 1698076800,
  "standings": [
    {
      "position": 1,
      "teamId": "654321",
      "teamName": "FC Bayern München",
      "teamLogo": "https://cdn.analyticom.de/clubs/654321/logo.png",
      "matchesPlayed": 15,
      "wins": 12,
      "draws": 2,
      "losses": 1,
      "goalsFor": 45,
      "goalsAgainst": 12,
      "goalDifference": 33,
      "points": 38
    },
    {
      "position": 2,
      "teamId": "654322",
      "teamName": "Borussia Dortmund",
      "teamLogo": "https://cdn.analyticom.de/clubs/654322/logo.png",
      "matchesPlayed": 15,
      "wins": 11,
      "draws": 1,
      "losses": 3,
      "goalsFor": 42,
      "goalsAgainst": 15,
      "goalDifference": 27,
      "points": 34
    }
  ]
}
```

---

## 7. Request/Response Format

### Datum/Zeitformate

**⚠️ Wichtig: Alle Date/DateTime Felder sind UNIX Timestamps (UTC)**

```javascript
// Beispiel: 25. Oktober 2025 15:30 UTC
Timestamp: 1729869000

// Umrechnung:
JavaScript: new Date(1729869000 * 1000)
PHP:        date('Y-m-d H:i:s', 1729869000)
Python:     datetime.fromtimestamp(1729869000)
```

### Response Format

```json
{
  "status": "success",
  "code": 200,
  "data": {
    "// actual data"
  },
  "pagination": {
    "page": 1,
    "limit": 50,
    "total": 306,
    "totalPages": 7
  },
  "meta": {
    "timestamp": 1698076800,
    "version": "1.0",
    "tenant": "dfb"
  }
}
```

### Error Response

```json
{
  "status": "error",
  "code": 401,
  "message": "Unauthorized: Invalid API Key",
  "error": {
    "type": "AUTHENTICATION_ERROR",
    "details": "API Key not valid or expired"
  },
  "timestamp": 1698076800
}
```

---

## 8. Fehlerbehandlung

### HTTP Status Codes

| Code | Bedeutung | Ursache |
|------|-----------|--------|
| 200 | OK | Request erfolgreich |
| 400 | Bad Request | Ungültige Parameter |
| 401 | Unauthorized | API Key fehlt/ungültig |
| 403 | Forbidden | Keine Berechtigung |
| 404 | Not Found | Ressource nicht vorhanden |
| 429 | Too Many Requests | Rate Limit überschritten |
| 500 | Internal Error | Server-Fehler |
| 503 | Service Unavailable | Service nicht erreichbar |

### Error Types

```json
{
  "AUTHENTICATION_ERROR": "API Key ungültig oder abgelaufen",
  "AUTHORIZATION_ERROR": "Keine Berechtigung für diese Ressource",
  "VALIDATION_ERROR": "Ungültige oder fehlende Parameter",
  "RESOURCE_NOT_FOUND": "Ressource existiert nicht",
  "RATE_LIMIT_EXCEEDED": "Zu viele Requests in kurzer Zeit",
  "TENANT_INVALID": "Ungültige oder fehlende Tenant ID",
  "SERVER_ERROR": "Interner Fehler"
}
```

### Fehlerbehandlung Best Practices

```javascript
// JavaScript/React Beispiel

async function fetchFromCometLive(endpoint, params = {}) {
  try {
    const response = await fetch(
      `${COMET_LIVE_URL}${endpoint}`,
      {
        headers: {
          'Authorization': `Bearer ${API_KEY}`,
          'Content-Type': 'application/json',
          'X-API-Version': '1.0'
        },
        params: {
          tenant: TENANT,
          ...params
        }
      }
    );

    if (response.status === 401) {
      throw new Error('Authentication failed - Check API Key');
    }

    if (response.status === 403) {
      throw new Error('Access denied - Check permissions');
    }

    if (response.status === 429) {
      // Rate limiting - exponential backoff
      await delay(5000);
      return fetchFromCometLive(endpoint, params);
    }

    if (!response.ok) {
      throw new Error(`API Error: ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error('COMET LIVE API Error:', error);
    // Show user-friendly error message
  }
}
```

---

## 9. Performance & Caching

### Caching Strategy

```
COMET LIVE API verwendet folgende Caching-Strategie:

┌─────────────────────────────────────┐
│     DATA FRESHNESS LEVELS           │
├─────────────────────────────────────┤
│                                     │
│ 🟢 LIVE DATA (< 5 Sekunden)         │
│    └─ Match Events                  │
│    └─ Current Scores                │
│    └─ Live Status                   │
│                                     │
│ 🟡 NEAR REAL-TIME (5-60 Sekunden)   │
│    └─ Team Standings                │
│    └─ Match Updates                 │
│    └─ Player Statistics             │
│                                     │
│ 🟠 SEMI-STATIC (1-24 Stunden)       │
│    └─ Team Information              │
│    └─ Player Details                │
│    └─ Competition Info              │
│                                     │
│ 🔴 STATIC (24+ Stunden)             │
│    └─ Historical Data               │
│    └─ Archived Results              │
│    └─ Past Statistics               │
│                                     │
└─────────────────────────────────────┘
```

### Caching Best Practices für Frontend

```javascript
// Vue.js / React Beispiel

// 1. Cache mit lokalem State Management
const useMatchStore = defineStore('match', {
  state: () => ({
    matches: [],
    lastUpdate: null,
    cacheExpiry: 60000 // 1 Minute
  }),

  getters: {
    isCacheValid: (state) => {
      return Date.now() - state.lastUpdate < state.cacheExpiry;
    }
  },

  actions: {
    async fetchMatches(competitionId) {
      // Prüfe Cache vor API-Call
      if (this.isCacheValid) {
        return this.matches;
      }

      // Hole neue Daten
      const response = await $api.live.getMatches(competitionId);
      this.matches = response.data;
      this.lastUpdate = Date.now();

      return this.matches;
    }
  }
});

// 2. Nutze Browser LocalStorage für längeres Caching
function cacheData(key, data, ttl = 3600000) {
  localStorage.setItem(`comet_${key}`, JSON.stringify({
    data,
    expiry: Date.now() + ttl
  }));
}

function getCachedData(key) {
  const cached = localStorage.getItem(`comet_${key}`);
  if (!cached) return null;

  const { data, expiry } = JSON.parse(cached);
  if (Date.now() > expiry) {
    localStorage.removeItem(`comet_${key}`);
    return null;
  }

  return data;
}

// 3. Nutze CDN für static Ressourcen (Logos, Bilder)
// Bilder werden vom CDN gecacht
const logoUrl = `https://cdn.analyticom.de/clubs/${clubId}/logo.png`;
// CDN cache: 24 Stunden
```

### Rate Limits

```
Standard Rate Limits:

- 1000 Requests pro Minute (Standard)
- 100 Requests pro Minute (bei Abuse)
- 10 Concurrent Connections

Limits hängen ab von:
- API Key Permission Level
- Subscription Plan
- Current Traffic
- Historical Abuse

Limit-Info im Response Header:
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 987
X-RateLimit-Reset: 1698080400
```

---

## 10. Laravel Integration

### 10.1 Service für COMET LIVE API

```php
// app/Services/CometLiveApiService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class CometLiveApiService
{
    private $apiUrl;
    private $apiKey;
    private $tenant;
    private $cacheTtl = 3600; // 1 Hour

    public function __construct()
    {
        $this->apiUrl = config('comet.live_api_url');
        $this->apiKey = config('comet.live_api_key');
        $this->tenant = config('comet.live_tenant');
    }

    /**
     * Get all competitions
     */
    public function getCompetitions(array $filters = []): array
    {
        $cacheKey = 'comet_live_competitions_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, $this->cacheTtl, function() use ($filters) {
            $response = $this->makeRequest('GET', '/api/live/competitions', $filters);
            return $response['data'] ?? [];
        });
    }

    /**
     * Get competition details
     */
    public function getCompetition(string $competitionId, bool $useCache = true): array
    {
        $cacheKey = "comet_live_competition_{$competitionId}";

        if (!$useCache) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->cacheTtl, function() use ($competitionId) {
            $response = $this->makeRequest('GET', "/api/live/competitions/{$competitionId}");
            return $response;
        });
    }

    /**
     * Get matches for competition
     */
    public function getMatches(string $competitionId, array $filters = []): array
    {
        $filters['competitionId'] = $competitionId;
        $cacheKey = 'comet_live_matches_' . md5(json_encode($filters));

        // Kürzeres Caching für Live-Daten
        $cacheTtl = 300; // 5 Minuten

        return Cache::remember($cacheKey, $cacheTtl, function() use ($filters) {
            $response = $this->makeRequest('GET', '/api/live/matches', $filters);
            return $response['data'] ?? [];
        });
    }

    /**
     * Get live match events
     */
    public function getLiveEvents(string $matchId): array
    {
        // Keine Caching für Live-Events
        $response = $this->makeRequest('GET', "/api/live/matches/{$matchId}/events");
        return $response['data'] ?? [];
    }

    /**
     * Get teams/clubs
     */
    public function getTeams(string $competitionId): array
    {
        $cacheKey = "comet_live_teams_{$competitionId}";

        return Cache::remember($cacheKey, $this->cacheTtl, function() use ($competitionId) {
            $response = $this->makeRequest('GET', '/api/live/teams', [
                'competitionId' => $competitionId
            ]);
            return $response['data'] ?? [];
        });
    }

    /**
     * Get standings
     */
    public function getStandings(string $competitionId): array
    {
        $cacheKey = "comet_live_standings_{$competitionId}";

        return Cache::remember($cacheKey, 600, function() use ($competitionId) { // 10 min
            $response = $this->makeRequest('GET', "/api/live/competitions/{$competitionId}/standings");
            return $response['standings'] ?? [];
        });
    }

    /**
     * Make HTTP request to COMET LIVE API
     */
    private function makeRequest(
        string $method,
        string $endpoint,
        array $params = []
    ): array {
        $params['tenant'] = $this->tenant;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-Version' => '1.0'
            ])->{strtolower($method)}($this->apiUrl . $endpoint, $params);

            if ($response->failed()) {
                \Log::error('COMET LIVE API Error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                return [];
            }

            return $response->json();

        } catch (\Exception $e) {
            \Log::error('COMET LIVE API Exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
```

### 10.2 Config File

```php
// config/comet.php

return [
    // COMET REST API (für Datensynchronisation)
    'api_url' => env('COMET_API_URL', 'https://api-hns.analyticom.de'),
    'username' => env('COMET_USERNAME'),
    'password' => env('COMET_PASSWORD'),

    // COMET LIVE API (für Echtzeit-Daten)
    'live_api_url' => env('COMET_LIVE_API_URL', 'https://api-' . env('COMET_TENANT') . '.analyticom.de'),
    'live_api_key' => env('COMET_LIVE_API_KEY'),
    'live_tenant' => env('COMET_TENANT', 'dfb'),

    // Cache Settings
    'cache_ttl' => env('COMET_CACHE_TTL', 3600),
    'live_cache_ttl' => env('COMET_LIVE_CACHE_TTL', 300),
];
```

### 10.3 .env File

```env
# COMET REST API (für Backend-Synchronisation)
COMET_API_URL=https://api-hns.analyticom.de
COMET_USERNAME=admin@example.com
COMET_PASSWORD=your_password

# COMET LIVE API (für Frontend)
COMET_LIVE_API_URL=https://api-dfb.analyticom.de
COMET_LIVE_API_KEY=your_live_api_key_here
COMET_TENANT=dfb

# Cache Settings
COMET_CACHE_TTL=3600
COMET_LIVE_CACHE_TTL=300
```

### 10.4 Controller mit LIVE API

```php
// app/Http/Controllers/Api/LiveMatchController.php

namespace App\Http\Controllers\Api;

use App\Services\CometLiveApiService;
use Illuminate\Http\JsonResponse;

class LiveMatchController
{
    public function __construct(private CometLiveApiService $liveApi) {}

    /**
     * GET /api/live/competitions
     */
    public function competitions(): JsonResponse
    {
        $competitions = $this->liveApi->getCompetitions();

        return response()->json([
            'success' => true,
            'data' => $competitions
        ]);
    }

    /**
     * GET /api/live/matches/{competitionId}
     */
    public function matches(string $competitionId): JsonResponse
    {
        $matches = $this->liveApi->getMatches($competitionId, [
            'status' => request('status', 'LIVE')
        ]);

        return response()->json([
            'success' => true,
            'data' => $matches
        ]);
    }

    /**
     * GET /api/live/matches/{matchId}/events
     */
    public function liveEvents(string $matchId): JsonResponse
    {
        $events = $this->liveApi->getLiveEvents($matchId);

        return response()->json([
            'success' => true,
            'data' => $events,
            'timestamp' => now()->timestamp
        ]);
    }

    /**
     * GET /api/live/standings/{competitionId}
     */
    public function standings(string $competitionId): JsonResponse
    {
        $standings = $this->liveApi->getStandings($competitionId);

        return response()->json([
            'success' => true,
            'data' => $standings
        ]);
    }
}
```

---

## 11. Best Practices

### ✅ DO's

1. **API Key sicher speichern**
   ```env
   COMET_LIVE_API_KEY=your_key_in_env
   # Nicht in Quellcode!
   ```

2. **Caching strategisch nutzen**
   ```php
   // Live Data: 5 Sekunden Cache
   // Static Data: 24 Stunden Cache
   ```

3. **Multi-Tenant Support**
   ```php
   'tenant' => auth()->user()->club->comet_abbreviation
   ```

4. **Conditional Requests implementieren**
   ```
   Nutze teamIdFilter wenn User zu Club gehört
   Nutze organizationIdFilter wenn User zu Org gehört
   ```

5. **Error Handling implementieren**
   ```php
   if ($response->status() === 429) {
       // Rate Limit respektieren
       return retry mit exponential backoff
   }
   ```

6. **Timestamps in UTC verwenden**
   ```javascript
   // UNIX Timestamps sind immer UTC
   new Date(timestamp * 1000).toUTCString()
   ```

### ❌ DON'Ts

1. **API Key hardcoden**
   ```
   ❌ const key = "abc-123-def"
   ✅ const key = process.env.COMET_LIVE_API_KEY
   ```

2. **Rate Limits ignorieren**
   ```
   ❌ Mache 10.000 Requests pro Minute
   ✅ Respektiere 1.000 Requests pro Minute
   ```

3. **Auf API für jeden View-Request abfragen**
   ```
   ❌ User-Request → API-Call → Response
   ✅ User-Request → Cache-Check → API-Call wenn nötig
   ```

4. **Alle Daten speichern**
   ```
   ❌ Speichere komplette API Responses
   ✅ Speichere nur relevante Daten
   ```

5. **Fehlgeschlagene Requests nicht handhaben**
   ```
   ❌ throw new Exception()
   ✅ Log Fehler + Return fallback data
   ```

---

## 12. FAQ & Häufige Probleme

### Q: Wie oft kann ich Daten abfragen?
**A:** 1000 Requests pro Minute (Standard). Nutze Caching um unter dieses Limit zu kommen.

### Q: Sind die Timestamps UTC oder lokal?
**A:** Alle Timestamps sind **UTC**. Konvertiere auf Client-Seite zur lokalen Zeit.

### Q: Kann ich Live-Events mit Polling abrufen?
**A:** Ja, aber nutze WebSockets oder Server-Sent Events für bessere Performance.

### Q: Wie handle ich Rate Limiting?
**A:** Check `X-RateLimit-Remaining` Header und implementiere exponential backoff.

### Q: Brauche ich noch die REST API?
**A:** Ja! Nutze REST API für Datensynchronisation und LIVE API für Frontend-Daten.

### Q: Wie lange werden Daten gecacht?
**A:** Abhängig vom Datentyp:
- Live Events: Keine Cache
- Live Scores: 5 Sekunden
- Standings: 10 Minuten
- Static Data: 24 Stunden

---

## Checkliste: COMET LIVE API Setup

```
✓ API Key vom Superuser erhalten
✓ Swagger URL Konfiguration verstanden
✓ Authentication (Bearer Token) implementiert
✓ Tenant Parameter konfiguriert
✓ Conditional Filters (teamIdFilter/organizationIdFilter) gesetzt
✓ Caching-Strategie implementiert
✓ Error Handling implemented
✓ Rate Limits beachtet
✓ .env File konfiguriert
✓ Service Class erstellt
✓ Controller mit LIVE API erstellt
✓ Frontend integriert
✓ Testing durchgeführt
✓ Production Ready
```

---

**Letzte Aktualisierung**: October 23, 2025  
**Version**: 1.0  
**Status**: ✅ Production Ready  
**API Version**: 1.0  
**Swagger**: https://api-<tenant>.analyticom.de/swagger-ui.html?urls.primaryName=live
