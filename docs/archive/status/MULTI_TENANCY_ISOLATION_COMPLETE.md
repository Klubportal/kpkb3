# Multi-Tenancy Isolation - Vollständige Übersicht

## Status aller Isolationsbereiche

| Bereich | Status | Priorität | Implementation | Datei/Location |
|---------|--------|-----------|----------------|----------------|
| **Datenbanken** | ✅ Vollständig | MUSS | Separate DB pro Tenant | `database.tenancy_db_name` |
| **Models** | ✅ Vollständig | MUSS | Getrennte Namespaces | `App\Models\Central\*` vs `App\Models\Tenant\*` |
| **Routen** | ✅ Vollständig | MUSS | Separate Route-Dateien | `routes/web.php` vs `routes/tenant.php` |
| **Migrations** | ✅ Vollständig | MUSS | Getrennte Ordner | `migrations/` vs `migrations/tenant/` |
| **Storage** | ✅ Vollständig | MUSS | FilesystemTenancyBootstrapper | `storage/app/tenants/{id}/` |
| **Cache** | ✅ Vollständig | MUSS | CacheTenancyBootstrapper | `tenant_{id}_cache` Prefix |
| **Sessions** | ✅ Vollständig | EMPFOHLEN | SessionTenancyBootstrapper | Separate Session-Stores |
| **Queues** | ✅ Vollständig | EMPFOHLEN | QueueTenancyBootstrapper | Tenant-Context in Jobs |
| **Seeder** | ✅ Vollständig | OPTIONAL | Tenant Seeder System | `database/seeders/tenant/` |
| **Config** | ✅ Vollständig | OPTIONAL | Event-basiert | `ConfigureTenantEnvironment` Listener |
| **Testing** | ✅ Vollständig | EMPFOHLEN | Test-Infrastruktur | `tests/TenantTestCase.php` |

---

## 1. Datenbanken - Separate DB pro Tenant ✅

### Implementation
```php
// config/tenancy.php
'database' => [
    'based_on_permission' => false,
    'prefix' => 'tenant',
    'suffix' => '',
],

// Tenant Model
protected $connection = 'central';

public function getTenancyDbNameAttribute()
{
    return 'tenant' . $this->id;
}
```

### Verwendung
```php
// Automatisch bei Tenant-Initialisierung
tenancy()->initialize($tenant);
// Wechselt zu tenant{id} Datenbank

// Manuell
DB::connection('tenant')->table('teams')->get();
```

### Dateien
- `config/tenancy.php`
- `app/Models/Central/Tenant.php`

---

## 2. Models - Getrennte Namespaces ✅

### Central Models
```
app/Models/Central/
├── Tenant.php
├── User.php
├── Plan.php
├── News.php
└── Page.php
```

**Namespace:** `App\Models\Central`

### Tenant Models
```
app/Models/Tenant/
├── Team.php
├── Player.php
├── FootballMatch.php
├── User.php
├── News.php
├── Event.php
├── Member.php
├── Training.php
├── Season.php
└── Standing.php
```

**Namespace:** `App\Models\Tenant`

### Best Practice
```php
// ✅ Richtig
use App\Models\Central\Tenant;
use App\Models\Tenant\Team;

// ❌ Falsch
use App\Models\Tenant; // Mehrdeutig!
```

---

## 3. Routen - Separate Route-Dateien ✅

### Central Routes (`routes/web.php`)
```php
// Landing Page, Registrierung, etc.
Route::get('/', [HomeController::class, 'index']);
Route::get('/pricing', [PricingController::class, 'index']);
Route::post('/register-tenant', [TenantController::class, 'register']);
```

### Tenant Routes (`routes/tenant.php`)
```php
// Nur erreichbar wenn Tenant initialisiert
Route::middleware(['web', 'tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('/teams', TeamController::class);
    Route::resource('/players', PlayerController::class);
});
```

### Registrierung
```php
// bootstrap/app.php oder TenancyServiceProvider
Route::middleware('tenant')
    ->group(base_path('routes/tenant.php'));
```

---

## 4. Migrations - Getrennte Ordner ✅

### Central Migrations (`database/migrations/`)
```
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
2024_01_15_000000_create_tenants_table.php
2024_01_15_000001_create_domains_table.php
2024_01_15_000002_create_plans_table.php
```

