<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventAccessFromExactCentralDomains
{
    /**
     * Prevent access from central domains (EXACT match only!)
     *
     * Anders als Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains
     * macht diese Middleware einen EXAKTEN Match, nicht str_contains()!
     */
    public function handle(Request $request, Closure $next)
    {
        $currentDomain = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        // EXAKTER Match (nicht str_contains!)
        if (in_array($currentDomain, $centralDomains, true)) {
            \Log::warning("PreventAccessFromExactCentralDomains: Access denied from central domain: {$currentDomain}");
            abort(403, 'Access from central domains is not allowed.');
        }

        return $next($request);
    }
}
