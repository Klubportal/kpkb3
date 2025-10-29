<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncRankingsToTenant extends Command
{
    protected $signature = 'tenant:sync-rankings {tenant?} {--all}';
    protected $description = 'Sync COMET rankings from central database to tenant database(s)';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $syncAll = $this->option('all');

        if ($syncAll) {
            // Sync all tenants
            $tenants = \App\Models\Central\Tenant::all();
            $this->info("🔄 Synchronisiere Rankings für alle Tenants (" . $tenants->count() . ")");
            
            foreach ($tenants as $tenant) {
                $this->syncTenant($tenant);
            }
        } elseif ($tenantId) {
            // Sync specific tenant
            $tenant = \App\Models\Central\Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("❌ Tenant {$tenantId} nicht gefunden!");
                return 1;
            }
            $this->syncTenant($tenant);
        } else {
            $this->error("❌ Bitte einen Tenant angeben oder --all verwenden!");
            $this->info("   Verwendung: php artisan tenant:sync-rankings nknapijed");
            $this->info("            oder: php artisan tenant:sync-rankings --all");
            return 1;
        }

        $this->info("\n✅ Rankings-Synchronisierung abgeschlossen!");
        return 0;
    }

    private function syncTenant($tenant)
    {
        $this->newLine();
        $this->info("🏢 Tenant: {$tenant->id} ({$tenant->name})");
        
        try {
            // Tenant initialisieren
            tenancy()->initialize($tenant);

            // Hole Club FIFA ID aus Tenant Settings
            $clubFifaId = DB::connection('tenant')
                ->table('template_settings')
                ->value('club_fifa_id');

            if (!$clubFifaId) {
                $this->warn("   ⚠️  Keine Club FIFA ID gefunden - überspringe");
                return;
            }

            $this->line("   Club FIFA ID: {$clubFifaId}");

            // Hole Competition IDs aus Central DB, wo der Club beteiligt ist
            $competitionIds = DB::connection('central')
                ->table('comet_matches')
                ->where(function($query) use ($clubFifaId) {
                    $query->where('team_fifa_id_home', $clubFifaId)
                          ->orWhere('team_fifa_id_away', $clubFifaId);
                })
                ->pluck('competition_fifa_id')
                ->unique();

            if ($competitionIds->isEmpty()) {
                $this->warn("   ⚠️  Keine Competitions gefunden für Club {$clubFifaId}");
                return;
            }

            $this->line("   Competitions: " . $competitionIds->count());

            // Hole Rankings aus Central DB für diese Competitions
            $rankings = DB::connection('central')
                ->table('comet_rankings')
                ->whereIn('competition_fifa_id', $competitionIds)
                ->get();

            $this->line("   Central Rankings: " . $rankings->count());

            if ($rankings->isEmpty()) {
                $this->warn("   ⚠️  Keine Rankings in Central DB gefunden");
                return;
            }

            // Lösche alte Rankings im Tenant
            DB::connection('tenant')
                ->table('comet_rankings')
                ->truncate();

            // Importiere Rankings in Tenant DB
            $inserted = 0;
            foreach ($rankings as $ranking) {
                DB::connection('tenant')
                    ->table('comet_rankings')
                    ->insert([
                        'competition_fifa_id' => $ranking->competition_fifa_id,
                        'international_competition_name' => $ranking->international_competition_name,
                        'age_category' => $ranking->age_category,
                        'age_category_name' => $ranking->age_category_name,
                        'position' => $ranking->position,
                        'team_fifa_id' => $ranking->team_fifa_id,
                        'team_image_logo' => $ranking->team_image_logo,
                        'international_team_name' => $ranking->international_team_name,
                        'matches_played' => $ranking->matches_played,
                        'wins' => $ranking->wins,
                        'draws' => $ranking->draws,
                        'losses' => $ranking->losses,
                        'goals_for' => $ranking->goals_for,
                        'goals_against' => $ranking->goals_against,
                        'goal_difference' => $ranking->goal_difference,
                        'points' => $ranking->points,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                $inserted++;
            }

            $this->info("   ✅ {$inserted} Rankings synchronisiert");

        } catch (\Exception $e) {
            $this->error("   ❌ Fehler: " . $e->getMessage());
        } finally {
            // Tenant deinitialisieren
            tenancy()->end();
        }
    }
}
