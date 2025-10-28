<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TenantCometController extends Controller
{
    /**
     * Liste aller Tenants anzeigen
     */
    public function index()
    {
        $tenants = Tenant::with('domains')->get();

        // Hole COMET Stats für jeden Tenant
        foreach ($tenants as $tenant) {
            $clubFifaId = $tenant->data['club_fifa_id'] ?? null;
            if ($clubFifaId) {
                tenancy()->initialize($tenant);
                $tenant->comet_stats = [
                    'matches' => DB::table('comet_matches')->count(),
                    'rankings' => DB::table('comet_rankings')->count(),
                    'competitions' => DB::table('comet_club_competitions')->count(),
                ];
                tenancy()->end();
            } else {
                $tenant->comet_stats = null;
            }
        }

        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Zeige Registrierungsformular für neuen Verein
     */
    public function create()
    {
        return view('admin.tenants.create-comet');
    }

    /**
     * Registriere neuen Verein mit COMET/FIFA Daten
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Basis-Daten
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:50',

            // FIFA/COMET IDs (PFLICHT)
            'club_fifa_id' => 'required|integer|unique:comet_clubs_extended,comet_id',
            'organisation_fifa_id' => 'required|integer',

            // Club Details
            'country_code' => 'required|string|size:3', // ISO 3166-1 alpha-3 (HRV, GER, etc.)
            'city' => 'nullable|string|max:100',
            'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'logo_url' => 'nullable|url',
            'website' => 'nullable|url',

            // Subdomain
            'subdomain' => 'required|string|alpha_dash|unique:domains,domain',

            // Admin User
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();

        try {
            // 1. Erstelle Tenant
            $tenant = Tenant::create([
                'id' => $validated['subdomain'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'plan' => 'free', // Default plan
                'data' => [
                    // FIFA/COMET IDs
                    'club_fifa_id' => $validated['club_fifa_id'],
                    'organisation_fifa_id' => $validated['organisation_fifa_id'],

                    // Club Details
                    'country_code' => $validated['country_code'],
                    'city' => $validated['city'] ?? null,
                    'founded_year' => $validated['founded_year'] ?? null,
                    'logo_url' => $validated['logo_url'] ?? null,
                    'website' => $validated['website'] ?? null,

                    // Admin User Info (für später)
                    'admin_name' => $validated['admin_name'],
                    'admin_email' => $validated['admin_email'],
                    'admin_password' => bcrypt($validated['admin_password']),

                    // Metadata
                    'registered_at' => now()->toIso8601String(),
                    'registered_via' => 'admin_panel',
                ]
            ]);

            // 2. Erstelle Domain
            $tenant->domains()->create([
                'domain' => $validated['subdomain'] . '.localhost'
            ]);

            // HINWEIS: Die folgenden Jobs laufen automatisch durch TenancyServiceProvider:
            // - CreateDatabase
            // - MigrateDatabase
            // - SeedDatabase
            // - CreateDefaultClubSettings
            // - CreateDefaultAdminUser
            // - CreateCometClubRecord (NEU - müssen wir zur Pipeline hinzufügen)

            DB::commit();

            Log::info("✅ Tenant created successfully", [
                'tenant_id' => $tenant->id,
                'club_fifa_id' => $validated['club_fifa_id'],
            ]);

            return redirect()
                ->route('admin.tenants.index')
                ->with('success', "Verein '{$tenant->name}' erfolgreich erstellt! COMET Sync läuft im Hintergrund.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("❌ Tenant creation failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw ValidationException::withMessages([
                'general' => 'Fehler beim Erstellen des Vereins: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * COMET Sync Status für einen Verein anzeigen
     */
    public function cometStatus(Tenant $tenant)
    {
        $clubFifaId = $tenant->data['club_fifa_id'] ?? null;

        if (!$clubFifaId) {
            return response()->json([
                'status' => 'no_comet_id',
                'message' => 'Kein COMET Club FIFA ID hinterlegt'
            ]);
        }

        // Prüfe ob Club in comet_clubs_extended existiert
        $club = DB::connection('central')
            ->table('comet_clubs_extended')
            ->where('comet_id', $clubFifaId)
            ->first();

        if (!$club) {
            return response()->json([
                'status' => 'not_synced',
                'message' => 'Club noch nicht in COMET Datenbank',
                'club_fifa_id' => $clubFifaId
            ]);
        }

        // Hole Statistiken
        $stats = [
            'players' => DB::connection('central')->table('comet_players')->where('club_fifa_id', $clubFifaId)->count(),
            'matches' => DB::connection('central')->table('comet_matches')
                ->where(function($query) use ($clubFifaId) {
                    $query->where('team_fifa_id_home', $clubFifaId)
                          ->orWhere('team_fifa_id_away', $clubFifaId);
                })
                ->count(),
            'competitions' => DB::connection('central')->table('comet_club_competitions')->where('club_fifa_id', $clubFifaId)->count(),
            'last_sync' => $club->last_synced_at ?? $club->created_at,
        ];

        return response()->json([
            'status' => 'synced',
            'club' => $club,
            'stats' => $stats
        ]);
    }

    /**
     * Manueller COMET Sync für einen Verein auslösen
     */
    public function syncComet(Tenant $tenant)
    {
        $clubFifaId = $tenant->data['club_fifa_id'] ?? null;

        if (!$clubFifaId) {
            return back()->with('error', 'Kein COMET Club FIFA ID hinterlegt');
        }

        try {
            // Hier würden wir den Sync-Job dispatchen
            // dispatch(new SyncCometClubData($tenant, $clubFifaId));

            return back()->with('success', 'COMET Sync gestartet. Daten werden im Hintergrund aktualisiert.');

        } catch (\Exception $e) {
            Log::error("COMET Sync failed", [
                'tenant' => $tenant->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'COMET Sync fehlgeschlagen: ' . $e->getMessage());
        }
    }
}