**Command:** `php artisan migrate`

### Tenant Migrations (`database/migrations/tenant/`)
```
2024_01_20_000000_create_teams_table.php
2024_01_20_000001_create_players_table.php
2024_01_20_000002_create_matches_table.php
2024_01_20_000003_create_news_table.php
2024_01_20_000004_create_events_table.php
```

**Command:** `php artisan tenants:migrate`

### Rollback
```bash
# Central
php artisan migrate:rollback

# All Tenants
php artisan tenants:migrate-rollback

# Specific Tenant
php artisan tenants:migrate-rollback --tenants=testclub
```

---

## 5. Storage - FilesystemTenancyBootstrapper ✅

### Konfiguration
```php
// config/tenancy.php
'bootstrappers' => [
    FilesystemTenancyBootstrapper::class,
    // ...
],
```

### Verwendung
```php
tenancy()->initialize($tenant);

// Speichert automatisch in storage/app/tenants/{id}/
Storage::disk('public')->put('logo.png', $file);

// Pfad: storage/app/tenants/testclub/logo.png
```

### Struktur
```
storage/app/
├── public/              # Central
└── tenants/
    ├── testclub/
    │   ├── public/
    │   ├── private/
    │   └── media/
    ├── arsenal/
    └── barcelona/
```

---

## 6. Cache - CacheTenancyBootstrapper ✅

### Konfiguration
```php
// config/tenancy.php
'bootstrappers' => [
    CacheTenancyBootstrapper::class,
    // ...
],
```

### Automatisches Prefix
```php
tenancy()->initialize($tenant);

// Cache-Key wird automatisch: tenant_testclub_cache:teams
Cache::put('teams', $teams, 3600);

// Jeder Tenant hat eigenen Cache-Namespace
```

### Manuell
```php
// In ConfigureTenantEnvironment.php
Config::set('cache.prefix', 'tenant_' . $tenant->id . '_cache');
```

---

## 7. Sessions - SessionTenancyBootstrapper ✅

### Konfiguration
```php
// config/tenancy.php
'bootstrappers' => [
    SessionTenancyBootstrapper::class,
    // ...
],
```

### Effekt
```php
// Session-ID wird tenant-spezifisch
// testclub: session_testclub_xyz123
// arsenal: session_arsenal_abc456

// Verhindert Session-Bleeding zwischen Tenants
```

### Session-Daten
```php
session(['user_preferences' => $data]);
// Nur für aktuellen Tenant verfügbar
```

---

## 8. Queues - QueueTenancyBootstrapper ✅

### Konfiguration
```php
// config/tenancy.php
'bootstrappers' => [
    QueueTenancyBootstrapper::class,
    // ...
],
```

### Jobs mit Tenant-Context
```php
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessMatchResults implements ShouldQueue
{
    use Queueable, TenantAware;

    public function handle()
    {
        // Läuft automatisch im richtigen Tenant-Context
        $matches = FootballMatch::where('status', 'finished')->get();
        // ...
    }
}

// Dispatchen
tenancy()->initialize($tenant);
ProcessMatchResults::dispatch();
```

### Queue-Isolation
```bash
# Tenant-spezifische Queue
php artisan queue:work --queue=tenant_testclub

# Oder mit Tags
ProcessMatchResults::dispatch()->onQueue('tenant_' . $tenant->id);
```

---

## 9. Seeder - Tenant Seeder System ✅

### Struktur
```
database/seeders/
├── DatabaseSeeder.php           # Central
├── tenant/
│   ├── TenantDatabaseSeeder.php # Master
│   ├── DemoUserSeeder.php
│   ├── TeamSeeder.php
│   ├── PlayerSeeder.php
│   ├── MatchSeeder.php
│   ├── TenantNewsSeeder.php
│   └── EventSeeder.php
```

### Commands
```bash
# Alle Tenants seeden
php artisan tenants:seed

# Spezifischer Tenant
php artisan tenants:seed --tenants=testclub

# Spezifische Seeder-Klasse
php artisan tenants:seed --class=Database\\Seeders\\Tenant\\TeamSeeder
```

