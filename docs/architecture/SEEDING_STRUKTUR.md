# 🌱 Seeding-Struktur: Central vs. Tenant

## 📋 Übersicht

Das Laravel Multi-Tenancy System trennt Seeders in zwei Kategorien:

| Kategorie | Ordner | Namespace | Command | Connection |
|-----------|--------|-----------|---------|------------|
| **Central Seeders** | `database/seeders/` | `Database\Seeders` | `php artisan db:seed` | `landlord` (explizit) |
| **Tenant Seeders** | `database/seeders/tenant/` | `Database\Seeders\Tenant` | `php artisan tenants:seed` | `tenant` (automatisch) |

---

## 📁 Aktuelle Ordnerstruktur

```
database/
└── seeders/
    ├── CmsSeeder.php                    # ✅ Central
    ├── DatabaseSeeder.php               # ✅ Central (Master)
    ├── MichaelSuperAdminSeeder.php      # ✅ Central
    ├── PlansSeeder.php                  # ✅ Central
    ├── RolesAndPermissionsSeeder.php    # ✅ Central
    ├── TenantSeeder.php                 # ✅ Central (erstellt Tenants)
    └── tenant/                          # 📁 Tenant Seeders Ordner
        ├── TenantDatabaseSeeder.php     # ✅ Tenant (Master)
        ├── DemoUserSeeder.php           # ✅ Tenant
        ├── TeamSeeder.php               # ✅ Tenant
        ├── PlayerSeeder.php             # ✅ Tenant
        ├── MatchSeeder.php              # ✅ Tenant
        ├── TenantNewsSeeder.php         # ✅ Tenant
        └── EventSeeder.php              # ✅ Tenant
```

---

## 🔧 Composer Autoloading Konfiguration

**WICHTIG:** Tenant-Seeders benötigen einen separaten PSR-4 Autoload-Eintrag!

### composer.json

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/",
            "Database\\Seeders\\Tenant\\": "database/seeders/tenant/"
        }
    }
}
```

**Nach Änderung ausführen:**

```bash
composer dump-autoload
```

---

## 🏢 Central Seeders

### Zweck
- Seeden die **Central/Landlord Datenbank**
- Erstellen globale Daten: Pläne, Super-Admins, Tenants, CMS-Inhalte
- Laufen **EINMAL** bei Setup

### DatabaseSeeder.php (Central Master)

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlansSeeder::class,                    // Subscription Plans
            MichaelSuperAdminSeeder::class,        // Super Admin für Central Panel
            TenantSeeder::class,                   // Erstellt Tenant-Records
            CmsSeeder::class,                      // Central CMS Content
            RolesAndPermissionsSeeder::class,      // Spatie Permissions
        ]);
    }
}
```

### PlansSeeder.php (Beispiel)

```php
<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        // ⚠️ WICHTIG: Explizit auf Central Connection!
        Plan::on('central')->firstOrCreate(
            ['name' => 'Free'],
            [
                'price' => 0,
                'features' => ['1 Team', 'Basic Support'],
            ]
        );

        Plan::on('central')->firstOrCreate(
            ['name' => 'Pro'],
            [
                'price' => 29.99,
                'features' => ['Unlimited Teams', 'Priority Support'],
            ]
        );
    }
}
```

### ⚠️ WICHTIG: Central Connection

```php
// ✅ RICHTIG - Explizit Central Connection
Plan::on('central')->create([...]);
User::on('central')->create([...]);

// ❌ FALSCH - Verwendet Tenant Connection!
Plan::create([...]);  // Fehler wenn Tenant aktiv ist
```

### Command ausführen

```bash
php artisan db:seed
# Oder spezifisch:
php artisan db:seed --class=PlansSeeder
```

---

## 🏘️ Tenant Seeders

### Zweck
- Seeden **jede einzelne Tenant-Datenbank**
- Erstellen tenant-spezifische Daten: Teams, Spieler, Matches
- Laufen für **JEDEN Tenant** einzeln

### TenantDatabaseSeeder.php (Tenant Master)

```php
<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("🌱 Seeding Tenant: " . tenant()->id);

        $this->call([
            \Database\Seeders\Tenant\DemoUserSeeder::class,
            \Database\Seeders\Tenant\TeamSeeder::class,
            \Database\Seeders\Tenant\PlayerSeeder::class,
            \Database\Seeders\Tenant\MatchSeeder::class,
            \Database\Seeders\Tenant\TenantNewsSeeder::class,
            \Database\Seeders\Tenant\EventSeeder::class,
        ]);

        $this->command->info("✅ Tenant '" . tenant()->id . "' seeded!");
    }
}
```

### TeamSeeder.php (Beispiel)

