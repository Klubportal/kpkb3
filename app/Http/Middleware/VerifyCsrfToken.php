<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Livewire Routes (für alle Domains)
        'livewire/*',
        'livewire/update',
        'livewire/upload-file',
        'livewire/message/*',

        // Filament Tenant Panel - GEZIELT nur Club-Panel
        'club/*',
        'club/livewire/*',
    ];

    /**
     * Handle an incoming request.
     *
     * Override für Multi-Tenancy: CSRF komplett überspringen für Tenant-Domains
     */
    public function handle($request, \Closure $next)
    {
        $host = $request->getHost();
        $centralDomains = ['localhost', '127.0.0.1', 'admin.klubportal.com'];

        // ✅ Tenant-Domain → CSRF komplett deaktivieren
        if (!in_array($host, $centralDomains)) {
            return $next($request);
        }

        // Central-Domain → Standard CSRF-Prüfung
        return parent::handle($request, $next);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * Gezielter Schutz: CSRF nur für Tenant-Domains deaktivieren, Central bleibt geschützt
     */
    protected function inExceptArray($request)
    {
        $host = $request->getHost();
        $path = $request->path();

        // Central-Domains (localhost, 127.0.0.1) - CSRF aktiv!
        $centralDomains = ['localhost', '127.0.0.1', 'admin.klubportal.com'];

        // ✅ Tenant-Domain erkannt → Livewire/Club-Routes ohne CSRF
        if (!in_array($host, $centralDomains)) {
            // Nur Livewire und Club-Panel ohne CSRF, andere Routes geschützt
            if (str_contains($path, 'livewire/') || str_starts_with($path, 'club/')) {
                return true;
            }
        }

        // ✅ Livewire auf ALLEN Domains ohne CSRF (für Filament)
        if (str_contains($path, 'livewire/')) {
            return true;
        }

        // Standard-Prüfung gegen $except Array
        return parent::inExceptArray($request);
    }

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * Override für Multi-Tenancy: Tenant-Domains ohne Token-Matching
     */
    protected function tokensMatch($request)
    {
        $host = $request->getHost();
        $path = $request->path();

        // Central-Domains (localhost, 127.0.0.1)
        $centralDomains = ['localhost', '127.0.0.1', 'admin.klubportal.com'];

        // ✅ Tenant-Domain → CSRF-Token-Matching überspringen für Livewire/Club
        if (!in_array($host, $centralDomains)) {
            // Nur für Livewire und Club-Routes Token-Matching deaktivieren
            if (str_contains($path, 'livewire/') || str_starts_with($path, 'club/')) {
                return true;  // Token gilt als "matched"
            }
        }

        // ✅ Livewire auf ALLEN Domains ohne Token-Matching (Filament-Kompatibilität)
        if (str_contains($path, 'livewire/')) {
            return true;
        }

        // Standard Laravel Token-Matching für alle anderen Routes
        return parent::tokensMatch($request);
    }
}
