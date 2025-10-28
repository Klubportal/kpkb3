# 🏗️ Fußball CMS - Multi-Tenancy Architektur

## System-Überblick

```
┌─────────────────────────────────────────────────────────────────────┐
│                      INTERNET / CLIENTS                             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────────┐  ┌──────────────────────────────────┐   │
│  │ Super Admin Portal   │  │   Club-Portale (1000 möglich)   │   │
│  │ admin.domain.com     │  │ club1.domain.com                │   │
│  │ localhost/super-admin│  │ club2.domain.com                │   │
│  │                      │  │ ...                             │   │
│  │ - Manage 1000 Clubs  │  │ - Verwalte eigene Daten         │   │
│  │ - Subscriptions      │  │ - Teams, Spieler, Spiele        │   │
│  │ - Statistics         │  │ - Trainings, Finances           │   │
│  └──────────┬───────────┘  └──────────────────┬───────────────┘   │
│             │                                 │                    │
└─────────────┼─────────────────────────────────┼────────────────────┘
              │                                 │
              ▼                                 ▼
┌──────────────────────────────────┬─────────────────────────────────┐
│   DOMAIN ROUTING & MIDDLEWARE    │   TENANCY INITIALIZATION        │
├──────────────────────────────────┼─────────────────────────────────┤
│ • InitializeTenancyByDomain      │ • Resolve Tenant by Domain      │
│ • PreventAccessFromCentralDomains│ • Initialize Tenant Context     │
│ • MultiTenantMiddleware          │ • Setup Database Connection     │
└──────────────────────────────────┴──────────┬──────────────────────┘
                                               │
        ┌──────────────────────────────────────┼──────────────────────────────────────┐
        │                                      │                                      │
        ▼                                      ▼                                      ▼
┌──────────────────────────────────┐ ┌──────────────────────────────┐ ┌──────────────┐
│   CENTRAL DATABASE               │ │  TENANT DATABASE 1           │ │ TENANT DB n  │
│   kp_club_management             │ │  tenant_[uuid-1]             │ │ tenant_[...] │
├──────────────────────────────────┤ ├──────────────────────────────┤ ├──────────────┤
│ • tenants (1000 Clubs)           │ │ • teams                      │ │ • teams      │
│ • domains                        │ │ • players                    │ │ • players    │
│ • users (Super Admins)           │ │ • matches                    │ │ • matches    │
│ • password_reset_tokens          │ │ • finances                   │ │ • finances   │
│ • cache                          │ │ • users (Club Staff)         │ │ • users      │
│ • sessions                       │ │ • notifications              │ │ • ...        │
│ • jobs                           │ │                              │ │              │
└──────────────────────────────────┘ └──────────────────────────────┘ └──────────────┘
        ▲                                      ▲                              ▲
        │                                      │                              │
        └──────────────────────────────────────┴──────────────────────────────┘
                       │ Alle über MySQL
                       │ Separate Datenbanken
                       │ Automatische Isolation
```

## 🗂️ Verzeichnisstruktur

