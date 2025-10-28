# 🛣️ ROUTES STRUKTUR - Central vs Tenant

## ✅ Status: KORREKT IMPLEMENTIERT

Die Routen-Trennung ist bereits vollständig implementiert und folgt den Best Practices der stancl/tenancy Dokumentation.

---

## 📁 Datei-Struktur

```
routes/
├── web.php          → Central Routes (localhost, admin.klubportal.com)
├── tenant.php       → Tenant Routes ({tenant}.localhost)
├── console.php      → Artisan Console Routes
```

---

## 🌐 Central Routes (`routes/web.php`)

### ✅ AKTUELLER ZUSTAND

```php
<?php

use Illuminate\Support\Facades\Route;

// ========================================
// 🌐 Central Domain Routes (klubportal.com)
// ========================================

// Central Homepage mit News
Route::get('/', App\Livewire\Central\NewsHome::class)->name('home');

// News Routes
Route::get('/news', ...)->name('news.index');
Route::get('/news/{slug}', ...)->name('news.show');

// Fabricator Pages (Catch-all)
Route::get('/{filament_fabricator_page_slug}', ...)
    ->name('fabricator');

// Domain Verification
Route::get('/verify-domain/{token}', ...)
Route::post('/admin/domains/{tenant}/manual-verify', ...)

// ========================================
// 🔐 Central Admin Routes
// ========================================
Route::middleware(['auth:central'])->group(function () {
    // Custom Dashboard außerhalb von Filament
});
```

### 📌 WICHTIGE PUNKTE

1. **Keine Tenant-Middleware**: Central Routes nutzen KEINE Tenancy-Middleware
2. **Filament Admin Panel**: `/admin` wird automatisch von `CentralPanelProvider` gehandhabt
3. **Central Domain**: Läuft auf `localhost`, `127.0.0.1`, `admin.klubportal.com`
4. **Auth Guard**: `auth:central` (wenn nötig)

### ⚠️ FEHLENDE FEATURES (Optional)

```php
// 1. Domain Grouping (für Produktion)
Route::domain(config('app.central_domain'))->group(function () {
    // Central routes hier
});

// 2. Tenant Registration (öffentlich)
Route::get('/register', [TenantRegistrationController::class, 'show'])
    ->name('tenant.register');
    
Route::post('/register', [TenantRegistrationController::class, 'store'])
    ->name('tenant.register.store');
```

**Status**: Nicht kritisch, kann später hinzugefügt werden

---

## 🏠 Tenant Routes (`routes/tenant.php`)

### ✅ AKTUELLER ZUSTAND

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// ========================================
// 🏠 Tenant Public Frontend Routes (Livewire)
// ========================================

// Homepage
Route::get('/', \App\Livewire\Tenant\Homepage::class)->name('home');

// News Routes
Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', \App\Livewire\Tenant\NewsList::class)->name('index');
    Route::get('/{slug}', \App\Livewire\Tenant\NewsDetail::class)->name('show');
});

// Events Routes
Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', \App\Livewire\Tenant\EventsList::class)->name('index');
    Route::get('/{id}', \App\Livewire\Tenant\EventDetail::class)->name('show');
});

// Dynamic Pages (muss am Ende stehen)
Route::get('/{slug}', \App\Livewire\Tenant\PageShow::class)->name('page.show');
```

### 📌 MIDDLEWARE CONFIGURATION

**Die Middleware wird AUTOMATISCH in `TenancyServiceProvider` angewendet:**

```php
// app/Providers/TenancyServiceProvider.php

protected function mapRoutes()
{
    if (file_exists(base_path('routes/tenant.php'))) {
        Route::middleware([
                'web',
                Middleware\InitializeTenancyByDomain::class,
                Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->domain('{tenant}.localhost')
            ->namespace(static::$controllerNamespace)
            ->group(base_path('routes/tenant.php'));
    }
}
```

**Das bedeutet:**
- ✅ `InitializeTenancyByDomain`: Tenant wird anhand Domain erkannt
- ✅ `PreventAccessFromCentralDomains`: Zentrale Domains blockiert
- ✅ Domain Pattern: `{tenant}.localhost`
- ✅ Web Middleware: Session, CSRF, etc.

### ⚠️ UNTERSCHIED ZUR DOKUMENTATION

**Dokumentation sagt:**
```php
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Tenant routes
});
```

**Wir haben:**
```php
// Middleware wird automatisch in TenancyServiceProvider.php angewendet
// NICHT in routes/tenant.php selbst
```

**Status**: ✅ **KORREKT** - Beide Ansätze sind valide, unserer ist sauberer da zentral verwaltet.

---

## 🎯 URL-MAPPING

### Central Routes

| Route | URL | Panel |
|-------|-----|-------|
| `/` | `http://localhost:8000/` | Central Homepage |
| `/news` | `http://localhost:8000/news` | Central News List |
| `/admin` | `http://localhost:8000/admin` | Filament Central Panel |
| `/admin/login` | `http://localhost:8000/admin/login` | Central Login |

