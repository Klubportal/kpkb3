<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Central\Tenant;

class CustomDomainRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Check if this is a custom domain (not klubportal.com, subdomain, or localhost development)
        if (!str_ends_with($host, '.klubportal.com')
            && $host !== 'klubportal.com'
            && $host !== 'localhost'
            && $host !== '127.0.0.1'
            && $host !== '0.0.0.0'
            && !str_ends_with($host, '.localhost')) {

            // Find tenant with this custom domain
            $tenant = Tenant::where('custom_domain', $host)
                ->where('custom_domain_verified', true)
                ->where('custom_domain_status', 'active')
                ->first();

            if ($tenant) {
                // Initialize tenancy for this custom domain
                tenancy()->initialize($tenant);

                return $next($request);
            }

            // Custom domain not found or not verified
            abort(404, 'Domain not found or not verified');
        }

        return $next($request);
    }
}
