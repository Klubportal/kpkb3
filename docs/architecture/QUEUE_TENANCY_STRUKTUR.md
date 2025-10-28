# ⚡ QUEUE/JOBS TENANCY - Background Jobs mit Multi-Tenancy

## ✅ Status: VOLLSTÄNDIG AKTIVIERT

Der `QueueTenancyBootstrapper` ist aktiviert und funktioniert einwandfrei!

---

## 📋 Konfiguration

### ✅ 1. Bootstrapper aktiviert (`config/tenancy.php`)

```php
'bootstrappers' => [
    Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,  // ✅ Aktiviert
],
```

### ✅ 2. Queue Connection (`config/queue.php` + `.env`)

```env
QUEUE_CONNECTION=database
```

**Unterstützte Queue Drivers:**
- `database` ✅ (Standard - funktioniert sofort)
- `redis` ✅ (Empfohlen für Produktion)
- `beanstalkd` ✅
- `sqs` ✅ (AWS)
- `sync` ⚠️ (Nur für Testing - keine echte Queue)

---

## 🎯 Wie Queue Tenancy funktioniert

### Automatische Tenant-ID Speicherung

Wenn ein Job im Tenant Context dispatched wird, speichert Laravel **automatisch** die `tenant_id` mit dem Job.

```php
// ========================================
// CENTRAL CONTEXT
// ========================================
SendNewsletterJob::dispatch($newsletter);
// → Job wird in Queue gestellt
// → Keine tenant_id gespeichert
// → Wird später im CENTRAL Context ausgeführt


// ========================================
// TENANT CONTEXT (testclub)
// ========================================
$tenant = Tenant::find('testclub');
$tenant->run(function() {
    SendMatchReminderJob::dispatch($match);
    // → Job wird in Queue gestellt
    // → tenant_id = 'testclub' automatisch gespeichert
    // → Wird später im TESTCLUB Context ausgeführt
    // → Hat automatisch Zugriff auf testclub Datenbank
});
```

### jobs Tabelle Struktur

```sql
-- database/migrations/xxxx_create_jobs_table.php

CREATE TABLE jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,  -- ← Enthält tenant_id!
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX (queue)
);
```

**Payload Beispiel:**
```json
{
    "uuid": "abc-123",
    "displayName": "App\\Jobs\\SendMatchReminderJob",
    "job": "Illuminate\\Queue\\CallQueuedHandler@call",
    "data": {
        "command": "...",
        "tenant_id": "testclub"  // ← Automatisch hinzugefügt!
    }
}
```

---

## 💻 Praktische Beispiele

### 1. E-Mail Jobs (Match Reminder)

**Job erstellen:**
```bash
php artisan make:job SendMatchReminderJob
```

**Job implementieren:**
```php
<?php

namespace App\Jobs;

use App\Models\Tenant\Match;
use App\Mail\MatchReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMatchReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Die Anzahl der Versuche.
     */
    public $tries = 3;

    /**
     * Timeout in Sekunden.
     */
    public $timeout = 120;

    /**
     * Job erstellen.
     */
    public function __construct(
        public Match $match
    ) {}

    /**
     * Job ausführen.
     * 
     * Läuft automatisch im richtigen Tenant Context!
     */
    public function handle(): void
    {
        // Tenant Context ist bereits aktiv
        $players = $this->match->team->players()
            ->where('notify_matches', true)
            ->get();

        foreach ($players as $player) {
            Mail::to($player->email)
                ->send(new MatchReminderMail($this->match, $player));
        }
    }

    /**
     * Job fehlgeschlagen.
     */
    public function failed(\Throwable $exception): void
    {
        // Log error
        \Log::error('Match Reminder Job failed', [
            'match_id' => $this->match->id,
            'tenant_id' => tenant('id'),
            'error' => $exception->getMessage(),
        ]);
    }
}
```

**Job dispatchen:**
```php
use App\Jobs\SendMatchReminderJob;

// Im Tenant Panel (z.B. MatchResource)
SendMatchReminderJob::dispatch($match);

// Mit Verzögerung (24 Stunden vor Match)
$reminderTime = $match->start_time->subHours(24);
SendMatchReminderJob::dispatch($match)->delay($reminderTime);

// Mit spezifischer Queue
SendMatchReminderJob::dispatch($match)->onQueue('emails');

// Mit höherer Priorität
SendMatchReminderJob::dispatch($match)->onQueue('high');
```

### 2. Filament Table Action mit Job