### Implementierung
```php
// database/seeders/tenant/TenantDatabaseSeeder.php
namespace Database\Seeders\Tenant;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoUserSeeder::class,
            TeamSeeder::class,
            PlayerSeeder::class,
            MatchSeeder::class,
            TenantNewsSeeder::class,
            EventSeeder::class,
        ]);
    }
}
```

---

## 10. Config - Event-basierte Überschreibung ✅

### Listener
```php
// app/Listeners/ConfigureTenantEnvironment.php
class ConfigureTenantEnvironment
{
    public function handle(TenancyInitialized $event): void
    {
        $tenant = $event->tenancy->tenant;
        $domain = $tenant->domains->first();

        // Mail Config
        Config::set('mail.from.address', $tenant->email);
        Config::set('mail.from.name', $tenant->name);

        // App Config
        Config::set('app.name', $tenant->name);
        Config::set('app.url', 'http://' . $domain->domain);

        // Cache Config
        Config::set('cache.prefix', 'tenant_' . $tenant->id . '_cache');

        // Filesystem Config
        Config::set('filesystems.disks.public.root', 
            storage_path('app/tenants/' . $tenant->id)
        );
    }
}
```

### Registrierung
```php
// app/Providers/TenancyServiceProvider.php
Event::listen(TenancyInitialized::class, ConfigureTenantEnvironment::class);
```

---

## 11. Testing - Test-Infrastruktur ✅

### Test-Klassen
```
tests/
├── Feature/
│   ├── TenantTest.php                    # 9 Tests
│   └── ExampleTenantFeatureTest.php      # 8 Beispiele
├── TenantTestCase.php                    # Base-Klasse
└── Traits/
    ├── CreatesTenantData.php             # Factory-Methoden
    └── TenantAssertions.php              # Custom Assertions
```

### Verwendung
```php
use Tests\TenantTestCase;
use Tests\Traits\CreatesTenantData;

class MyTest extends TenantTestCase
{
    use CreatesTenantData;

    public function test_tenant_isolation()
    {
        $tenant = $this->createTestTenant('club1');
        
        $this->actingAsTenant($tenant, function () {
            $team = $this->createTeam();
            $this->assertEquals(1, Team::count());
        });
    }
}
```

### Helper-Methoden
- `createTestTenant()` - Tenant mit DB & Migrations
- `actingAsTenant()` - Callback mit auto cleanup
- `seedTenantData()` - Demo-Daten laden
- `assertInTenantContext()` - Context prüfen
- `assertTenantDatabase()` - DB-Connection prüfen

---

## Checkliste für neue Tenants

### Setup-Schritte

1. **Tenant erstellen**
   ```php
   $tenant = Tenant::create([
       'id' => 'newclub',
       'name' => 'New Club',
       'email' => 'admin@newclub.com',
   ]);
   ```

2. **Domain hinzufügen**
   ```php
   $tenant->domains()->create([
       'domain' => 'newclub.localhost',
   ]);
   ```

3. **Datenbank erstellen**
   ```bash
   # Automatisch via Event oder manuell:
   CREATE DATABASE IF NOT EXISTS `tenantnewclub`;
   ```

4. **Migrations ausführen**
   ```bash
   php artisan tenants:migrate --tenants=newclub
   ```

5. **Demo-Daten seeden**
   ```bash
   php artisan tenants:seed --tenants=newclub
   ```

6. **Hosts-Datei aktualisieren** (Development)
   ```
   127.0.0.1    newclub.localhost
   ```

7. **Testen**
   ```
   http://newclub.localhost:8000/login
   ```

---

## Troubleshooting

### Problem: "Tenant database does not exist"
```bash
# Datenbank manuell erstellen
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS tenantnewclub"

# Oder via Script
php create-tenant-databases.php
```

### Problem: Sessions überschneiden sich
```php
// SessionTenancyBootstrapper aktivieren
'bootstrappers' => [
    SessionTenancyBootstrapper::class,
],
```

### Problem: Cache-Daten gemischt
```php
// CacheTenancyBootstrapper aktivieren
'bootstrappers' => [
    CacheTenancyBootstrapper::class,
],

// Oder manuell in Listener
Config::set('cache.prefix', 'tenant_' . $tenant->id . '_cache');
```

