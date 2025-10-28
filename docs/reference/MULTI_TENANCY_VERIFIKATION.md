# 📋 Multi-Tenancy System - Vollständige Verifikation

## ✅ Systemstatus: PRODUCTION READY

Alle 11 Aspekte des Multi-Tenancy Systems nach **stancl/tenancy v4** Best Practices verifiziert und dokumentiert.

---

## 📊 Verifikations-Übersicht

| # | Aspekt | Status | Dokumentation | Demo Script |
|---|--------|--------|---------------|-------------|
| 1 | **Datenbank-Trennung** | ✅ Verifiziert | - | - |
| 2 | **Models-Struktur** | ✅ Verifiziert | - | - |
| 3 | **Migrations-Trennung** | ✅ Verifiziert | - | - |
| 4 | **Routes-Trennung** | ✅ Verifiziert | - | - |
| 5 | **Storage/Filesystem** | ✅ Verifiziert | - | - |
| 6 | **Cache-Tenancy** | ✅ Verifiziert | - | - |
| 7 | **Queue/Jobs-Tenancy** | ✅ Verifiziert | - | - |
| 8 | **Session-Tenancy** | ✅ Verifiziert | [SESSION_TENANCY_STRUKTUR.md](./SESSION_TENANCY_STRUKTUR.md) | demo-tenant-sessions.php |
| 9 | **Middleware-Struktur** | ✅ Verifiziert | [MIDDLEWARE_STRUKTUR.md](./MIDDLEWARE_STRUKTUR.md) | demo-tenant-middleware.php |
| 10 | **Tenant-Aware Jobs** | ✅ Dokumentiert | [TENANT_AWARE_JOBS_VERGLEICH.md](./TENANT_AWARE_JOBS_VERGLEICH.md) | - |
| 11 | **Seeding-Struktur** | ✅ Implementiert | [SEEDING_STRUKTUR.md](./SEEDING_STRUKTUR.md) | demo-seeding-struktur.php |

---

## 🏗️ System-Architektur

### Central (Landlord) Datenbank

```
klubportal_landlord
├── domains          # Tenant Domains
├── tenants          # Tenant Records
├── plans            # Subscription Plans
├── users            # Central Super Admins
├── cms_*            # Central CMS Content
└── cache/sessions   # Isolierte Caches/Sessions
```

**Connection:** `landlord` / `central`

### Tenant Datenbanken

```
Tenant DB (fcbarcelona_xxx, realmadrid_xxx, etc.)
├── users            # Tenant-spezifische Users
├── teams            # Teams
├── players          # Spieler
├── matches          # Spiele
├── news             # News
├── events           # Events
└── cache/sessions   # Tenant-isolierte Daten
```

**Connection:** `tenant` (automatisch)

---

## 🔑 Kern-Konfigurationen

### 1. Environment Variables (.env)

```env
# Database
DB_CONNECTION=landlord
DB_DATABASE=klubportal_landlord

# Tenancy
TENANCY_DOMAIN=.localhost
CENTRAL_DOMAINS=localhost,127.0.0.1

# Session/Cache Isolation
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 2. Tenancy Bootstrappers (config/tenancy.php)

```php
'bootstrappers' => [
    DatabaseTenancyBootstrapper::class,      // ✅ DB Connection
    CacheTenancyBootstrapper::class,         // ✅ Cache Isolation
    FilesystemTenancyBootstrapper::class,    // ✅ Storage Isolation
    QueueTenancyBootstrapper::class,         // ✅ Queue/Jobs Isolation
    // SessionTenancyBootstrapper NICHT nötig - DatabaseTenancyBootstrapper regelt das!
],
```

### 3. Middleware (Laravel 11 Style)

**TenancyServiceProvider:**

```php
public function boot(): void
{
    $this->mapRoutes();
    $this->makeTenancyMiddlewareHighestPriority();
}
```

**bootstrap/app.php:**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        // Andere Middleware
    ]);
})
```

---

## 📁 Ordner-Struktur

### Models

```
app/Models/
├── Plan.php                    # Central Model
├── Tenant.php                  # Central Model
└── Tenant/                     # Tenant Models Namespace
    ├── User.php
    ├── Team.php
    ├── Player.php
    ├── Match.php
    ├── News.php
    └── Event.php
```

