<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * Für Tenant-Panel: CSRF-Check überspringen (File-Sessions funktionieren nicht richtig mit CSRF)
     * Für Central-Panel: Normaler CSRF-Check
     */
    public function handle(Request $request, Closure $next)
    {
        // Wenn Tenancy initialisiert ist, überspringe CSRF-Validierung
        if (tenancy()->initialized) {
            \Log::info('TenantCsrfToken: Tenancy active, skipping CSRF validation');
            return $next($request);
        }

        // Für Central: Normale CSRF-Validierung
        \Log::info('TenantCsrfToken: Central domain, performing CSRF validation');
        return app(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)->handle($request, $next);
    }
}
