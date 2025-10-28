<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SyncLog;

class SyncCometForClub extends Command
{
    protected $signature = 'comet:sync-club {club_fifa_id}';
    protected $description = 'Sync COMET data only for a specific club';

    public function handle()
    {
        $clubFifaId = $this->argument('club_fifa_id');

        $this->info("🎯 Synchronisiere COMET-Daten NUR für Club FIFA ID: {$clubFifaId}");
        $this->line(str_repeat('=', 60));

        // API Settings
        $apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
        $username = 'nkprigorje';
        $password = '3c6nR$dS';
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        // Schritt 1: Hole nur aktive Wettbewerbe für diesen Club
        $this->info("\n📥 Lade aktive Wettbewerbe für Club {$clubFifaId}...");
        $ch = curl_init("{$apiUrl}/competitions?teamFifaId={$clubFifaId}&active=true");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $this->error("❌ Fehler beim Laden der Competitions: HTTP {$httpCode}");
            return 1;
        }

        $allCompetitions = json_decode($response, true);

        if (empty($allCompetitions)) {
            $this->warn("⚠️  Keine aktiven Wettbewerbe gefunden für Club {$clubFifaId}");
            $this->warn("   Club ist möglicherweise nicht im COMET-System registriert.");
            return 0;
        }

        // Schritt 2: Filtere nach aktuellem und nächstem Jahr
        $relevantCompetitions = [];
        foreach ($allCompetitions as $comp) {
            $season = $comp['season'] ?? 0;
            if ($season == $currentYear || $season == $nextYear) {
                $relevantCompetitions[] = $comp;
            }
        }

        $this->info("   Gesamt Wettbewerbe: " . count($allCompetitions));
        $this->info("   Gefiltert (Jahr {$currentYear}/{$nextYear}): " . count($relevantCompetitions));

        if (empty($relevantCompetitions)) {
            $this->warn("⚠️  Keine Wettbewerbe für {$currentYear}/{$nextYear} gefunden");
            return 0;
        }        // Schritt 3: Speichere nur diese Competitions
        $this->info("\n💾 Speichere gefundene Wettbewerbe...");

        foreach ($relevantCompetitions as $comp) {
            $this->line("   - {$comp['internationalName']} (Season: {$comp['season']})");

            DB::connection('central')->table('comet_club_competitions')->updateOrInsert(
                ['competitionFifaId' => $comp['competitionFifaId']],
                [
                    'ageCategory' => $comp['ageCategory'] ?? 'SENIORS',
                    'ageCategoryName' => $comp['ageCategoryName'] ?? 'label.seniors',
                    'internationalName' => $comp['internationalName'] ?? 'Unknown',
                    'season' => $comp['season'] ?? date('Y'),
                    'status' => $comp['status'] ?? 'ACTIVE',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }        // Schritt 4: Synchronisiere Matches, Rankings, TopScorers für diese Competitions
        $this->info("\n🔄 Starte Synchronisierung...");

        $competitionIds = array_column($relevantCompetitions, 'competitionFifaId');

        $this->call('comet:sync-matches');
        $this->call('comet:sync-rankings');
        $this->call('comet:sync-topscorers');

        $this->newLine();
        $this->info("✅ COMET-Synchronisierung für Club {$clubFifaId} abgeschlossen!");

        // Zeige Statistik
        $matchCount = DB::connection('central')->table('comet_matches')
            ->where(function($q) use ($clubFifaId) {
                $q->where('team_fifa_id_home', $clubFifaId)
                  ->orWhere('team_fifa_id_away', $clubFifaId);
            })->count();

        $this->info("\n📊 Statistik:");
        $this->line("   Matches für Club {$clubFifaId}: {$matchCount}");

        return 0;
    }
}
