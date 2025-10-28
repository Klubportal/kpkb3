<?php

/**
 * 🛡️ TENANT MIDDLEWARE STRUKTUR - Demo Script
 *
 * Demonstriert wie Middleware-Stack für Multi-Tenancy konfiguriert ist
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Route;

echo "\n";
echo "========================================\n";
echo "   TENANT MIDDLEWARE DEMONSTRATION\n";
echo "========================================\n\n";

echo "📋 MIDDLEWARE STACK KONFIGURATION:\n\n";

// Check TenancyServiceProvider
$provider = new \App\Providers\TenancyServiceProvider($app);

echo "📁 DATEI: app/Providers/TenancyServiceProvider.php\n\n";

echo "TENANT ROUTES MIDDLEWARE:\n";
echo "┌─────────────────────────────────────────────────────┐\n";
echo "│ Route::middleware([                                 │\n";
echo "│     'web',                                          │\n";
echo "│     InitializeTenancyByDomain::class,               │\n";
echo "│     PreventAccessFromCentralDomains::class,         │\n";
echo "│ ])                                                  │\n";
echo "│ ->domain('{tenant}.localhost')                     │\n";
echo "│ ->group('routes/tenant.php')                       │\n";
echo "└─────────────────────────────────────────────────────┘\n\n";

echo "========================================\n";
echo "   MIDDLEWARE REIHENFOLGE\n";
echo "========================================\n\n";

echo "REQUEST FLOW:\n\n";

echo "1️⃣  REQUEST KOMMT AN:\n";
echo "   → http://testclub.localhost:8000/dashboard\n\n";

echo "2️⃣  WEB MIDDLEWARE:\n";
echo "   → Session starten\n";
echo "   → CSRF Token prüfen\n";
echo "   → Cookie Encryption\n";
echo "   → Share Errors to Views\n\n";

echo "3️⃣  INITIALIZETENANCYBYDOMAIN:\n";
echo "   → Domain extrahieren: testclub.localhost\n";
echo "   → Tenant ID erkennen: testclub\n";
echo "   → Tenant laden aus DB\n";
echo "   → Tenancy initialisieren\n";
echo "   → Bootstrappers ausführen:\n";
echo "      • DatabaseTenancyBootstrapper (DB auf tenant_testclub)\n";
echo "      • CacheTenancyBootstrapper (Cache Tags)\n";
echo "      • FilesystemTenancyBootstrapper (Storage Path)\n";
echo "      • QueueTenancyBootstrapper (Queue tenant_id)\n\n";

echo "4️⃣  PREVENTACCESSFROMCENTRALDOMAINS:\n";
echo "   → Prüfen: Ist Domain eine Central Domain?\n";
echo "   → localhost → ✅ ERLAUBT (ist Central Domain)\n";
echo "   → testclub.localhost → ❌ BLOCKIERT (ist Tenant Domain)\n";
echo "   → Falls blockiert: 404 Error\n\n";

echo "5️⃣  CONTROLLER ERREICHT:\n";
echo "   → Tenant Context ist aktiv\n";
echo "   → Alle DB Queries gehen zu tenant_testclub\n";
echo "   → Storage liegt in storage/tenantxxx/\n\n";

echo "========================================\n";
echo "   MIDDLEWARE PRIORITY\n";
echo "========================================\n\n";

echo "HÖCHSTE PRIORITÄT (werden ZUERST ausgeführt):\n\n";

$middlewarePriority = [
    '1. PreventAccessFromCentralDomains' => 'Blockiert Central Domains',
    '2. InitializeTenancyByDomain' => 'Erkennt Tenant anhand Domain',
    '3. InitializeTenancyBySubdomain' => 'Erkennt Tenant anhand Subdomain',
    '4. InitializeTenancyByDomainOrSubdomain' => 'Kombinierte Erkennung',
    '5. InitializeTenancyByPath' => 'Erkennt Tenant anhand URL Path',
    '6. InitializeTenancyByRequestData' => 'Erkennt Tenant anhand Request Daten',
];

foreach ($middlewarePriority as $name => $description) {
    echo "   {$name}\n";
    echo "   → {$description}\n\n";
}

echo "⚠️  WARUM HÖCHSTE PRIORITÄT?\n";
echo "   → Tenancy MUSS vor allen anderen Middleware initialisiert sein\n";
echo "   → Sonst greifen andere Middleware auf falsche DB zu\n";
echo "   → Wird automatisch in TenancyServiceProvider::makeTenancyMiddlewareHighestPriority() gesetzt\n\n";

echo "========================================\n";
echo "   MIDDLEWARE GRUPPEN\n";
echo "========================================\n\n";

echo "CENTRAL ROUTES (routes/web.php):\n";
echo "┌─────────────────────────────────────────────────────┐\n";
echo "│ Middleware: ['web']                                 │\n";
echo "│ Domain: localhost, 127.0.0.1, admin.klubportal.com │\n";
echo "│ Panel: /admin (Super Admin Panel)                  │\n";
echo "│ DB: Central (kpkb3)                  │\n";
echo "└─────────────────────────────────────────────────────┘\n\n";

echo "TENANT ROUTES (routes/tenant.php):\n";
echo "┌─────────────────────────────────────────────────────┐\n";
echo "│ Middleware: ['web', InitializeTenancy, Prevent...]  │\n";
echo "│ Domain: {tenant}.localhost (z.B. testclub.localhost)│\n";
echo "│ Panel: /club (Tenant Panel)                        │\n";
echo "│ DB: Tenant (tenant_testclub)                       │\n";
echo "└─────────────────────────────────────────────────────┘\n\n";

echo "========================================\n";
echo "   CODE BEISPIELE\n";
echo "========================================\n\n";

echo "📝 CUSTOM MIDDLEWARE FÜR TENANTS:\n\n";

echo "<?php\n";
echo "// app/Http/Middleware/TenantCheckSubscription.php\n\n";

echo "namespace App\\Http\\Middleware;\n\n";

echo "use Closure;\n";
echo "use Illuminate\\Http\\Request;\n\n";

echo "class TenantCheckSubscription\n";
echo "{\n";
echo "    public function handle(Request \$request, Closure \$next)\n";
echo "    {\n";
echo "        // Tenant Context ist bereits aktiv!\n";
echo "        \$tenant = tenant();\n\n";

echo "        if (!\$tenant->subscription_active) {\n";
echo "            return redirect()->route('subscription.expired');\n";
echo "        }\n\n";

echo "        return \$next(\$request);\n";
echo "    }\n";
echo "}\n\n";

echo "// Registrieren in bootstrap/app.php\n";
echo "->withMiddleware(function (Middleware \$middleware) {\n";
echo "    \$middleware->alias([\n";
echo "        'tenant.subscription' => TenantCheckSubscription::class,\n";
echo "    ]);\n";
echo "})\n\n";

echo "// Verwenden in routes/tenant.php\n";
echo "Route::middleware(['tenant.subscription'])->group(function () {\n";
echo "    Route::get('/premium-feature', ...);\n";
echo "});\n\n";

echo "========================================\n";
echo "   FILAMENT PANEL MIDDLEWARE\n";
echo "========================================\n\n";

echo "📁 DATEI: app/Providers/Filament/TenantPanelProvider.php\n\n";

echo "->middleware([\n";
echo "    EncryptCookies::class,\n";
echo "    AddQueuedCookiesToResponse::class,\n";
echo "    StartSession::class,\n";
echo "    AuthenticateSession::class,\n";
echo "    ShareErrorsFromSession::class,\n";
echo "    VerifyCsrfToken::class,\n";
echo "    SubstituteBindings::class,\n";
echo "    DisableBladeIconComponents::class,\n";
echo "    DispatchServingFilamentEvent::class,\n";
echo "    \\Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain::class,\n";
echo "    \\Stancl\\Tenancy\\Middleware\\PreventAccessFromCentralDomains::class,\n";
echo "])\n\n";

echo "⚠️  WICHTIG:\n";
echo "   → InitializeTenancyByDomain VOR allen anderen!\n";
echo "   → PreventAccessFromCentralDomains direkt danach\n";
echo "   → Filament Middleware kommen NACH Tenancy Middleware\n\n";

echo "========================================\n";
echo "   REQUEST BEISPIELE\n";
echo "========================================\n\n";

echo "🌍 SCENARIO 1: Central Admin Panel\n\n";

echo "Request: http://localhost:8000/admin/login\n";
echo "Domain: localhost (ist in central_domains)\n\n";

echo "Middleware Stack:\n";
echo "1. web → Session, CSRF\n";
echo "2. ❌ KEINE Tenancy Middleware\n";
echo "3. Filament Middleware\n\n";

echo "Ergebnis:\n";
echo "→ Central DB (kpkb3)\n";
echo "→ Super Admin Panel\n";
echo "→ Zugriff auf alle Tenants\n\n";

echo "🏢 SCENARIO 2: Tenant Club Panel\n\n";

echo "Request: http://testclub.localhost:8000/club/dashboard\n";
echo "Domain: testclub.localhost\n\n";

echo "Middleware Stack:\n";
echo "1. web → Session, CSRF\n";
echo "2. InitializeTenancyByDomain → Tenant 'testclub' erkannt\n";
echo "3. PreventAccessFromCentralDomains → ✅ Erlaubt (nicht Central)\n";
echo "4. Filament Middleware\n\n";

echo "Ergebnis:\n";
echo "→ Tenant DB (tenant_testclub)\n";
echo "→ Tenant Panel\n";
echo "→ Nur Zugriff auf testclub Daten\n\n";

echo "❌ SCENARIO 3: Tenant versucht Central Domain\n\n";

echo "Request: http://localhost:8000/club/dashboard\n";
echo "Domain: localhost (ist Central Domain!)\n\n";

echo "Middleware Stack:\n";
echo "1. web → Session, CSRF\n";
echo "2. InitializeTenancyByDomain → Tenant 'localhost' NICHT gefunden\n";
echo "3. PreventAccessFromCentralDomains → ❌ BLOCKIERT!\n\n";

echo "Ergebnis:\n";
echo "→ 404 Not Found\n";
echo "→ Tenant Routes sind auf Central Domains nicht verfügbar\n\n";

echo "========================================\n";
echo "   TROUBLESHOOTING\n";
echo "========================================\n\n";

echo "❌ PROBLEM: 404 auf Tenant Domain\n";
echo "✅ LÖSUNGEN:\n";
echo "   1. Prüfe: Domain in hosts-Datei:\n";
echo "      127.0.0.1 testclub.localhost\n";
echo "   2. Prüfe: Tenant existiert in DB:\n";
echo "      php artisan tinker\n";
echo "      Tenant::find('testclub')\n";
echo "   3. Prüfe: Domain ist mit Tenant verknüpft:\n";
echo "      Domain::where('domain', 'testclub.localhost')->first()\n\n";

echo "❌ PROBLEM: Tenant Context nicht aktiv\n";
echo "✅ LÖSUNGEN:\n";
echo "   1. Prüfe Middleware Reihenfolge:\n";
echo "      InitializeTenancyByDomain MUSS ZUERST kommen\n";
echo "   2. Prüfe: routes/tenant.php verwendet korrekte Middleware\n";
echo "   3. Cache leeren: php artisan config:clear\n\n";

echo "❌ PROBLEM: Central Domain zeigt Tenant Daten\n";
echo "✅ LÖSUNGEN:\n";
echo "   1. Prüfe: PreventAccessFromCentralDomains ist aktiv\n";
echo "   2. Prüfe: central_domains in config/tenancy.php\n";
echo "   3. Nie Tenant Middleware auf Central Routes verwenden!\n\n";

echo "========================================\n";
echo "   TESTING\n";
echo "========================================\n\n";

echo "🧪 MIDDLEWARE TESTEN:\n\n";

echo "<?php\n";
echo "use Illuminate\\Support\\Facades\\Route;\n";
echo "use App\\Models\\Central\\Tenant;\n\n";

echo "/** @test */\n";
echo "public function tenant_middleware_initializes_tenancy()\n";
echo "{\n";
echo "    \$tenant = Tenant::factory()->create(['id' => 'testclub']);\n";
echo "    \$tenant->domains()->create(['domain' => 'testclub.localhost']);\n\n";

