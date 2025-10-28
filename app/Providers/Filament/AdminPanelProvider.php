<?php

namespace App\Providers\Filament;

use App\Settings\GeneralSettings;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
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
use Illuminate\Support\Facades\DB;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Lade Settings aus der Central DB (cross-database)
        try {
            $settings = DB::connection('central')
                ->table('settings')
                ->where('group', 'general')
                ->pluck('payload', 'name')
                ->map(fn($value) => json_decode($value, true));

            $favicon = isset($settings['favicon']) && $settings['favicon']
                ? Storage::url($settings['favicon'])
                : asset('images/logo.svg');

            $siteName = $settings['site_name'] ?? 'Klubportal';
            $logo = isset($settings['logo']) && $settings['logo']
                ? Storage::url($settings['logo'])
                : null;
        } catch (\Exception $e) {
            // Fallback wenn Settings nicht geladen werden können
            $favicon = asset('images/logo.svg');
            $siteName = 'Klubportal';
            $logo = null;
        }

        return $panel
            ->id('admin')
            ->path('club')  // Tenant Admin auf /club (nicht /admin wie Central)
            ->login()
            ->authGuard('central')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandLogo(fn () => view('filament.admin.brand-logo'))
            ->brandName('') // Deaktiviere den Standard-Namen (leer), da im Custom Template enthalten
            ->favicon($favicon)
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
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
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
