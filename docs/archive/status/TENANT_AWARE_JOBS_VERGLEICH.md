# 🎯 TENANT-AWARE JOBS - Zwei Ansätze

## 📋 Übersicht: Zwei Methoden für Tenant Jobs

Laravel Tenancy bietet **zwei Ansätze** für tenant-aware Jobs:

1. **Automatisch** (empfohlen, bereits aktiv) - via `QueueTenancyBootstrapper`
2. **Explizit** - via `TenantAwareJob` Parent Class

---

## ✅ Methode 1: Automatisch (BEREITS AKTIV)

### Wie es funktioniert

Mit aktiviertem `QueueTenancyBootstrapper` wird die `tenant_id` **automatisch** gespeichert wenn du im Tenant Context bist.

### Job Beispiel

```php
<?php

namespace App\Jobs;

use App\Models\Tenant\News;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewsPublishedNotification;

class SendNewsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public int $newsId  // ← Nur die ID übergeben!
    ) {}

    public function handle(): void
    {
        // Tenant Context ist AUTOMATISCH aktiv!
        // Kein manuelles tenant() setup nötig
        
        $news = News::findOrFail($this->newsId);
        $users = User::whereNotNull('email_verified_at')->get();
        
        Notification::send($users, new NewsPublishedNotification($news));
    }
}
```

### Verwendung

```php
// Im Tenant Panel (z.B. NewsResource)
use App\Jobs\SendNewsNotification;

// Job dispatchen - tenant_id wird automatisch gespeichert!
SendNewsNotification::dispatch($news->id);

// Mit Verzögerung
SendNewsNotification::dispatch($news->id)->delay(now()->addMinutes(10));

// Mit Queue
SendNewsNotification::dispatch($news->id)->onQueue('notifications');
```

### ✅ Vorteile

- ✅ **Einfacher** - Weniger Code
- ✅ **Automatisch** - Keine manuelle Tenant-Verwaltung
- ✅ **Funktioniert sofort** - QueueTenancyBootstrapper ist bereits aktiv
- ✅ **Best Practice** - Empfohlener Ansatz in der Tenancy-Dokumentation

---

## 🎯 Methode 2: Explizit mit TenantAwareJob

### Wie es funktioniert

Der Job erbt von `TenantAwareJob` und erhält den `$tenant` explizit als Parameter.

### Job Beispiel

```php
<?php

namespace App\Jobs;

use App\Models\Tenant\News;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Jobs\TenantAwareJob;
use App\Notifications\NewsPublishedNotification;

class SendNewsNotification extends TenantAwareJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;
    
    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public TenantWithDatabase $tenant,  // ← Tenant explizit übergeben
        public int $newsId
    ) {
        // Parent Constructor MUSS aufgerufen werden!
        parent::__construct($tenant);
    }
    
    public function handle(): void
    {
        // Läuft automatisch im Tenant Context
        // $this->tenant ist verfügbar
        
        $news = News::findOrFail($this->newsId);
        $users = User::whereNotNull('email_verified_at')->get();
        
        Notification::send($users, new NewsPublishedNotification($news));
    }
}
```

### Verwendung

```php
// Im Tenant Panel - Tenant MUSS explizit übergeben werden
use App\Jobs\SendNewsNotification;
use App\Models\Central\Tenant;

// Tenant holen (im Tenant Context ist das tenant())
$tenant = tenant();

// Job dispatchen mit Tenant
SendNewsNotification::dispatch($tenant, $news->id);

// Oder außerhalb Tenant Context
$tenant = Tenant::find('testclub');
SendNewsNotification::dispatch($tenant, $news->id);
```

### ⚠️ Wichtige Unterschiede

1. **Parent Constructor** muss aufgerufen werden:
   ```php
   parent::__construct($tenant);
   ```

2. **Tenant muss übergeben werden**:
   ```php
   SendNewsNotification::dispatch(tenant(), $news->id);
   ```

3. **Mehr Boilerplate Code** - Tenant muss immer mit übergeben werden

---

## 📊 Vergleich: Automatisch vs. Explizit

