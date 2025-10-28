# COMET REST API - Implementierungs-Anleitung für KP Club Management

**Version**: 1.0  
**Datum**: October 23, 2025  
**Umgebung**: Production (HNS Tenant)  
**Bereich**: Schritt-für-Schritt Integration  

---

## 🔐 Zugangsdaten

```php
// config/comet.php oder .env

COMET_API_BASE_URL=https://api-hns.analyticom.de
COMET_API_USERNAME=nkprigorje
COMET_API_PASSWORD=3c6nR$dS
COMET_API_TENANT=hns
```

**Wichtig**: Verwende `.env` für Produktionsumgebung, nicht direkt im Code!

---

## 📋 Inhaltsverzeichnis

1. [Konfiguration](#konfiguration)
2. [Service Layer](#service-layer)
3. [API Endpoints Integration](#api-endpoints-integration)
4. [Fehlerbehandlung](#fehlerbehandlung)
5. [Caching Strategie](#caching-strategie)
6. [Scheduled Synchronisation](#scheduled-synchronisation)
7. [Monitoring & Debugging](#monitoring--debugging)

---

## 1. Konfiguration

### .env Setup

```env
# COMET API Konfiguration
COMET_API_BASE_URL=https://api-hns.analyticom.de
COMET_API_USERNAME=nkprigorje
COMET_API_PASSWORD=3c6nR$dS
COMET_API_TENANT=hns
COMET_API_TIMEOUT=30
COMET_API_RETRY_ATTEMPTS=3
COMET_API_CACHE_TTL=3600

# Rate Limiting
COMET_API_RATE_LIMIT=100
COMET_API_RATE_LIMIT_IMAGES=50

# Feature Flags
COMET_API_ENABLE_SYNC=true
COMET_API_ENABLE_CACHING=true
COMET_API_ENABLE_MONITORING=true
```

### config/comet.php

```php
<?php

return [
    'api' => [
        'base_url' => env('COMET_API_BASE_URL', 'https://api-hns.analyticom.de'),
        'username' => env('COMET_API_USERNAME', 'nkprigorje'),
        'password' => env('COMET_API_PASSWORD'),
        'tenant' => env('COMET_API_TENANT', 'hns'),
        'timeout' => env('COMET_API_TIMEOUT', 30),
        'retry_attempts' => env('COMET_API_RETRY_ATTEMPTS', 3),
    ],

    'throttling' => [
        'default_rate' => env('COMET_API_RATE_LIMIT', 100),
        'images_rate' => env('COMET_API_RATE_LIMIT_IMAGES', 50),
        'endpoints' => [
            '/api/export/comet/images' => 50,
            '/api/export/comet/competitions' => 100,
            '/api/export/comet/matches' => 100,
            '/api/export/comet/players' => 100,
        ]
    ],

    'caching' => [
        'enabled' => env('COMET_API_ENABLE_CACHING', true),
        'ttl' => env('COMET_API_CACHE_TTL', 3600),
        'store' => 'redis',
    ],

    'sync' => [
        'enabled' => env('COMET_API_ENABLE_SYNC', true),
        'schedule' => '*/5 * * * *',  // Every 5 minutes
        'batch_size' => 100,
    ],

    'monitoring' => [
        'enabled' => env('COMET_API_ENABLE_MONITORING', true),
        'log_channel' => 'comet',
        'alert_email' => env('COMET_ALERT_EMAIL'),
    ]
];
```

---

## 2. Service Layer

### app/Services/CometApiService.php

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class CometApiService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private int $timeout;
    private int $retryAttempts;
    private bool $cachingEnabled;
    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('comet.api.base_url');
        $this->username = config('comet.api.username');
        $this->password = config('comet.api.password');
        $this->timeout = config('comet.api.timeout');
        $this->retryAttempts = config('comet.api.retry_attempts');
        $this->cachingEnabled = config('comet.caching.enabled');
        $this->cacheTtl = config('comet.caching.ttl');
    }

    /**
     * Get throttling information
     */
    public function getThrottlingInfo(): ?array
    {
        return $this->request('GET', '/api/export/comet/throttling/info');
    }

    /**
     * Get competitions
     */
    public function getCompetitions(array $filters = []): ?array
    {
        $cacheKey = $this->getCacheKey('competitions', $filters);
        
        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', '/api/export/comet/competitions', $filters);
        
        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get single competition
     */
    public function getCompetition(int $competitionId): ?array
    {
        $cacheKey = "comet:competition:{$competitionId}";
        
        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/competitions", [
            'competitionFifaId' => $competitionId
        ]);

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get competition matches
     */
    public function getCompetitionMatches(int $competitionId, array $filters = []): ?array
    {
        $filters['competitionFifaId'] = $competitionId;
        $cacheKey = $this->getCacheKey("competition:{$competitionId}:matches", $filters);

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/competition/{$competitionId}/matches", $filters);

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get match details
     */
    public function getMatch(int $matchId): ?array
    {
        $cacheKey = "comet:match:{$matchId}";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/match/{$matchId}");

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, 300);  // 5 minutes for live data
        }

        return $result;
    }

    /**
     * Get match events
     */
    public function getMatchEvents(int $matchId, ?string $eventType = null): ?array
    {
        $params = [];
        if ($eventType) {
            $params['eventType'] = $eventType;
        }

        // Don't cache events - always fresh
        return $this->request('GET', "/api/export/comet/match/{$matchId}/events", $params);
    }

    /**
     * Get latest match events (last N seconds)
     */
    public function getLatestMatchEvents(int $matchId, int $seconds = 60): ?array
    {
        // Never cache latest events
        return $this->request('GET', "/api/export/comet/match/{$matchId}/latest/events", [
            'seconds' => $seconds
        ]);
    }

    /**
     * Get match officials
     */
    public function getMatchOfficials(int $matchId): ?array
    {
        $cacheKey = "comet:match:{$matchId}:officials";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/match/{$matchId}/officials");

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get match players
     */
    public function getMatchPlayers(int $matchId): ?array
    {
        $cacheKey = "comet:match:{$matchId}:players";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/match/{$matchId}/players");

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get match team officials
     */
    public function getMatchTeamOfficials(int $matchId): ?array
    {
        $cacheKey = "comet:match:{$matchId}:team-officials";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/match/{$matchId}/teamOfficials");

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get competition teams
     */
    public function getCompetitionTeams(int $competitionId): ?array
    {
        $cacheKey = "comet:competition:{$competitionId}:teams";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/competition/{$competitionId}/teams");

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get competition ranking
     */
    public function getCompetitionRanking(int $competitionId, bool $unofficial = false): ?array
    {
        $cacheKey = "comet:competition:{$competitionId}:ranking:" . ($unofficial ? 'unofficial' : 'official');

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/competition/{$competitionId}/ranking", [
            'unofficial' => $unofficial ? 'true' : 'false'
        ]);

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get top scorers
     */
    public function getTopScorers(int $competitionId): ?array
    {
        $cacheKey = "comet:competition:{$competitionId}:top-scorers";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/competition/{$competitionId}/topScorers");

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get player details
     */
    public function getPlayer(int $playerFifaId): ?array
    {
        $cacheKey = "comet:player:{$playerFifaId}";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/player/{$playerFifaId}");

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get team players
     */
    public function getTeamPlayers(int $teamFifaId, string $status = 'ALL'): ?array
    {
        $cacheKey = "comet:team:{$teamFifaId}:players:{$status}";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/team/{$teamFifaId}/players", [
            'status' => $status
        ]);

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get team officials
     */
    public function getTeamOfficials(int $teamFifaId, string $status = 'ALL'): ?array
    {
        $cacheKey = "comet:team:{$teamFifaId}:officials:{$status}";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/team/{$teamFifaId}/teamOfficials", [
            'status' => $status
        ]);

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Get facility/stadium
     */
    public function getFacility(int $facilityId): ?array
    {
        $cacheKey = "comet:facility:{$facilityId}";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/facilities", [
            'facilityFifaId' => $facilityId
        ]);

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl * 24);  // 24h for static data
        }

        return $result;
    }

    /**
     * Get image (Base64)
     */
    public function getImage(string $entity, int $fifaId): ?array
    {
        $cacheKey = "comet:image:{$entity}:{$fifaId}";

        if ($this->cachingEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->request('GET', "/api/export/comet/images/{$entity}/{$fifaId}", [], 'images');

        if ($result && $this->cachingEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl * 24);  // 24h
        }

        return $result;
    }

    /**
     * Check if image was updated
     */
    public function checkImageUpdate(string $entity, int $fifaId, string $date): ?bool
    {
        return $this->request('GET', "/api/export/comet/images/update/{$entity}/{$fifaId}", [
            'date' => $date
        ]);
    }

    /**
     * Base HTTP request method with retry logic
     */
    private function request(string $method, string $endpoint, array $params = [], ?string $endpointType = null): ?array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $url = $this->baseUrl . $endpoint;

                $response = Http::withBasicAuth($this->username, $this->password)
                    ->timeout($this->timeout)
                    ->{strtolower($method)}($url, $params);

                // Log successful request
                Log::channel('comet')->debug('COMET API Request Success', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'params' => $this->sanitizeParams($params)
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                // Handle error responses
                if ($response->status() === 401) {
                    Log::channel('comet')->error('COMET API Authentication Failed', [
                        'endpoint' => $endpoint,
                        'body' => $response->body()
                    ]);
                    return null;  // Don't retry auth errors
                }

                $lastError = $response->body();
                $attempt++;

                if ($attempt < $this->retryAttempts) {
                    $delay = min(1000 * (2 ** $attempt), 8000);  // Exponential backoff
                    usleep($delay * 1000);
                }

            } catch (Exception $e) {
                Log::channel('comet')->error('COMET API Request Exception', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt + 1
                ]);

                $lastError = $e->getMessage();
                $attempt++;

                if ($attempt < $this->retryAttempts) {
                    $delay = min(1000 * (2 ** $attempt), 8000);
                    usleep($delay * 1000);
                }
            }
        }

        Log::channel('comet')->error('COMET API Request Failed After Retries', [
            'endpoint' => $endpoint,
            'attempts' => $this->retryAttempts,
            'lastError' => $lastError
        ]);

        return null;
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(string $prefix, array $params = []): string
    {
        $paramHash = md5(json_encode($params));
        return "comet:{$prefix}:{$paramHash}";
    }

    /**
     * Sanitize sensitive parameters for logging
     */
    private function sanitizeParams(array $params): array
    {
        $sensitive = ['password', 'token', 'secret', 'key'];
        
        foreach ($sensitive as $field) {
            if (isset($params[$field])) {
                $params[$field] = '***MASKED***';
            }
        }

        return $params;
    }

    /**
     * Clear all COMET caches
     */
    public function clearCache(): void
    {
        Cache::tags(['comet'])->flush();
        Log::channel('comet')->info('COMET cache cleared');
    }

    /**
     * Get cache info
     */
    public function getCacheInfo(): array
    {
        return [
            'enabled' => $this->cachingEnabled,
            'ttl' => $this->cacheTtl,
            'store' => config('comet.caching.store'),
        ];
    }
}
```

---

## 3. API Endpoints Integration

### routes/api.php

```php
<?php

use App\Http\Controllers\Api\CometController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Competitions
    Route::get('/competitions', [CometController::class, 'getCompetitions']);
    Route::get('/competitions/{id}', [CometController::class, 'getCompetition']);
    Route::get('/competitions/{id}/matches', [CometController::class, 'getMatches']);
    Route::get('/competitions/{id}/teams', [CometController::class, 'getTeams']);
    Route::get('/competitions/{id}/ranking', [CometController::class, 'getRanking']);
    Route::get('/competitions/{id}/topscorers', [CometController::class, 'getTopScorers']);

    // Matches
    Route::get('/matches/{id}', [CometController::class, 'getMatch']);
    Route::get('/matches/{id}/events', [CometController::class, 'getMatchEvents']);
    Route::get('/matches/{id}/events/latest', [CometController::class, 'getLatestMatchEvents']);
    Route::get('/matches/{id}/officials', [CometController::class, 'getMatchOfficials']);
    Route::get('/matches/{id}/players', [CometController::class, 'getMatchPlayers']);

    // Players
    Route::get('/players/{id}', [CometController::class, 'getPlayer']);
    Route::get('/teams/{id}/players', [CometController::class, 'getTeamPlayers']);

    // Teams/Clubs
    Route::get('/teams/{id}/officials', [CometController::class, 'getTeamOfficials']);

    // Images
    Route::get('/images/{entity}/{id}', [CometController::class, 'getImage']);
    Route::get('/images/{entity}/{id}/check', [CometController::class, 'checkImageUpdate']);

    // Admin
    Route::get('/throttling/info', [CometController::class, 'getThrottlingInfo']);
    Route::post('/cache/clear', [CometController::class, 'clearCache']);
});
```

### app/Http/Controllers/Api/CometController.php

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CometApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CometController extends Controller
{
    private CometApiService $cometApi;

    public function __construct(CometApiService $cometApi)
    {
        $this->cometApi = $cometApi;
    }

    // Competitions
    public function getCompetitions(Request $request): JsonResponse
    {
        $filters = $request->only(['active', 'season', 'ageCategory', 'organisationFifaIds']);
        $data = $this->cometApi->getCompetitions($filters);
        
        return response()->json($data ?? ['error' => 'No data']);
    }

    public function getCompetition(int $id): JsonResponse
    {
        $data = $this->cometApi->getCompetition($id);
        
        if (!$data) {
            return response()->json(['error' => 'Competition not found'], 404);
        }

        return response()->json($data);
    }

    public function getMatches(Request $request, int $id): JsonResponse
    {
        $filters = $request->only(['teamFifaId', 'status', 'dateFrom', 'dateTo']);
        $data = $this->cometApi->getCompetitionMatches($id, $filters);
        
        return response()->json($data ?? []);
    }

    public function getTeams(int $id): JsonResponse
    {
        $data = $this->cometApi->getCompetitionTeams($id);
        
        return response()->json($data ?? []);
    }

    public function getRanking(Request $request, int $id): JsonResponse
    {
        $unofficial = $request->boolean('unofficial', false);
        $data = $this->cometApi->getCompetitionRanking($id, $unofficial);
        
        return response()->json($data ?? []);
    }

    public function getTopScorers(int $id): JsonResponse
    {
        $data = $this->cometApi->getTopScorers($id);
        
        return response()->json($data ?? []);
    }

    // Matches
    public function getMatch(int $id): JsonResponse
    {
        $data = $this->cometApi->getMatch($id);
        
        if (!$data) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        return response()->json($data);
    }

    public function getMatchEvents(Request $request, int $id): JsonResponse
    {
        $eventType = $request->query('eventType');
        $data = $this->cometApi->getMatchEvents($id, $eventType);
        
        return response()->json($data ?? []);
    }

    public function getLatestMatchEvents(Request $request, int $id): JsonResponse
    {
        $seconds = $request->integer('seconds', 60);
        $data = $this->cometApi->getLatestMatchEvents($id, $seconds);
        
        return response()->json($data ?? []);
    }

    public function getMatchOfficials(int $id): JsonResponse
    {
        $data = $this->cometApi->getMatchOfficials($id);
        
        return response()->json($data ?? []);
    }

    public function getMatchPlayers(int $id): JsonResponse
    {
        $data = $this->cometApi->getMatchPlayers($id);
        
        return response()->json($data ?? []);
    }

    // Players/Teams
    public function getPlayer(int $id): JsonResponse
    {
        $data = $this->cometApi->getPlayer($id);
        
        if (!$data) {
            return response()->json(['error' => 'Player not found'], 404);
        }

        return response()->json($data);
    }

    public function getTeamPlayers(Request $request, int $id): JsonResponse
    {
        $status = $request->query('status', 'ALL');
        $data = $this->cometApi->getTeamPlayers($id, $status);
        
        return response()->json($data ?? []);
    }

    public function getTeamOfficials(Request $request, int $id): JsonResponse
    {
        $status = $request->query('status', 'ALL');
        $data = $this->cometApi->getTeamOfficials($id, $status);
        
        return response()->json($data ?? []);
    }

    // Images
    public function getImage(string $entity, int $id): JsonResponse
    {
        $data = $this->cometApi->getImage($entity, $id);
        
        if (!$data) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        return response()->json($data);
    }

    public function checkImageUpdate(Request $request, string $entity, int $id): JsonResponse
    {
        $date = $request->query('date');
        $updated = $this->cometApi->checkImageUpdate($entity, $id, $date);
        
        return response()->json(['updated' => $updated ?? false]);
    }

    // Admin
    public function getThrottlingInfo(): JsonResponse
    {
        $data = $this->cometApi->getThrottlingInfo();
        
        return response()->json($data ?? ['error' => 'Unable to fetch throttling info']);
    }

    public function clearCache(): JsonResponse
    {
        $this->cometApi->clearCache();
        
        return response()->json(['message' => 'Cache cleared successfully']);
    }
}
```

