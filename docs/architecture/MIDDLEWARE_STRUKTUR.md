# 🛡️ MIDDLEWARE STRUKTUR

## 📋 Übersicht

Die Middleware-Struktur für Multi-Tenancy ist korrekt in **Laravel 11** konfiguriert.  
Keine `app/Http/Kernel.php` mehr - alles läuft über `bootstrap/app.php` und Service Provider.

**Wichtig**: 
- ✅ Tenant Middleware ist in `TenancyServiceProvider` konfiguriert
- ✅ Middleware Priority wird automatisch gesetzt
- ✅ Separate Route-Dateien für Central und Tenant

---

## ⚙️ Konfiguration

### 1. TenancyServiceProvider (✅ Bereits konfiguriert)

**Datei**: `app/Providers/TenancyServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();
        $this->makeTenancyMiddlewareHighestPriority();
    }

    /**
     * 🎯 TENANT ROUTES MAPPING
     * Wendet Middleware automatisch auf routes/tenant.php an
     */
    protected function mapRoutes()
    {
        if (file_exists(base_path('routes/tenant.php'))) {
            Route::middleware([
                    'web',  // Laravel Standard Web Middleware
                    Middleware\InitializeTenancyByDomain::class,
                    Middleware\PreventAccessFromCentralDomains::class,
                ])
                ->domain('{tenant}.localhost')  // Subdomain Pattern
                ->namespace(static::$controllerNamespace)
                ->group(base_path('routes/tenant.php'));
        }
    }

    /**
     * 🔝 MIDDLEWARE PRIORITY
     * Stellt sicher dass Tenancy Middleware ZUERST ausgeführt wird
     */
    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            // Höchste Priorität
            Middleware\PreventAccessFromCentralDomains::class,

            // Tenancy Initialization Middleware
            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]
                ->prependToMiddlewarePriority($middleware);
        }
    }
}
```

### 2. Bootstrap App (Laravel 11)

**Datei**: `bootstrap/app.php`

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Custom Middleware hier registrieren
        $middleware->web(append: [
            \App\Http\Middleware\CustomDomainRedirect::class,
        ]);
        
        // Middleware Aliases
        $middleware->alias([
            'tenant.subscription' => \App\Http\Middleware\TenantCheckSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

### 3. Filament Panel Middleware

**Datei**: `app/Providers/Filament/TenantPanelProvider.php`

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('club')
            ->path('club')
            ->domain('{tenant}.localhost:8000')
            
            // 🛡️ MIDDLEWARE STACK
            ->middleware([
                // Laravel Standard Middleware
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\Session\Middleware\AuthenticateSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Filament\Http\Middleware\DisableBladeIconComponents::class,
                \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
                
                // ⚠️ WICHTIG: Tenancy Middleware an ERSTER Stelle!
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ]);
    }
}
```

---

## 🚀 Request Flow

### Ablauf bei Tenant Request

```
┌─────────────────────────────────────────────────────────────────────┐
│ 1. REQUEST EINGANG                                                  │
│    http://testclub.localhost:8000/club/dashboard                    │
└─────────────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 2. WEB MIDDLEWARE GROUP                                             │
│    • EncryptCookies                                                 │
│    • StartSession                                                   │
│    • VerifyCsrfToken                                                │
│    • ShareErrorsFromSession                                         │
└─────────────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 3. INITIALIZETENANCYBYDOMAIN                                        │
│    • Domain extrahieren: testclub.localhost                         │
│    • Tenant ID ermitteln: testclub                                  │
│    • Tenant Model laden: Tenant::find('testclub')                   │
│    • Tenancy initialisieren: tenancy()->initialize($tenant)         │
│    • Bootstrappers ausführen:                                       │
│      - DatabaseTenancyBootstrapper → DB: tenant_testclub            │
│      - CacheTenancyBootstrapper → Cache Tags                        │
│      - FilesystemTenancyBootstrapper → Storage Path                 │
│      - QueueTenancyBootstrapper → Queue tenant_id                   │
└─────────────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 4. PREVENTACCESSFROMCENTRALDOMAINS                                  │
│    • Prüfen: Ist testclub.localhost eine Central Domain?            │
│    • central_domains: [localhost, 127.0.0.1, admin.klubportal.com] │
│    • testclub.localhost NICHT in Liste → ✅ ERLAUBT                 │
│    • Falls in Liste → ❌ 404 Error                                  │
└─────────────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 5. FILAMENT MIDDLEWARE                                              │
│    • Authenticate (Login Check)                                     │
│    • Authorization (Policies/Permissions)                           │
└─────────────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│ 6. CONTROLLER/LIVEWIRE                                              │
│    • Tenant Context ist aktiv                                       │
│    • DB Connection: tenant_testclub                                 │
│    • Storage: storage/tenantxxx/                                    │
│    • Cache: Tags mit 'tenanttestclub'                               │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Middleware Priority