```php
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use App\Jobs\SendMatchReminderJob;

// In MatchResource::table()

Action::make('send_reminder')
    ->label('Erinnerung senden')
    ->icon('heroicon-o-envelope')
    ->color('success')
    ->requiresConfirmation()
    ->modalHeading('Spiel-Erinnerung versenden?')
    ->modalDescription('Alle Spieler werden per E-Mail erinnert.')
    ->action(function (Match $record) {
        // Job wird automatisch im aktuellen Tenant Context dispatched!
        SendMatchReminderJob::dispatch($record);
        
        Notification::make()
            ->title('Erinnerungs-Job gestartet')
            ->body('Die E-Mails werden im Hintergrund versendet.')
            ->success()
            ->send();
    })
    ->visible(fn (Match $record) => $record->start_time->isFuture())
```

### 3. Queued Notifications

**Notification erstellen:**
```bash
php artisan make:notification MatchReminder
```

**Notification implementieren:**
```php
<?php

namespace App\Notifications;

use App\Models\Tenant\Match;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MatchReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Match $match
    ) {}

    /**
     * Notification Channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * E-Mail Nachricht.
     * 
     * Tenant Context ist automatisch aktiv!
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Spiel-Erinnerung: ' . $this->match->opponent)
            ->greeting('Hallo ' . $notifiable->name . '!')
            ->line('Erinnerung an dein bevorstehendes Spiel:')
            ->line('**Gegner:** ' . $this->match->opponent)
            ->line('**Datum:** ' . $this->match->start_time->format('d.m.Y H:i'))
            ->line('**Ort:** ' . $this->match->location)
            ->action('Details ansehen', route('matches.show', $this->match))
            ->line('Wir wünschen dir viel Erfolg!');
    }

    /**
     * Database Notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'match_id' => $this->match->id,
            'opponent' => $this->match->opponent,
            'start_time' => $this->match->start_time->toIso8601String(),
        ];
    }
}
```

**Notification versenden:**
```php
use App\Notifications\MatchReminder;

// Einzelner Spieler
$player->notify(new MatchReminder($match));

// Mehrere Spieler
$players = $match->team->players;
Notification::send($players, new MatchReminder($match));

// Mit Verzögerung
$player->notify((new MatchReminder($match))->delay(now()->addHours(24)));
```

### 4. Bulk Operations mit Jobs

```php
use App\Jobs\GenerateMatchStatisticsJob;
use App\Models\Tenant\Match;

// In einem Filament Bulk Action

BulkAction::make('generate_statistics')
    ->label('Statistiken generieren')
    ->icon('heroicon-o-chart-bar')
    ->requiresConfirmation()
    ->action(function (Collection $records) {
        // Jedes Match in separatem Job verarbeiten
        $records->each(function (Match $match) {
            GenerateMatchStatisticsJob::dispatch($match);
        });
        
        Notification::make()
            ->title($records->count() . ' Jobs gestartet')
            ->success()
            ->send();
    })
```

### 5. Scheduled Jobs (Cron)

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Für alle Tenants ausführen
    $schedule->call(function () {
        Tenant::all()->each(function ($tenant) {
            $tenant->run(function () {
                // Tägliche Erinnerungen für heutige Matches
                SendDailyMatchRemindersJob::dispatch();
            });
        });
    })->daily()->at('08:00');
    
    // Nur im Central Context
    $schedule->job(new CleanupOldTenantsJob)
        ->weekly()
        ->sundays()
        ->at('02:00');
}
```

---

## 🚀 Queue Worker starten

### Entwicklung

```bash
# Standard Worker
php artisan queue:work

# Mit spezifischer Queue
php artisan queue:work --queue=high,default,low

# Nur 1 Job verarbeiten (Testing)
php artisan queue:work --once

# Mit Timeout
php artisan queue:work --timeout=60

