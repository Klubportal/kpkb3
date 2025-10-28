# PWA & Push Notifications - Implementierungsguide

**Vollständige Dokumentation für Progressive Web App, Push-Benachrichtigungen, In-App Messaging und E-Mail System**

## 📋 Inhaltsverzeichnis

1. [Übersicht](#übersicht)
2. [PWA Setup](#pwa-setup)
3. [Push-Benachrichtigungen](#push-benachrichtigungen)
4. [In-App Messaging](#in-app-messaging)
5. [E-Mail System](#e-mail-system)
6. [API Endpoints](#api-endpoints)
7. [Client-Side Integration](#client-side-integration)
8. [Best Practices](#best-practices)

---

## 🎯 Übersicht

Das KP Club Management System implementiert eine vollständige **Progressive Web App (PWA)** mit:

- ✅ **Offline-Funktionalität** - Works without internet
- ✅ **Push-Benachrichtigungen** - Web Push API Integration
- ✅ **In-App Messaging** - Direct & Group Conversations
- ✅ **E-Mail System** - Templates + Mass Email
- ✅ **Background Sync** - Automatic retry on reconnect
- ✅ **Multilingual** - 11 Sprachen unterstützt

### Architektur-Übersicht

```
┌─────────────────────────────────────────┐
│         Browser/Client Layer            │
├─────────────────────────────────────────┤
│  PWA App + Service Worker               │
│  Push API + Web Notifications           │
└────────────────┬────────────────────────┘
                 │
         ┌───────┴────────┐
         │                │
    ┌────▼───────────┐   ┌──────────────────┐
    │  HTTP/HTTPS    │   │  WebSocket/SSE   │
    └────┬───────────┘   └──────────────────┘
         │
┌────────▼──────────────────────────────────┐
│        Laravel API (Tenant)                │
├────────────────────────────────────────────┤
│  Push Subscription Controller              │
│  Notification Controller                   │
│  Message Controller                        │
│  Email Template Controller                 │
└────────┬───────────────────────────────────┘
         │
    ┌────▼────────────────────┐
    │  Services Layer         │
    ├────────────────────────┤
    │ PushNotificationService│
    │ MessageService         │
    │ EmailService           │
    │ OrchestrationService   │
    └────┬───────────────────┘
         │
    ┌────▼─────────────┐
    │  Models          │
    ├──────────────────┤
    │ PushSubscription │
    │ PushNotification │
    │ Message          │
    │ MessageRecipient │
    │ Conversation     │
    │ EmailTemplate    │
    │ NotificationLog  │
    └──────────────────┘
```

---

## 🔧 PWA Setup

### 1. Manifest registrieren

Die `manifest.json` wird automatisch von Laravel geladen. Sicherstelle, dass sie in `public/` liegt:

```html
<!-- In resources/views/app.blade.php -->
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/images/icon-192x192.png">
<meta name="theme-color" content="#1F2937">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
```

### 2. Service Worker registrieren

```javascript
// In resources/js/app.js oder bootstrap.js

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => {
                console.log('Service Worker registered:', registration);
                
                // Check for updates periodically
                setInterval(() => {
                    registration.update();
                }, 60000); // Check every minute
            })
            .catch(error => {
                console.error('Service Worker registration failed:', error);
            });
    });
}
```

### 3. Push-Berechtigung anfordern

```javascript
// Frage User nach Push-Berechtigung
async function requestPushPermission() {
    if (!('serviceWorker' in navigator)) {
        console.error('Service Workers not supported');
        return;
    }

    if (Notification.permission === 'granted') {
        // Already granted
        return subscribeToPushNotifications();
    }

    if (Notification.permission === 'denied') {
        console.log('Push notifications denied');
        return;
    }

    // Request permission
    const permission = await Notification.requestPermission();
    
    if (permission === 'granted') {
        subscribeToPushNotifications();
    }
}

async function subscribeToPushNotifications() {
    const registration = await navigator.serviceWorker.ready;
    
    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(
            import.meta.env.VITE_VAPID_PUBLIC_KEY
        ),
    });

    // Send subscription to server
    await fetch('/api/push-subscriptions/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${getAuthToken()}`,
        },
        body: JSON.stringify({
            endpoint: subscription.endpoint,
            keys: {
                p256dh: arrayBufferToBase64(subscription.getKey('p256dh')),
                auth: arrayBufferToBase64(subscription.getKey('auth')),
            },
            browser: getBrowserName(),
            deviceType: getDeviceType(), // 'mobile', 'desktop', 'tablet'
            deviceName: getDeviceName(),
        }),
    });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    return new Uint8Array([...rawData].map(char => char.charCodeAt(0)));
}

function arrayBufferToBase64(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
}
```

---

## 📢 Push-Benachrichtigungen

### Arten von Benachrichtigungen

```
1. General      - Standard-Benachrichtigungen
2. Match        - Spiel-Updates
3. Ranking      - Tabellen-Updates
4. Event        - Veranstaltung-Mitteilungen
5. Message      - Neue Nachrichten
6. Alert        - Wichtige Warnungen
7. Broadcast    - System-Mitteilungen
```

### Push erstellen & versenden

```php
// In Controller oder Command

use App\Services\PushNotificationService;

class SendNotificationCommand
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

    public function handle()
    {
        $club = Club::find(1);
        $admin = $club->admin; // Authorized user
        
        // Create notification
        $notification = $this->pushService->createNotification(
            $club,
            $admin,
            [
                'title' => 'Nächstes Spiel',
                'body' => 'FC München vs Borussia Dortmund am Samstag',
                'type' => 'match',
                'priority' => 'high',
                'icon' => 'https://example.com/icon.png',
                'action_url' => '/matches/123',
                'target_groups' => ['parents', 'players'],
                'target_tags' => ['first-team'],
            ]
        );

        // Send immediately
        $result = $this->pushService->send($notification);
        
        echo "Sent to: {$result['sent']} recipients";
        echo "Delivered: {$result['delivered']}";
        echo "Failed: {$result['failed']}";
    }
}
```

### Geplante Benachrichtigungen

```php
// Schedule notification for later

$notification = $this->pushService->createNotification(
    $club,
    $admin,
    [
        'title' => 'Training heute',
        'body' => 'Training startet in 2 Stunden',
        'type' => 'event',
        'status' => 'scheduled',
        'scheduled_at' => now()->addHours(2),
    ]
);

// Or schedule existing
$this->pushService->scheduleNotification(
    $notification,
    new \DateTime('+2 hours')
);

// Send scheduled notifications (via scheduled command)
$scheduled = PushNotification::where('status', 'scheduled')
    ->where('scheduled_at', '<=', now())
    ->get();

foreach ($scheduled as $notification) {
    $this->pushService->send($notification);
}
```

### Statistiken & Tracking

```php
// Get notification statistics

$stats = $this->pushService->getStats($notification);

// Result:
[
    'id' => 123,
    'title' => 'Match Update',
    'status' => 'sent',
    'total_recipients' => 150,
    'sent_count' => 150,
    'delivered_count' => 145,
    'clicked_count' => 42,
    'failed_count' => 5,
    'delivery_rate' => '96.67%',
    'click_rate' => '28.97%',
]
```

---

## 💬 In-App Messaging

### Direct Messages

```php
use App\Services\MessageService;

$messageService = app(MessageService::class);

// Send direct message
$message = $messageService->sendDirectMessage(
    sender: $user1, // Current user
    recipient: $user2,
    club: $club,
    subject: 'Training morgen?',
    body: 'Können wir morgen trainieren?',
    [
        'priority' => 'high',
        'locale' => 'de',
    ]
);

// Mark as read
$messageService->markAsRead($message, $user2);

// Get unread count
$unreadCount = $messageService->getUnreadCount($user2, $club);

// Get user messages
$messages = $messageService->getUserMessages($user2, $club, page: 1, perPage: 20);
```

### Group Conversations

```php
// Create conversation
$conversation = $messageService->createConversation(
    creator: $admin,
    club: $club,
    title: 'U21-Team',
    participantIds: [1, 2, 3, 4, 5],
    [
        'type' => 'group',
        'description' => 'Kommunikation für die U21',
        'is_public' => false,
    ]
);

// Add participant
$messageService->addParticipant($conversation, $newUser);

// Send message to group
$message = $messageService->sendGroupMessage(
    sender: $admin,
    conversation: $conversation,
    body: 'Nächstes Training: Dienstag 19:00 Uhr',
);

// Get conversation messages
$messages = $messageService->getConversationMessages(
    $conversation,
    $user,
    page: 1
);

// Mark conversation as read
$readCount = $messageService->markConversationAsRead($conversation, $user);

// Archive conversation
$messageService->archiveConversation($conversation);
```

### Message Replies

```php
// Reply to a message
$reply = $messageService->replyToMessage(
    sender: $user,
    originalMessage: $message,
    body: 'Ja, ich bin dabei!',
);

// Get message thread
$thread = $messageService->getMessageThread($message);
// Returns: $message, all $replies
```

---

## 📧 E-Mail System

### Templates verwalten

```php
use App\Services\EmailService;

$emailService = app(EmailService::class);

// Create template
$template = $emailService->createTemplate(
    club: $club,
    creator: $admin,
    data: [
        'name' => 'Match Report',
        'slug' => 'match-report', // auto-generated if omitted
        'subject' => 'Spielbericht: {match_date}',
        'body' => '<h2>Liebe {recipient_group},</h2>' .
                  '<p>Das Spiel {team1} vs {team2} hat stattgefunden.</p>' .
                  '<p>Ergebnis: {result}</p>',
        'target_groups' => ['parents', 'players'],
        'variables' => [
            'match_date' => 'Datum des Spiels',
            'team1' => 'Erste Mannschaft',
            'team2' => 'Gegner',
            'result' => 'Endstand',
            'recipient_group' => 'Empfängergruppe',
        ],
        'from_email' => 'info@club.de',
        'include_logo' => true,
        'include_footer' => true,
    ]
);

// Get template
$template = $emailService->getTemplateBySlug($club, 'match-report');

// Update template
$emailService->updateTemplate($template, [
    'subject' => 'Aktualisierter Betreff',
    'body' => '...',
]);

// Clone template
$clone = $emailService->cloneTemplate($template, $admin);
```

### Template Rendering

```php
// Render template with variables
$rendered = $emailService->renderTemplate(
    template: $template,
    variables: [
        'match_date' => '2025-10-25',
        'team1' => 'FC München',
        'team2' => 'Borussia Dortmund',
        'result' => '3:2',
        'recipient_group' => 'Eltern',
    ],
    locale: 'de' // German
);

// Result:
[
    'subject' => 'Spielbericht: 2025-10-25',
    'body' => '<h2>Liebe Eltern,</h2>' .
              '<p>Das Spiel FC München vs Borussia Dortmund hat stattgefunden.</p>' .
              '<p>Ergebnis: 3:2</p>',
    'plain_text' => 'Plain text version...',
    'from_name' => 'FC Club',
    'from_email' => 'info@club.de',
]
```

### E-Mails versenden

```php
// Send to single user
$log = $emailService->sendToUser(
    template: $template,
    recipient: $user,
    variables: [
        'match_date' => '2025-10-25',
        'result' => '3:2',
    ],
    [
        'locale' => 'de',
    ]
);

// Send mass email to group
$result = $emailService->sendMassEmail(
    template: $template,
    recipients: $users->collect(), // Collection of users
    variables: [
        'match_date' => '2025-10-25',
        'result' => '3:2',
    ],
);

// Result:
[
    'total' => 100,
    'sent' => 98,
    'failed' => 2,
    'logs' => [/*notification logs*/],
]

// Send to group (parents, players, coaches)
$result = $emailService->sendToGroup(
    template: $template,
    group: 'parents', // or 'players', 'coaches'
    club: $club,
    variables: [/*...*/],
);

// Schedule email
$log = $emailService->scheduleEmail(
    template: $template,
    recipient: $user,
    variables: [/*...*/],
    sendAt: new DateTime('+2 hours'),
);
```

### Template Statistiken

```php
// Get template usage statistics
$stats = $emailService->getTemplateStats($template);

// Result:
[
    'template_id' => 1,
    'template_name' => 'Match Report',
    'total_sent' => 250,
    'delivered' => 248,
    'failed' => 2,
    'open_rate' => '45.67%',
    'click_rate' => '12.34%',
    'last_used' => '2025-10-25 14:30:00',
    'usage_count' => 5,
]
```

---

## 🔌 API Endpoints

### Push Subscriptions

```
POST   /api/push-subscriptions/register
       Register device for push notifications
       
       Body:
       {
           "endpoint": "https://push.service/...",
           "keys": {
               "p256dh": "...",
               "auth": "..."
           },
           "browser": "Chrome",
           "deviceType": "mobile",
           "deviceName": "iPhone 12"
       }

GET    /api/push-subscriptions
       Get user's active subscriptions

PUT    /api/push-subscriptions/{id}
       Update subscription settings
       
       Body:
       {
           "notifications_enabled": true,
           "sound_enabled": true,
           "badge_enabled": true
       }

DELETE /api/push-subscriptions/{id}
       Unregister device
```

### Push Notifications

```
GET    /api/push-notifications
       List notifications for club
       
       Query:
       ?status=sent&limit=20

POST   /api/push-notifications
       Create notification
       
       Body:
       {
           "title": "...",
           "body": "...",
           "type": "match",
           "priority": "high",
           "target_groups": ["parents"],
           "scheduled_at": "2025-10-25 14:30:00"
       }

POST   /api/push-notifications/{id}/send
       Send notification immediately

POST   /api/push-notifications/{id}/schedule
       Schedule for later
       
       Body:
       {
           "scheduled_at": "2025-10-25 14:30:00"
       }

GET    /api/push-notifications/{id}/stats
       Get delivery statistics
```

### Messages

```
GET    /api/messages
       Get user messages
       
       Query:
       ?page=1&per_page=20

POST   /api/messages/direct
       Send direct message
       
       Body:
       {
           "recipient_id": 5,
           "subject": "...",
           "body": "...",
           "priority": "normal"
       }

GET    /api/messages/unread-count
       Get unread message count

POST   /api/messages/{id}/reply
       Reply to message
       
       Body:
       {
           "body": "..."
       }
```

### Conversations

```
GET    /api/conversations
       Get user's conversations

POST   /api/conversations
       Create new conversation
       
       Body:
       {
           "title": "U21-Team",
           "participant_ids": [1, 2, 3],
           "type": "group",
           "description": "..."
       }

GET    /api/conversations/{id}
       Get conversation with messages
       
       Query:
       ?page=1&per_page=50

POST   /api/conversations/{id}/messages
       Send message to group
       
       Body:
       {
           "body": "..."
       }
```

### Email Templates

```
GET    /api/email-templates
       List templates
       
       Query:
       ?group=parents

POST   /api/email-templates
       Create template
       
       Body:
       {
           "name": "...",
           "subject": "...",
           "body": "...",
           "target_groups": ["parents"],
           "variables": {...}
       }

POST   /api/email-templates/{id}/preview
       Preview rendered template
       
       Body:
       {
           "variables": {...},
           "locale": "de"
       }

POST   /api/email-templates/{id}/test
       Send test email to self

GET    /api/email-templates/{id}/stats
       Get template statistics
```

---

## 🖥️ Client-Side Integration

### React/Vue Example

```javascript
// composable/useNotifications.js

export const useNotifications = () => {
    const requestPushPermission = async () => {
        if (!('serviceWorker' in navigator)) return;
        
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            await subscribeToPushNotifications();
        }
    };

    const subscribeToPushNotifications = async () => {
        const registration = await navigator.serviceWorker.ready;
        
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(
                import.meta.env.VITE_VAPID_PUBLIC_KEY
            ),
        });

        await axios.post('/api/push-subscriptions/register', {
            endpoint: subscription.endpoint,
            keys: {
                p256dh: arrayBufferToBase64(subscription.getKey('p256dh')),
                auth: arrayBufferToBase64(subscription.getKey('auth')),
            },
            browser: navigator.userAgent,
            deviceType: getDeviceType(),
        });
    };

    const sendMessage = async (conversationId, body) => {
        return await axios.post(
            `/api/conversations/${conversationId}/messages`,
            { body }
        );
    };

    const getUnreadCount = async () => {
        const response = await axios.get('/api/messages/unread-count');
        return response.data.data.unread_count;
    };

    return {
        requestPushPermission,
        sendMessage,
        getUnreadCount,
    };
};
```

---

## ✨ Best Practices

### 1. Push Notifications

- ✅ Immer User Permission abfragen
- ✅ Aussagekräftige Titles & Bodies
- ✅ Action URLs bereitstellen
- ✅ Nicht zu viele Pushes versenden
- ✅ Personalisierung nutzen
- ✅ Zu Ruhezeiten begrenzen

```php
// ❌ FALSCH
$notification->title = 'Update';
$notification->body = 'Es gibt etwas Neues';