| Feature | Automatisch (Methode 1) | Explizit (Methode 2) |
|---------|-------------------------|----------------------|
| **Parent Class** | Standard Laravel Job | `TenantAwareJob` |
| **Tenant Parameter** | ❌ Nicht nötig | ✅ Erforderlich |
| **Constructor** | Standard | `parent::__construct($tenant)` erforderlich |
| **Dispatch** | `Job::dispatch($id)` | `Job::dispatch(tenant(), $id)` |
| **Code Menge** | ✅ Weniger | ⚠️ Mehr |
| **Automatisch** | ✅ Ja | ⚠️ Semi-automatisch |
| **Empfohlen** | ✅ **Ja (Best Practice)** | ⚠️ Nur für Spezialfälle |
| **QueueTenancyBootstrapper** | ✅ Nutzt es | ⚠️ Umgeht es |

---

## 🚀 Praktische Beispiele - BEIDE Methoden

### Beispiel 1: Match Reminder

#### Automatisch (Empfohlen)

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

    public function __construct(
        public int $matchId
    ) {}

    public function handle(): void
    {
        $match = Match::with('team.players')->findOrFail($this->matchId);
        
        foreach ($match->team->players as $player) {
            Mail::to($player->email)->send(new MatchReminderMail($match));
        }
    }
}

// Verwendung
SendMatchReminderJob::dispatch($match->id);
```

#### Explizit mit TenantAwareJob

```php
<?php

namespace App\Jobs;

use App\Models\Tenant\Match;
use App\Mail\MatchReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Jobs\TenantAwareJob;

class SendMatchReminderJob extends TenantAwareJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct(
        public TenantWithDatabase $tenant,
        public int $matchId
    ) {
        parent::__construct($tenant);
    }

    public function handle(): void
    {
        $match = Match::with('team.players')->findOrFail($this->matchId);
        
        foreach ($match->team->players as $player) {
            Mail::to($player->email)->send(new MatchReminderMail($match));
        }
    }
}

// Verwendung
SendMatchReminderJob::dispatch(tenant(), $match->id);
```

### Beispiel 2: Bulk Newsletter an alle Tenants

Hier ist `TenantAwareJob` **sinnvoll**, da wir aus dem Central Context alle Tenants durchgehen:

```php
<?php

namespace App\Jobs;

use App\Models\Tenant\User;
use App\Mail\NewsletterMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Jobs\TenantAwareJob;

class SendTenantNewsletterJob extends TenantAwareJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct(
        public TenantWithDatabase $tenant,
        public string $subject,
        public string $content
    ) {
        parent::__construct($tenant);
    }

    public function handle(): void
    {
        // Läuft im spezifischen Tenant Context
        $users = User::whereNotNull('email_verified_at')
            ->where('newsletter_subscription', true)
            ->get();
        
        foreach ($users as $user) {
            Mail::to($user->email)->send(
                new NewsletterMail($this->subject, $this->content)
            );
        }
    }
}
```

**Verwendung im Central Context:**

```php
use App\Models\Central\Tenant;
use App\Jobs\SendTenantNewsletterJob;

// Newsletter an ALLE Tenants senden
$subject = 'Wichtiges Update';
$content = 'Neue Features verfügbar...';

Tenant::all()->each(function (Tenant $tenant) use ($subject, $content) {
    SendTenantNewsletterJob::dispatch($tenant, $subject, $content);
});
```

---

## 💡 Wann welche Methode?

### ✅ Automatisch verwenden (Methode 1) - EMPFOHLEN

- ✅ **Job läuft nur im Tenant Context** (z.B. aus Filament Panel)
- ✅ **Einfache Jobs** mit wenig Komplexität
- ✅ **Standard-Anwendungsfälle**: E-Mails, Notifications, Reports
- ✅ **Best Practice** - Weniger Code, automatisch

**Beispiele:**
- Match Reminder aus Tenant Panel
- News Notification aus Tenant Panel
- Player Statistics aus Tenant Panel
- Report Generation aus Tenant Panel

### ⚠️ TenantAwareJob verwenden (Methode 2) - SPEZIALFÄLLE

- ⚠️ **Job wird aus Central Context** für mehrere Tenants dispatched
- ⚠️ **Bulk Operations** über alle Tenants
- ⚠️ **Explizite Tenant-Kontrolle** gewünscht
- ⚠️ **Debugging** - Tenant ist sichtbar im Code

**Beispiele:**
- Newsletter an alle Tenants (aus Central Panel)
- System-Updates für alle Tenants
- Cleanup-Jobs für spezifische Tenants
- Migration/Sync Jobs zwischen Tenants

---

## 🔧 Migration: Automatisch → TenantAwareJob

Falls du einen automatischen Job zu TenantAwareJob konvertieren möchtest:

### Vorher (Automatisch)

```php
class MyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $dataId
    ) {}

    public function handle(): void
    {
        // ...
    }
}

