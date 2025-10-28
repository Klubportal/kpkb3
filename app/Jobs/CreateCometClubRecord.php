<?php

namespace App\Jobs;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateCometClubRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected TenantWithDatabase $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Erstellt den zentralen COMET Club-Datensatz in kpkb3
     * nach FIFA-Vorgaben
     */
    public function handle(): void
    {
        try {
            Log::info("🏢 Creating COMET club record for tenant: {$this->tenant->id}");

            // Hole Tenant-Daten (FIFA ID sollte bei Registrierung angegeben werden)
            $tenantData = $this->tenant->data ?? [];
            $clubFifaId = $tenantData['club_fifa_id'] ?? null;
            $organisationFifaId = $tenantData['organisation_fifa_id'] ?? null;

            if (!$clubFifaId) {
                Log::warning("⚠️  No club_fifa_id provided for tenant {$this->tenant->id}");
                return;
            }

            // Erstelle Club-Datensatz in comet_clubs_extended
            $club = DB::connection('central')->table('comet_clubs_extended')->updateOrInsert(
                ['comet_id' => $clubFifaId],
                [
                    'name' => $this->tenant->name,
                    'short_name' => $this->generateShortName($this->tenant->name),
                    'slug' => $this->tenant->id,
                    'organisation_fifa_id' => $organisationFifaId,
                    'country_code' => $tenantData['country_code'] ?? 'HRV', // Default Kroatien
                    'city' => $tenantData['city'] ?? null,
                    'founded_year' => $tenantData['founded_year'] ?? null,
                    'logo_url' => $tenantData['logo_url'] ?? null,
                    'website' => $tenantData['website'] ?? null,
                    'email' => $this->tenant->email,
                    'phone' => $this->tenant->phone,
                    'status' => 'active',
                    'is_synced' => false,
                    'tenant_id' => $this->tenant->id, // Link zum Tenant
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Speichere COMET Club ID im Tenant
            $this->tenant->update([
                'data' => array_merge($tenantData, [
                    'comet_club_id' => $clubFifaId,
                    'comet_record_created' => true,
                    'comet_created_at' => now()->toIso8601String(),
                ])
            ]);

            Log::info("✅ COMET club record created for {$this->tenant->name} (FIFA ID: {$clubFifaId})");

        } catch (\Exception $e) {
            Log::error("❌ Failed to create COMET club record: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generiere Kurzname aus Vollname
     */
    private function generateShortName(string $name): string
    {
        // NK Prigorje Markuševec -> NK Prigorje
        // FC Bayern München -> FCB

        $words = explode(' ', $name);

        if (count($words) <= 2) {
            return $name;
        }

        // Wenn Name mit Abkürzung beginnt (NK, HNK, FC, etc.)
        if (strlen($words[0]) <= 3 && strtoupper($words[0]) === $words[0]) {
            return $words[0] . ' ' . $words[1];
        }

        // Sonst erste 2-3 Wörter
        return implode(' ', array_slice($words, 0, 3));
    }
}