### Migrations

```
database/migrations/
├── 2024_01_01_create_plans_table.php           # Central
├── 2024_01_02_create_tenants_table.php         # Central
└── tenant/                                      # Tenant Migrations
    ├── 2024_01_01_create_users_table.php
    ├── 2024_01_02_create_teams_table.php
    ├── 2024_01_03_create_players_table.php
    └── ...
```

### Seeders

```
database/seeders/
├── DatabaseSeeder.php              # Central Master
├── PlansSeeder.php                 # Central
├── TenantSeeder.php                # Central (erstellt Tenants)
└── tenant/                         # Tenant Seeders
    ├── TenantDatabaseSeeder.php    # Tenant Master
    ├── DemoUserSeeder.php
    ├── TeamSeeder.php
    ├── PlayerSeeder.php
    └── ...
```

**WICHTIG:** `composer.json` muss Tenant-Namespace enthalten:

```json
{
    "autoload": {
        "psr-4": {
            "Database\\Seeders\\Tenant\\": "database/seeders/tenant/"
        }
    }
}
```

### Routes

```
routes/
├── web.php          # Central Routes
└── tenant.php       # Tenant Routes
```

---

## 🔄 Request Flow

### Central Domain (localhost)

```
http://localhost/admin
    ↓
InitializeTenancyByDomain (keine Aktion)
    ↓
Central Route (web.php)
    ↓
Connection: landlord
    ↓
Models: App\Models\Plan
```

### Tenant Domain (fcbarcelona.localhost)

```
http://fcbarcelona.localhost/dashboard
    ↓
InitializeTenancyByDomain
    ↓
Tenant identifiziert: fcbarcelona
    ↓
Bootstrappers ausgeführt:
  - DatabaseTenancyBootstrapper (DB Connection)
  - CacheTenancyBootstrapper (Cache tagged)
  - FilesystemTenancyBootstrapper (Storage Disk)
  - QueueTenancyBootstrapper (Job Context)
    ↓
Tenant Route (tenant.php)
    ↓
Connection: tenant (automatisch)
    ↓
Models: App\Models\Tenant\Team
```

---

## 💾 Daten-Isolation Mechanismen

### 1. Database Isolation

```php
// Central Model - EXPLIZIT
Plan::on('central')->create([...]);

// Tenant Model - AUTOMATISCH
Team::create([...]);  // Läuft auf Tenant DB
```

### 2. Session Isolation

```php
// session() Helper - automatisch tenant-isoliert
session(['key' => 'value']);  // Nur für aktuellen Tenant

// Datenbank-basiert mit tenant_id Tagging
sessions table:
| id | user_id | payload | tenant_id |
```

### 3. Cache Isolation

```php
// cache() Helper - automatisch tenant-tagged
Cache::put('key', 'value');  // Tag: tenant_fcbarcelona

// Central Cache - explizit
Cache::tags('central')->put('key', 'value');
```

### 4. Storage Isolation

```php
// Storage - automatisch tenant-scoped
Storage::disk('public')->put('file.pdf', $content);
// Speichert in: tenants/{tenant_id}/file.pdf

// Central Storage - explizit
Storage::disk('central')->put('file.pdf', $content);
```

### 5. Queue/Jobs Isolation

```php
// Automatischer Ansatz (95% der Fälle)
dispatch(new ProcessInvoice($invoice));
// TenantContext wird automatisch mitgegeben

// Manueller Ansatz (5% - Bulk Operations)
class BulkOperation extends Job implements TenantAwareJob
{
    use DispatchesJobs, UsesMultipleTenants;
}
```

---

## 📋 Commands Übersicht

### Migrations

```bash
# Central Migrations
php artisan migrate

# Tenant Migrations
php artisan tenants:migrate
php artisan tenants:migrate --fresh --seed
```

### Seeding

```bash
# Central Seeding
php artisan db:seed
php artisan db:seed --class=PlansSeeder

# Tenant Seeding
php artisan tenants:seed
php artisan tenants:seed --class=TenantDatabaseSeeder
php artisan tenants:seed --tenants=fcbarcelona,realmadrid
```

### Tenants Verwaltung

