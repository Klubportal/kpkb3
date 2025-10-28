# Tenant Registration - Automatische Erstellung

## Übersicht

Das Klubportal verwendet eine **JobPipeline** für die automatische Tenant-Erstellung. Wenn ein neuer Tenant erstellt wird, werden automatisch folgende Schritte ausgeführt:

1. ✅ **Database Creation** - Neue Datenbank erstellen
2. ✅ **Database Migration** - Alle Migrations ausführen  
3. ✅ **Database Seeding** - Demo-Daten einfügen
4. ✅ **Default Settings** - Theme, Club, Notification Settings
5. ✅ **Admin User** - Erster Admin-Benutzer mit zufälligem Passwort

---

## Inhaltsverzeichnis

- [Architektur](#architektur)
- [JobPipeline](#jobpipeline)
- [Custom Jobs](#custom-jobs)
- [Verwendung](#verwendung)
- [Konfiguration](#konfiguration)
- [Production Setup](#production-setup)
- [Troubleshooting](#troubleshooting)

---

## Architektur

### Event-Driven Pipeline

```
Tenant::create()
    │
    ├─→ TenantCreated Event
    │       │
    │       ├─→ CreateDatabase Job
    │       ├─→ MigrateDatabase Job
    │       ├─→ SeedDatabase Job
    │       ├─→ CreateDefaultClubSettings Job
    │       └─→ CreateDefaultAdminUser Job
    │
    └─→ Tenant Ready! 🎉
```

### Dateistruktur

```
app/
├── Providers/
│   └── TenancyServiceProvider.php      # Event Listeners & Pipeline
├── Jobs/
│   ├── CreateDefaultClubSettings.php   # Default Settings Job
│   └── CreateDefaultAdminUser.php      # Admin User Job
└── Models/
    └── Central/
        └── Tenant.php                  # Tenant Model
```

---

## JobPipeline

### Konfiguration

**Datei:** `app/Providers/TenancyServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;

class TenancyServiceProvider extends ServiceProvider
{
    public function events()
    {
        return [
            Events\TenantCreated::class => [
                JobPipeline::make([
                    // Built-in Jobs
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    Jobs\SeedDatabase::class,

                    // Custom Jobs
                    \App\Jobs\CreateDefaultClubSettings::class,
                    \App\Jobs\CreateDefaultAdminUser::class,

                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // false = synchron
            ],

            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],
        ];
    }
}
```

### Synchrone vs. Asynchrone Ausführung

**Synchron (Development):**
```php
->shouldBeQueued(false)
```
- Jobs laufen sofort
- Blockiert Request bis fertig
- Einfaches Debugging
- **Gut für Development**

**Asynchron (Production):**
```php
->shouldBeQueued(true)
```
- Jobs in Queue
- Request kehrt sofort zurück
- Requires Queue Worker
- **Empfohlen für Production**

---

## Custom Jobs

### 1. CreateDefaultClubSettings

**Datei:** `app/Jobs/CreateDefaultClubSettings.php`

Erstellt Default-Settings für neuen Tenant:

**Theme Settings:**
- active_theme
- primary_color
- secondary_color  
- header_bg_color
- footer_bg_color

**Club Settings:**
- club_name
- club_email
- club_phone
- club_address
- club_logo
- founded_year

**Notification Settings:**
- email_notifications
- push_notifications
- sms_notifications

**Email Settings:**
- from_name
- from_address
- reply_to

**Struktur:**
```php
<?php

namespace App\Jobs;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDefaultClubSettings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant
    ) {}

    public function handle(): void
    {
        tenancy()->initialize($this->tenant);

        try {
            $this->createThemeSettings();
            $this->createClubSettings();
            $this->createNotificationSettings();
            $this->createEmailSettings();
        } finally {
            tenancy()->end();
        }
    }
}
```

### 2. CreateDefaultAdminUser

**Datei:** `app/Jobs/CreateDefaultAdminUser.php`

Erstellt ersten Admin-User:

**Features:**
- ✅ Generiert sicheres Zufallspasswort
- ✅ Email basiert auf Tenant-Email
- ✅ Loggt Credentials (Development)
- ⚠️  TODO: Email-Versand (Production)

**Passwort-Generierung:**
```php
protected function generateSecurePassword(): string
{
    $lowercase = Str::lower(Str::random(3));
    $uppercase = Str::upper(Str::random(3));
    $numbers = '';
    for ($i = 0; $i < 3; $i++) {
        $numbers .= rand(0, 9);
    }
    $special = '!@#$%';
    $specialChars = substr(str_shuffle($special), 0, 3);

    return str_shuffle($lowercase . $uppercase . $numbers . $specialChars);
}
```

**Admin Email:**
```php
protected function getAdminEmail(): string
{
    if ($this->tenant->email) {
        $domain = explode('@', $this->tenant->email)[1] ?? 'example.com';
        return "admin@{$domain}";
    }
    
    return "admin@{$this->tenant->id}.com";
}
```

---

## Verwendung

### Tenant erstellen (Code)

```php
use App\Models\Central\Tenant;

// Tenant erstellen (Pipeline startet automatisch!)
$tenant = Tenant::create([
    'id' => 'myfootballclub',
    'name' => 'My Football Club',
    'email' => 'contact@myfootballclub.com',
]);

// Domain hinzufügen
$tenant->domains()->create([
    'domain' => 'myfootballclub.localhost',
]);

// Fertig! 
// - Datenbank erstellt ✅
// - Migrations gelaufen ✅
// - Demo-Daten eingefügt ✅
// - Settings erstellt ✅
// - Admin User erstellt ✅
```

### Tenant erstellen (Artisan)

```bash
php artisan tinker

>>> $tenant = Tenant::create(['id' => 'testclub', 'name' => 'Test Club']);
>>> $tenant->domains()->create(['domain' => 'testclub.localhost']);
>>> exit
```

### Tenant erstellen (Demo-Script)

```bash
php demo-tenant-registration.php
```

Interaktives Script:
- Erstellt Demo-Tenant
- Zeigt alle erstellten Daten
- Bietet Option zum Löschen

---

## Konfiguration

### Pipeline anpassen

**Jobs hinzufügen:**
```php
Events\TenantCreated::class => [
    JobPipeline::make([
        Jobs\CreateDatabase::class,
        Jobs\MigrateDatabase::class,
        Jobs\SeedDatabase::class,
        
        // Eigene Jobs
        \App\Jobs\CreateDefaultClubSettings::class,
        \App\Jobs\CreateDefaultAdminUser::class,
        \App\Jobs\SetupStripeAccount::class,          // NEU
        \App\Jobs\CreateDefaultEmailTemplates::class, // NEU
        \App\Jobs\SendWelcomeEmail::class,            // NEU
    ])->send(function (Events\TenantCreated $event) {
        return $event->tenant;
    })->shouldBeQueued(false),
],
```

**Jobs entfernen:**
```php
Events\TenantCreated::class => [
    JobPipeline::make([
        Jobs\CreateDatabase::class,
        Jobs\MigrateDatabase::class,
        // Jobs\SeedDatabase::class,  // ← Auskommentiert
    ])->send(function (Events\TenantCreated $event) {
        return $event->tenant;
    })->shouldBeQueued(false),
],
```

**Reihenfolge ändern:**
```php
// WICHTIG: Reihenfolge beachten!
// ❌ FALSCH - Settings vor Database
JobPipeline::make([
    \App\Jobs\CreateDefaultClubSettings::class, // ← Fehler!
    Jobs\CreateDatabase::class,
    Jobs\MigrateDatabase::class,
])

// ✅ RICHTIG - Database, dann Migrations, dann Settings
JobPipeline::make([
    Jobs\CreateDatabase::class,
    Jobs\MigrateDatabase::class,
    \App\Jobs\CreateDefaultClubSettings::class, // ← OK
])
```

### Seeder anpassen

**Welcher Seeder läuft:**

Der `SeedDatabase` Job verwendet automatisch:
- `DatabaseSeeder` (Central)
- `TenantDatabaseSeeder` (Tenant) ← **Wenn vorhanden**

**Tenant Seeder konfigurieren:**

`database/seeders/tenant/TenantDatabaseSeeder.php`:
```php
<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

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

**Seeder deaktivieren:**

Kommentiere im `TenancyServiceProvider` aus:
```php
JobPipeline::make([
    Jobs\CreateDatabase::class,
    Jobs\MigrateDatabase::class,
    // Jobs\SeedDatabase::class, // ← Deaktiviert
])
```

---

## Production Setup

### Queue Workers konfigurieren

**1. shouldBeQueued auf true setzen:**
```php
->shouldBeQueued(true)
```

**2. Queue Worker starten:**
```bash
php artisan queue:work --tries=3 --timeout=300
```

**3. Supervisor einrichten** (empfohlen):

`/etc/supervisor/conf.d/tenant-worker.conf`:
```ini
[program:tenant-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --timeout=300
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
```

### Email-Versand implementieren

**CreateDefaultAdminUser erweitern:**

```php
protected function handle(): void
{
    tenancy()->initialize($this->tenant);

    try {
        $password = $this->generateSecurePassword();
        
        $admin = TenantUser::create([
            'first_name' => 'Admin',
            'last_name' => $this->tenant->name,
            'email' => $this->getAdminEmail(),
            'password' => Hash::make($password),
            // ...
        ]);

        // EMAIL VERSENDEN statt loggen
        $this->sendWelcomeEmail($admin, $password);

    } finally {
        tenancy()->end();
    }
}

protected function sendWelcomeEmail($admin, $password): void
{
    Mail::to($admin->email)->send(
        new WelcomeEmail($admin, $password, $this->tenant)
    );
}
```

**WelcomeEmail Mailable:**

```php
<?php

namespace App\Mail;

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $password,
        public Tenant $tenant
    ) {}

    public function build()
    {
        return $this->subject('Welcome to ' . $this->tenant->name)
            ->markdown('emails.tenant.welcome');
    }
}
```

### Monitoring & Logging

**Job Tags verwenden:**
```php
public function tags(): array
{
    return [
        'tenant:' . $this->tenant->id,
        'tenant-setup',
        'admin-user'
    ];
}
```

**Horizon Dashboard:**
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

URL: `http://localhost/horizon`

**Job Events loggen:**
```php
use Illuminate\Support\Facades\Event;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;

Event::listen(JobProcessed::class, function ($event) {
    if (str_contains($event->job->payload()['displayName'], 'CreateDefaultAdminUser')) {
        Log::info('Admin user job completed', [
            'job' => $event->job->payload()['displayName'],
        ]);
    }
});

Event::listen(JobFailed::class, function ($event) {
    Log::error('Job failed', [
        'job' => $event->job->payload()['displayName'],
        'exception' => $event->exception->getMessage(),
    ]);
});
```

---

## Troubleshooting

### Problem: Job schlägt fehl mit "Database does not exist"

**Ursache:** Reihenfolge der Jobs falsch

**Lösung:**
```php
// RICHTIG:
JobPipeline::make([
    Jobs\CreateDatabase::class,        // 1. ERST Database
    Jobs\MigrateDatabase::class,       // 2. DANN Migrations
    \App\Jobs\CreateSettings::class,   // 3. DANN Custom Jobs
])
```

### Problem: Seeder findet Tabellen nicht

**Ursache:** SeedDatabase läuft vor MigrateDatabase

**Lösung:**
```php
JobPipeline::make([
    Jobs\CreateDatabase::class,
    Jobs\MigrateDatabase::class,  // VOR Seeding!
    Jobs\SeedDatabase::class,
])
```

### Problem: Settings werden nicht erstellt

**Ursache:** Tenant-Context nicht initialisiert

**Lösung:**
```php
public function handle(): void
{
    tenancy()->initialize($this->tenant); // ← WICHTIG!

    try {
        DB::table('settings')->insert([...]);
    } finally {
        tenancy()->end(); // ← WICHTIG!
    }
}
```

### Problem: Admin-Passwort nicht in Log

**Ursache:** Log-Level zu hoch

**Lösung:**

`.env`:
```env
LOG_LEVEL=info  # oder debug
```

Log-Datei prüfen:
```bash
tail -f storage/logs/laravel.log | grep "Admin user created"
```

### Problem: Queue Jobs laufen nicht

**Ursache:** Kein Queue Worker

**Lösung:**
```bash
# Worker starten
php artisan queue:work

# Oder für Development:
php artisan queue:listen --tries=1
```

### Problem: Datenbank existiert bereits

**Ursache:** Tenant-ID wurde wiederverwendet

**Lösung:**
```bash
# Alte Datenbank löschen
DROP DATABASE IF EXISTS `tenant_{id}`;

# Oder Tenant mit anderer ID erstellen
$tenant = Tenant::create(['id' => 'newclub2']);
```

---

## Erweiterte Szenarien

### Custom Job erstellen

**1. Job generieren:**
```bash
php artisan make:job SetupStripeAccount
```

**2. Job implementieren:**
```php
<?php

namespace App\Jobs;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetupStripeAccount implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tenant $tenant
    ) {}

    public function handle(): void
    {
        // Stripe Account für Tenant erstellen
        $account = \Stripe\Account::create([
            'type' => 'standard',
            'email' => $this->tenant->email,
            'metadata' => [
                'tenant_id' => $this->tenant->id,
            ],
        ]);

        // Account ID speichern
        $this->tenant->update([
            'stripe_account_id' => $account->id,
        ]);
    }
}
```

**3. Zur Pipeline hinzufügen:**
```php
JobPipeline::make([
    Jobs\CreateDatabase::class,
    Jobs\MigrateDatabase::class,
    \App\Jobs\SetupStripeAccount::class, // ← NEU
])
```

### Conditional Jobs

**Nur bei bestimmten Plans:**
```php
Events\TenantCreated::class => [
    JobPipeline::make([
        Jobs\CreateDatabase::class,
        Jobs\MigrateDatabase::class,
    ])
    ->when(
        fn($tenant) => $tenant->plan === 'premium',
        [
            \App\Jobs\SetupAdvancedFeatures::class,
            \App\Jobs\EnableAPIAccess::class,
        ]
    )
    ->send(function (Events\TenantCreated $event) {
        return $event->tenant;
    })
    ->shouldBeQueued(false),
],
```

### Retry-Strategie

**Job mit Retries:**
```php
class CreateDefaultAdminUser implements ShouldQueue
{
    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 30, 60];

    public function handle(): void
    {
        // ...
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to create admin user after 3 tries', [
            'tenant_id' => $this->tenant->id,
            'exception' => $exception->getMessage(),
        ]);
        
        // Notification an Admins
        // Mail::to('admin@system.com')->send(...);
    }
}
```

---

## Zusammenfassung

### ✅ Automatische Pipeline

```
Tenant::create()
    ↓
TenantCreated Event
    ↓
┌─────────────────────────────────┐
│ 1. CreateDatabase               │
│ 2. MigrateDatabase              │
│ 3. SeedDatabase                 │
│ 4. CreateDefaultClubSettings    │
│ 5. CreateDefaultAdminUser       │
└─────────────────────────────────┘
    ↓
Tenant Ready! 🎉
```

### 📁 Dateien

- **TenancyServiceProvider.php** - Pipeline-Konfiguration
- **CreateDefaultClubSettings.php** - Default Settings Job
- **CreateDefaultAdminUser.php** - Admin User Job
- **demo-tenant-registration.php** - Demo-Script
- **TENANT_REGISTRATION.md** - Diese Dokumentation

### 🚀 Quick Start

```bash
# Demo ausführen
php demo-tenant-registration.php

# Eigenen Tenant erstellen
php artisan tinker
>>> Tenant::create(['id' => 'myclub', 'name' => 'My Club'])
```

### 📝 Production Checklist

- [ ] `shouldBeQueued(true)` setzen
- [ ] Queue Worker mit Supervisor einrichten
- [ ] Email-Versand implementieren
- [ ] Horizon für Monitoring installieren
- [ ] Job-Retries konfigurieren
- [ ] Failed Jobs Queue einrichten
- [ ] Logging & Monitoring aktivieren

**Tenant-Registrierung ist jetzt vollautomatisch!** 🎉