```
kp_club_management/
├── app/
│   ├── Models/
│   │   ├── User.php              # Super Admin User
│   │   ├── Club.php              # Tenant Model (Verein)
│   │   ├── Team.php              # Tenant-Model
│   │   ├── Player.php            # Tenant-Model
│   │   ├── Match.php             # Tenant-Model
│   │   ├── TrainingSession.php   # Tenant-Model
│   │   └── Finance.php           # Tenant-Model
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── SuperAdminController.php    # Central Admin API
│   │   │   ├── ClubController.php          # Tenant-spezifisch
│   │   │   ├── PlayerController.php        # Tenant-spezifisch
│   │   │   └── ...
│   │   └── Middleware/
│   │       ├── AuthenticateSuperAdmin.php
│   │       └── AuthenticateClubUser.php
│   │
│   ├── Filament/
│   │   └── SuperAdmin/
│   │       ├── Resources/
│   │       │   └── Clubs/
│   │       │       ├── ClubResource.php
│   │       │       ├── Schemas/
│   │       │       │   └── ClubForm.php
│   │       │       └── Tables/
│   │       │           └── ClubsTable.php
│   │       ├── Pages/
│   │       └── Widgets/
│   │           └── StatsOverview.php
│   │
│   └── Providers/
│       ├── SuperAdminPanelProvider.php     # Filament Super Admin Panel
│       ├── ClubPanelProvider.php           # Filament Club Panel (TODO)
│       └── TenancyServiceProvider.php
│
├── routes/
│   ├── web.php                  # Central Routes (Super Admin)
│   ├── tenant.php               # Tenant Routes (Club-spezifisch)
│   ├── api.php                  # Central API
│   └── tenant-api.php           # Tenant API (TODO)
│
├── database/
│   ├── migrations/
│   │   ├── 2025_10_23_*_create_tenants_table.php
│   │   ├── 2025_10_23_*_create_domains_table.php
│   │   └── ... (Central Migrations)
│   │
│   ├── migrations/tenant/       # Tenant Migrations
│   │   ├── 2025_10_23_000010_create_teams_table.php
│   │   ├── 2025_10_23_000020_create_players_table.php
│   │   ├── 2025_10_23_000030_create_matches_table.php
│   │   └── 2025_10_23_000050_create_finances_table.php
│   │
│   ├── factories/
│   │   └── ClubFactory.php      # Test Data Generation
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php   # Central Seeder
│       └── TenantSeeder.php     # Tenant Seeder
│
├── config/
│   ├── tenancy.php              # Stancl Tenancy Config
│   ├── database.php             # Database Connections
│   │                             # - central (Hauptdatenbank)
│   │                             # - mysql (Template für Tenants)
│   ├── filament.php
│   └── ...
│
├── .env                         # Environment Config
├── .env.example                 # Environment Template
│
├── GETTING_STARTED.md           # Diese Datei
├── DATABASE_SETUP.md            # Datenbank Setup Anleitung
├── ARCHITECTURE.md              # Diese Datei
└── README.md                    # Laravel Default README
```

## 🔄 Request-Flow

### Super Admin Request
```
1. Browser → admin.domain.com/super-admin
2. Laravel Router
3. TenancyMiddleware prüft Domain
4. → ist in central_domains → kein Tenant aktiv
5. Filament SuperAdminPanel lädt
6. SuperAdminController reagiert
7. Queries auf Central Database (kp_club_management)
8. Response mit Übersicht aller Clubs
```

### Club/Tenant Request
```
1. Browser → club1.domain.com/players
2. Laravel Router
3. TenancyMiddleware prüft Domain
4. → Domain in central_domains.domains finden
5. → Zugehörigen Tenant (Club) laden
6. TenancyBootstrapper lädt Tenant Context
7. DatabaseBootstrapper umschaltet auf tenant_[uuid] Datenbank
8. FilesystemBootstrapper umschaltet auf tenant_[uuid] storage
9. CacheBootstrapper prefixed Cache Keys mit tenant_[uuid]
10. QueueBootstrapper isoliert Jobs
11. ClubController reagiert
12. Queries auf Tenant Database (tenant_[uuid])
13. Response mit Club-spezifischen Daten
```

## 🗄️ Datenbank-Verbindungen

```php
// config/database.php

// Central Connection (Hauptdatenbank für alle Vereine)
'central' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'kp_club_management',
    'username' => 'root',
    'prefix' => 'central_',
    // Enthält: tenants, domains, users, ...
]

// Tenant Template Connection
'mysql' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    // Wird als Template für neue Tenant-Datenbanken verwendet
]

// Dynamisch erzeugte Tenant-Verbindungen
// tenant_{uuid} → tenant_a1b2c3d4e5f6... (jeder Verein)
```

## 🎯 Multi-Tenancy Features

### 1. Database-Level Isolation
- Jeder Club hat separate Datenbank
- Zero Daten-Leakage zwischen Clubs
- Bessere Performance bei vielen Clubs
- Einfacheres Backup pro Club

### 2. Domain-Based Tenant Identification
```
URLs:
- admin.localhost → Super Admin (Central)
- club1.localhost → Club 1 (Tenant 1)
- club2.localhost → Club 2 (Tenant 2)
- fcbayern.example.com → FC Bayern (Tenant N)
```

### 3. Automatic Tenant Initialization
```
Bootstrappers (in config/tenancy.php):
✓ DatabaseTenancyBootstrapper
  → Umschaltung auf tenant_[uuid] Datenbank
✓ CacheTenancyBootstrapper
  → Cache-Keys prefixed mit tenant_[uuid]
✓ FilesystemTenancyBootstrapper
  → storage_path() → storage/app/tenant_[uuid]
✓ QueueTenancyBootstrapper
  → Jobs automatisch auf Tenant-Datenbank
```

## 🔐 Sicherheit & Isolation