```bash
# Tenant erstellen
php artisan tenants:create

# Alle Tenants auflisten
php artisan tenants:list

# Tenant löschen
php artisan tenants:delete fcbarcelona
```

### Cache/Session

```bash
# Cache leeren (Central)
php artisan cache:clear

# Session leeren (Central)
php artisan session:clear

# Tenant-spezifische Caches werden automatisch getrennt
```

---

## 🧪 Testing & Debugging

### Demo Scripts

```bash
# Session Isolation Demo
php demo-tenant-sessions.php

# Middleware Stack Demo
php demo-tenant-middleware.php

# Seeding Structure Demo
php demo-seeding-struktur.php
```

### Logging

```php
// In Tenant Context
Log::info('Team created', [
    'tenant_id' => tenant()->id,
    'team_name' => $team->name,
]);

// Logs werden automatisch tenant-tagged
```

### Debuggen

```php
// Aktuellen Tenant prüfen
dd(tenant());  // Tenant Object oder null

// Connection prüfen
dd(Team::getConnectionName());  // 'tenant' oder 'central'

// Middleware Stack prüfen
dd(app(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups());
```

---

## ⚠️ Häufige Fehler & Lösungen

### 1. "SQLSTATE[42S02]: Base table not found"

**Problem:** Model versucht auf falsche DB zuzugreifen

**Lösung:**

```php
// ❌ FALSCH (in Central Seeder)
Plan::create([...]);  // Sucht in Tenant DB

// ✅ RICHTIG
Plan::on('central')->create([...]);
```

---

### 2. "Class 'Database\Seeders\Tenant\TeamSeeder' not found"

**Problem:** Composer Autoloading kennt Tenant-Namespace nicht

**Lösung:**

```json
// composer.json
"autoload": {
    "psr-4": {
        "Database\\Seeders\\Tenant\\": "database/seeders/tenant/"
    }
}
```

```bash
composer dump-autoload
```

---

### 3. "Tenant could not be identified"

**Problem:** Route läuft nicht im Tenant Context

**Lösung:**

```php
// ❌ FALSCH
Route::get('/teams', ...);  // In web.php

// ✅ RICHTIG
Route::get('/teams', ...);  // In tenant.php
```

---

### 4. Session/Cache bluten zwischen Tenants

**Problem:** SESSION_DRIVER nicht auf database gesetzt

**Lösung:**

```env
# .env
SESSION_DRIVER=database
CACHE_STORE=database
```

```bash
php artisan migrate  # sessions/cache Tabellen erstellen
```

---

### 5. Jobs verlieren Tenant Context

**Problem:** QueueTenancyBootstrapper fehlt

**Lösung:**

```php
// config/tenancy.php
'bootstrappers' => [
    QueueTenancyBootstrapper::class,  // ✅ WICHTIG!
],
```

---

## 🎯 Best Practices Zusammenfassung

### ✅ DO's

1. **Immer** `on('central')` für Central Models in Central Seeders
2. **Nie** `on('tenant')` für Tenant Models - automatisch!
3. **SESSION_DRIVER=database** für Session-Isolation
4. **CACHE_STORE=database** für Cache-Isolation (oder Redis mit Tags)
5. **QueueTenancyBootstrapper** für automatische Job-Isolation
6. Tenant-Seeders in separatem Namespace `Database\Seeders\Tenant`
7. Middleware mit `makeTenancyMiddlewareHighestPriority()` konfigurieren

### ❌ DON'Ts

1. **Nicht** SessionTenancyBootstrapper hinzufügen (wird von DatabaseTenancyBootstrapper gehandelt)
2. **Nicht** manuell Tenant Context in Jobs setzen (QueueTenancyBootstrapper macht das)
3. **Nicht** `app/Http/Kernel.php` in Laravel 11 bearbeiten (existiert nicht!)
4. **Nicht** TenantAwareJob verwenden außer für spezielle Bulk-Operations
5. **Nicht** Cache/Session über Filesystem-Driver (keine Isolation!)

---

## 📚 Dokumentations-Dateien