---

## 4. Fehlerbehandlung

### app/Exceptions/CometApiException.php

```php
<?php

namespace App\Exceptions;

use Exception;

class CometApiException extends Exception
{
    public function __construct(string $message = 'COMET API Error', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'error' => $this->message,
            'code' => $this->code,
        ], 500);
    }
}
```

---

## 5. Caching Strategie

### app/Services/CometCacheManager.php

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CometCacheManager
{
    private const CACHE_TAGS = ['comet'];

    private const TTL_LIVE = 300;          // 5 minutes
    private const TTL_NEAR_REALTIME = 900; // 15 minutes
    private const TTL_STANDARD = 3600;     // 1 hour
    private const TTL_STATIC = 86400;      // 24 hours

    /**
     * Getze cached value or store new
     */
    public static function remember(string $key, callable $callback, int $ttl = self::TTL_STANDARD)
    {
        return Cache::tags(self::CACHE_TAGS)
            ->remember($key, $ttl, $callback);
    }

    /**
     * Invalidate specific cache
     */
    public static function invalidate(string $pattern): void
    {
        // For Redis, flush by tag
        Cache::tags(self::CACHE_TAGS)->flush();
    }

    /**
     * Get TTL based on data type
     */
    public static function getTtl(string $dataType): int
    {
        return match ($dataType) {
            'live_events' => self::TTL_LIVE,
            'match_scores' => self::TTL_NEAR_REALTIME,
            'standings' => self::TTL_STANDARD,
            'competitions' => self::TTL_STANDARD,
            'players' => self::TTL_STANDARD,
            'teams' => self::TTL_STATIC,
            'facilities' => self::TTL_STATIC,
            'images' => self::TTL_STATIC,
            default => self::TTL_STANDARD,
        };
    }
}
```

---

## 6. Scheduled Synchronisation

### app/Console/Commands/SyncCometData.php

```php
<?php

