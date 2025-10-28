<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InitializeTenancyByDomainExceptCentral
{
    /**
     * Handle an incoming request.
     *
     * Initialisiert Tenancy NUR wenn Domain NICHT in central_domains ist
     */
    public function handle(Request $request, Closure $next)
    {
        $currentDomain = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        // ✅ Wenn Central Domain → SKIP Tenancy-Initialisierung
        if (in_array($currentDomain, $centralDomains, true)) {
            return $next($request);
        }

        // ✅ Wenn Tenant Domain → Initialisiere Tenancy
        return app(\Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class)
            ->handle($request, $next);
    }
}
