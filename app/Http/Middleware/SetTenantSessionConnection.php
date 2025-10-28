<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetTenantSessionConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Wenn Tenancy initialisiert ist, Session-Connection SOFORT ändern!
        if (tenancy()->initialized) {
            \Log::info('SetTenantSessionConnection: Switching to tenant session DB for ' . tenant('id'));

            // ❗ WICHTIG: Config VOR Session-Start ändern!
            config([
                'session.connection' => 'tenant',
                'session.table' => 'sessions',
            ]);

            // ✅ KRITISCH: Session komplett neu initialisieren
            $sessionManager = app('session');
            $driver = $sessionManager->getDefaultDriver();

            // Session-Driver neu erstellen mit tenant connection
            app()->forgetInstance('session');
            app()->forgetInstance('session.store');
            app()->singleton('session', function ($app) {
                return new \Illuminate\Session\SessionManager($app);
            });
        }

        return $next($request);
    }
}
