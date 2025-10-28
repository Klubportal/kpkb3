<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventTenancyOnCentralRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ⚠️ WICHTIG: Blockiere Central-Panel auf Tenant-Domains komplett!
        $host = $request->getHost();
        $centralDomains = ['localhost', '127.0.0.1', 'admin.klubportal.com'];

        if (!in_array($host, $centralDomains)) {
            // Wir sind auf einer Tenant-Domain, aber versuchen auf Central-Panel zuzugreifen
            abort(403, 'Central-Panel ist nur auf localhost verfügbar. Bitte nutzen Sie /club für das Verein-Backend.');
        }

        // Wenn Tenancy initialisiert ist, aber wir auf einer Central-Route sind, beende Tenancy!
        if (tenancy()->initialized) {
            \Log::warning('PreventTenancyOnCentralRoutes: Tenancy war initialisiert auf Central-Route! Beende Tenancy...');
            tenancy()->end();

            // Force Central Connection
            config(['database.default' => 'mysql']);
            config(['session.connection' => 'central']);
            config(['session.driver' => 'database']); // ❗ WICHTIG: Zurück zu database driver!

            \Log::info('PreventTenancyOnCentralRoutes: Tenancy beendet, Central Connection wiederhergestellt');
        } else {
            // Stelle sicher, dass wir Central-Config haben
            config(['database.default' => 'mysql']);
            config(['session.connection' => 'central']);
            config(['session.driver' => 'database']);
        }

        return $next($request);
    }
}