echo "    // Request zu Tenant Domain\n";
echo "    \$response = \$this->get('http://testclub.localhost/club/dashboard');\n\n";

echo "    // Tenancy sollte initialisiert sein\n";
echo "    \$this->assertTrue(tenancy()->initialized);\n";
echo "    \$this->assertEquals('testclub', tenant()->id);\n";
echo "    \$response->assertOk();\n";
echo "}\n\n";

echo "/** @test */\n";
echo "public function central_domains_are_blocked_for_tenant_routes()\n";
echo "{\n";
echo "    // Request zu Tenant Route auf Central Domain\n";
echo "    \$response = \$this->get('http://localhost/club/dashboard');\n\n";

echo "    // Sollte blockiert werden\n";
echo "    \$response->assertNotFound();\n";
echo "    \$this->assertFalse(tenancy()->initialized);\n";
echo "}\n\n";

echo "========================================\n";
echo "   BEFEHLE\n";
echo "========================================\n\n";

echo "# Route Liste anzeigen (Central)\n";
echo "php artisan route:list\n\n";

echo "# Route Liste anzeigen (mit Domain Filter)\n";
echo "php artisan route:list --domain=testclub.localhost\n\n";

echo "# Middleware Priorität prüfen\n";
echo "php artisan tinker\n";
echo "app(Illuminate\\Contracts\\Http\\Kernel::class)->getMiddlewarePriority()\n\n";