```php
<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Automatisch auf Tenant Connection - kein on() nötig!
        Team::firstOrCreate(
            ['name' => 'Erste Mannschaft'],
            [
                'category' => 'Herren',
                'league' => 'Bundesliga',
            ]
        );

        Team::firstOrCreate(
            ['name' => 'U19'],
            [
                'category' => 'Jugend',
                'league' => 'A-Junioren Bundesliga',
            ]
        );
    }
}
```

### PlayerSeeder.php (Beispiel)

```php
<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Player;
use App\Models\Tenant\Team;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::where('name', 'Erste Mannschaft')->first();

        if (!$team) {
            $this->command->warn('⚠️ Team not found - skipping players');
            return;
        }

        // ✅ Automatisch im Tenant Context
        Player::firstOrCreate(
            ['team_id' => $team->id, 'number' => 1],
            ['name' => 'Max Mustermann', 'position' => 'Torwart']
        );

        Player::firstOrCreate(
            ['team_id' => $team->id, 'number' => 10],
            ['name' => 'Tom Schmidt', 'position' => 'Sturm']
        );
    }
}
```

### ✅ WICHTIG: Automatische Tenant Connection

```php
// ✅ RICHTIG - Automatisch auf Tenant Connection
Team::create([...]);
Player::create([...]);

// ❌ NICHT NÖTIG
Team::on('tenant')->create([...]);  // Unnötig - bereits automatisch!
```

### Commands ausführen

```bash
# Alle Tenants seeden
php artisan tenants:seed --class=TenantDatabaseSeeder

# Nur spezifische Tenants
php artisan tenants:seed --tenants=fcbarcelona,realmadrid

# Spezifischen Seeder
php artisan tenants:seed --class=TeamSeeder
```

---

## 🔄 Vergleich: Central vs. Tenant

| Aspekt | Central Seeder | Tenant Seeder |
|--------|----------------|---------------|
| **Namespace** | `Database\Seeders` | `Database\Seeders\Tenant` |
| **Ordner** | `database/seeders/` | `database/seeders/tenant/` |
| **Connection** | `Model::on('central')` ERFORDERLICH | Automatisch `tenant` |
| **Command** | `php artisan db:seed` | `php artisan tenants:seed` |
| **Läuft für** | Einmal (Landlord DB) | Jeden Tenant einzeln |
| **Model Namespace** | `App\Models\Plan` | `App\Models\Tenant\Team` |
| **Beispiele** | Plans, Super-Admins, Tenants | Teams, Players, Matches |
| **Tenant Context** | ❌ Nicht verfügbar | ✅ `tenant()` Helper verfügbar |

---

## 🎯 Best Practices

### 1. **Explizite Central Connection**

```php
// Central Seeder - IMMER on('central') verwenden
Plan::on('central')->create([...]);
CentralUser::on('central')->create([...]);
```

### 2. **Automatische Tenant Connection**

```php
// Tenant Seeder - KEINE on() Methode nötig
Team::create([...]);
Player::create([...]);
```

### 3. **Tenant-Aware Demo-Daten**

```php
// Email-Adressen mit Tenant-ID
User::create([
    'email' => 'admin@' . tenant()->id . '.com',
    'name' => 'Admin',
]);
```

### 4. **firstOrCreate für Idempotenz**

```php
// Verhindert Duplikate bei wiederholtem Seeden
Team::firstOrCreate(
    ['name' => 'Erste Mannschaft'],  // Suchkriterien
    ['league' => 'Bundesliga']        // Zusätzliche Daten
);
```

### 5. **Abhängigkeiten prüfen**

```php
$team = Team::where('name', 'Erste Mannschaft')->first();

if (!$team) {
    $this->command->warn('Team not found - skipping');
    return;
}

// Jetzt Player erstellen
```

---

## 🐛 Troubleshooting

### Problem 1: "Class not found"

```bash
# Fehler:
Class 'Database\Seeders\Tenant\TeamSeeder' not found
```

**Lösung:**

```bash
# composer.json prüfen:
"autoload": {
    "psr-4": {
        "Database\\Seeders\\Tenant\\": "database/seeders/tenant/"
    }
}

# Dann:
composer dump-autoload
```

---

### Problem 2: "SQLSTATE[42S02]: Base table or view not found"

```bash
# Fehler bei Central Seeder:
Table 'klubportal_landlord.teams' doesn't exist
```

**Ursache:** Model verwendet Tenant Connection statt Central

**Lösung:**

```php
// ❌ FALSCH
Team::create([...]);

// ✅ RICHTIG
Team::on('central')->create([...]);
```

---

### Problem 3: "Tenant could not be identified on domain"

```bash
# Fehler bei Tenant Seeder:
Tenant could not be identified
```

