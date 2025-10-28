<?php

namespace App\Providers\Filament;

use App\Filament\Central\Resources\ClubResource;
use App\Filament\Central\Resources\NewsCategoryResource;
use App\Filament\Central\Resources\NewsResource;
use App\Filament\Central\Resources\PageResource;
use App\Filament\Central\Resources\TenantResource;
use App\Filament\Central\Widgets\CentralStatsOverview;
use App\Filament\Central\Widgets\LatestTenants;
use App\Filament\Central\Widgets\PlansDistribution;
use App\Filament\Central\Widgets\TenantsChart;
use App\Settings\GeneralSettings;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Http\Middleware\Authenticate;
use Kenepa\TranslationManager\TranslationManagerPlugin;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use Z3d0X\FilamentFabricator\FilamentFabricatorPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Storage;

class CentralPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // TEMPORÄR DEAKTIVIERT zum Testen des Refresh-Loops
        /*
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['de', 'en'])
                ->displayLocale('de')
                ->visible(fn() =>
                    // Nur in Central und Super-Admin Panels anzeigen, NICHT im Tenant-Panel
                    in_array(\Filament\Facades\Filament::getCurrentPanel()?->getId(), ['central', 'super-admin'])
                );
        });
        */
    }

    public function panel(Panel $panel): Panel
    {
        // Lade Settings ohne Redis-Abhängigkeit (vermeidet "Class Redis not found" in Dev)
        // Hinweis: Wir verzichten hier bewusst auf cache()->remember(), da die Umgebung ggf. Redis erwartet.
        // Für Prod kann wieder gezielt gecached werden (z.B. cache()->store('array')->remember(...)).
        $settings = app(GeneralSettings::class);

        $favicon = $settings->favicon ? Storage::url($settings->favicon) : asset('images/logo.svg');

        return $panel
            ->id('central')
            ->path('admin')  // Central Admin auf /admin
            ->login()
            ->authGuard('web')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandLogo(fn () => view('filament.central.brand-logo'))
            ->brandName('') // Deaktiviere den Standard-Namen (leer), da im Template enthalten
            ->favicon($favicon)
            ->resources([
                TenantResource::class,
                ClubResource::class,
                NewsResource::class,
                NewsCategoryResource::class,
                PageResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Central/Pages'), for: 'App\\Filament\\Central\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Central/Widgets'), for: 'App\\Filament\\Central\\Widgets')
            ->widgets([
                CentralStatsOverview::class,
                TenantsChart::class,
                PlansDistribution::class,
                LatestTenants::class,
            ])
            // Optional plugins (only if installed)
            ->plugins(array_filter([
                class_exists(\Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin::class)
                    ? FilamentApexChartsPlugin::make()
                    : null,
                class_exists(\ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin::class)
                    ? FilamentSpatieLaravelBackupPlugin::make()->usingPage(\App\Filament\Central\Pages\Backups::class)
                    : null,
                class_exists(\Kenepa\TranslationManager\TranslationManagerPlugin::class)
                    ? TranslationManagerPlugin::make()->quickTranslateNavigationRegistration()
                    : null,
                class_exists(\Z3d0X\FilamentFabricator\FilamentFabricatorPlugin::class)
                    ? FilamentFabricatorPlugin::make()
                    : null,
            ]))
            ->middleware([
                \App\Http\Middleware\PreventTenancyOnCentralRoutes::class, // ❗ WICHTIG: Tenancy auf Central-Routes beenden!
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
