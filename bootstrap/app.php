<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Storage route is registered in TenantStorageServiceProvider (runs before this)

            // Tenant Routes mit eigener Middleware-Gruppe
            // NUR laden wenn wir NICHT auf einer Central Domain sind
            $host = request()->getHost();
            $centralDomains = config('tenancy.central_domains', ['localhost', '127.0.0.1']);

            if (!in_array($host, $centralDomains)) {
                // Protected Tenant Routes
                Route::middleware(['web', 'tenant'])
                    ->group(base_path('routes/tenant.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Initialize Tenancy EARLY in the web stack so sessions/CSRF use the correct connection
        $middleware->web(prepend: [
            \App\Http\Middleware\ConditionalInitializeTenancy::class,
            // Serve storage files BEFORE routing (prevents Fabricator catch-all)
            \App\Http\Middleware\ServeStorageFiles::class,
        ]);

        // Additional web middlewares
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\CustomDomainRedirect::class,
        ]);

        // Tenancy Middleware-Gruppe (für routes/tenant.php)
        $middleware->group('tenant', [
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
