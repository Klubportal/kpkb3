# E-Mail System - Komplettreferenz

**Production-Ready Email Management System mit Templates, Queue Jobs, Audit Trail und GDPR Compliance**

---

## 📋 Inhaltsverzeichnis

1. [System-Übersicht](#system-übersicht)
2. [Setup & Konfiguration](#setup--konfiguration)
3. [Email Templates](#email-templates)
4. [Mass Email Sending](#mass-email-sending)
5. [Queue Jobs](#queue-jobs)
6. [Audit & Compliance](#audit--compliance)
7. [API Endpoints](#api-endpoints)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 System-Übersicht

Das Email-System besteht aus mehreren Komponenten:

```
┌─────────────────────────────────────┐
│      EmailService (CRUD)            │
├─────────────────────────────────────┤
│ - Create/Update/Delete Templates    │
│ - Render Templates with Variables   │
│ - Send to Single/Multiple Users     │
└────────────┬────────────────────────┘
             │
    ┌────────┴─────────┐
    │                  │
┌───▼──────────────┐  ┌──▼──────────────────┐
│ SendMassEmailJob │  │ EmailAuditService   │
├──────────────────┤  ├─────────────────────┤
│ - Batch send     │  │ - Log events        │
│ - Retry logic    │  │ - Audit trail       │
│ - Tracking       │  │ - GDPR compliance   │
└──────────────────┘  └─────────────────────┘
```

---

## ⚙️ Setup & Konfiguration

### 1. Environment-Variablen

```env
# config/mail.php
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@club.de"
MAIL_FROM_NAME="Club Management"

# Für WebPush (optional, für Fallback)
VAPID_SUBJECT="mailto:admin@club.de"
VAPID_PUBLIC_KEY=your_public_key
VAPID_PRIVATE_KEY=your_private_key
```

### 2. Database Migration

```bash
# Migration wurde bereits erstellt in:
database/migrations/2025_10_24_000160_create_email_templates_table.php

php artisan migrate --path=database/migrations/tenant
```

### 3. Queue Configuration

```env
# config/queue.php
QUEUE_CONNECTION=database
# Oder: redis, sync (development only)
```

### 4. Scheduled Commands

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Process scheduled emails every minute
    $schedule->command('notifications:process-scheduled-emails')
        ->everyMinute()
        ->withoutOverlapping();
    
    // Clean up old logs (GDPR) - weekly
    $schedule->command('notifications:cleanup-audit-logs --retention=90')
        ->weekly();
    
    // Send pending notifications
    $schedule->command('notifications:send-pending')
        ->everyFiveMinutes()
        ->withoutOverlapping();
}
```

---

## 📧 Email Templates

### Template erstellen

```php
use App\Services\EmailService;

$emailService = app(EmailService::class);

// Einfaches Template
$template = $emailService->createTemplate(
    club: $club,
    creator: $admin,
    data: [
        'name' => 'Training Schedule',
        'slug' => 'training-schedule',
        'subject' => 'Training: {day} um {time} Uhr',
        'body' => '
            <h2>Liebe {recipient_group},</h2>
            <p>das Training findet statt:</p>
            <ul>
                <li><strong>Tag:</strong> {day}</li>
                <li><strong>Zeit:</strong> {time}</li>
                <li><strong>Ort:</strong> {location}</li>
            </ul>
            <p>Bis bald!</p>
        ',
        'target_groups' => ['players', 'coaches'],
        'variables' => [
            'day' => 'Wochentag',
            'time' => 'Uhrzeit',
            'location' => 'Trainingsort',
            'recipient_group' => 'Empfängergruppe',
        ],
        'from_email' => 'info@club.de',
        'include_logo' => true,
        'include_footer' => true,
    ]
);

// Mit lokalisiertem Inhalt
$template = $emailService->createTemplate(
    club: $club,
    creator: $admin,
    data: [
        'name' => 'Match Report',
        'localized_content' => [
            'de' => [
                'subject' => 'Spielbericht: {date}',
                'body' => '<h2>Spielbericht</h2>...',
            ],
            'en' => [
                'subject' => 'Match Report: {date}',
                'body' => '<h2>Match Report</h2>...',
            ],
            'hr' => [
                'subject' => 'Izvještaj utakmice: {date}',
                'body' => '<h2>Izvještaj utakmice</h2>...',
            ],
        ],
        'target_groups' => ['parents'],
    ]
);
```

### Template rendern

```php
// Render with variables
$rendered = $emailService->renderTemplate(
    template: $template,
    variables: [
        'day' => 'Montag',
        'time' => '19:00',
        'location' => 'Stadion Müller',
        'recipient_group' => 'Spieler',
    ],
    locale: 'de'
);

// Result:
$rendered = [
    'subject' => 'Training: Montag um 19:00 Uhr',
    'body' => '<h2>Liebe Spieler,</h2><p>das Training findet statt...</p>',
    'plain_text' => 'TRAINING: MONTAG UM 19:00 UHR...',
    'from_name' => 'Club Name',
    'from_email' => 'info@club.de',
];
```

### Template updaten

```php
$emailService->updateTemplate($template, [
    'subject' => 'Neuer Betreff',
    'body' => '<p>Neuer Inhalt</p>',
    'variables' => [
        'new_var' => 'Neue Variable',
    ],
]);
```

### Template klonen

```php
$clone = $emailService->cloneTemplate(
    original: $template,
    creator: $admin
);

// Result: neue Template mit Name = "{Original} - Copy"
```

### Template löschen

```php
$emailService->deleteTemplate($template);
```

---

## 📨 Mass Email Sending

### Zu einzelnem User versenden

```php
$log = $emailService->sendToUser(
    template: $template,
    recipient: $user,
    variables: [
        'day' => 'Mittwoch',
        'time' => '20:00',
        'location' => 'Trainingszentrum',
        'recipient_group' => 'Trainer',
    ],
    options: [
        'locale' => 'de',
        'send_push' => true,    // Send push notification too
        'send_sms' => false,    // SMS disabled
    ]
);
```

### Zu Gruppe versenden

```php
// Send to all players
$result = $emailService->sendToGroup(
    template: $template,
    group: 'players', // or 'parents', 'coaches'
    club: $club,
    variables: [
        'day' => 'Samstag',
        'time' => '15:00',
        'location' => 'Stadion',
        'recipient_group' => 'Spieler',
    ],
    options: [
        'locale' => 'de',
    ]
);

// Result:
$result = [
    'total' => 250,
    'sent' => 248,
    'failed' => 2,
    'logs' => [/* NotificationLog entries */],
];
```

### Mass Email Queue Job

```php
use App\Jobs\SendMassEmailJob;

// Dispatch job for background processing
SendMassEmailJob::dispatch(
    templateId: $template->id,
    clubId: $club->id,
    recipientIds: [1, 2, 3, 4, 5], // Up to 1000
    variables: [
        'day' => 'Freitag',
        'time' => '18:00',
    ],
    locale: 'de',
    notificationLogId: $log->id,
);

// Job configuration:
// - Timeout: 1 hour
// - Retries: 3 attempts
// - Backoff: exponential (60s, 120s, 240s)
// - Batch size: 500 recipients at a time
```

---

## ⏰ Queue Jobs

### SendMassEmailJob

Versendet Mails an mehrere User in Batches:

```php
namespace App\Jobs;

class SendMassEmailJob implements ShouldQueue
{
    public int $timeout = 3600;  // 1 hour
    public int $tries = 3;       // Retry 3 times
    public int $backoff = 60;    // Exponential backoff

    public function __construct(
        private int $templateId,
        private int $clubId,
        private array $recipientIds,
        private array $variables = [],
        private string $locale = 'de',
        private ?int $notificationLogId = null,
    ) {}
}
```

**Verwendung:**

```php
// Send to 250 recipients
$recipients = User::where('club_id', $club->id)
    ->where('group', 'players')
    ->limit(250)
    ->pluck('id');

SendMassEmailJob::dispatch(
    templateId: $template->id,
    clubId: $club->id,
    recipientIds: $recipients->toArray(),
    variables: ['day' => 'Sonntag'],
    locale: 'de',
);
```

### SendScheduledEmailJob

Versendet einzelne Email zu bestimmtem Zeitpunkt:

```php
use App\Jobs\SendScheduledEmailJob;

$log = NotificationLog::create([
    'tenant_id' => $club->id,
    'type' => 'email',
    'status' => 'pending',
    'send_at' => now()->addHours(2),
]);

SendScheduledEmailJob::dispatch(
    notificationLogId: $log->id,
    templateId: $template->id,
    recipientId: $user->id,
    variables: ['day' => 'Montag'],
    locale: 'de',
);
```

**Zeitgesteuerte Verarbeitung:**

```bash
# Manually trigger
php artisan notifications:process-scheduled-emails

# Or with options:
php artisan notifications:process-scheduled-emails \
    --limit=100 \
    --delay=5   # Only process emails scheduled 5+ minutes ago
```

### SendPushNotificationJob

Versendet Push Notifications:

```php
use App\Jobs\SendPushNotificationJob;

SendPushNotificationJob::dispatch(
    pushNotificationId: $notification->id,
);

// Job automatically:
// 1. Gets all subscriptions matching target criteria
// 2. Sends to each subscription via Web Push API
// 3. Tracks delivery status
// 4. Retries on failure (3 attempts)
// 5. Updates statistics
```

---

## 🔐 Audit & Compliance

### EmailAuditService

```php
use App\Services\EmailAuditService;

$auditService = app(EmailAuditService::class);
```

#### Event Logging

```php
// Log email sent
$auditService->recordEmailSent(
    recipientId: $user->id,
    templateId: $template->id,
    clubId: $club->id,
    locale: 'de',
    variables: ['day' => 'Montag'],
);

// Log email opened (from tracking pixel)
$auditService->recordEmailOpened(
    recipientId: $user->id,
    templateId: $template->id,
    clubId: $club->id,
);

// Log link clicked
$auditService->recordEmailClicked(
    recipientId: $user->id,
    templateId: $template->id,
    clubId: $club->id,
    url: 'https://club.de/matches/123',
);

// Log bounce
$auditService->recordEmailBounced(
    recipientId: $user->id,
    templateId: $template->id,
    clubId: $club->id,
    bounceType: 'soft', // or 'hard'
);

// Log unsubscribe
$auditService->recordEmailUnsubscribe(
    recipientId: $user->id,
    clubId: $club->id,
    reason: 'Nicht interessiert',
);
```

#### Audit Trails

```php
// Get user email history
$history = $auditService->getUserEmailHistory(
    user: $user,
    clubId: $club->id,
    template: $template, // Optional
    page: 1,
    perPage: 50,
);

foreach ($history as $log) {
    echo $log->type;        // email_sent, email_opened, etc
    echo $log->logged_at;   // Timestamp
    echo $log->metadata;    // Additional info
}

// Get template audit trail
$trail = $auditService->getTemplateAuditTrail(
    template: $template,
    eventType: 'email_sent', // Optional filter
    page: 1,
    perPage: 50,
);
```

#### Compliance Reports

```php
// Get compliance report (last 3 months)
$report = $auditService->getComplianceReport(
    clubId: $club->id,
    startDate: now()->subMonths(3),
    endDate: now(),
);

// Result:
$report = [
    'period' => [
        'start' => '2025-07-23 00:00:00',
        'end' => '2025-10-23 23:59:59',
    ],
    'total_sent' => 5000,
    'total_opened' => 2250,
    'total_clicked' => 450,
    'total_bounced' => 75,
    'total_unsubscribed' => 25,
    'open_rate' => 45.00,
    'click_rate' => 9.00,
    'bounce_rate' => 1.50,
    'unsubscribe_rate' => 0.50,
    'list_cleanliness' => 98.00,
];
```

#### Engagement Metrics

```php
// Get engagement by date
$metrics = $auditService->getEngagementMetrics(
    template: $template,
    startDate: now()->subWeeks(2),
    endDate: now(),
);

// Result:
$metrics = [
    'template_id' => 5,
    'template_name' => 'Match Report',
    'period' => ['start' => '2025-10-09', 'end' => '2025-10-23'],
    'total' => [
        'sent' => 150,
        'opened' => 67,
        'clicked' => 12,
        'open_rate' => 44.67,
        'click_rate' => 8.00,
        'click_to_open_rate' => 17.91,
    ],
    'by_date' => [
        '2025-10-23' => ['sent' => 10, 'opened' => 5, 'clicked' => 1],
        '2025-10-22' => ['sent' => 12, 'opened' => 6, 'clicked' => 1],
        // ...
    ],
];
```

#### GDPR Compliance

```php
// Check data retention compliance
$compliance = $auditService->verifyGDPRCompliance(
    clubId: $club->id,
    retentionDays: 90,
);

// Result:
$compliance = [
    'retention_days' => 90,
    'cutoff_date' => '2025-07-25',
    'logs_to_delete' => 1500,
    'logs_retained' => 8500,
    'compliant' => true,
];

// Delete old logs (with dry-run)
$result = $auditService->deleteOldLogs(
    clubId: $club->id,
    retentionDays: 90,
    dryRun: true, // Set to false to actually delete
);

// Result:
$result = [
    'dry_run' => true,
    'deleted' => 0,
    'would_delete' => 1500,
    'cutoff_date' => '2025-07-25',
];
```

#### Export für Compliance-Berichte

```php
// Export audit trail als CSV
$csv = $auditService->exportAuditTrail(
    clubId: $club->id,
    format: 'csv',
    startDate: now()->subMonths(3),
    endDate: now(),
);

// Save to file
file_put_contents(
    storage_path('exports/audit-trail.csv'),
    $csv
);

// Or as JSON
$json = $auditService->exportAuditTrail(
    clubId: $club->id,
    format: 'json',
);
```

---

## 🔌 API Endpoints

### Email Templates

```
GET     /api/email-templates
        List templates (with pagination)
        
        Query:
        ?page=1
        ?per_page=20
        ?group=parents

        Response:
        {
            "data": [
                {
                    "id": 1,
                    "name": "Match Report",
                    "slug": "match-report",
                    "target_groups": ["parents", "players"],
                    "created_at": "2025-10-23T10:00:00Z",
                    "usage_count": 5
                }
            ],
            "pagination": {
                "current_page": 1,
                "total": 25,
                "per_page": 20
            }
        }

POST    /api/email-templates
        Create template
        
        Body:
        {
            "name": "New Template",
            "slug": "new-template",
            "subject": "Subject: {date}",
            "body": "<h2>Content</h2>",
            "target_groups": ["players"],
            "variables": {
                "date": "Event date",
                "location": "Event location"
            }
        }

GET     /api/email-templates/{id}
        Get template details

PUT     /api/email-templates/{id}
        Update template
        
        Body: Same as POST

DELETE  /api/email-templates/{id}
        Delete template

POST    /api/email-templates/{id}/preview
        Preview rendered template
        
        Body:
        {
            "variables": {
                "date": "2025-10-25",
                "location": "Stadium"
            },
            "locale": "de"
        }

POST    /api/email-templates/{id}/test
        Send test email to authenticated user

GET     /api/email-templates/{id}/stats
        Get template statistics
        
        Response:
        {
            "total_sent": 250,
            "open_rate": 45.67,
            "click_rate": 12.34,
            "bounce_rate": 1.50,
            "last_used": "2025-10-23T14:30:00Z"
        }

POST    /api/email-templates/{id}/clone
        Duplicate template
        
        Response:
        {
            "id": 10,
            "name": "New Template - Copy",
            "slug": "new-template-copy"
        }
```

---

## ✨ Best Practices

### 1. Template Design

✅ **Gut:**
```html
<h2>Liebe {{recipient_group}},</h2>
<p>wir möchten Sie informieren über:</p>
<h3>{{event_title}}</h3>
<p><strong>Wann:</strong> {{date}} um {{time}}</p>
<p><strong>Wo:</strong> {{location}}</p>
```

❌ **Schlecht:**
```html
Hallo,
neuer Update hier
```

### 2. Variable Handling

```php
// ✅ RICHTIG: Dokumentiere all deine Variablen
$template->variables = [
    'date' => 'Format: YYYY-MM-DD',
    'time' => 'Format: HH:MM (z.B. 19:00)',
    'location' => 'Ort des Events',
    'recipient_group' => 'Auto-filled: parents|players|coaches',
];

// ❌ FALSCH: Undokumentierte Variablen
$template->variables = [
    'd' => '?',
    't' => '?',
];
```

### 3. Audit Logging

```php
// ✅ RICHTIG: Log wichtige Ereignisse
$auditService->recordEmailSent($user->id, $template->id, $club->id);

// Nach Webhook vom Mail-Dienst:
$auditService->recordEmailOpened(...);
$auditService->recordEmailClicked(...);

// ❌ FALSCH: Kein Logging
// ... send email ...
// (keine Verfolgung)
```

### 4. Mass Email Performance

```php
// ✅ RICHTIG: Batch größe optimal einstellen
foreach (User::where('club_id', $club->id)->chunk(500) as $users) {
    SendMassEmailJob::dispatch($users->pluck('id')->toArray());
}

// ❌ FALSCH: Zu viele auf einmal
$allUsers = User::where('club_id', $club->id)->pluck('id');
SendMassEmailJob::dispatch($allUsers->toArray()); // 10,000+ in one job!
```

### 5. Retry Logic

```php
// ✅ RICHTIG: Exponential backoff
// Job tries: 1 -> 2 -> 3
// Backoff: 60s -> 120s -> 240s

// ❌ FALSCH: Immediate retry
// Job tries too many times immediately
```

### 6. GDPR Compliance

```php
// ✅ RICHTIG: Retention Policy
// - Audit logs: 90 days
// - Open/Click tracking: 30 days
// - Unsubscribes: Keep indefinitely

// Cleanup regularly:
$auditService->deleteOldLogs($club->id, retentionDays: 90);

// ❌ FALSCH: Keine Retention Policy
// (Alle logs bleiben für immer)
```

---

## 🐛 Troubleshooting

### E-Mails werden nicht versendet

```bash
# 1. Check queue is running
php artisan queue:work

# 2. Check failed jobs
php artisan queue:failed

# 3. Check configuration
php artisan config:show mail

# 4. Check logs
tail storage/logs/laravel.log | grep -i email
```

### Template mit Variablen wird nicht gerendert

```php
// Debug rendering
$rendered = $emailService->renderTemplate(
    $template,
    variables: ['date' => '2025-10-25'],
    locale: 'de'
);

// Check if variables match
dd($template->variables); // Should have 'date'
dd($rendered); // Should have rendered values
```

### Audit logs nicht zu finden

```php
// Check if logging is enabled
$logs = NotificationLog::where('template_id', $template->id)->get();
if ($logs->isEmpty()) {
    // Make sure you're calling audit service methods
    $auditService->recordEmailSent(...);
}
```

### Scheduled emails nicht versendet

```bash
# Check scheduler is running
php artisan schedule:run

# Or run manually
php artisan notifications:process-scheduled-emails

# Check scheduled emails exist
php artisan tinker
>>> NotificationLog::where('status', 'pending')->count()
```

---

## 📊 Reporting & Analytics

### Dashboard Queries

```php
// Email statistics by day (last 30 days)
$stats = NotificationLog::where('tenant_id', $club->id)
    ->where('type', 'email_sent')
    ->where('logged_at', '>=', now()->subDays(30))
    ->selectRaw('DATE(logged_at) as date, COUNT(*) as sent_count')
    ->groupBy('date')
    ->get();

// Top performing templates
$topTemplates = EmailTemplate::where('club_id', $club->id)
    ->withCount('notificationLogs')
    ->orderBy('notification_logs_count', 'desc')
    ->limit(10)
    ->get();

// Engagement trend
$trend = NotificationLog::where('tenant_id', $club->id)
    ->whereIn('type', ['email_opened', 'email_clicked'])
    ->selectRaw('DATE(logged_at) as date, type, COUNT(*) as count')
    ->groupBy('date', 'type')
    ->orderBy('date')
    ->get();
```

---

**Letzte Aktualisierung:** 2025-10-23
**Version:** 2.0
**Status:** ✅ Production Ready

Mit diesem System kannst du:
- ✅ Professionelle E-Mail-Vorlagen verwalten
- ✅ Mass-Emails an tausende Empfänger versenden
- ✅ Zeitgesteuerte E-Mails automatisieren
- ✅ Vollständige Audit Trails führen
- ✅ GDPR-konform arbeiten
- ✅ Detaillierte Analytics abrufen
