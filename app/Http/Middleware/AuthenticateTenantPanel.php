<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTenantPanel
{
    /**
     * Handle an incoming request.
     *
     * Authentifiziert User für das Tenant-Panel mit dem 'tenant' Guard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prüfe ob User auf 'tenant' Guard eingeloggt ist
        if (!auth('tenant')->check()) {
            // Nicht eingeloggt → Redirect zum Login
            return redirect()->route('filament.club.auth.login');
        }

        $user = auth('tenant')->user();
        $panel = \Filament\Facades\Filament::getPanel('club');

        // Prüfe ob User Zugriff auf Panel hat
        if (!$user->canAccessPanel($panel)) {
            // Kein Zugriff → 403
            abort(403, 'Sie haben keinen Zugriff auf dieses Panel.');
        }

        return $next($request);
    }
}