### Automatische Priority (via TenancyServiceProvider)

```php
protected function makeTenancyMiddlewareHighestPriority()
{
    $tenancyMiddleware = [
        // 1. HÖCHSTE PRIORITÄT (blockiert Central Access)
        Middleware\PreventAccessFromCentralDomains::class,

        // 2. Tenant Initialization (verschiedene Methoden)
        Middleware\InitializeTenancyByDomain::class,
        Middleware\InitializeTenancyBySubdomain::class,
        Middleware\InitializeTenancyByDomainOrSubdomain::class,
        Middleware\InitializeTenancyByPath::class,
        Middleware\InitializeTenancyByRequestData::class,
    ];

    // Wird an den ANFANG der Middleware Priority Liste gesetzt
    foreach (array_reverse($tenancyMiddleware) as $middleware) {
        $this->app[\Illuminate\Contracts\Http\Kernel::class]
            ->prependToMiddlewarePriority($middleware);
    }
}
```

### Warum höchste Priorität?

1. **Database Zugriff**: Andere Middleware könnten DB-Queries machen
2. **Session Isolation**: Session muss in richtiger Tenant-DB sein
3. **Cache Isolation**: Cache-Tags müssen korrekt gesetzt sein
4. **Filesystem**: Storage Paths müssen tenant-spezifisch sein

**Resultat**: Tenancy wird **VOR** allen anderen Middleware initialisiert!

---

## 📁 Route-Struktur

### Central Routes (routes/web.php)

```php
<?php

use Illuminate\Support\Facades\Route;

// ❌ KEINE Tenancy Middleware
// ✅ Nur 'web' Middleware Group

Route::get('/', function () {
    return view('welcome');
});

// Central Admin Panel (Filament)
// Middleware wird in SuperAdminPanelProvider definiert
```

**Eigenschaften**:
- Middleware: `['web']`
- Domain: `localhost`, `127.0.0.1`, `admin.klubportal.com`
- DB Connection: `central` (klubportal_landlord)
- Panel: `/admin` (Super Admin Panel)

### Tenant Routes (routes/tenant.php)

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

// ✅ Middleware wird automatisch von TenancyServiceProvider hinzugefügt
// Route::middleware(['web', InitializeTenancyByDomain, PreventAccess...])

Route::get('/dashboard', function () {
    // Tenant Context ist automatisch aktiv!
    $tenant = tenant();
    return view('tenant.dashboard', compact('tenant'));
})->name('dashboard');

// Custom Middleware für Tenants
Route::middleware(['tenant.subscription'])->group(function () {
    Route::get('/premium', function () {
        return 'Premium Feature';
    });
});
```

**Eigenschaften**:
- Middleware: `['web', InitializeTenancyByDomain, PreventAccessFromCentralDomains]`
- Domain Pattern: `{tenant}.localhost` (z.B. testclub.localhost)
- DB Connection: `tenant` (dynamisch: tenant_testclub)
- Panel: `/club` (Tenant Panel)

---

## 💻 Code Beispiele

### 1. Custom Tenant Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prüft ob Tenant aktives Abonnement hat
 */
class TenantCheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        // Tenant Context ist bereits durch InitializeTenancyByDomain aktiv!
        $tenant = tenant();

        if (!$tenant) {
            // Sollte nicht passieren - Middleware nach Tenancy Middleware
            abort(500, 'Tenant context not initialized');
        }

        // Prüfe Subscription Status
        if (!$tenant->subscription_active) {
            return redirect()
                ->route('subscription.expired')
                ->with('error', 'Ihr Abonnement ist abgelaufen');
        }

        // Prüfe Subscription Level für bestimmte Features
        if ($request->routeIs('premium.*') && $tenant->subscription_level !== 'premium') {
            abort(403, 'Premium Abonnement erforderlich');
        }

        return $next($request);
    }
}
```

