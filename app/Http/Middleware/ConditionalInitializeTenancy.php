<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConditionalInitializeTenancy
{
    /**
     * Handle an incoming request.
     *
     * Initialisiert Tenancy NUR für Tenant-Domains, überspringt Central-Domains
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $centralDomains = ['localhost', '127.0.0.1', 'admin.klubportal.com'];

        // Nur für Tenant-Domains Tenancy initialisieren
        if (!in_array($host, $centralDomains)) {
            // Tenancy manuell initialisieren
            $domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $host)->first();

            if ($domain) {
                tenancy()->initialize($domain->tenant);
            }
        }

        return $next($request);
    }
}
