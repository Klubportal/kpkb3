<?php

namespace App\Providers\Filament;

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Services\TenantMenuService;
use App\Filament\Club\Pages\Senioren;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TenantPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Registriere dynamische Menüpunkte für jeden Request
        // Dies wird NACH dem Panel-Setup ausgeführt
    }

    public function panel(Panel $panel): Panel
    {
        // Lade Settings aus CENTRAL DB (nicht Tenant!) - mit Caching
        try {
            // Cache Central Settings für 1 Stunde
            $cacheKey = 'central_general_settings_for_tenant';

            $centralSettings = cache()->remember($cacheKey, 3600, function () {
                return DB::connection('central')
                    ->table('settings')
                    ->where('group', 'general')
                    ->pluck('payload', 'name')
                    ->map(fn($value) => json_decode($value, true));
            });

            $brandName = $centralSettings['site_name'] ?? 'Club Portal';

            // Logo aus Central Settings - WICHTIG: Muss von Central-Domain geladen werden
            // Da Tenants auf Subdomains laufen, können sie nicht direkt auf /storage zugreifen
            // Wir verwenden daher die absolute URL zur zentralen Domain
            if (isset($centralSettings['logo']) && $centralSettings['logo']) {
                // Extrahiere die Hauptdomain aus APP_URL (z.B. localhost:8000)
                $centralDomain = config('app.url');
                $logo = $centralDomain . '/storage/' . $centralSettings['logo'];
            } else {
                $logo = null;
            }

            // Favicon aus Central Settings
            if (isset($centralSettings['favicon']) && $centralSettings['favicon']) {
                $centralDomain = config('app.url');
                $favicon = $centralDomain . '/storage/' . $centralSettings['favicon'];
            } else {
                $favicon = null;
            }

            // Primärfarbe aus Central Settings
            $primaryColor = $centralSettings['primary_color'] ?? '#ef4444';
            $logoHeight = $centralSettings['logo_height'] ?? '3rem';
        } catch (\Exception $e) {
            // Fallback bei Fehler
            $brandName = 'Club Portal';
            $logo = null;
            $favicon = null;
            $primaryColor = '#ef4444';
            $logoHeight = '3rem';
        }

        return $panel
            ->id('club')
            ->path('club')  // Tenant Backend unter /club statt /admin
            ->default()     // ✅ KRITISCH: Default Panel für Multi-Tenancy!
            ->login()
            // Kein ->domain() hier - Tenancy Middleware handhabt Domain-Routing

            // 🔐 Auth Configuration - Tenant Users (Per-Club DB)
            ->authGuard('tenant')
            ->authPasswordBroker('tenants')

            // ✅ WICHTIG: Authorization deaktivieren für Multi-Tenancy
            // Ohne Shield/Permissions würde jeder Resource-Zugriff 403 werfen
            ->tenantRoutePrefix('club')
            ->databaseNotifications()

            // 🎨 Club Color Scheme - Primärfarbe aus Club Settings
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'primary' => Color::hex($primaryColor),
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])

            // 🏢 Branding - Aus CENTRAL DB Settings via Custom View
            ->brandLogo(fn () => view('filament.admin.brand-logo'))
            ->brandName('') // Deaktiviere Standard-Namen, da Custom View Logo+Name zeigt
            ->favicon($favicon)

            // ✅ Custom Script: Logo-Link auf klubportal.com umleiten + Name anzeigen
            // Entfernt: DOM-Manipulation per RenderHook, um doppelte/fehlerhafte Ausgabe zu vermeiden

            // 📱 Sidebar Configuration
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')

            // 🔍 Global Search
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchFieldSuffix(fn (): ?string => 'Suchen...')

            // 📊 Navigation
            ->navigation(true)
            ->topNavigation(false)
            ->maxContentWidth('full')

            // 🔔 Database Notifications
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')

            // 📂 Resource Discovery
            ->discoverResources(in: app_path('Filament/Club/Resources'), for: 'App\\Filament\\Club\\Resources')
            ->discoverPages(in: app_path('Filament/Club/Pages'), for: 'App\\Filament\\Club\\Pages')
            ->pages([
                Pages\Dashboard::class,
                Senioren::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Club/Widgets'), for: 'App\\Filament\\Club\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])

            // 🔧 Middleware - VEREINFACHT für Multi-Tenancy
            ->middleware([
                // 1. Cookie-Verschlüsselung
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,

                // 2. Tenancy initialisieren VOR Session
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                \App\Http\Middleware\PreventAccessFromExactCentralDomains::class,

                // 3. Standard Laravel Session Middleware
                // Session Connection wird automatisch durch MultiTenantSessionManager gewählt
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,

                // 4. CSRF & Routing
                \App\Http\Middleware\VerifyCsrfToken::class,
                SubstituteBindings::class,

                // 5. Filament-spezifische Middlewares
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                // Eigene Middleware für Tenant Guard Authentication
                \App\Http\Middleware\AuthenticateTenantPanel::class,
            ])

            // 🔌 Plugins
            ->plugins([
                // Shield DEAKTIVIERT - kann 403 verursachen bei fehlenden Permissions
                // \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])

            // ❌ Entferne Language Switcher Middleware
            ->renderHook('panels::auth.login.form.after', fn() => '');
    }
}