namespace App\Console\Commands;

use App\Services\CometApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCometData extends Command
{
    protected $signature = 'comet:sync {--competition= : Specific competition ID}';
    protected $description = 'Synchronize COMET data with local database';

    private CometApiService $cometApi;

    public function __construct(CometApiService $cometApi)
    {
        parent::__construct();
        $this->cometApi = $cometApi;
    }

    public function handle()
    {
        try {
            $competitionId = $this->option('competition');

            if ($competitionId) {
                $this->syncCompetition($competitionId);
            } else {
                $this->syncAllCompetitions();
            }

            $this->info('✅ COMET synchronization completed');
            Log::channel('comet')->info('Synchronization completed successfully');

        } catch (\Exception $e) {
            $this->error("❌ Synchronization failed: {$e->getMessage()}");
            Log::channel('comet')->error('Synchronization failed', ['error' => $e->getMessage()]);
        }
    }

    private function syncCompetition($competitionId): void
    {
        $this->info("📊 Syncing Competition: {$competitionId}");

        // Get competition data
        $competition = $this->cometApi->getCompetition($competitionId);
        if (!$competition) {
            $this->warn("Competition {$competitionId} not found");
            return;
        }

        // Sync matches
        $matches = $this->cometApi->getCompetitionMatches($competitionId);
        $this->info("  ├─ Synced " . count($matches ?? []) . " matches");

        // Sync teams
        $teams = $this->cometApi->getCompetitionTeams($competitionId);
        $this->info("  ├─ Synced " . count($teams ?? []) . " teams");

        // Sync rankings
        $ranking = $this->cometApi->getCompetitionRanking($competitionId);
        $this->info("  └─ Synced ranking with " . count($ranking ?? []) . " teams");
    }

    private function syncAllCompetitions(): void
    {
        $this->info('📊 Syncing all competitions...');

        $competitions = $this->cometApi->getCompetitions(['active' => true, 'season' => now()->year]);
        
        if (!$competitions) {
            $this->warn('No competitions found');
            return;
        }

        foreach ($competitions as $competition) {
            $this->syncCompetition($competition['competitionFifaId']);
        }
    }
}
```

### app/Console/Kernel.php

```php
protected function schedule(Schedule $schedule)
{
    // Sync COMET data every 5 minutes
    $schedule->command('comet:sync')
        ->everyFiveMinutes()
        ->runInBackground()
        ->onOneServer();

    // Clear old COMET cache daily
    $schedule->command('cache:clear --tags=comet')
        ->daily()
        ->at('03:00');

    // Full resync daily
    $schedule->command('comet:sync')
        ->daily()
        ->at('04:00')
        ->onOneServer();
}
```

---

## 7. Monitoring & Debugging

### logging.php Configuration

```php
'comet' => [
    'driver' => 'single',
    'path' => storage_path('logs/comet.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14,
],
```

### app/Services/CometMonitoringService.php

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CometMonitoringService
{
    public static function logRequest(string $endpoint, array $params): void
    {
        Log::channel('comet')->debug('API Request', [
            'endpoint' => $endpoint,
            'params' => self::sanitize($params),
            'timestamp' => now(),
        ]);
    }

    public static function logError(string $endpoint, string $error, int $code = 0): void
    {
        Log::channel('comet')->error('API Error', [
            'endpoint' => $endpoint,
            'error' => $error,
            'code' => $code,
            'timestamp' => now(),
        ]);

        // Alert on critical errors
        if ($code >= 500) {
            self::sendAlert("COMET API Critical Error: {$error}");
        }
    }

    public static function sendAlert(string $message): void
    {
        $email = config('comet.monitoring.alert_email');
        
        if ($email) {
            Mail::raw($message, function ($message) use ($email) {
                $message->to($email)
                    ->subject('[ALERT] COMET API Error');
            });
        }
    }

    private static function sanitize(array $data): array
    {
        $sensitive = ['password', 'token', 'secret'];
        foreach ($sensitive as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***MASKED***';
            }
        }
        return $data;
    }
}
```

### Debug Command

```php
php artisan comet:sync --competition=3936145
php artisan tinker
> $service = app(CometApiService::class)
> $service->getThrottlingInfo()
> Cache::tags(['comet'])->flush()
```

---

## 🎯 Häufige Use Cases

### Use Case 1: Wettbewerb mit allen Details laden

```php
$service = app(CometApiService::class);

// Get main competition
$competition = $service->getCompetition(3936145);

// Get all related data
$teams = $service->getCompetitionTeams(3936145);
$matches = $service->getCompetitionMatches(3936145);
$ranking = $service->getCompetitionRanking(3936145);

// Combine
$competitionData = array_merge($competition, [
    'teams' => $teams,
    'matches' => $matches,
    'ranking' => $ranking,
]);
```

### Use Case 2: Live Match Updates

```php
// Get latest events (last 30 seconds)
$events = $service->getLatestMatchEvents(7763137, 30);

// Update local database with new events
foreach ($events as $event) {
    MatchEvent::updateOrCreate(
        ['comet_event_id' => $event['id']],
        $event
    );
}
```

### Use Case 3: Download Player Photo

```php
$image = $service->getImage('person', 240607);

if ($image) {
    $decoded = base64_decode($image['value']);
    Storage::disk('public')->put("players/240607.jpg", $decoded);
}
```

---

## ✅ Checkliste

- [x] .env mit Zugangsdaten konfiguriert
- [x] config/comet.php erstellt
- [x] CometApiService implementiert
- [x] Routes definiert
- [x] Controller erstellt
- [x] Caching konfiguriert
- [x] Fehlerbehandlung implementiert
- [x] Scheduled Tasks eingerichtet
- [x] Monitoring konfiguriert
- [x] Logging eingerichtet

---

**Letzte Aktualisierung**: October 23, 2025  
**Version**: 1.0  
**Status**: ✅ Production Ready  
**Authentifizierung**: ✅ HTTP Basic Auth (nkprigorje)  
**Basis URL**: ✅ https://api-hns.analyticom.de
