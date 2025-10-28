<?php

namespace App\Providers;

use App\Models\Central\News;
use App\Observers\NewsObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenantCreated;
use App\Listeners\CreateTenantSuperAdmin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ✅ Multi-Tenant Session Manager
        $this->app->singleton('session', function ($app) {
            return new \App\Session\MultiTenantSessionManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observer für News registrieren
        News::observe(NewsObserver::class);

        // View Composer for layout stats (only if in tenant context)
        view()->composer(['templates.kp.layout', 'templates.bm.layout'], function ($view) {
            try {
                // Only calculate stats if we're in a tenant context
                if (tenancy()->initialized) {
                    // Get template settings
                    $settings = \App\Models\Tenant\TemplateSetting::first() ?? new \App\Models\Tenant\TemplateSetting();
                    $clubFifaId = $settings->club_fifa_id ?? 396; // Default to NK Naprijed

                    $totalMatches = DB::table('comet_matches')
                        ->where(function($query) use ($clubFifaId) {
                            $query->where('team_fifa_id_home', $clubFifaId)
                                  ->orWhere('team_fifa_id_away', $clubFifaId);
                        })
                        ->count();

                    // Use comet_top_scorers instead of non-existent tables
                    $totalGoals = DB::table('comet_top_scorers')
                        ->where('club_id', $clubFifaId)
                        ->sum('goals');

                    // Count unique players from comet_top_scorers
                    $totalPlayers = DB::table('comet_top_scorers')
                        ->where('club_id', $clubFifaId)
                        ->distinct('player_fifa_id')
                        ->count('player_fifa_id');

                    $view->with('settings', $settings);
                    $view->with('totalMatches', $totalMatches ?? 0);
                    $view->with('totalGoals', $totalGoals ?? 0);
                    $view->with('totalPlayers', $totalPlayers ?? 0);
                }
            } catch (\Exception $e) {
                // If error, provide default values
                $view->with('settings', new \App\Models\Tenant\TemplateSetting());
                $view->with('totalMatches', 0);
                $view->with('totalGoals', 0);
                $view->with('totalPlayers', 0);
            }
        });

        // ✅ Policy Registration für Multi-Tenancy
        Gate::policy(\App\Models\Tenant\Player::class, \App\Policies\PlayerPolicy::class);

        // Admin hat ALLE Permissions
        // ONLY apply this Gate when user is authenticated
        Gate::before(function ($user = null, $ability) {
            // Skip if no user (e.g., login page)
            if (!$user) {
                return null;
            }

            // ✅ TENANT USERS: Haben immer vollen Zugriff (ohne Shield)
            if ($user instanceof \App\Models\Tenant\User) {
                return true;
            }

            // Cache User Roles (nur für Central Users)
            if (!isset($user->roles_cached)) {
                $user->roles_cached = $user->roles()->pluck('name')->toArray();
            }

            // Check both naming conventions for super admin
            if ($user->hasRole('super-admin') || $user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });

        // Event Listener für automatischen Admin bei Tenant-Erstellung
        // DISABLED: Using CreateDefaultAdminUser job in TenantRegistrationController instead
        // Event::listen(
        //     TenantCreated::class,
        //     CreateTenantSuperAdmin::class
        // );
    }
}
