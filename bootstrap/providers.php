<?php

return [
    App\Providers\TenantStorageServiceProvider::class,  // MUST BE FIRST - registers storage route before Fabricator
    App\Providers\AppServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,  // Multi-Tenancy Support
    // App\Providers\TenantSessionServiceProvider::class,  // ❌ DEAKTIVIERT - extend() funktioniert nicht
    App\Providers\CentralSettingsServiceProvider::class,  // Central Settings
    App\Providers\TemplateServiceProvider::class,  // Template Loader Service
    App\Providers\Filament\CentralPanelProvider::class,
    App\Providers\Filament\TenantPanelProvider::class,
    // App\Providers\TelescopeServiceProvider::class, // Disabled: Telescope package not installed in this env
    App\Providers\VoltServiceProvider::class,
];
