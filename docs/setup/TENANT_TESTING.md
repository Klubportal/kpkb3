# Tenant Testing - Comprehensive Guide

## Übersicht

Das Klubportal Multi-Tenancy Testing-System bietet eine vollständige Infrastruktur zum Testen von mandantenfähigen Anwendungen mit vollständiger Datenisolation und automatischem Cleanup.

## Inhaltsverzeichnis

- [Test-Infrastruktur](#test-infrastruktur)
- [Verwendung](#verwendung)
- [Code-Beispiele](#code-beispiele)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

---

## Test-Infrastruktur

### 1. TenantTest.php - Feature Tests

**Datei:** `tests/Feature/TenantTest.php`

Enthält 9 umfassende Tests für Multi-Tenancy:

```php
✓ test_tenant_can_be_created_with_domain
✓ test_tenant_database_can_be_created_and_migrated
✓ test_tenant_data_isolation
✓ test_tenant_can_access_own_data
✓ test_switching_between_tenants_updates_connection
✓ test_ending_tenancy_returns_to_central_database
✓ test_tenant_users_are_isolated_from_central_users
✓ test_tenant_can_be_identified_by_domain
✓ test_multiple_domains_can_point_to_same_tenant
```

**Key Features:**
- ✅ Automatisches Setup/Teardown
- ✅ Datenbank-Cleanup nach jedem Test
- ✅ Context-Isolation (Central ↔ Tenant)
- ✅ Vollständige Datenisolation zwischen Tenants

### 2. TenantTestCase.php - Base Test Class

**Datei:** `tests/TenantTestCase.php`

Basis-Klasse für alle Tenant-Tests mit Helper-Methoden.

#### Helper-Methoden

```php
// Tenant erstellen mit DB und Migrations
$tenant = $this->createTestTenant('club1', [
    'name' => 'Test Club',
    'email' => 'admin@testclub.com'
]);

// Tenant-Kontext initialisieren
$this->initializeTenant($tenant);

// Zurück zu Central
$this->endTenancy();

// Zwischen Tenants wechseln
$this->switchToTenant($tenant2);

// Callback im Tenant-Kontext ausführen (auto cleanup)
$this->actingAsTenant($tenant, function ($tenant) {
    // Code hier läuft im Tenant-Kontext
});

// Tenant mit Demo-Daten füllen
$this->seedTenantData($tenant);

// Multiple Tenants erstellen
$tenants = $this->createMultipleTenants(5);
```

#### Assertions

```php
// Context-Assertions
$this->assertInTenantContext();
$this->assertInCentralContext();
$this->assertCurrentTenant($tenant);

// Database-Assertions
$this->assertTenantDatabase($tenant);
$this->assertCentralDatabase();
$this->assertTenantTableExists('teams');
$this->assertCentralTableExists('tenants');
```

### 3. CreatesTenantData Trait

**Datei:** `tests/Traits/CreatesTenantData.php`

Factory-Methoden für Test-Daten.

```php
use Tests\Traits\CreatesTenantData;

// Benutzer erstellen
$user = $this->createTenantUser([
    'first_name' => 'John',
    'email' => 'john@example.com'
]);

// Team erstellen
$team = $this->createTeam([
    'name' => 'First Team',
    'age_group' => 'senior'
]);

// Spieler erstellen
$player = $this->createPlayer($team, [
    'first_name' => 'Max',
    'last_name' => 'Mustermann',
    'jersey_number' => 10
]);

// Match erstellen
$match = $this->createMatch($team, [
    'opponent_name' => 'FC Example',
    'match_date' => now()->addWeek()
]);

// News erstellen
$news = $this->createNews($user, [
    'title' => 'Important News',
    'status' => 'published'
]);

// Event erstellen
$event = $this->createEvent($user, [
    'title' => 'Team Meeting',
    'start_date' => now()->addDay()
]);

// Team mit Spielern
$team = $this->createTeamWithPlayers(11);

// Vollständiges Match
$match = $this->createCompleteMatch();

// Multiple Entities
$users = $this->createMultipleUsers(5);
$teams = $this->createMultipleTeams(3);
```

### 4. TenantAssertions Trait

**Datei:** `tests/Traits/TenantAssertions.php`

Spezielle Assertions für Tenant-Testing.

```php
use Tests\Traits\TenantAssertions;

// Tenant hat Domain
$this->assertTenantHasDomain($tenant, 'club.localhost');

// Daten isoliert zu Tenant
$this->assertDataIsolatedToTenant('teams', ['name' => 'Team 1'], $tenant);

// Tenant-Anzahl
$this->assertTenantCount(5);

// Tenant-Datenbank existiert
$this->assertTenantDatabaseExists($tenant);
$this->assertTenantDatabaseNotExists($tenant);

// Config-Assertions
$this->assertTenantConfig('app.name', 'Test Club');
$this->assertTenantCachePrefix($tenant);
$this->assertTenantFilesystemDisk($tenant);
```

---

## Verwendung

### Tests ausführen

```bash
# Alle Tenant-Tests
php artisan test tests/Feature/TenantTest.php

# Einzelner Test
php artisan test --filter test_tenant_data_isolation

# Mit Coverage
php artisan test --coverage

# Verbose Output
php artisan test tests/Feature/TenantTest.php -v

# Parallele Ausführung
php artisan test --parallel

# Stop on Failure
php artisan test --stop-on-failure
```

### Demo-Script ausführen

```bash
# Demo-Übersicht anzeigen
php demo-tenant-testing.php

# Mit Test-Ausführung
php demo-tenant-testing.php --run-test
```

---

## Code-Beispiele

### Beispiel 1: Einfacher Tenant-Test

```php
<?php

namespace Tests\Feature;

use Tests\TenantTestCase;

class MyTenantTest extends TenantTestCase
{
    public function test_tenant_isolation()
    {
        // Tenant erstellen
        $tenant1 = $this->createTestTenant('club1');
        $tenant2 = $this->createTestTenant('club2');

        // In Tenant1 arbeiten
        $this->initializeTenant($tenant1);
        $this->assertInTenantContext();
        $this->assertCurrentTenant($tenant1);
        $this->assertTenantDatabase($tenant1);

        // Zu Tenant2 wechseln
        $this->switchToTenant($tenant2);
        $this->assertCurrentTenant($tenant2);
        $this->assertTenantDatabase($tenant2);

        // Zurück zu Central
        $this->endTenancy();
        $this->assertInCentralContext();
        $this->assertCentralDatabase();
    }
}
```

### Beispiel 2: Daten-Isolation testen

```php
<?php

use App\Models\Tenant\Team;
use Tests\TenantTestCase;
use Tests\Traits\CreatesTenantData;

class DataIsolationTest extends TenantTestCase
{
    use CreatesTenantData;

    public function test_teams_are_isolated_between_tenants()
    {
        $tenant1 = $this->createTestTenant('club1');
        $tenant2 = $this->createTestTenant('club2');

        // Team in Tenant 1 erstellen
        $this->actingAsTenant($tenant1, function () {
            $this->createTeam(['name' => 'Team from Tenant 1']);
            $this->assertEquals(1, Team::count());
        });

        // Team in Tenant 2 erstellen
        $this->actingAsTenant($tenant2, function () {
            $this->createTeam(['name' => 'Team from Tenant 2']);
            $this->assertEquals(1, Team::count()); // Nur 1 Team!
        });

        // Tenant 1 sieht nur sein Team
        $this->actingAsTenant($tenant1, function () {
            $this->assertEquals(1, Team::count());
            $this->assertEquals('Team from Tenant 1', Team::first()->name);
        });

        // Tenant 2 sieht nur sein Team
        $this->actingAsTenant($tenant2, function () {
            $this->assertEquals(1, Team::count());
            $this->assertEquals('Team from Tenant 2', Team::first()->name);
        });
    }
}
```

### Beispiel 3: Mit Seedern arbeiten

```php
<?php

use Tests\TenantTestCase;
use App\Models\Tenant\Team;
use App\Models\Tenant\Player;
use App\Models\Tenant\User as TenantUser;

class SeederTest extends TenantTestCase
{
    public function test_tenant_seeder_creates_demo_data()
    {
        $tenant = $this->createTestTenant('club1');
        
        $this->actingAsTenant($tenant, function () use ($tenant) {
            // Seeder ausführen
            $this->seedTenantData($tenant);
            
            // Assertions
            $this->assertEquals(3, TenantUser::count());
            $this->assertEquals(5, Team::count());
            $this->assertEquals(11, Player::count());
            
            // Spezifische Team-Names
            $this->assertDatabaseHas('teams', ['name' => 'Erste Mannschaft']);
            $this->assertDatabaseHas('teams', ['name' => 'Zweite Mannschaft']);
        });
    }
}
```

### Beispiel 4: Multiple Tenants testen

```php
<?php

use Tests\TenantTestCase;
use Tests\Traits\CreatesTenantData;

class MultiTenantTest extends TenantTestCase
{
    use CreatesTenantData;

    public function test_multiple_tenants_can_coexist()
    {
        // 5 Tenants erstellen
        $tenants = $this->createMultipleTenants(5);
        
        $this->assertTenantCount(5);

        // Jeder Tenant bekommt eigene Daten
        foreach ($tenants as $index => $tenant) {
            $this->actingAsTenant($tenant, function () use ($index) {
                $team = $this->createTeam([
                    'name' => "Team {$index}"
                ]);
                
                $this->assertEquals(1, Team::count());
                $this->assertEquals("Team {$index}", Team::first()->name);
            });
        }

        // Alle Tenants haben nur ihr eigenes Team
        foreach ($tenants as $index => $tenant) {
            $this->actingAsTenant($tenant, function () use ($index) {
                $this->assertEquals(1, Team::count());
                $this->assertEquals("Team {$index}", Team::first()->name);
            });
        }
    }
}
```

### Beispiel 5: User-Isolation testen

```php
<?php

use App\Models\Central\User as CentralUser;
use App\Models\Tenant\User as TenantUser;
use Tests\TenantTestCase;
use Tests\Traits\CreatesTenantData;

class UserIsolationTest extends TenantTestCase
{
    use CreatesTenantData;

    public function test_users_are_isolated()
    {
        // Central User erstellen
        CentralUser::create([
            'name' => 'Central Admin',
            'email' => 'admin@central.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertEquals(1, CentralUser::count());

        // Tenant erstellen und User hinzufügen
        $tenant = $this->createTestTenant('club1');
        
        $this->actingAsTenant($tenant, function () {
            // Tenant User erstellen
            $user = $this->createTenantUser([
                'first_name' => 'Tenant',
                'last_name' => 'User',
                'email' => 'user@tenant.com'
            ]);

            // Nur Tenant Users sichtbar
            $this->assertEquals(1, TenantUser::count());
            $this->assertEquals('user@tenant.com', TenantUser::first()->email);
        });

        // Central hat immer noch nur einen User
        $this->assertEquals(1, CentralUser::count());
        $this->assertEquals('admin@central.com', CentralUser::first()->email);
    }
}
```

### Beispiel 6: Config-Tests

```php
<?php

use Tests\TenantTestCase;

class ConfigTest extends TenantTestCase
{
    public function test_tenant_config_is_applied()
    {
        $tenant = $this->createTestTenant('club1', [
            'name' => 'Test Club',
            'email' => 'admin@testclub.com'
        ]);

        $this->actingAsTenant($tenant, function () use ($tenant) {
            // Config-Werte prüfen
            $this->assertTenantConfig('app.name', 'Test Club');
            $this->assertTenantConfig('mail.from.address', 'admin@testclub.com');
            
            // Cache Prefix
            $this->assertTenantCachePrefix($tenant);
            
            // Filesystem
            $this->assertTenantFilesystemDisk($tenant);
            
            // App URL
            $appUrl = config('app.url');
            $this->assertStringContainsString('club1.localhost', $appUrl);
        });
    }
}
```

### Beispiel 7: Domain-Tests

```php
<?php

use App\Models\Central\Tenant;
use Tests\TenantTestCase;

class DomainTest extends TenantTestCase
{
    public function test_tenant_can_have_multiple_domains()
    {
        $tenant = Tenant::create([
            'id' => 'test_multi',
            'name' => 'Multi Domain Club'
        ]);

        // 3 Domains erstellen
        $tenant->domains()->create(['domain' => 'club1.localhost']);
        $tenant->domains()->create(['domain' => 'club2.localhost']);
        $tenant->domains()->create(['domain' => 'club3.localhost']);

        $this->assertEquals(3, $tenant->domains()->count());

        // Jede Domain findet den Tenant
        $this->assertTenantHasDomain($tenant, 'club1.localhost');
        $this->assertTenantHasDomain($tenant, 'club2.localhost');
        $this->assertTenantHasDomain($tenant, 'club3.localhost');

        // Über Domain finden
        foreach (['club1.localhost', 'club2.localhost'] as $domain) {
            $found = Tenant::whereHas('domains', function ($q) use ($domain) {
                $q->where('domain', $domain);
            })->first();

            $this->assertEquals('test_multi', $found->id);
        }
    }
}
```

---

## Best Practices

### ✅ DO

1. **Immer TenantTestCase verwenden**
   ```php
   class MyTest extends TenantTestCase { }
   ```

2. **actingAsTenant() für automatisches Cleanup**
   ```php
   $this->actingAsTenant($tenant, function () {
       // Code hier
   }); // Automatisch zurück zu Central
   ```

3. **Traits für wiederverwendbare Funktionalität**
   ```php
   use CreatesTenantData;
   use TenantAssertions;
   ```

4. **Test-Prefix für Tenants**
   ```php
   $tenant = $this->createTestTenant('test_club');
   // ID wird automatisch: test_club
   ```

5. **Spezifische Assertions verwenden**
   ```php
   $this->assertInTenantContext();
   $this->assertCurrentTenant($tenant);
   ```

6. **Datenbank-Isolation testen**
   ```php
   // Immer prüfen dass Daten zwischen Tenants isoliert sind
   $this->assertEquals(1, Team::count());
   ```

### ❌ DON'T

1. **Nie tenancy() ohne Cleanup**
   ```php
   // ❌ Falsch
   tenancy()->initialize($tenant);
   // ... Tests ...
   // Vergessen zu cleanen!
   
   // ✅ Richtig
   $this->actingAsTenant($tenant, function () {
       // ... Tests ...
   });
   ```

2. **Nicht in Tenant-Context zwischen Tests bleiben**
   ```php
   // tearDown() kümmert sich darum, aber besser:
   $this->endTenancy(); // Explizit
   ```

3. **Keine Production-Tenants in Tests**
   ```php
   // ❌ Falsch
   $tenant = Tenant::find('real-club');
   
   // ✅ Richtig
   $tenant = $this->createTestTenant('test_club');
   ```

4. **Nicht zwischen Tests auf Datenbanken angewiesen sein**
   ```php
   // Jeder Test sollte unabhängig sein
   // RefreshDatabase kümmert sich um Cleanup
   ```

5. **Keine shared state zwischen Tests**
   ```php
   // ❌ Falsch
   protected $sharedTenant;
   
   // ✅ Richtig
   public function test_something() {
       $tenant = $this->createTestTenant('club1');
       // ...
   }
   ```

---

## Test-Lifecycle

### Setup-Phase

```php
protected function setUp(): void
{
    parent::setUp();
    
    // TenantTestCase sorgt automatisch für:
    // 1. Central Context
    // 2. Saubere Ausgangslage
}
```

### Test-Ausführung

```php
public function test_something()
{
    // 1. Tenant erstellen
    $tenant = $this->createTestTenant('club1');
    
    // 2. Im Tenant-Context arbeiten
    $this->actingAsTenant($tenant, function () {
        // 3. Test-Daten erstellen
        $team = $this->createTeam();
        
        // 4. Assertions
        $this->assertEquals(1, Team::count());
    });
    
    // 5. Automatisch zurück zu Central
}
```

### Teardown-Phase

```php
protected function tearDown(): void
{
    // TenantTestCase kümmert sich automatisch um:
    // 1. Tenancy beenden
    // 2. Test-Datenbanken löschen
    // 3. Test-Tenants löschen
    
    parent::tearDown();
}
```

---

## Troubleshooting

### Problem: Tests schlagen fehl mit "Tenant database does not exist"

**Lösung:**
```php
// Sicherstellen dass Migrations laufen
$tenant = $this->createTestTenant('club1', [], migrate: true);
```

### Problem: "Column not found" Fehler

**Lösung:**
```php
// Migrations prüfen
$this->actingAsTenant($tenant, function () {
    $this->assertTenantTableExists('teams');
});

// Oder manuell migrieren
$this->migrateTenantDatabase($tenant);
```

### Problem: Daten aus anderem Tenant sichtbar

**Lösung:**
```php
// Context prüfen
$this->assertInTenantContext();
$this->assertCurrentTenant($expectedTenant);
$this->assertTenantDatabase($tenant);
```

### Problem: Test-Datenbanken bleiben nach Tests

**Lösung:**
```php
// TenantTestCase cleanup prüfen
protected function tearDown(): void
{
    $this->cleanupTestTenants(); // Sollte automatisch passieren
    parent::tearDown();
}

// Manuell cleanup
php artisan db:wipe --database=tenant
```

### Problem: "SQLSTATE[HY000] [1049] Unknown database"

**Lösung:**
```php
// Datenbank vor Verwendung erstellen
$this->createTenantDatabase($tenant);
$this->migrateTenantDatabase($tenant);
```

### Problem: Config-Werte nicht gesetzt

**Lösung:**
```php
// ConfigureTenantEnvironment Listener prüfen
$this->actingAsTenant($tenant, function () use ($tenant) {
    // Config wird beim initialize() gesetzt
    $this->assertEquals($tenant->email, config('mail.from.address'));
});
```

---

## Performance-Tipps

### 1. Database Transactions verwenden

```php
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FastTest extends TenantTestCase
{
    use DatabaseTransactions;
    
    // Tests laufen schneller durch Rollback statt Rebuild
}
```

### 2. Gemeinsame Tenants für Read-Only Tests

```php
protected static $sharedTenant;

public static function setUpBeforeClass(): void
{
    parent::setUpBeforeClass();
    // Einmal erstellen, mehrfach verwenden
}
```

### 3. Parallele Test-Ausführung

```bash
php artisan test --parallel
```

### 4. Selective Testing

```bash
# Nur spezifische Tests
php artisan test --filter test_tenant_data_isolation

# Nur Tenant-Tests
php artisan test tests/Feature/TenantTest.php
```

---

## Erweiterte Szenarien

### Test mit API-Requests

```php
public function test_api_request_in_tenant_context()
{
    $tenant = $this->createTestTenant('club1');
    
    $this->actingAsTenant($tenant, function () {
        $user = $this->createTenantUser();
        
        $response = $this->actingAs($user)
            ->getJson('/api/teams');
            
        $response->assertStatus(200);
    });
}
```

### Test mit Events

```php
use Illuminate\Support\Facades\Event;

public function test_tenant_events()
{
    Event::fake();
    
    $tenant = $this->createTestTenant('club1');
    
    $this->actingAsTenant($tenant, function () {
        $team = $this->createTeam();
        
        Event::assertDispatched(TeamCreated::class);
    });
}
```

### Test mit Jobs

```php
use Illuminate\Support\Facades\Queue;

public function test_tenant_jobs()
{
    Queue::fake();
    
    $tenant = $this->createTestTenant('club1');
    
    $this->actingAsTenant($tenant, function () {
        dispatch(new ProcessMatchResults());
        
        Queue::assertPushed(ProcessMatchResults::class);
    });
}
```

---

## Zusammenfassung

✅ **TenantTestCase** - Basis für alle Tenant-Tests  
✅ **CreatesTenantData** - Factory-Methoden für Test-Daten  
✅ **TenantAssertions** - Spezielle Assertions  
✅ **TenantTest.php** - 9 umfassende Feature-Tests  
✅ **Automatisches Cleanup** - Keine manuellen Aufräumarbeiten  
✅ **Vollständige Isolation** - Daten zwischen Tenants getrennt  

**Demo ausführen:**
```bash
php demo-tenant-testing.php
```

**Tests ausführen:**
```bash
php artisan test tests/Feature/TenantTest.php
```

**Eigene Tests erstellen:**
```php
class MyTest extends TenantTestCase
{
    use CreatesTenantData, TenantAssertions;
    
    public function test_my_feature() { }
}
```