// Dispatch
MyJob::dispatch($data->id);
```

### Nachher (TenantAwareJob)

```php
class MyJob extends TenantAwareJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;  // ← SerializesModels entfernt!

    public function __construct(
        public TenantWithDatabase $tenant,  // ← NEU
        public int $dataId
    ) {
        parent::__construct($tenant);  // ← NEU
    }

    public function handle(): void
    {
        // Gleicher Code
    }
}

// Dispatch
MyJob::dispatch(tenant(), $data->id);  // ← tenant() hinzugefügt
```

### ⚠️ Wichtige Änderungen

1. **Extends** `TenantAwareJob` statt normales Job
2. **Remove** `SerializesModels` Trait (TenantAwareJob hat eigene Serialisierung)
3. **Add** `TenantWithDatabase $tenant` Parameter
4. **Call** `parent::__construct($tenant)` im Constructor
5. **Update** alle `dispatch()` Calls mit `tenant()`

---

## 🧪 Testing - Beide Methoden

### Automatisch testen

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendNewsNotification;
use App\Models\Central\Tenant;

/** @test */
public function it_dispatches_news_notification()
{
    Queue::fake();
    
    $tenant = Tenant::factory()->create();
    
    $tenant->run(function () {
        $news = News::factory()->create();
        
        SendNewsNotification::dispatch($news->id);
        
        Queue::assertPushed(SendNewsNotification::class, function ($job) use ($news) {
            return $job->newsId === $news->id;
        });
    });
}
```

### TenantAwareJob testen

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendNewsNotification;
use App\Models\Central\Tenant;

/** @test */
public function it_dispatches_tenant_aware_news_notification()
{
    Queue::fake();
    
    $tenant = Tenant::factory()->create();
    $news = News::factory()->create();
    
    SendNewsNotification::dispatch($tenant, $news->id);
    
    Queue::assertPushed(SendNewsNotification::class, function ($job) use ($tenant, $news) {
        return $job->tenant->id === $tenant->id
            && $job->newsId === $news->id;
    });
}
```

---

## 📚 Zusammenfassung

### ✅ **Empfehlung: Automatisch (Methode 1)**

Für **95% der Anwendungsfälle** ist die automatische Methode die richtige Wahl:

```php
class MyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $id) {}
    
    public function handle(): void {
        // Tenant Context ist automatisch aktiv!
    }
}

// Einfach dispatchen
MyJob::dispatch($data->id);
```

**Warum?**
- ✅ Weniger Code
- ✅ Automatisch
- ✅ Best Practice
- ✅ QueueTenancyBootstrapper ist bereits aktiv

### ⚠️ **TenantAwareJob nur für Spezialfälle**

Nur verwenden wenn:
- Job aus Central Context für mehrere Tenants läuft
- Explizite Tenant-Kontrolle erforderlich
- Bulk Operations über alle Tenants

---

## 🔗 Weiterführende Dokumentation

- [QUEUE_TENANCY_STRUKTUR.md](./QUEUE_TENANCY_STRUKTUR.md) - Queue Tenancy Basis
- [stancl/tenancy Jobs Docs](https://tenancyforlaravel.com/docs/v4/jobs) - Official Documentation
- [Laravel Queue Docs](https://laravel.com/docs/11.x/queues) - Laravel Queue Documentation

**Letzte Aktualisierung**: 2025-10-26
