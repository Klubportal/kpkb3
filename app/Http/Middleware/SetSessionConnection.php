<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSessionConnection
{
    /**
     * Handle an incoming request.
     *
     * Setzt die Session-Connection basierend auf der Domain:
     * - Central Domains (localhost, admin.klubportal.com) → 'central' connection
     * - Tenant Domains (*.localhost, etc.) → 'central' connection ABER eigene Session-Tabelle in Tenant-DB
     *
     * WICHTIG: Diese Middleware MUSS NACH InitializeTenancyByDomain laufen!
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $centralDomains = ['localhost', '127.0.0.1', 'admin.klubportal.com'];

        if (in_array($host, $centralDomains)) {
            // Central Domain → Central Connection
            config(['session.connection' => 'central']);
        } else {
            // Tenant Domain → Prüfe ob Tenancy initialisiert ist
            if (tenancy()->initialized) {
                // Tenancy IST initialisiert → nutze 'tenant' connection
                config(['session.connection' => 'tenant']);
            } else {
                // Tenancy NICHT initialisiert → nutze 'central' als Fallback
                // (sollte nicht passieren, aber sicherer)
                config(['session.connection' => 'central']);
            }
        }

        return $next($request);
    }
}
