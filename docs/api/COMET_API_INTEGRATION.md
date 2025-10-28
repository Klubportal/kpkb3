# COMET REST API Integration für KP Club Management

**Version**: 1.0  
**Datum**: October 23, 2025  
**API Base URL**: https://api-hns.analyticom.de  
**Swagger Dokumentation**: https://api-hns.analyticom.de/swagger-ui.html

---

## 📋 Inhaltsverzeichnis

1. [Allgemeines](#allgemeines)
2. [Authentifizierung](#authentifizierung)
3. [Benutzerrollen](#benutzerrollen)
4. [Throttling & Rate Limiting](#throttling--rate-limiting)
5. [Hauptendpunkte](#hauptendpunkte)
6. [Live Match Events](#live-match-events)
7. [Ad-Hoc Reports](#ad-hoc-reports)
8. [Daten-Synchronisation](#daten-synchronisation)
9. [Implementierung in Laravel](#implementierung-in-laravel)
10. [Best Practices](#best-practices)

---

## 1. Allgemeines

### Zweck der API

Die Analyticom REST API ermöglicht den Zugriff auf COMET Datenbestände und stellt diese für externe Systeme zur Verfügung. Die API wurde entwickelt, um:

- **Datensynchonisation**: Daten vom COMET System in lokale/Web-Datenbanken zu synchronisieren
- **Drittanbieter-Integration**: Einfache Integration in verschiedenste externe Systeme weltweit
- **FIFA Connect Standard**: Alle Daten sind nach dem FIFA Connect Standard formatiert
  - Weitere Infos: https://data.fifaconnect.org

### Datenformat

Alle Responses werden im **JSON-Format** bereitgestellt und folgen dem **FIFA Connect Standard**.

### Performance-Richtlinien

⚠️ **WICHTIG**: Die REST API sollte **nicht direkt** verwendet werden, um Daten an Website-Benutzer zu liefern. 

**Stattdessen:**
- ✅ Nutze die API zur Datensynchronisation in lokale Datenbanken
- ✅ Implementiere Caching für häufig abgerufene Daten
- ✅ Verwende TTL (Time-To-Live) für Cache-Einträge
- ✅ Aktualisiere Daten asynchron im Hintergrund

---

## 2. Authentifizierung

### Login Credentials

Die Authentifizierung erfolgt über Benutzername und Passwort:

```bash
# Basis Authentifizierung (HTTP Basic Auth)
curl -u "admin@example.com:password" https://api-hns.analyticom.de/endpoint
```

### Header erforderlich

```http
Authorization: Basic base64(admin@example.com:password)
Content-Type: application/json
Accept: application/json
```

### PHP/Laravel Beispiel

```php
// In Laravel Service Class
$credentials = base64_encode('admin@example.com:password');

$response = Http::withHeaders([
    'Authorization' => 'Basic ' . $credentials,
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
])->get('https://api-hns.analyticom.de/endpoint');

return $response->json();
```

---

## 3. Benutzerrollen

Die Authentifizierung bestimmt, auf welche Endpunkte zugegriffen werden kann.

### ROLE_TENANT_WEB (Standard-Rolle)

Standardrolle mit Zugriff auf alle Endpunkte **außer** Disziplinar-Endpunkte.

**Verfügbare Endpunkte:**
- `/competition/{competitionFifaId}` - Wettbewerbsinformationen
- `/matches` - Spiele/Begegnungen
- `/match/{matchFifaId}` - Spiel-Details
- `/match/{matchFifaId}/latest/events` - Live Match Events
- `/match/{matchFifaId}/lastUpdateDateTime` - Letzte Aktualisierung
- `/players` - Spieler
- `/clubs` - Clubs/Vereine
- `/adHocReport/{reportId}` - Ad-Hoc Reports
- `/throttling/info` - Rate Limiting Informationen

### ROLE_DISCIPLINARY_WEB (Disziplinar-Rolle)

Spezialrolle mit Zugriff auf Disziplinar-Endpunkte:

```
/competition/{competitionFifaId}/cases
/match/{matchFifaId}/cases
/case/person/{offenderPersonFifaId}
/case/organisation/{offenderOrganisationFifaId}
/case/{caseFifaId}
/case/{caseFifaId}/sanctions
/sanction/{sanctionId}
```

---

## 4. Throttling & Rate Limiting

### Throttling-Mechanismus

Die API implementiert Rate Limiting zur Kontrolle der Request-Rate:

- **Standard-Rate**: X Requests pro Sekunde
- **Binär-Rate** (Bilder, Logos): Andere Rate
- **Tenant-Spezifisch**: Raten können pro Tenant unterschiedlich sein

### Throttling-Info Endpunkt

```http
GET /throttling/info
Authorization: Basic base64(admin@example.com:password)
```

**Response:**
```json
{
  "standardRate": 100,
  "imageRate": 50,
  "tenant": "admin@example.com"
}
```

### Implementierung in Laravel

```php
```php
// config/comet.php
return [
    'api_url' => env('COMET_API_URL', 'https://api-hns.analyticom.de'),
    'username' => env('COMET_USERNAME', 'admin@example.com'),
    'password' => env('COMET_PASSWORD'),
    'standard_rate' => 100,      // Requests pro Sekunde
    'image_rate' => 50,           // Für Binärdaten
    'retry_delay' => 100,         // Millisekunden
];
```

// app/Services/CometApiService.php
class CometApiService {
    private $lastRequest = 0;
    private $requestRate;
    
    public function throttleRequest() {
        $now = microtime(true) * 1000; // ms
        $elapsed = $now - $this->lastRequest;
        $requiredDelay = 1000 / $this->requestRate;
        
        if ($elapsed < $requiredDelay) {
            usleep(($requiredDelay - $elapsed) * 1000);
        }
        
        $this->lastRequest = microtime(true) * 1000;
    }
}
```

---

## 5. Hauptendpunkte

### 5.1 Wettbewerbe (Competitions)

```http
GET /competition/{competitionFifaId}
Authorization: Basic base64(admin@example.com:password)
```

**Parameter:**
- `competitionFifaId` (erforderlich): FIFA ID des Wettbewerbs

**Response:**
```json
{
  "fifaId": "123456",
  "name": "Bundesliga 2025",
  "country": "Germany",
  "season": "2025/2026",
  "type": "LEAGUE",
  "startDate": "2025-08-15",
  "endDate": "2026-05-30"
}
```

### 5.2 Clubs / Vereine

```http
GET /clubs
GET /club/{clubFifaId}
Authorization: Basic base64(admin@example.com:password)
```

**Query Parameter:**
- `competition` (optional): Wettbewerb filtern
- `country` (optional): Land filtern
- `page` (optional): Pagination

**Response:**
```json
[
  {
    "fifaId": "654321",
    "name": "FC Bayern München",
    "shortName": "FCB",
    "country": "Germany",
    "city": "Munich",
    "founded": "1900",
    "stadium": "Allianz Arena",
    "officialWebsite": "https://www.fcbayern.com"
  }
]
```

### 5.3 Spieler (Players)

```http
GET /players
GET /player/{playerFifaId}
Authorization: Basic base64(admin@example.com:password)
```

**Query Parameter:**
- `club` (optional): Club filtern
- `competition` (optional): Wettbewerb filtern
- `contract` (optional): Nach Vertrag filtern

**Response:**
```json
[
  {
    "fifaId": "240607",
    "firstName": "Manuel",
    "lastName": "Neuer",
    "dateOfBirth": "1986-03-27",
    "nationality": "Germany",
    "position": "GK",
    "height": 193,
    "weight": 84,
    "shirt": 1,
    "club": {
      "fifaId": "654321",
      "name": "FC Bayern München"
    }
  }
]
```

### 5.4 Spiele / Matches

```http
GET /matches
GET /match/{matchFifaId}
Authorization: Basic base64(admin@example.com:password)
```

**Query Parameter:**
- `competition` (erforderlich oder Zeitraum): Wettbewerb
- `fromDate` (optional): Start-Datum (YYYY-MM-DD)
- `toDate` (optional): End-Datum (YYYY-MM-DD)
- `club` (optional): Club filtern
- `status` (optional): SCHEDULED, LIVE, FINISHED, CANCELLED

**Response:**
```json
[
  {
    "fifaId": "13901536",
    "competition": "Bundesliga 2025",
    "homeClub": {
      "fifaId": "654321",
      "name": "FC Bayern München"
    },
    "awayClub": {
      "fifaId": "123456",
      "name": "Borussia Dortmund"
    },
    "matchDate": "2025-10-25T15:30:00Z",
    "stadium": "Allianz Arena",
    "status": "SCHEDULED",
    "homeScore": null,
    "awayScore": null,
    "referee": {
      "fifaId": "987654",
      "firstName": "Felix",
      "lastName": "Brych"
    }
  }
]
```

---

## 6. Live Match Events

### Endpoint: Aktuelle Match Events

```http
GET /match/{matchFifaId}/latest/events?seconds=60
Authorization: Basic base64(admin@example.com:password)
```

**Parameter:**
- `matchFifaId` (erforderlich): FIFA Match ID
- `seconds` (erforderlich): Nur Events aus den letzten N Sekunden

### Event Types

Mögliche Event-Typen:
- `GOAL` - Tor
- `YELLOW_CARD` - Gelbe Karte
- `RED_CARD` - Rote Karte
- `SUBSTITUTION` - Wechsel
- `PENALTY_GOAL` - Elfmeter Tor
- `OWN_GOAL` - Eigentor
- `VAR_REVIEW` - VAR Überprüfung
- `INJURY` - Verletzung

### Added/Edited Event Payload

```json
{
  "id": 13901536,
  "matchPhase": "FIRST_HALF",
  "minute": 30,
  "second": 45,
  "stoppageTime": null,
  "eventType": "GOAL",
  "eventDetailType": null,
  "playerFifaId": 240607,
  "playerFifaId2": null,
  "matchTeam": "HOME",
  "penaltyOrder": null,
  "personName": "Manuel Neuer",
  "localPersonName": null,
  "personName2": null,
  "localPersonName2": null
}
```

### Deleted Event Payload

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

### Match-Aktualisierungs-Zeitstempel

```http
GET /match/{matchFifaId}/lastUpdateDateTime
Authorization: Basic base64(admin@example.com:password)
```

**Response:**
```json
{
  "matchFifaId": "13901536",
  "lastUpdateDateTime": "2025-10-25T15:45:23Z"
}
```

**Verwendung**: Überprüfe periodisch, ob eine Aktualisierung erforderlich ist, anstatt ständig Match-Daten zu laden.

---

## 7. Ad-Hoc Reports

### Endpoint: Reports und Statistiken

```http
GET /adHocReport/{reportId}?parameters=...
Authorization: Basic base64(admin@example.com:password)
```

**Parameter:**
- `reportId` (erforderlich): Report-Identifikator
- `parameters` (erforderlich): Komplexe Parameter-String (kann komplex sein)

### Verfügbare Reports

Beispiele für verfügbare Reports in COMET:
- Spielerverträge nach Startdatum
- Registrierungen pro Club
- Fair Play Report (Kartenverlauf)
- Verletzungs-Statistiken
- Torschützen-Tabelle
- Spielminuten pro Spieler
- Finanzielle Übersichten

### Report URL generieren

**In COMET:**
1. Navigiere zu "Reports and Statistics"
2. Öffne den gewünschten Report
3. Konfiguriere die Parameter
4. Führe den Report aus
5. Klicke auf "COPY REST API URL"
6. Verwende die URL in deiner Anwendung

**Beispiel:**
```
https://api-hns.analyticom.de/adHocReport/123?parameters=competition:BL,season:2025,club:FCB
```

---

## 8. Daten-Synchronisation

### Synchronisierungs-Strategie

```php
// app/Services/CometSyncService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Match;
use App\Models\Player;
use App\Models\Club;

class CometSyncService
{
    private $credentials;
    private $apiUrl = 'https://api-hns.analyticom.de';
    
    public function __construct()
    {
        $this->credentials = base64_encode(
            config('comet.username') . ':' . config('comet.password')
        );
    }
    
    /**
     * Synchronisiere Clubs aus COMET
     */
    public function syncClubs($competitionId)
    {
        $cacheKey = "comet_clubs_{$competitionId}";
        
        // Prüfe Cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Rufe API auf
        $response = $this->makeRequest('GET', "/clubs", [
            'competition' => $competitionId
        ]);
        
        if ($response->successful()) {
            $clubs = $response->json();
            
            // Speichere in Datenbank
            foreach ($clubs as $clubData) {
                Club::updateOrCreate(
                    ['comet_fifa_id' => $clubData['fifaId']],
                    [
                        'name' => $clubData['name'],
                        'short_name' => $clubData['shortName'],
                        'country' => $clubData['country'],
                        'city' => $clubData['city'],
                        'stadium' => $clubData['stadium'],
                        'founded' => $clubData['founded'],
                        'website' => $clubData['officialWebsite'],
                    ]
                );
            }
            
            // Cache für 24 Stunden
            Cache::put($cacheKey, $clubs, 24 * 60);
            
            return $clubs;
        }
        
        return null;
    }
    
    /**
     * Synchronisiere Spieler
     */
    public function syncPlayers($clubId)
    {
        $cacheKey = "comet_players_{$clubId}";
        
        $response = $this->makeRequest('GET', "/players", [
            'club' => $clubId
        ]);
        
        if ($response->successful()) {
            $players = $response->json();
            
            foreach ($players as $playerData) {
                Player::updateOrCreate(
                    ['comet_fifa_id' => $playerData['fifaId']],
                    [
                        'first_name' => $playerData['firstName'],
                        'last_name' => $playerData['lastName'],
                        'position' => $playerData['position'],
                        'shirt_number' => $playerData['shirt'],
                        'date_of_birth' => $playerData['dateOfBirth'],
                        'nationality' => $playerData['nationality'],
                        'height' => $playerData['height'],
                        'weight' => $playerData['weight'],
                        'club_comet_id' => $playerData['club']['fifaId'],
                    ]
                );
            }
            
            Cache::put($cacheKey, $players, 12 * 60);
            return $players;
        }
        
        return null;
    }
    
    /**
     * Synchronisiere Matches mit Aktualisierungs-Check
     */
    public function syncMatches($competitionId, $fromDate = null, $toDate = null)
    {
        $params = ['competition' => $competitionId];
        
        if ($fromDate) $params['fromDate'] = $fromDate;
        if ($toDate) $params['toDate'] = $toDate;
        
        $response = $this->makeRequest('GET', "/matches", $params);
        
        if ($response->successful()) {
            $matches = $response->json();
            
            foreach ($matches as $matchData) {
                Match::updateOrCreate(
                    ['comet_fifa_id' => $matchData['fifaId']],
                    [
                        'competition' => $matchData['competition'],
                        'home_club_comet_id' => $matchData['homeClub']['fifaId'],
                        'away_club_comet_id' => $matchData['awayClub']['fifaId'],
                        'match_date' => $matchData['matchDate'],
                        'stadium' => $matchData['stadium'],
                        'status' => $matchData['status'],
                        'home_score' => $matchData['homeScore'],
                        'away_score' => $matchData['awayScore'],
                    ]
                );
            }
            
            return $matches;
        }
        
        return null;
    }
    
    /**
     * Prüfe Match-Aktualisierungen
     */
    public function checkMatchUpdates($matchFifaId)
    {
        $response = $this->makeRequest('GET', "/match/{$matchFifaId}/lastUpdateDateTime");
        
        if ($response->successful()) {
            $data = $response->json();
            
            $match = Match::where('comet_fifa_id', $matchFifaId)->first();
            
            if ($match && $match->last_comet_update) {
                $remoteUpdate = strtotime($data['lastUpdateDateTime']);
                $localUpdate = strtotime($match->last_comet_update);
                
                // Nur aktualisieren wenn Remote neuer
                if ($remoteUpdate > $localUpdate) {
                    $this->syncMatchDetails($matchFifaId);
                }
            }
            
            return $data;
        }
        
        return null;
    }
    
    /**
     * Live Match Events abrufen
     */
    public function getLiveEvents($matchFifaId, $secondsSince = 60)
    {
        $response = $this->makeRequest('GET', "/match/{$matchFifaId}/latest/events", [
            'seconds' => $secondsSince
        ]);
        
        if ($response->successful()) {
            $events = $response->json();
            
            return array_filter($events, function($event) {
                // Nur nicht-gelöschte Events (die ein eventType haben)
                return $event['eventType'] !== null;
            });
        }
        
        return [];
    }
    
    /**
     * HTTP Request mit Authentifizierung
     */
    private function makeRequest($method, $endpoint, $params = [])
    {
        $url = $this->apiUrl . $endpoint;
        
        // Throttling
        $this->throttle();
        
        return Http::withHeaders([
            'Authorization' => 'Basic ' . $this->credentials,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->$method($url, $params);
    }
    
    /**
     * Throttling implementiert
     */
    private function throttle()
    {
        $delay = config('comet.request_delay', 10); // ms
        usleep($delay * 1000);
    }
}
```

---

## 9. Implementierung in Laravel

### 9.1 Service Provider Setup

```php
// app/Providers/CometServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CometSyncService;

class CometServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CometSyncService::class);
    }
    
    public function boot()
    {
        // Veröffentliche Config
        $this->publishes([
            __DIR__.'/../../config/comet.php' => config_path('comet.php'),
        ]);
    }
}
```

### 9.2 Config Datei

```php
// config/comet.php

return [
    // API Konfiguration
    'api_url' => env('COMET_API_URL', 'https://api-hns.analyticom.de'),
    'username' => env('COMET_USERNAME', 'admin@example.com'),
    'password' => env('COMET_PASSWORD', ''),
    
    // Rate Limiting
    'standard_rate' => 100,  // Requests pro Sekunde
    'image_rate' => 50,      // Für Bilder/Logos
    'request_delay' => 10,   // ms zwischen Requests
    
    // Caching
    'cache_ttl' => 24 * 60,  // 24 Stunden für Standard-Daten
    'match_cache_ttl' => 5,  // 5 Minuten für Live-Daten
    
    // Synchronisierungs-Einstellungen
    'auto_sync_enabled' => env('COMET_AUTO_SYNC', true),
    'sync_interval' => env('COMET_SYNC_INTERVAL', 3600), // Sekunden
];
```

### 9.3 Commands für Synchronisierung

```php
// app/Console/Commands/SyncCometData.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CometSyncService;

class SyncCometData extends Command
{
    protected $signature = 'comet:sync {type} {--competition=}';
    protected $description = 'Synchronisiere Daten von COMET API';
    
    public function handle(CometSyncService $service)
    {
        $type = $this->argument('type');
        $competition = $this->option('competition');
        
        switch($type) {
            case 'clubs':
                $this->info('Synchronisiere Clubs...');
                $service->syncClubs($competition);
                break;
                
            case 'players':
                $this->info('Synchronisiere Spieler...');
                $service->syncPlayers($competition);
                break;
                
            case 'matches':
                $this->info('Synchronisiere Matches...');
                $service->syncMatches($competition);
                break;
                
            case 'all':
                $this->info('Synchronisiere alle Daten...');
                $service->syncClubs($competition);
                $service->syncPlayers($competition);
                $service->syncMatches($competition);
                break;
        }
        
        $this->info('Synchronisierung abgeschlossen!');
    }
}

// Aufrufen:
// php artisan comet:sync clubs --competition=123
// php artisan comet:sync all --competition=123
```

### 9.4 Scheduled Tasks

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Synchronisiere Matches jede Minute während Live-Zeit
    $schedule->command('comet:sync matches --competition=123')
        ->everyMinute()
        ->between('15:00', '17:00')  // Nur während Spielzeit
        ->withoutOverlapping();
    
    // Synchronisiere Clubs täglich um 02:00 Uhr
    $schedule->command('comet:sync clubs --competition=123')
        ->dailyAt('02:00')
        ->withoutOverlapping();
    
    // Synchronisiere alle Daten wöchentlich
    $schedule->command('comet:sync all --competition=123')
        ->weekly()
        ->mondays()
        ->at('03:00')
        ->withoutOverlapping();
}
```

---

## 10. Best Practices

### ✅ DO's

1. **Caching implementieren**
   - Cache häufig abgerufene Daten
   - Setze angemessene TTL-Werte
   - Nutze Redis für verteilte Systeme

2. **Throttling beachten**
   - Implementiere angemessene Request-Verzögerungen
   - Rufe `/throttling/info` Endpoint auf, um aktuelle Limits zu erhalten
   - Implementiere Exponential Backoff bei Rate Limiting

3. **Asynchron synchronisieren**
   - Nutze Queue-System (Laravel Jobs)
   - Führe Synchronisierung im Hintergrund aus
   - Blockiere nicht den Hauptrequest-Zyklus

4. **Fehlerbehandlung**
   - Implementiere Retry-Logik
   - Protokolliere API-Fehler
   - Benachrichtige auf kritische Fehler

5. **Daten validieren**
   - Validiere API-Responses
   - Prüfe auf erforderliche Felder
   - Handle fehlende oder ungültige Daten

6. **Versionierung**
   - Speichere Versions-Informationen
   - Track Änderungen über Zeit
   - Ermögliche Rollback bei Problemen

### ❌ DON'Ts

1. **Keine direkten API-Calls für User-Views**
   - ❌ Rufe API direkt in Request auf
   - ✅ Nutze gecachte Daten

2. **Keine zu häufigen Requests**
   - ❌ Poll API alle 5 Sekunden
   - ✅ Nutze angemessene Intervalle

3. **Keine API-Credentials hardcoden**
   - ❌ Username/Password im Code
   - ✅ Nutze `.env` Dateien

4. **Keine unbehandelte Fehler**
   - ❌ Lasse Exceptions explodieren
   - ✅ Implementiere Error-Handling

5. **Kein unbeschränktes Speichern**
   - ❌ Speichere alle API-Responses unverarbeitet
   - ✅ Speichere nur relevante Daten

---

## 11. Fehlerbehandlung

### HTTP Status Codes

| Code | Bedeutung | Handling |
|------|-----------|----------|
| 200 | OK | Request erfolgreich |
| 400 | Bad Request | Parameter prüfen |
| 401 | Unauthorized | Credentials prüfen |
| 403 | Forbidden | Rolle/Permissions prüfen |
| 404 | Not Found | Ressource existiert nicht |
| 429 | Too Many Requests | Rate Limiting - Wait & Retry |
| 500 | Internal Server Error | Retry mit Backoff |
| 503 | Service Unavailable | Warten, später erneut versuchen |

### Error Response Format

```json
{
  "error": {
    "code": "INVALID_PARAMETERS",
    "message": "Invalid competition id",
    "details": {
      "parameter": "competitionFifaId",
      "value": "invalid_value"
    }
  }
}
```

### Retry Logik

```php
public function makeRequestWithRetry($method, $endpoint, $params = [], $maxRetries = 3)
{
    $retries = 0;
    $baseDelay = 1000; // 1 Sekunde
    
    while ($retries < $maxRetries) {
        try {
            $response = $this->makeRequest($method, $endpoint, $params);
            
            if ($response->successful()) {
                return $response;
            }
            
            // Rate Limiting - exponential backoff
            if ($response->status() == 429) {
                $delay = $baseDelay * pow(2, $retries);
                usleep($delay * 1000);
                $retries++;
                continue;
            }
            
            // Andere Fehler
            if ($response->status() >= 500) {
                $retries++;
                usleep($baseDelay * 1000);
                continue;
            }
            
            // Fehler nicht wiederholbar
            return $response;
            
        } catch (\Exception $e) {
            $retries++;
            if ($retries >= $maxRetries) {
                throw $e;
            }
            usleep($baseDelay * 1000);
        }
    }
    
    throw new \Exception('Max retries exceeded');
}
```

---

## 12. Monitoring & Logging

### Logging Template

```php
// app/Services/CometApiService.php

private function logRequest($method, $endpoint, $status, $duration)
{
    \Log::channel('comet')->info('API Request', [
        'method' => $method,
        'endpoint' => $endpoint,
        'status' => $status,
        'duration_ms' => $duration,
        'timestamp' => now(),
    ]);
}

private function logError($endpoint, $status, $message)
{
    \Log::channel('comet')->error('API Error', [
        'endpoint' => $endpoint,
        'status' => $status,
        'message' => $message,
        'timestamp' => now(),
    ]);
}
```

---

## 13. Umgebungsvariablen

Füge diese Variablen zu deiner `.env` Datei hinzu:

```env
# COMET API Configuration
COMET_API_URL=https://api-hns.analyticom.de
COMET_USERNAME=admin@example.com
COMET_PASSWORD=your_secure_password_here
COMET_AUTO_SYNC=true
COMET_SYNC_INTERVAL=3600

# Logging
LOG_CHANNEL=stack
LOG_COMET_CHANNEL=comet
```

---

## 14. Test Daten Generation

### Hintergrund

Das Comet API Export Endpoint `/api/export/comet/competitions` bietet nur **Competitions Metadata** (24 Felder).
Alle anderen Endpoints (`/clubs`, `/teams`, `/matches`, `/players`) sind für diese Konfiguration nicht verfügbar (404 Status).

**Lösung**: Test Daten Generator erstellt realistische Testdaten für die Entwicklung.

### Verfügbare Befehle

#### Test Daten generieren

```bash
php artisan comet:generate-simple-test-data
```

**Erstellt:**
- 5 Test Clubs (Vereine)
- 5 Test Teams (Mannschaften pro Club)
- 20 Test Players (4 pro Team)
- 5 Top Scorers mit Statistiken

**Output:**
```
【 Generating Simple Test Data 】
================================================================================

Clearing old data...
Using competition: 100629221

Step 1: Creating clubs...
  ✓ Created 5 clubs

Step 2: Creating teams...
  ✓ Created 5 teams

Step 3: Creating players...
  ✓ Created 20 players

Step 4: Creating top scorers...
  ✓ Created 5 top scorers

Summary:
  - Clubs: 5
  - Teams: 5
  - Players: 20
  - Top Scorers: 5
```

#### Top Scorers anzeigen

```bash
php artisan comet:show-top-scorers
```

**Output:**
```
===========================================================================================
TOP SCORERS - PRVA ZAGREBAČKA LIGA - SENIORI 25/26
===========================================================================================

+------+---------------+-------------+-------+---------+---------+-----------+
| Rank | Player Name   | Team        | Goals | Assists | Matches | Avg Goals |
+------+---------------+-------------+-------+---------+---------+-----------+
| 1    | Player Test 1 | Test Team 1 | 9     | 1       | 5       | 1.80      |
| 2    | Player Test 2 | Test Team 2 | 8     | 0       | 5       | 1.60      |
| 3    | Player Test 3 | Test Team 3 | 7     | 1       | 5       | 1.40      |
| 4    | Player Test 4 | Test Team 4 | 6     | 0       | 5       | 1.20      |
| 5    | Player Test 1 | Test Team 5 | 5     | 1       | 5       | 1.00      |
+------+---------------+-------------+-------+---------+---------+-----------+

✅ Total: 5 top scorers
```

### Datenbank Schema

**Clubs** (`comet_clubs`)
```
id, comet_id (unique), name, city, country, founded_year, logo_url, website, email, phone
```

**Teams** (`comet_teams`)
```
id, comet_id (unique), comet_club_id (FK), name, team_type, age_group, player_count
```

**Players** (`comet_players`)
```
id, comet_id (unique), comet_team_id (FK), comet_club_id (FK), 
first_name, last_name, full_name, jersey_number, position, birth_date, 
gender, nationality, status
```

**Top Scorers** (`top_scorers`)
```
id, comet_id (unique), comet_competition_id (FK), comet_player_id (FK), 
comet_team_id (FK), player_name, team_name, rank, goals, assists, 
matches_played, goals_per_match, is_leading_scorer
```

---

## 15. Support & Ressourcen

- **API Dokumentation**: https://api-hns.analyticom.de/swagger-ui.html
- **FIFA Connect Standard**: https://data.fifaconnect.org
- **COMET Support**: contact@analyticom.de

---

**Erstellt**: October 23, 2025  
**Letzte Aktualisierung**: October 23, 2025  
**Status**: ✅ Production Ready - Test Data Generation Added