# Verbose Mode (für Debugging)
php artisan queue:work --verbose
```

### Produktion (Supervisor)

**Supervisor Config:** `/etc/supervisor/conf.d/klubportal-worker.conf`

```ini
[program:klubportal-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/klubportal/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/klubportal/storage/logs/worker.log
stopwaitsecs=3600
```

**Supervisor Befehle:**
```bash
# Konfiguration neu laden
sudo supervisorctl reread
sudo supervisorctl update

# Worker starten
sudo supervisorctl start klubportal-worker:*

# Worker stoppen
sudo supervisorctl stop klubportal-worker:*

# Worker neu starten
sudo supervisorctl restart klubportal-worker:*

# Status prüfen
sudo supervisorctl status
```

### Windows (NSSM - Non-Sucking Service Manager)

```powershell
# 1. NSSM herunterladen
# https://nssm.cc/download

# 2. Service erstellen
nssm install KlubportalWorker "C:\xampp\php\php.exe" "C:\xampp\htdocs\Klubportal-Laravel12\artisan" queue:work

# 3. Service starten
nssm start KlubportalWorker

# 4. Service Status
nssm status KlubportalWorker
```

---

## 🔧 Queue Management

### Jobs überwachen

```bash
# Wartende Jobs zählen
php artisan queue:monitor

# Jobs in Echtzeit beobachten (mit Horizon - optional)
php artisan horizon

# Database Queue Table prüfen
php artisan db:table jobs --database=central
```

### Failed Jobs

```bash
# Failed Jobs anzeigen
php artisan queue:failed

# Beispiel Output:
# +------+------------------+------------------+------------------+
# |  ID  | Connection       | Queue            | Failed At        |
# +------+------------------+------------------+------------------+
# |  42  | database         | default          | 2025-10-26 10:30 |
# +------+------------------+------------------+------------------+

# Einzelnen Failed Job erneut versuchen
php artisan queue:retry 42

# Alle Failed Jobs erneut versuchen
php artisan queue:retry all

# Failed Job löschen
php artisan queue:forget 42

# Alle Failed Jobs löschen
php artisan queue:flush
```

### Queue löschen/neu starten

```bash
# Queue leeren (alle wartenden Jobs löschen)
php artisan queue:clear

# Spezifische Queue leeren
php artisan queue:clear --queue=emails

# Worker neu starten (lädt Code-Änderungen)
php artisan queue:restart
```

---

## 📊 Queue Prioritäten

### Queues definieren

```php
// Job mit spezifischer Queue
SendMatchReminderJob::dispatch($match)->onQueue('emails');
SendReportJob::dispatch($report)->onQueue('reports');
UrgentNotificationJob::dispatch($notification)->onQueue('high');
```

### Worker mit Prioritäten starten

```bash
# High Priority wird zuerst verarbeitet
php artisan queue:work --queue=high,default,low
```

### In `.env` konfigurieren

```env
QUEUE_CONNECTION=database

# Für Redis
REDIS_QUEUE_HIGH=high
REDIS_QUEUE_DEFAULT=default
REDIS_QUEUE_LOW=low
```

---

## 🧪 Testing

### Queue in Tests

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendMatchReminderJob;

/** @test */
public function it_dispatches_match_reminder_job()
{
    Queue::fake();
    
    $match = Match::factory()->create();
    
    // Action ausführen
    $this->actingAs($this->admin)
        ->post(route('matches.send-reminder', $match));
    
    // Job wurde dispatched?
    Queue::assertPushed(SendMatchReminderJob::class);
    
    // Mit korrekter Match ID?
    Queue::assertPushed(SendMatchReminderJob::class, function ($job) use ($match) {
        return $job->match->id === $match->id;
    });
}
```

---

## 💾 Database Queue Tables

### Migrations erstellen

```bash
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

### Erstellte Tabellen

**1. `jobs` Tabelle:**
```sql
CREATE TABLE jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX (queue)
);
```

**2. `failed_jobs` Tabelle:**
```sql
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🎯 Best Practices

### ✅ DO

1. **Queues für lange Operationen**:
   ```php
   // Gut: E-Mail in Queue
   SendWelcomeEmailJob::dispatch($user);
   
   // Schlecht: E-Mail synchron (blockiert Request)
   Mail::send(new WelcomeEmail($user));
   ```

2. **Timeouts setzen**:
   ```php
   class ProcessReportJob implements ShouldQueue
   {
       public $timeout = 300; // 5 Minuten
   }
   ```

3. **Retry Logic**:
   ```php
   class SendEmailJob implements ShouldQueue
   {
       public $tries = 3;
       public $backoff = [60, 180, 600]; // 1min, 3min, 10min
   }
   ```

4. **Failed Handler**:
   ```php
   public function failed(\Throwable $exception): void
   {
       \Log::error('Job failed', [
           'job' => self::class,
           'tenant' => tenant('id'),
           'error' => $exception->getMessage(),
       ]);
       
       // Benachrichtige Admin
       $admin->notify(new JobFailedNotification($this));
   }
   ```