**Registrieren**:

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'tenant.subscription' => \App\Http\Middleware\TenantCheckSubscription::class,
        'tenant.verified' => \App\Http\Middleware\TenantEmailVerified::class,
    ]);
})
```

**Verwenden**:

```php
// routes/tenant.php
Route::middleware(['tenant.subscription'])->group(function () {
    Route::get('/premium/dashboard', [PremiumController::class, 'index']);
    Route::get('/premium/analytics', [PremiumController::class, 'analytics']);
});
```

### 2. Tenant API Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Initialisiert Tenancy für API Requests via Header
 */
class InitializeTenancyByHeader
{
    public function handle(Request $request, Closure $next)
    {
        // Tenant ID aus Header lesen
        $tenantId = $request->header('X-Tenant-ID');

        if (!$tenantId) {
            return response()->json([
                'error' => 'X-Tenant-ID header required'
            ], 400);
        }

        // Tenant laden und initialisieren
        $tenant = \App\Models\Central\Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant not found'
            ], 404);
        }

        tenancy()->initialize($tenant);

        return $next($request);
    }
}
```

**Verwenden**:

```php
// routes/api.php
Route::middleware(['api', InitializeTenancyByHeader::class])->group(function () {
    Route::get('/players', [ApiPlayerController::class, 'index']);
});
```

### 3. Conditional Tenant Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Wendet Tenant-Features basierend auf Subscription an
 */
class TenantFeatureGate
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        $tenant = tenant();

        // Feature-Mapping
        $features = [
            'analytics' => ['premium', 'enterprise'],
            'api' => ['premium', 'enterprise'],
            'custom_domain' => ['enterprise'],
            'sso' => ['enterprise'],
        ];

        if (!isset($features[$feature])) {
            abort(500, "Unknown feature: {$feature}");
        }

        $allowedPlans = $features[$feature];

        if (!in_array($tenant->subscription_level, $allowedPlans)) {
            abort(403, "Feature '{$feature}' requires: " . implode(', ', $allowedPlans));
        }

        return $next($request);
    }
}
```

**Registrieren & Verwenden**:

```php
// bootstrap/app.php
$middleware->alias([
    'tenant.feature' => \App\Http\Middleware\TenantFeatureGate::class,
]);

// routes/tenant.php
Route::middleware(['tenant.feature:analytics'])->group(function () {
    Route::get('/analytics', [AnalyticsController::class, 'index']);
});

