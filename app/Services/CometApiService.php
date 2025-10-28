<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CometApiService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private int $timeout = 30;
    private int $rateLimit = 100; // requests per second

    public function __construct()
    {
        $this->baseUrl = config('services.comet.api_url', 'https://api-hns.analyticom.de/api/export/comet');
        $this->username = config('services.comet.username');
        $this->password = config('services.comet.password');
    }

    /**
     * Get all competitions
     */
    public function getCompetitions(array $filters = []): array
    {
        return $this->get('/competitions', $filters);
    }

    /**
     * Get teams for a competition
     */
    public function getCompetitionTeams(int $competitionFifaId): array
    {
        return $this->get("/competition/{$competitionFifaId}/teams");
    }

    /**
     * Get matches for a competition
     */
    public function getCompetitionMatches(int $competitionFifaId, array $filters = []): array
    {
        return $this->get("/competition/{$competitionFifaId}/matches", $filters);
    }

    /**
     * Get competition ranking
     */
    public function getCompetitionRanking(int $competitionFifaId): array
    {
        return $this->get("/competition/{$competitionFifaId}/ranking");
    }

    /**
     * Get top scorers for a competition
     */
    public function getCompetitionTopScorers(int $competitionFifaId): array
    {
        return $this->get("/competition/{$competitionFifaId}/topScorers");
    }

    /**
     * Get own goal scorers for a competition
     */
    public function getCompetitionOwnGoalScorers(int $competitionFifaId): array
    {
        return $this->get("/competition/{$competitionFifaId}/ownGoalScorers");
    }

    /**
     * Get match details
     */
    public function getMatch(int $matchFifaId): array
    {
        return $this->get("/match/{$matchFifaId}");
    }

    /**
     * Get match events
     */
    public function getMatchEvents(int $matchFifaId, array $filters = []): array
    {
        return $this->get("/match/{$matchFifaId}/events", $filters);
    }

    /**
     * Get match phases
     */
    public function getMatchPhases(int $matchFifaId): array
    {
        return $this->get("/match/{$matchFifaId}/phases");
    }

    /**
     * Get match players
     */
    public function getMatchPlayers(int $matchFifaId): array
    {
        return $this->get("/match/{$matchFifaId}/players");
    }

    /**
     * Get match officials
     */
    public function getMatchOfficials(int $matchFifaId): array
    {
        return $this->get("/match/{$matchFifaId}/officials");
    }

    /**
     * Get team players
     */
    public function getTeamPlayers(int $teamFifaId, string $status = 'ACTIVE'): array
    {
        return $this->get("/team/{$teamFifaId}/players", ['status' => $status]);
    }

    /**
     * Get team officials
     */
    public function getTeamOfficials(int $teamFifaId, string $status = 'ACTIVE'): array
    {
        return $this->get("/team/{$teamFifaId}/teamOfficials", ['status' => $status]);
    }

    /**
     * Get competition cases (disciplinary)
     */
    public function getCompetitionCases(int $competitionFifaId): array
    {
        return $this->get("/competition/{$competitionFifaId}/cases");
    }

    /**
     * Get case sanctions
     */
    public function getCaseSanctions(int $caseFifaId, string $status = 'active'): array
    {
        return $this->get("/case/{$caseFifaId}/sanctions", ['status' => $status]);
    }

    /**
     * Get facility details
     */
    public function getFacility(int $facilityFifaId): array
    {
        return $this->get("/facility/{$facilityFifaId}");
    }

    /**
     * Get throttling info
     */
    public function getThrottlingInfo(): array
    {
        return $this->get("/throttling/info");
    }

    /**
     * Generic GET request
     */
    private function get(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . $endpoint;

        try {
            Log::info("Comet API Request", ['endpoint' => $endpoint, 'params' => $params]);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->retry(3, 1000) // Retry 3 times with 1 second delay
                ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("Comet API Success", [
                    'endpoint' => $endpoint,
                    'count' => is_array($data) ? count($data) : 1
                ]);
                return is_array($data) ? $data : [$data];
            }

            Log::error("Comet API Error", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception("API request failed with status {$response->status()}");

        } catch (\Exception $e) {
            Log::error("Comet API Exception", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get data with caching
     */
    public function getCached(string $endpoint, array $params = [], int $ttl = 300): array
    {
        $cacheKey = 'comet_api_' . md5($endpoint . json_encode($params));

        return Cache::remember($cacheKey, $ttl, function() use ($endpoint, $params) {
            return $this->get($endpoint, $params);
        });
    }

    /**
     * Clear cache for endpoint
     */
    public function clearCache(string $endpoint, array $params = []): void
    {
        $cacheKey = 'comet_api_' . md5($endpoint . json_encode($params));
        Cache::forget($cacheKey);
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $this->getThrottlingInfo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