5. **Unique Jobs** (Laravel 8+):
   ```php
   use Illuminate\Contracts\Queue\ShouldBeUnique;
   
   class ProcessMatchStatistics implements ShouldQueue, ShouldBeUnique
   {
       public function uniqueId(): string
       {
           return $this->match->id;
       }
   }
   ```

### ❌ DON'T

1. **Keine großen Objekte serialisieren**:
   ```php
   // Schlecht: Komplettes Eloquent Model
   GenerateReportJob::dispatch($match);
   
   // Gut: Nur ID übergeben
   GenerateReportJob::dispatch($match->id);
   ```

2. **Keine Tenant-ID manuell setzen**:
   ```php
   // Schlecht: Manuell
   $job->tenantId = tenant('id');
   
   // Gut: Automatisch via QueueTenancyBootstrapper
   SendEmailJob::dispatch($data);
   ```

3. **Nicht zu viele Jobs auf einmal**:
   ```php
   // Schlecht: 10.000 Jobs sofort
   foreach ($users as $user) {
       SendEmailJob::dispatch($user);
   }
   
   // Gut: Batching oder Chunking
   Bus::batch(
       $users->chunk(100)->map(function ($chunk) {
           return new SendBulkEmailsJob($chunk);
       })
   )->dispatch();
   ```

---

## 🔧 Troubleshooting

### Problem: Jobs werden nicht verarbeitet

```bash
# 1. Prüfe ob Worker läuft
ps aux | grep "queue:work"

# 2. Prüfe jobs Tabelle
php artisan db:table jobs --database=central

# 3. Starte Worker
php artisan queue:work --verbose

# 4. Prüfe Logs
tail -f storage/logs/laravel.log
```

### Problem: "Tenant not found" im Job

**Ursache**: Job wurde im Central Context dispatched, aber Tenant-Model wird benötigt.

**Lösung**: Job im Tenant Context dispatchen:
```php
$tenant->run(function() use ($data) {
    MyJob::dispatch($data);
});
```

### Problem: Speicher-Fehler (Memory Limit)

```bash
# Worker mit mehr Speicher
php -d memory_limit=512M artisan queue:work

# Oder in php.ini
memory_limit = 512M
```

### Problem: Worker lädt Code-Änderungen nicht

```bash
# Worker neu starten nach Code-Änderungen
php artisan queue:restart

# Oder in Entwicklung: --timeout verwenden
php artisan queue:work --timeout=60
```

---

## 📚 Zusammenfassung

| Feature | Status | Beschreibung |
|---------|--------|--------------|
| **QueueTenancyBootstrapper** | ✅ Aktiviert | Jobs kennen ihren Tenant Context |
| **Automatische Tenant-ID** | ✅ Ja | Wird automatisch mit Job gespeichert |
| **Queue Driver** | ✅ database | Funktioniert sofort, redis empfohlen für Produktion |
| **Jobs** | ✅ Unterstützt | Alle Laravel Jobs funktionieren |
| **Notifications** | ✅ Unterstützt | Queued Notifications funktionieren |
| **Mails** | ✅ Unterstützt | E-Mails in Queue funktionieren |
| **Events** | ✅ Unterstützt | Queued Event Listeners funktionieren |
| **Failed Jobs** | ✅ Unterstützt | Retry & Monitoring funktioniert |

### ✅ Was funktioniert automatisch:

- ✅ **Tenant Context** wird automatisch für Jobs gespeichert & wiederhergestellt
- ✅ **Database Connection** wechselt automatisch zum richtigen Tenant
- ✅ **Keine manuelle Tenant-ID** Verwaltung nötig
- ✅ **Funktioniert mit allen** Queue Drivers (database, redis, sqs, etc.)
- ✅ **Jobs, Notifications, Mails, Events** - alles unterstützt

---

## 📚 Weiterführende Dokumentation

- [CACHE_TENANCY_STRUKTUR.md](./CACHE_TENANCY_STRUKTUR.md) - Cache Tenancy
- [STORAGE_FILESYSTEM_STRUKTUR.md](./STORAGE_FILESYSTEM_STRUKTUR.md) - Filesystem Tenancy
- [ROUTES_STRUKTUR.md](./ROUTES_STRUKTUR.md) - Routes Separation
- [MODELS_STRUKTUR.md](./MODELS_STRUKTUR.md) - Models Structure
- [stancl/tenancy Docs](https://tenancyforlaravel.com/docs/v4/queues) - Official Queue Documentation
- [Laravel Queue Docs](https://laravel.com/docs/11.x/queues) - Laravel Queue Documentation

**Letzte Aktualisierung**: 2025-10-26