Route::middleware(['tenant.feature:api'])->prefix('api')->group(function () {
    Route::get('/export', [ApiController::class, 'export']);
});
```

---

## 🧪 Testing

### Test 1: Middleware initialisiert Tenancy

```php
<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tenant_middleware_initializes_tenancy_correctly()
    {
        // Tenant erstellen
        $tenant = Tenant::factory()->create(['id' => 'testclub']);
        $tenant->domains()->create(['domain' => 'testclub.localhost']);

        // Request zu Tenant Domain
        $response = $this->get('http://testclub.localhost:8000/club/dashboard');

        // Assertions
        $this->assertTrue(tenancy()->initialized, 'Tenancy should be initialized');
        $this->assertNotNull(tenant(), 'Tenant should be set');
        $this->assertEquals('testclub', tenant()->id, 'Correct tenant should be loaded');
        
        $response->assertOk();
    }

    /** @test */
    public function central_domains_are_blocked_for_tenant_routes()
    {
        // Request zu Tenant Route auf Central Domain (sollte blockiert werden)
        $response = $this->get('http://localhost:8000/club/dashboard');

        // Tenancy darf NICHT initialisiert sein
        $this->assertFalse(tenancy()->initialized);
        $this->assertNull(tenant());
        
        // 404 weil PreventAccessFromCentralDomains greift
        $response->assertNotFound();
    }

    /** @test */
    public function tenant_context_is_isolated_between_requests()
    {
        $tenant1 = Tenant::factory()->create(['id' => 'club1']);
        $tenant1->domains()->create(['domain' => 'club1.localhost']);

        $tenant2 = Tenant::factory()->create(['id' => 'club2']);
        $tenant2->domains()->create(['domain' => 'club2.localhost']);

        // Request zu Tenant 1
        $this->get('http://club1.localhost:8000/club/dashboard');
        $this->assertEquals('club1', tenant()->id);

        // Tenancy Context beenden (simuliert neue Request)
        tenancy()->end();

        // Request zu Tenant 2
        $this->get('http://club2.localhost:8000/club/dashboard');
        $this->assertEquals('club2', tenant()->id);

        // Isoliert - kein Überlauf
        $this->assertNotEquals('club1', tenant()->id);
    }
}
```

### Test 2: Custom Middleware

```php
<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSubscriptionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function inactive_subscription_is_redirected()
    {
        $tenant = Tenant::factory()->create([
            'id' => 'testclub',
            'subscription_active' => false,
        ]);
        $tenant->domains()->create(['domain' => 'testclub.localhost']);

        $response = $this->get('http://testclub.localhost:8000/club/dashboard');

        $response->assertRedirect(route('subscription.expired'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function active_subscription_allows_access()
    {
        $tenant = Tenant::factory()->create([
            'id' => 'testclub',
            'subscription_active' => true,
        ]);
        $tenant->domains()->create(['domain' => 'testclub.localhost']);

        $response = $this->get('http://testclub.localhost:8000/club/dashboard');

        $response->assertOk();
    }
}
```

---

## 🔍 Debugging

### Middleware Stack anzeigen

```bash
# Route Liste mit Middleware
php artisan route:list --columns=uri,middleware,domain

# Middleware Priority
php artisan tinker
app(Illuminate\Contracts\Http\Kernel::class)->getMiddlewarePriority()
```

### Tenancy Status prüfen

```php
// In Controller oder Tinker
dd([
    'initialized' => tenancy()->initialized,
    'tenant' => tenant()?->id,
    'db_connection' => config('database.default'),
    'current_domain' => request()->getHost(),
]);
```

### Request Lifecycle Debug

```php
// app/Http/Middleware/DebugTenancyMiddleware.php
public function handle(Request $request, Closure $next)
{
    \Log::info('Before Tenancy', [
        'url' => $request->url(),
        'domain' => $request->getHost(),
        'tenancy_initialized' => tenancy()->initialized,
        'tenant' => tenant()?->id,
    ]);

    $response = $next($request);

    \Log::info('After Tenancy', [
        'tenancy_initialized' => tenancy()->initialized,
        'tenant' => tenant()?->id,
        'db' => DB::connection()->getDatabaseName(),
    ]);

    return $response;
}
```

---

## ⚠️ Troubleshooting

### Problem 1: 404 auf Tenant Domain

**Symptome**: `http://testclub.localhost:8000` gibt 404

**Ursachen & Lösungen**:

```bash
# 1. Domain in hosts-Datei fehlt
# Windows: C:\Windows\System32\drivers\etc\hosts
127.0.0.1 testclub.localhost

# 2. Tenant existiert nicht
php artisan tinker
\App\Models\Central\Tenant::find('testclub')  // Sollte Tenant zurückgeben

# 3. Domain nicht mit Tenant verknüpft
php artisan tinker
\Stancl\Tenancy\Database\Models\Domain::where('domain', 'testclub.localhost')->first()

# 4. routes/tenant.php fehlt oder leer
ls routes/tenant.php

# 5. Cache leeren
php artisan config:clear
php artisan route:clear
```

### Problem 2: Tenant Context nicht aktiv

**Symptome**: `tenant()` gibt `null` zurück in Tenant Routes

**Lösungen**:

```php
// 1. Middleware Reihenfolge prüfen
// InitializeTenancyByDomain MUSS VOR anderen Middleware kommen

// 2. Domain Pattern prüfen
// TenancyServiceProvider.php
->domain('{tenant}.localhost')  // ✅ Richtig
->domain('testclub.localhost')  // ❌ Falsch (statisch)

// 3. Middleware in Filament Panel
// TenantPanelProvider.php - InitializeTenancyByDomain an ERSTER Stelle!

// 4. Central Domain Check
// Ist die Domain in config/tenancy.php als central_domain definiert?
'central_domains' => ['localhost']  // testclub.localhost NICHT hier!
```

### Problem 3: Middleware wird doppelt ausgeführt

**Symptome**: InitializeTenancyByDomain wird 2x ausgeführt

**Ursache**: Middleware sowohl in TenancyServiceProvider als auch in Panel definiert

**Lösung**:

```php
// ENTWEDER in TenancyServiceProvider (für alle Tenant Routes)
protected function mapRoutes()
{
    Route::middleware([
        'web',
        Middleware\InitializeTenancyByDomain::class,  // ✅ Hier
    ])->domain('{tenant}.localhost')
      ->group(base_path('routes/tenant.php'));
}

// ODER in Filament Panel (nur für Panel)
// TenantPanelProvider.php
->middleware([
    \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,  // ✅ Hier
])

// ❌ NICHT BEIDE!
```

---

## 📚 Best Practices

### ✅ DO - Empfohlen

```php
// 1. Separate Route-Dateien
routes/web.php      // Central Routes (kein Tenancy)
routes/tenant.php   // Tenant Routes (mit Tenancy)
routes/api.php      // API Routes (optional Tenancy)

// 2. Middleware Priority nutzen
// Automatisch via makeTenancyMiddlewareHighestPriority()

// 3. Custom Middleware NACH Tenancy Middleware
Route::middleware(['web', InitializeTenancy, 'custom'])->group(...)

// 4. Tenant Helper verwenden
$tenant = tenant();  // Statt direkter DB Query

// 5. Domain Patterns korrekt
->domain('{tenant}.localhost')  // ✅ Platzhalter
->domain('{tenant}.*.example.com')  // ✅ Wildcard möglich

// 6. Central Domains definieren
'central_domains' => [
    'localhost',
    '127.0.0.1',
    'admin.example.com',
]
```

### ❌ DON'T - Vermeiden

```php
// 1. NICHT: Tenancy Middleware auf Central Routes
// routes/web.php
Route::middleware([InitializeTenancyByDomain::class])->group(...)  // ❌

// 2. NICHT: Gemischte Routes
// routes/web.php
Route::get('/admin', ...)   // Central ✅
Route::get('/club', ...)    // Tenant ❌ Falsche Datei!

// 3. NICHT: Tenancy manuell initialisieren wenn Middleware existiert
// Im Controller
tenancy()->initialize($tenant);  // ❌ Middleware macht das!

// 4. NICHT: Statische Domains
->domain('testclub.localhost')  // ❌ Nur für diesen Tenant

// 5. NICHT: Tenant Model in Middleware
// Middleware sollte nur Tenant ID/Domain verwenden
```

---

## 🎯 Zusammenfassung

### ✅ Middleware ist korrekt konfiguriert wenn:

1. ✅ `TenancyServiceProvider` registriert in `config/app.php`
2. ✅ `mapRoutes()` wendet Middleware auf `routes/tenant.php` an
3. ✅ `makeTenancyMiddlewareHighestPriority()` wird aufgerufen
4. ✅ Filament Panels haben Tenancy Middleware an **erster** Stelle
5. ✅ `central_domains` in `config/tenancy.php` korrekt definiert

### 🚀 Request Flow Zusammenfassung:

```
Request → Web Middleware → InitializeTenancyByDomain 
       → PreventAccessFromCentralDomains → Filament/Custom Middleware 
       → Controller (Tenant Context aktiv)
```

### 📁 Dateien die Middleware konfigurieren:

- `app/Providers/TenancyServiceProvider.php` - Route Mapping & Priority
- `bootstrap/app.php` - Middleware Aliases (Laravel 11)
- `app/Providers/Filament/TenantPanelProvider.php` - Panel Middleware
- `app/Providers/Filament/SuperAdminPanelProvider.php` - Central Panel

---

## 🔗 Weiterführende Dokumentation

- [ROUTES_STRUKTUR.md](./ROUTES_STRUKTUR.md) - Route Separation
- [SESSION_TENANCY_STRUKTUR.md](./SESSION_TENANCY_STRUKTUR.md) - Session Isolation
- [stancl/tenancy Middleware Docs](https://tenancyforlaravel.com/docs/v4/routes)

**Letzte Aktualisierung**: 2025-10-26