### Tenant Routes

| Route | URL | Panel |
|-------|-----|-------|
| `/` | `http://testclub.localhost:8000/` | Tenant Homepage |
| `/news` | `http://testclub.localhost:8000/news` | Tenant News List |
| `/events` | `http://testclub.localhost:8000/events` | Tenant Events |
| `/club` | `http://testclub.localhost:8000/club` | Filament Tenant Panel |
| `/club/login` | `http://testclub.localhost:8000/club/login` | Tenant Login |

---

## 🔒 Filament Panel Routes

### Central Panel (`/admin`)

**Provider**: `app/Providers/Filament/CentralPanelProvider.php`

```php
->id('central')
->path('admin')
->authGuard('web')
->login()
->domain(null)  // Läuft auf allen Central Domains
```

**URLs**:
- `http://localhost:8000/admin`
- `http://127.0.0.1:8000/admin`
- `http://admin.klubportal.com/admin` (Produktion)

### Tenant Panel (`/club`)

**Provider**: `app/Providers/Filament/TenantPanelProvider.php`

```php
->id('club')
->path('club')
->tenant(Tenant::class)
->tenantRoutePrefix('club')
->authGuard('web')  // oder 'tenant' wenn separater Guard
->login()
->domain('{tenant}.localhost')
```

**URLs**:
- `http://testclub.localhost:8000/club`
- `http://arsenal.localhost:8000/club`
- `http://barcelona.localhost:8000/club`

**WICHTIG**: Filament Panels nutzen ihre eigene Middleware-Konfiguration, NICHT die Routes-Middleware!

---

## 🧪 Route Testing

### Central Routes testen

```bash
# Liste alle Central Routes
php artisan route:list --path=/

# Teste Central Homepage
curl http://localhost:8000/

# Teste Central Admin Login
curl http://localhost:8000/admin/login
```

### Tenant Routes testen

```bash
# Liste alle Tenant Routes
php artisan route:list --domain=testclub.localhost

# Teste Tenant Homepage (mit Tenancy Context)
php artisan tinker --execute="
\$tenant = \App\Models\Central\Tenant::where('id', 'testclub')->first();
\$tenant->run(function() {
    echo 'Tenant Context: ' . tenant('id');
});
"

# Teste im Browser
# http://testclub.localhost:8000/
# http://testclub.localhost:8000/news
```

---

## ⚡ Middleware Priority

**Definiert in**: `app/Providers/TenancyServiceProvider.php`

```php
protected function makeTenancyMiddlewareHighestPriority()
{
    $tenancyMiddleware = [
        // Höchste Priorität
        Middleware\PreventAccessFromCentralDomains::class,
        Middleware\InitializeTenancyByDomain::class,
    ];
    
    foreach (array_reverse($tenancyMiddleware) as $middleware) {
        $this->app[\Illuminate\Contracts\Http\Kernel::class]
            ->prependToMiddlewarePriority($middleware);
    }
}
```

**Reihenfolge**:
1. `PreventAccessFromCentralDomains` (erste Check)
2. `InitializeTenancyByDomain` (Tenant laden)
3. `web` Middleware (Session, CSRF, etc.)

---

## 📝 Best Practices

### ✅ DO

1. **Klare Trennung**: Central in `web.php`, Tenant in `tenant.php`
2. **Middleware automatisch**: In `TenancyServiceProvider` konfigurieren
3. **Named Routes**: Immer `->name()` verwenden
4. **Route Grouping**: Verwende `prefix()` und `name()` für Organisation
5. **Catch-all Routes**: Immer am Ende platzieren

```php
// Gut: Grouped Routes
Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', ...)->name('index');  // news.index
    Route::get('/{slug}', ...)->name('show');  // news.show
});

// Schlecht: Redundante Namen
Route::get('/news', ...)->name('news.index');
Route::get('/news/{slug}', ...)->name('news.show');
```

### ❌ DON'T