// ✅ RICHTIG
$notification->title = 'Spielplan Update';
$notification->body = 'Nächstes Spiel: Sa., 15:00 vs. FC Köln';
$notification->action_url = '/matches/123';
```

### 2. In-App Messaging

- ✅ Read-Status tracken
- ✅ Unread-Count im UI anzeigen
- ✅ Conversations archivieren
- ✅ Nachrichtenthreads unterstützen
- ✅ Verschlüsselung für sensitive Inhalte

### 3. E-Mail

- ✅ Templates verwenden (nie hardcoded)
- ✅ Variables dokumentieren
- ✅ Multilinguale Varianten
- ✅ Plain-Text Fallback
- ✅ Unsubscribe Link
- ✅ Spam Check (SPF, DKIM, DMARC)

### 4. Datenschutz

- ✅ DSGVO konform (Consent, Deletion)
- ✅ Audit Trail führen
- ✅ Verschlüsselte Übertragung
- ✅ Keine sensitive Daten in Logs
- ✅ Retention Policy (z.B. 90 Tage)

---

## 🐛 Troubleshooting

### Push Notifications funktionieren nicht

```javascript
// 1. Service Worker Check
navigator.serviceWorker.getRegistrations()
    .then(registrations => {
        console.log('Service Workers:', registrations);
    });

// 2. Notification Permission Check
console.log('Permission:', Notification.permission);
// Should be: 'granted'

// 3. Push Manager Check
const registration = await navigator.serviceWorker.ready;
const subscription = await registration.pushManager.getSubscription();
console.log('Subscription:', subscription);
```

### Offline funktioniert nicht

```javascript
// Check if offline.html is being served
fetch('/offline.html')
    .then(r => r.text())
    .then(html => console.log('Offline page:', html));

// Check cache
caches.keys().then(names => {
    console.log('Caches:', names);
    names.forEach(name => {
        caches.open(name).then(cache => {
            cache.keys().then(requests => {
                console.log(`${name}:`, requests.map(r => r.url));
            });
        });
    });
});
```

---

## 📚 Weitere Ressourcen

- [Web Push Protocol](https://tools.ietf.org/html/draft-thomson-webpush-protocol)
- [Service Worker Spec](https://w3c.github.io/ServiceWorker/)
- [Web Notifications](https://www.w3.org/TR/notifications/)
- [Web App Manifest](https://www.w3.org/TR/appmanifest/)

---

**Letzte Aktualisierung:** 2025-10-23
**Version:** 3.0
**Status:** ✅ Production Ready