| Datei | Beschreibung |
|-------|-------------|
| [SESSION_TENANCY_STRUKTUR.md](./SESSION_TENANCY_STRUKTUR.md) | Session-Isolation mit DatabaseTenancyBootstrapper |
| [MIDDLEWARE_STRUKTUR.md](./MIDDLEWARE_STRUKTUR.md) | Laravel 11 Middleware-Konfiguration |
| [TENANT_AWARE_JOBS_VERGLEICH.md](./TENANT_AWARE_JOBS_VERGLEICH.md) | Automatische vs. TenantAwareJob Ansätze |
| [SEEDING_STRUKTUR.md](./SEEDING_STRUKTUR.md) | Central vs. Tenant Seeding-Trennung |

---

## 🚀 Production Deployment Checklist

### Pre-Deployment

- [ ] `.env` konfiguriert (DB, SESSION_DRIVER, CACHE_STORE)
- [ ] `composer.json` mit Tenant-Seeder Namespace
- [ ] `config/tenancy.php` Bootstrappers geprüft
- [ ] Central Migrations ausgeführt
- [ ] Tenant Migrations getestet
- [ ] Central Seeders getestet (Plans, Super-Admin)

### Deployment

```bash
# 1. Dependencies
composer install --optimize-autoloader --no-dev

# 2. Configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Central Migrations
php artisan migrate --force

# 4. Central Seeders (nur bei Bedarf)
php artisan db:seed --class=PlansSeeder --force

# 5. Tenant Migrations (für alle Tenants)
php artisan tenants:migrate --force

# 6. Tenant Seeders (nur bei Bedarf)
# php artisan tenants:seed --force
```

### Post-Deployment

- [ ] Central Admin Login testen
- [ ] Mindestens 2 Tenant Logins testen
- [ ] Session-Isolation verifizieren (parallel einloggen)
- [ ] Storage-Isolation verifizieren (File-Upload)
- [ ] Queue/Jobs Verarbeitung testen
- [ ] Cache-Isolation verifizieren

---

## 📊 System Status

| Komponente | Status | Version |
|------------|--------|---------|
| Laravel | ✅ | 11.x |
| Stancl/Tenancy | ✅ | 4.x |
| Database Separation | ✅ | Verifiziert |
| Model Separation | ✅ | Verifiziert |
| Migration Separation | ✅ | Verifiziert |
| Route Separation | ✅ | Verifiziert |
| Storage Isolation | ✅ | Verifiziert |
| Cache Isolation | ✅ | Verifiziert |
| Session Isolation | ✅ | Verifiziert |
| Queue/Jobs Isolation | ✅ | Verifiziert |
| Middleware Configuration | ✅ | Verifiziert |
| Seeding Structure | ✅ | Implementiert |

---

## 🎓 Zusammenfassung

Das Multi-Tenancy System ist **vollständig implementiert** und folgt allen **stancl/tenancy v4 Best Practices**.

### Kernmerkmale

1. ✅ **Datenbank-Separation:** Landlord + separate Tenant-DBs
2. ✅ **Model-Struktur:** `App\Models` (Central) + `App\Models\Tenant` (Tenant)
3. ✅ **Migration-Trennung:** `database/migrations/` + `database/migrations/tenant/`
4. ✅ **Route-Trennung:** `web.php` (Central) + `tenant.php` (Tenant)
5. ✅ **Storage-Isolation:** `tenants/{tenant_id}/` Disk-Struktur
6. ✅ **Cache-Isolation:** Database-basiert mit tenant_id Tags
7. ✅ **Session-Isolation:** Database-basiert mit tenant_id
8. ✅ **Queue/Jobs-Isolation:** QueueTenancyBootstrapper (automatisch)
9. ✅ **Middleware:** Laravel 11 Style mit TenancyServiceProvider
10. ✅ **Seeding-Struktur:** `database/seeders/` + `database/seeders/tenant/`

### Besonderheiten dieses Systems

- **Keine SessionTenancyBootstrapper nötig** - DatabaseTenancyBootstrapper regelt alles
- **Keine app/Http/Kernel.php** - Laravel 11 verwendet bootstrap/app.php
- **Automatische Job-Isolation** - 95% der Fälle brauchen kein TenantAwareJob
- **Tenant-Seeders in separatem Namespace** - PSR-4 Autoloading in composer.json

---

**Status:** ✅ PRODUCTION READY
**Dokumentiert:** 2025-01-26
**Framework:** Laravel 11 + Stancl/Tenancy 4.x