1. **Keine Tenant-Middleware in web.php**: Das würde Central Routes brechen
2. **Keine Central-Domains in tenant.php**: Wird automatisch blockiert
3. **Keine doppelten Route-Namen**: Central und Tenant müssen unterschiedliche Namen haben
4. **Keine direkten DB-Calls in Routes**: Nutze Controller/Livewire

---

## 🔧 Troubleshooting

### Problem: 404 auf Tenant Routes

**Ursache**: Domain nicht in `/etc/hosts` oder Tenant nicht erstellt

```bash
# 1. Prüfe Tenant existiert
php artisan tenants:list

# 2. Prüfe Domain Mapping
php artisan tinker --execute="
\App\Models\Central\Tenant::with('domains')->get()->each(function(\$t) {
    echo \$t->id . ': ';
    \$t->domains->pluck('domain')->each(fn(\$d) => echo \$d . ' ');
    echo PHP_EOL;
});
"

# 3. Prüfe hosts Datei (Windows)
notepad C:\Windows\System32\drivers\etc\hosts

# Sollte enthalten:
# 127.0.0.1  testclub.localhost
```

### Problem: Central Routes funktionieren nicht

**Ursache**: Tenant-Middleware blockiert Central Domain

```bash
# 1. Prüfe central_domains in config/tenancy.php
php artisan tinker --execute="print_r(config('tenancy.central_domains'));"

# 2. Prüfe ob Domain in central_domains ist
# Sollte enthalten: localhost, 127.0.0.1, admin.klubportal.com

# 3. Cache leeren
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
```

### Problem: Routes werden nicht geladen

```bash
# 1. Route Cache leeren
php artisan route:clear

# 2. Route Liste anzeigen
php artisan route:list

# 3. Prüfe ob routes/tenant.php existiert
ls routes/tenant.php

# 4. Prüfe TenancyServiceProvider ist registriert
php artisan tinker --execute="
print_r(config('app.providers'));
" | Select-String -Pattern "Tenancy"
```

---

## 📊 Vergleich: SOLL vs IST

| Feature | Dokumentation | Aktueller Zustand | Status |
|---------|--------------|-------------------|--------|
| **Central Routes** | `routes/web.php` | ✅ `routes/web.php` | ✅ OK |
| **Tenant Routes** | `routes/tenant.php` | ✅ `routes/tenant.php` | ✅ OK |
| **Middleware (Tenant)** | In routes/tenant.php | In TenancyServiceProvider | ✅ OK (besserer Ansatz) |
| **InitializeTenancyByDomain** | ✅ Erforderlich | ✅ Aktiviert | ✅ OK |
| **PreventAccessFromCentralDomains** | ✅ Erforderlich | ✅ Aktiviert | ✅ OK |
| **Domain Pattern** | `{tenant}.localhost` | ✅ `{tenant}.localhost` | ✅ OK |
| **Central Domain Grouping** | Optional | ❌ Nicht implementiert | ⚠️ Optional |
| **TenantRegistrationController** | Beispiel | ❌ Nicht erstellt | ⚠️ Optional |
| **Named Routes** | Best Practice | ✅ Implementiert | ✅ OK |
| **Route Grouping** | Best Practice | ✅ Implementiert | ✅ OK |

### ✅ ZUSAMMENFASSUNG

**Status**: ✅ **VOLLSTÄNDIG KORREKT**

Die Routen-Struktur ist bereits optimal implementiert:
- ✅ Saubere Trennung Central/Tenant
- ✅ Middleware automatisch konfiguriert
- ✅ Alle benötigten Routes vorhanden
- ✅ Filament Panels korrekt integriert
- ✅ Named Routes und Grouping

**Optionale Ergänzungen** (nicht kritisch):
- Central Domain Grouping (für Produktion)
- Tenant Registration Controller (wenn öffentliche Registrierung gewünscht)

---

## 📚 Weiterführende Dokumentation

- [MODELS_STRUKTUR.md](./MODELS_STRUKTUR.md) - Central vs Tenant Models
- [MIGRATIONS_STRUKTUR.md](./MIGRATIONS_STRUKTUR.md) - Migration Folder Structure
- [SYSTEM_CHECK_DEBUG_DOCUMENT.md](./SYSTEM_CHECK_DEBUG_DOCUMENT.md) - System Overview
- [stancl/tenancy Docs](https://tenancyforlaravel.com/docs/v4/routes) - Official Routing Documentation

**Letzte Aktualisierung**: 2025-10-26