### Problem: Dateien im falschen Storage
```php
// FilesystemTenancyBootstrapper aktivieren
'bootstrappers' => [
    FilesystemTenancyBootstrapper::class,
],
```

### Problem: Migrations auf falscher DB
```bash
# Central Migrations
php artisan migrate

# Tenant Migrations (NICHT migrate!)
php artisan tenants:migrate
```

---

## Best Practices

### ✅ DO

1. **Immer Namespaces prüfen**
   ```php
   use App\Models\Central\Tenant;  // ✓
   use App\Models\Tenant\Team;     // ✓
   ```

2. **Tenant-Context beenden**
   ```php
   tenancy()->initialize($tenant);
   // ... work ...
   tenancy()->end();
   ```

3. **Bootstrapper nutzen**
   ```php
   'bootstrappers' => [
       DatabaseTenancyBootstrapper::class,
       FilesystemTenancyBootstrapper::class,
       CacheTenancyBootstrapper::class,
       SessionTenancyBootstrapper::class,
       QueueTenancyBootstrapper::class,
   ],
   ```

4. **Tests schreiben**
   ```php
   class MyTest extends TenantTestCase { }
   ```

### ❌ DON'T

1. **Keine gemischten Models**
   ```php
   // ❌ Falsch
   User::where('tenant_id', $id)->get();
   
   // ✓ Richtig
   tenancy()->initialize($tenant);
   User::all(); // Automatisch gefiltert
   ```

2. **Nicht Central & Tenant mischen**
   ```php
   // ❌ Falsch
   $centralUser = CentralUser::find(1);
   $tenantTeam = Team::find(1); // Welcher Tenant?
   ```

3. **Keine harten DB-Namen**
   ```php
   // ❌ Falsch
   DB::connection('tenanttestclub')->...
   
   // ✓ Richtig
   tenancy()->initialize($tenant);
   DB::table('teams')->...
   ```

---

## Performance-Tipps

### 1. Config Caching
```php
// In ConfigureTenantEnvironment
Cache::remember('tenant_' . $tenant->id . '_config', 3600, function () {
    return DB::table('settings')->pluck('payload', 'name');
});
```

### 2. Eager Loading
```php
$tenant = Tenant::with('domains', 'plan')->find($id);
```

### 3. Queue-Jobs nutzen
```php
// Schwere Operationen in Queue
ProcessTenantReport::dispatch($tenant);
```

### 4. Database Indexing
```php
Schema::table('teams', function (Blueprint $table) {
    $table->index('tenant_id'); // Falls tenant_id Spalte
    $table->index('is_active');
});
```

---

## Zusammenfassung

| Feature | Status | Priorität | Dokumentation |
|---------|--------|-----------|---------------|
| Datenbank-Isolation | ✅ | KRITISCH | config/tenancy.php |
| Model-Separation | ✅ | KRITISCH | app/Models/Central vs Tenant |
| Routen-Isolation | ✅ | KRITISCH | routes/web.php vs tenant.php |
| Migration-Trennung | ✅ | KRITISCH | migrations/ vs tenant/ |
| Storage-Isolation | ✅ | KRITISCH | FilesystemTenancyBootstrapper |
| Cache-Isolation | ✅ | HOCH | CacheTenancyBootstrapper |
| Session-Isolation | ✅ | HOCH | SessionTenancyBootstrapper |
| Queue-Isolation | ✅ | MITTEL | QueueTenancyBootstrapper |
| Seeder-System | ✅ | MITTEL | database/seeders/tenant/ |
| Config-Override | ✅ | NIEDRIG | ConfigureTenantEnvironment |
| Test-Infrastruktur | ✅ | HOCH | tests/TenantTestCase.php |

**Status:** 11/11 Bereiche vollständig implementiert ✅

---

## Nächster Schritt

Das Multi-Tenancy System ist vollständig isoliert und produktionsbereit.

**Empfohlene Erweiterung:**
- Automatische Tenant-Registrierung mit Self-Service
- Billing-Integration (Stripe/PayPal)
- Admin-Dashboard für Tenant-Verwaltung
- Backup & Restore pro Tenant
- Analytics & Reporting

**Siehe:** `TENANT_REGISTRATION.md` (als nächstes zu erstellen)