### Request-Level Isolation
```php
// Middleware: InitializeTenancyByDomain
if (isDomainInCentralDomains()) {
    // Super Admin Request
    activate('central');
} else {
    // Tenant Request
    $tenant = resolveTenantByDomain();
    activate($tenant);
}
```

### Query-Level Isolation
```php
// Super Admin Query
$clubs = DB::connection('central')->table('tenants')->get();

// Tenant Query (automatisch isoliert)
$players = DB::table('players')->get(); 
// → Nutzt aktuellen Tenant DB Connection
// → tenant_[uuid].players
```

### Filesystem-Level Isolation
```
// Club 1 Uploads
/storage/app/tenant_uuid1/images/...

// Club 2 Uploads  
/storage/app/tenant_uuid2/images/...

// Central Assets
/storage/app/central_/... (falls benötigt)
```

## 📊 Subscription & Billing

Modell:
```
Club → subscription_plan → subscription_expires_at
  │
  ├─ basic      (€29/Monat)
  ├─ premium    (€59/Monat)
  └─ professional (€99/Monat)
```

Implementation:
```php
// Club Model
public function isSubscriptionActive(): bool
{
    return $this->subscription_expires_at?->isFuture();
}

// Middleware (TODO)
middleware(['checkSubscription' => function ($club) {
    if (!$club->isSubscriptionActive()) {
        return redirect()->route('renew-subscription');
    }
}])
```

## 🚀 Performance bei 1000 Clubs

### Optimierungen
1. **Database Indices**
   ```sql
   INDEX ON tenants(is_active)
   INDEX ON tenants(league, division)
   INDEX ON tenants(country)
   INDEX ON tenants(subscription_plan)
   INDEX ON domains(domain) [UNIQUE]
   ```

2. **Caching**
   - Tenant-Lookup cachen
   - Club-Info cachen (5 Min)
   - Domain-Resolution cachen

3. **Connection Pooling**
   - ProxySQL für MySQL Connection Pooling
   - Reduziert Verbindungs-Overhead

4. **Query Optimization**
   - Eager Loading verwenden
   - Select spezifische Spalten
   - Pagination für große Ergebnisse

### Expected Performance
```
Scenarios bei 1000 Clubs + 50.000 Spieler:

1. Domain Resolution        → ~5ms (cached)
2. Tenant Initialization    → ~20ms
3. Player List Query        → ~100ms (mit Pagination)
4. Super Admin Statistics   → ~200ms (aggregated)
5. Login/Auth               → ~50ms

Gesamt durchschnittliche Request-Time: 150-250ms
```

## 🛠️ Deployment Considerations

### Server Requirements (für 1000 Clubs)
```
RAM:      32+ GB
CPU:      16+ Cores
Disk:     1-2 TB SSD
MySQL:    Separate Server oder Optimierte Config
Redis:    Cache & Queue Backend
```

### Database Replication
```
Master (Write)      Slave 1 (Read)    Slave 2 (Read)
└─ 1000 Databases   └─ Replica        └─ Replica
```

### Load Balancing
```
Client → Load Balancer → App Server 1
                      → App Server 2
                      → App Server 3
```

### Monitoring
- New Relic / DataDog für Application Monitoring
- MySQL Slow Query Log
- Laravel Horizon für Queue Monitoring
- Custom Alerts für Subscription Renewals

## 🔄 Update Cycle

### Zentral Deployment
```bash
# Single Point of Deployment
php artisan migrate --database=central
php artisan filament:optimize
php artisan config:cache
```

### Tenant Deployment
```bash
# Für alle Tenants
php artisan tenants:migrate
php artisan tenants:artisan "command:run"
```

## 📈 Skalierungspfad

```
Phase 1 (Alpha):       1-10 Clubs
  ├─ Single Server
  ├─ Single MySQL Database
  └─ SQLite für Development

Phase 2 (Beta):        10-100 Clubs
  ├─ Dedicated MySQL Server
  ├─ Redis Cache
  └─ Backup Strategy

Phase 3 (Production):  100-1000 Clubs
  ├─ Multi-Server App
  ├─ MySQL Replication
  ├─ Advanced Caching
  └─ CDN for Assets

Phase 4 (Enterprise):  1000+ Clubs
  ├─ Kubernetes Orchestration
  ├─ Database Sharding
  ├─ Advanced Monitoring
  └─ Global CDN
```

---

**Version:** 1.0  
**Status:** Ready for Development  
**Last Updated:** 2025-10-23