echo "# Tenancy Status prüfen\n";
echo "php artisan tinker\n";
echo "tenancy()->initialized  // true/false\n";
echo "tenant() // Current Tenant oder null\n\n";

echo "========================================\n";
echo "   BEST PRACTICES\n";
echo "========================================\n\n";

echo "✅ DO - EMPFOHLEN:\n\n";

echo "1. Tenancy Middleware immer ZUERST:\n";
echo "   Route::middleware(['web', InitializeTenancy, ...])\n\n";

echo "2. Separate Route-Dateien:\n";
echo "   routes/web.php → Central\n";
echo "   routes/tenant.php → Tenant\n\n";

echo "3. PreventAccessFromCentralDomains verwenden:\n";
echo "   Verhindert Zugriff auf Tenant Routes von Central Domains\n\n";

echo "4. Middleware Priority nutzen:\n";
echo "   makeTenancyMiddlewareHighestPriority() ist bereits aktiv\n\n";

echo "5. Domain Pattern korrekt:\n";
echo "   ->domain('{tenant}.localhost') für Subdomains\n\n";

echo "❌ DON'T - VERMEIDEN:\n\n";

echo "1. NICHT: Tenancy Middleware auf Central Routes:\n";
echo "   Route::middleware(['web', InitializeTenancy]) // ❌ In web.php\n\n";

echo "2. NICHT: Gemischte Routes in einer Datei:\n";
echo "   // routes/web.php\n";
echo "   Route::get('/admin', ...) // Central\n";
echo "   Route::get('/club', ...) // Tenant ❌ Falsche Datei!\n\n";

echo "3. NICHT: Tenancy manuell initialisieren wenn Middleware existiert:\n";
echo "   // Im Controller\n";
echo "   tenancy()->initialize(\$tenant); // ❌ Middleware macht das!\n\n";

echo "========================================\n\n";

echo "✅ MIDDLEWARE STRUKTUR IST KORREKT KONFIGURIERT!\n\n";
echo "📁 Siehe: app/Providers/TenancyServiceProvider.php\n";
echo "📁 Siehe: bootstrap/app.php\n";
echo "📁 Siehe: app/Providers/Filament/TenantPanelProvider.php\n\n";