**Ursache:** Command läuft nicht im Tenant Context

**Lösung:**

```bash
# ❌ FALSCH
php artisan db:seed --class=TeamSeeder

# ✅ RICHTIG
php artisan tenants:seed --class=TeamSeeder
```

---

### Problem 4: Seeder läuft nicht für alle Tenants

```bash
# Nur fcbarcelona wird geseeded
```

**Lösung:**

```bash
# Alle Tenants:
php artisan tenants:seed

# Spezifische Tenants:
php artisan tenants:seed --tenants=fcbarcelona,realmadrid

# Mit --tenancy Option für Custom Commands:
php artisan db:seed --class=TeamSeeder --tenancy
```

---

### Problem 5: Foreign Key Violations

```bash
# Fehler:
SQLSTATE[23000]: Integrity constraint violation
```

**Lösung:** Richtige Reihenfolge in `TenantDatabaseSeeder`:

```php
$this->call([
    DemoUserSeeder::class,      // 1️⃣ Zuerst Users
    TeamSeeder::class,          // 2️⃣ Dann Teams
    PlayerSeeder::class,        // 3️⃣ Dann Players (brauchen Teams)
    MatchSeeder::class,         // 4️⃣ Dann Matches (brauchen Teams)
]);
```

---

## 📊 Beispiel-Workflow

### Initial Setup (einmalig)

```bash
# 1. Central DB seeden
php artisan db:seed

# Erstellt:
# - 4 Subscription Plans (Free, Basic, Pro, Enterprise)
# - 1 Super Admin (michael@admin.com)
# - 3 Tenants (fcbarcelona, realmadrid, bayernmunich)
# - Central CMS Content
```

### Tenant DBs seeden (für alle Tenants)

```bash
# 2. Alle Tenant DBs seeden
php artisan tenants:seed --class=TenantDatabaseSeeder

# Erstellt für JEDEN Tenant:
# - 3 Demo Users (admin@, trainer@, manager@)
# - 5 Teams (Erste Mannschaft, U19, U17, etc.)
# - 11 Players für Erste Mannschaft
# - 3 Matches
# - 3 News Items
# - 3 Events
```

### Nur neue Tenants seeden

```bash
# 3. Nur neu erstellte Tenants
php artisan tenants:seed --tenants=realmadrid --class=TenantDatabaseSeeder
```

---

## 🚀 Production Deployment

### .env Configuration

```env
# Central Database
DB_DATABASE=klubportal_landlord
DB_CONNECTION=central

# Session/Cache auf Tenant Database
SESSION_DRIVER=database
CACHE_STORE=database
```

### Deployment Script

```bash
#!/bin/bash

# 1. Migrations
php artisan migrate --force

# 2. Tenant Migrations
php artisan tenants:migrate --force

# 3. Central Seeders (nur bei Bedarf)
php artisan db:seed --class=PlansSeeder --force

# 4. Tenant Seeders (nur bei Bedarf)
# php artisan tenants:seed --class=TenantDatabaseSeeder --force
```

**⚠️ ACHTUNG:** Seeders in Production nur bei Bedarf ausführen (z.B. neue Plans)!

---

## 📦 Zusammenfassung

### ✅ Central Seeders
- Ordner: `database/seeders/`
- Namespace: `Database\Seeders`
- Command: `php artisan db:seed`
- Connection: **Explizit** `Model::on('central')`
- Zweck: Globale Daten (Plans, Tenants, Super-Admins)

### ✅ Tenant Seeders
- Ordner: `database/seeders/tenant/`
- Namespace: `Database\Seeders\Tenant`
- Command: `php artisan tenants:seed`
- Connection: **Automatisch** (kein `on()` nötig)
- Zweck: Tenant-spezifische Daten (Teams, Players, Matches)

### 🔑 Wichtigste Regel

```php
// Central Seeder - EXPLIZIT
Plan::on('central')->create([...]);

// Tenant Seeder - AUTOMATISCH
Team::create([...]);
```

---

## 📚 Weitere Dokumentation

- [TENANT_AWARE_JOBS_VERGLEICH.md](./TENANT_AWARE_JOBS_VERGLEICH.md) - Jobs mit/ohne TenantAwareJob
- [MIDDLEWARE_STRUKTUR.md](./MIDDLEWARE_STRUKTUR.md) - Middleware Konfiguration
- [SESSION_TENANCY_STRUKTUR.md](./SESSION_TENANCY_STRUKTUR.md) - Session Isolation
- [Stancl/Tenancy Docs](https://tenancyforlaravel.com/docs/v4/seeding)

---

**Status:** ✅ Vollständig implementiert und dokumentiert
**Letztes Update:** 2025-01-26
