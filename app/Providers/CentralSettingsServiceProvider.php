<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CentralSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Settings werden on-demand geladen
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Setze Central Connection als Standard für Settings-Klassen
        // wenn wir nicht in einem Tenant-Context sind
    }
}
