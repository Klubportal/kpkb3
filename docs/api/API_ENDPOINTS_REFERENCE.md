# API Endpoints Reference - Vollständige Dokumentation

**Alle 33+ REST API Endpoints für Push Notifications, Messaging, Email Templates und mehr**

---

## 🗂️ Index der Endpoints

### Push Subscriptions (6 Endpoints)
- [POST /api/push-subscriptions/register](#post-apipush-subscriptionsregister)
- [POST /api/push-subscriptions/unregister](#post-apipush-subscriptionsunregister)
- [GET /api/push-subscriptions](#get-apipush-subscriptions)
- [PUT /api/push-subscriptions/{id}](#put-apipush-subscriptionsid)
- [POST /api/push-subscriptions/{id}/test](#post-apipush-subscriptionsidtest)
- [DELETE /api/push-subscriptions/{id}](#delete-apipush-subscriptionsid)

### Push Notifications (8 Endpoints)
- [GET /api/push-notifications](#get-apipush-notifications)
- [POST /api/push-notifications](#post-apipush-notifications)
- [POST /api/push-notifications/{id}/send](#post-apipush-notificationsidsend)
- [POST /api/push-notifications/{id}/schedule](#post-apipush-notificationsidschedule)
- [POST /api/push-notifications/{id}/cancel](#post-apipush-notificationsidcancel)
- [GET /api/push-notifications/{id}/stats](#get-apipush-notificationsidstats)
- [POST /api/webhooks/push/delivery](#post-apihookspushdelivery)
- [POST /api/webhooks/push/click](#post-apihookspushclick)

### Messages (10 Endpoints)
- [GET /api/messages](#get-apimessages)
- [POST /api/messages/direct](#post-apimessagesdirect)
- [GET /api/messages/unread-count](#get-apimessagesunread-count)
- [POST /api/messages/{id}/reply](#post-apimessagesidreply)
- [POST /api/messages/{id}/read](#post-apimessagesidread)
- [POST /api/messages/{id}/archive](#post-apimessagesidarchive)
- [DELETE /api/messages/{id}](#delete-apimessagesid)
- [POST /api/conversations](#post-apiconversations)
- [GET /api/conversations](#get-apiconversations)
- [GET /api/conversations/{id}](#get-apiconversationsid)

### Email Templates (9 Endpoints)
- [GET /api/email-templates](#get-apiemail-templates)
- [POST /api/email-templates](#post-apiemail-templates)
- [GET /api/email-templates/{id}](#get-apiemail-templatesid)
- [PUT /api/email-templates/{id}](#put-apiemail-templatesid)
- [DELETE /api/email-templates/{id}](#delete-apiemail-templatesid)
- [POST /api/email-templates/{id}/preview](#post-apiemail-templatesidpreview)
- [POST /api/email-templates/{id}/test](#post-apiemail-templatesidtest)
- [GET /api/email-templates/{id}/stats](#get-apiemail-templatesidstats)
- [POST /api/email-templates/{id}/clone](#post-apiemail-templatesidclone)

---

## 📍 Push Subscriptions

### POST /api/push-subscriptions/register

**Registriere ein Gerät für Push-Benachrichtigungen**

```bash
curl -X POST https://api.club.local/api/push-subscriptions/register \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "endpoint": "https://fcm.googleapis.com/fcm/send/...",
    "keys": {
      "p256dh": "BEl62iUAm7...",
      "auth": "FZxIqWJ5DqHyM..."
    },
    "browser": "Chrome",
    "deviceType": "mobile",
    "deviceName": "iPhone 12"
  }'
```

**Request Parameters:**
- `endpoint` (string, required): Web Push Endpoint URL
- `keys` (object, required):
  - `p256dh` (string): ECDH public key (base64)
  - `auth` (string): Authentication secret (base64)
- `browser` (string): Browser name (Chrome, Firefox, Safari, etc.)
- `deviceType` (string): mobile | desktop | tablet
- `deviceName` (string): Device identifier

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "endpoint": "https://fcm.googleapis.com/fcm/send/...",
    "browser": "Chrome",
    "deviceType": "mobile",
    "isActive": true,
    "lastUsedAt": "2025-10-23T14:30:00Z",
    "createdAt": "2025-10-23T10:00:00Z"
  }
}
```

---

### POST /api/push-subscriptions/unregister

**Entferne Geräteregistrierung**

```bash
curl -X POST https://api.club.local/api/push-subscriptions/unregister \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "endpoint": "https://fcm.googleapis.com/fcm/send/..."
  }'
```

**Response (200 OK):**
```json
{
  "message": "Successfully unregistered",
  "subscription_id": 1
}
```

---

### GET /api/push-subscriptions

**Liste alle aktiven Subscriptions des Users**

```bash
curl -X GET https://api.club.local/api/push-subscriptions \
  -H "Authorization: Bearer {TOKEN}"
```

**Query Parameters:**
- `page` (int): Seite (default: 1)
- `per_page` (int): Pro Seite (default: 20)
- `status` (string): active | inactive | expired

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "browser": "Chrome",
      "deviceType": "mobile",
      "deviceName": "iPhone 12",
      "isActive": true,
      "lastUsedAt": "2025-10-23T14:30:00Z",
      "createdAt": "2025-10-23T10:00:00Z"
    }
  ],
  "pagination": {
    "currentPage": 1,
    "total": 5,
    "perPage": 20
  }
}
```

---

### PUT /api/push-subscriptions/{id}

**Update Subscription-Einstellungen**

```bash
curl -X PUT https://api.club.local/api/push-subscriptions/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "notificationsEnabled": true,
    "soundEnabled": true,
    "badgeEnabled": true
  }'
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "notificationsEnabled": true,
    "soundEnabled": true,
    "badgeEnabled": true
  }
}
```

---

### POST /api/push-subscriptions/{id}/test

**Sende Test-Benachrichtigung**

```bash
curl -X POST https://api.club.local/api/push-subscriptions/1/test \
  -H "Authorization: Bearer {TOKEN}"
```

**Response (200 OK):**
```json
{
  "message": "Test notification sent",
  "subscriptionId": 1
}
```

---

### DELETE /api/push-subscriptions/{id}

**Lösche Subscription**

```bash
curl -X DELETE https://api.club.local/api/push-subscriptions/1 \
  -H "Authorization: Bearer {TOKEN}"
```

**Response (204 No Content):**
```
(leerer Body)
```

---

## 📢 Push Notifications

### GET /api/push-notifications

**Liste alle Benachrichtigungen des Clubs**

```bash
curl -X GET https://api.club.local/api/push-notifications \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123"
```

**Query Parameters:**
- `page` (int): Seite (default: 1)
- `per_page` (int): Pro Seite (default: 20)
- `status` (string): draft | scheduled | sent | failed | cancelled
- `type` (string): general | match | ranking | event | message | alert | broadcast

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Nächstes Spiel",
      "body": "FC München vs Borussia Dortmund",
      "type": "match",
      "status": "sent",
      "sentCount": 150,
      "deliveredCount": 145,
      "clickedCount": 42,
      "failedCount": 5,
      "createdAt": "2025-10-23T10:00:00Z"
    }
  ],
  "pagination": {
    "currentPage": 1,
    "total": 45,
    "perPage": 20
  }
}
```

---

### POST /api/push-notifications

**Erstelle neue Push-Benachrichtigung**

```bash
curl -X POST https://api.club.local/api/push-notifications \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Spielplan Update",
    "body": "Nächstes Spiel: Samstag um 15:00",
    "type": "match",
    "priority": "high",
    "icon": "https://club.local/icon.png",
    "actionUrl": "/matches/123",
    "targetGroups": ["parents", "players"],
    "targetTags": ["first-team"],
    "actions": [
      {
        "action": "view",
        "title": "Anschauen"
      },
      {
        "action": "dismiss",
        "title": "Schließen"
      }
    ]
  }'
```

**Request Parameters:**
- `title` (string, required): Titel
- `body` (string, required): Nachrichtentext
- `type` (string): general | match | ranking | event | message | alert | broadcast
- `priority` (string): low | normal | high
- `icon` (string): Icon URL
- `actionUrl` (string): URL beim Click
- `targetGroups` (array): parents | players | coaches
- `targetTags` (array): Custom tags
- `actions` (array): Action buttons
- `status` (string): draft | pending | scheduled

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "title": "Spielplan Update",
    "body": "Nächstes Spiel: Samstag um 15:00",
    "type": "match",
    "status": "pending",
    "createdAt": "2025-10-23T10:00:00Z"
  }
}
```

---

### POST /api/push-notifications/{id}/send

**Sende Benachrichtigung sofort**

```bash
curl -X POST https://api.club.local/api/push-notifications/1/send \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123"
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "status": "sent",
    "sentCount": 150,
    "deliveredCount": 145,
    "failedCount": 5,
    "sentAt": "2025-10-23T14:00:00Z"
  }
}
```

---

### POST /api/push-notifications/{id}/schedule

**Plane Benachrichtigung für später**

```bash
curl -X POST https://api.club.local/api/push-notifications/1/schedule \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123" \
  -H "Content-Type: application/json" \
  -d '{
    "scheduledAt": "2025-10-25T15:00:00Z"
  }'
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "status": "scheduled",
    "scheduledAt": "2025-10-25T15:00:00Z"
  }
}
```

---

### POST /api/push-notifications/{id}/cancel

**Breche geplante Benachrichtigung ab**

```bash
curl -X POST https://api.club.local/api/push-notifications/1/cancel \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123"
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "status": "cancelled",
    "cancelledAt": "2025-10-23T14:30:00Z"
  }
}
```

---

### GET /api/push-notifications/{id}/stats

**Hole Statistiken einer Benachrichtigung**

```bash
curl -X GET https://api.club.local/api/push-notifications/1/stats \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123"
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "title": "Spielplan Update",
    "totalRecipients": 150,
    "sentCount": 150,
    "deliveredCount": 145,
    "failedCount": 5,
    "clickedCount": 42,
    "deliveryRate": "96.67%",
    "clickRate": "28.97%",
    "failureRate": "3.33%"
  }
}
```

---

### POST /api/webhooks/push/delivery

**Webhook: Benachrichtigung zugestellt (von Push-Provider)**

```bash
curl -X POST https://api.club.local/api/webhooks/push/delivery \
  -H "Content-Type: application/json" \
  -d '{
    "notificationId": 1,
    "subscriptionId": 5,
    "status": "delivered",
    "timestamp": "2025-10-23T14:15:00Z"
  }'
```

**Response (200 OK):**
```json
{
  "message": "Delivery receipt recorded"
}
```

---

### POST /api/webhooks/push/click

**Webhook: Benachrichtigung geklickt**

```bash
curl -X POST https://api.club.local/api/webhooks/push/click \
  -H "Content-Type: application/json" \
  -d '{
    "notificationId": 1,
    "subscriptionId": 5,
    "action": "view",
    "timestamp": "2025-10-23T14:15:30Z"
  }'
```

**Response (200 OK):**
```json
{
  "message": "Click recorded"
}
```

---

## 💬 Messages

### GET /api/messages

**Liste Nachrichten des Users**

```bash
curl -X GET https://api.club.local/api/messages \
  -H "Authorization: Bearer {TOKEN}"
```

**Query Parameters:**
- `page` (int): Seite (default: 1)
- `per_page` (int): Pro Seite (default: 20)
- `type` (string): direct | group | broadcast | system
- `status` (string): pending | delivered | read

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "senderId": 5,
      "senderName": "Max Müller",
      "subject": "Training morgen",
      "body": "Können wir morgen trainieren?",
      "type": "direct",
      "isRead": false,
      "createdAt": "2025-10-23T14:00:00Z"
    }
  ],
  "pagination": {
    "currentPage": 1,
    "total": 125,
    "perPage": 20
  }
}
```

---

### POST /api/messages/direct

**Sende Direktnachricht**

```bash
curl -X POST https://api.club.local/api/messages/direct \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "recipientId": 5,
    "subject": "Training morgen?",
    "body": "Können wir trainieren?",
    "priority": "normal"
  }'
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "senderId": 3,
    "recipientId": 5,
    "subject": "Training morgen?",
    "body": "Können wir trainieren?",
    "type": "direct",
    "createdAt": "2025-10-23T14:00:00Z"
  }
}
```

---

### GET /api/messages/unread-count

**Hole Anzahl ungelesener Nachrichten**

```bash
curl -X GET https://api.club.local/api/messages/unread-count \
  -H "Authorization: Bearer {TOKEN}"
```

**Response (200 OK):**
```json
{
  "data": {
    "unreadCount": 5,
    "unreadMessages": [
      {
        "id": 1,
        "senderId": 5,
        "senderName": "Max Müller",
        "subject": "Training",
        "createdAt": "2025-10-23T14:00:00Z"
      }
    ]
  }
}
```

---

### POST /api/messages/{id}/reply

**Antworte auf Nachricht**

```bash
curl -X POST https://api.club.local/api/messages/1/reply \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "body": "Ja, ich bin dabei!"
  }'
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 2,
    "senderId": 3,
    "replyToId": 1,
    "body": "Ja, ich bin dabei!",
    "type": "direct",
    "createdAt": "2025-10-23T14:05:00Z"
  }
}
```

---

### POST /api/messages/{id}/read

**Markiere Nachricht als gelesen**

```bash
curl -X POST https://api.club.local/api/messages/1/read \
  -H "Authorization: Bearer {TOKEN}"
```

**Response (200 OK):**
```json
{
  "message": "Message marked as read"
}
```

---

### POST /api/messages/{id}/archive

**Archiviere Nachricht**

```bash
curl -X POST https://api.club.local/api/messages/1/archive \
  -H "Authorization: Bearer {TOKEN}"
```

**Response (200 OK):**
```json
{
  "message": "Message archived"
}
```

---

### DELETE /api/messages/{id}

**Lösche Nachricht**

```bash
curl -X DELETE https://api.club.local/api/messages/1 \
  -H "Authorization: Bearer {TOKEN}"
```

**Response (204 No Content):**
```
(leerer Body)
```

---

### POST /api/conversations

**Erstelle Gruppenchat**

```bash
curl -X POST https://api.club.local/api/conversations \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "U21-Team",
    "participantIds": [5, 6, 7, 8],
    "type": "group",
    "description": "Kommunikation für U21"
  }'
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "title": "U21-Team",
    "type": "group",
    "participantCount": 4,
    "createdAt": "2025-10-23T10:00:00Z"
  }
}
```

---

### GET /api/conversations

**Liste Conversations des Users**

```bash
curl -X GET https://api.club.local/api/conversations \
  -H "Authorization: Bearer {TOKEN}"
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "U21-Team",
      "type": "group",
      "participantCount": 4,
      "lastMessage": "Nächstes Training Dienstag",
      "lastMessageAt": "2025-10-23T14:00:00Z",
      "unreadCount": 2
    }
  ]
}
```

---

### GET /api/conversations/{id}

**Hole Conversation mit Nachrichten**

```bash
curl -X GET https://api.club.local/api/conversations/1?page=1 \
  -H "Authorization: Bearer {TOKEN}"
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "title": "U21-Team",
    "type": "group",
    "messages": [
      {
        "id": 10,
        "senderId": 5,
        "senderName": "Max",
        "body": "Hallo zusammen",
        "createdAt": "2025-10-23T10:00:00Z"
      }
    ]
  },
  "pagination": {
    "currentPage": 1,
    "total": 50,
    "perPage": 20
  }
}
```

---

## 📧 Email Templates

### GET /api/email-templates

**Liste Email-Templates**

```bash
curl -X GET https://api.club.local/api/email-templates \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123"
```

**Query Parameters:**
- `page` (int): default 1
- `per_page` (int): default 20
- `group` (string): parents | players | coaches

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Match Report",
      "slug": "match-report",
      "targetGroups": ["parents", "players"],
      "usageCount": 5,
      "createdAt": "2025-10-20T10:00:00Z"
    }
  ],
  "pagination": {
    "currentPage": 1,
    "total": 12,
    "perPage": 20
  }
}
```

---

### POST /api/email-templates

**Erstelle Email-Template**

```bash
curl -X POST https://api.club.local/api/email-templates \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Training Update",
    "slug": "training-update",
    "subject": "Training: {day} um {time}",
    "body": "<h2>Liebes Team</h2><p>Training: {location}</p>",
    "targetGroups": ["players", "coaches"],
    "variables": {
      "day": "Wochentag",
      "time": "Uhrzeit",
      "location": "Ort"
    }
  }'
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 2,
    "name": "Training Update",
    "slug": "training-update",
    "createdAt": "2025-10-23T10:00:00Z"
  }
}
```

---

### GET /api/email-templates/{id}

**Hole Template-Details**

```bash
curl -X GET https://api.club.local/api/email-templates/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123"
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "name": "Match Report",
    "slug": "match-report",
    "subject": "Spielbericht: {date}",
    "body": "<h2>Liebe {group}</h2>...",
    "targetGroups": ["parents"],
    "variables": {
      "date": "Spieldatum",
      "result": "Endergebnis",
      "group": "Empfängergruppe"
    }
  }
}
```

---

### PUT /api/email-templates/{id}

**Update Template**

```bash
curl -X PUT https://api.club.local/api/email-templates/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123" \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Neuer Betreff",
    "body": "<h2>Neuer Inhalt</h2>"
  }'
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "name": "Match Report",
    "updatedAt": "2025-10-23T15:00:00Z"
  }
}
```

---

### DELETE /api/email-templates/{id}

**Lösche Template**

```bash
curl -X DELETE https://api.club.local/api/email-templates/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123"
```

**Response (204 No Content):**
```
(leerer Body)
```

---

### POST /api/email-templates/{id}/preview

**Vorschau gerendertes Template**

```bash
curl -X POST https://api.club.local/api/email-templates/1/preview \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123" \
  -H "Content-Type: application/json" \
  -d '{
    "variables": {
      "date": "2025-10-25",
      "result": "3:2",
      "group": "Eltern"
    },
    "locale": "de"
  }'
```

**Response (200 OK):**
```json
{
  "data": {
    "subject": "Spielbericht: 2025-10-25",
    "body": "<h2>Liebe Eltern</h2><p>Endstand: 3:2</p>",
    "plainText": "SPIELBERICHT: 2025-10-25..."
  }
}
```

---

### POST /api/email-templates/{id}/test

**Sende Test-E-Mail an dich selbst**

```bash
curl -X POST https://api.club.local/api/email-templates/1/test \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123" \
  -H "Content-Type: application/json" \
  -d '{
    "variables": {
      "date": "2025-10-25"
    }
  }'
```

**Response (200 OK):**
```json
{
  "message": "Test email sent to your@email.com"
}
```

---

### GET /api/email-templates/{id}/stats

**Hole Template-Statistiken**

```bash
curl -X GET https://api.club.local/api/email-templates/1/stats \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123"
```

**Response (200 OK):**
```json
{
  "data": {
    "templateId": 1,
    "name": "Match Report",
    "totalSent": 250,
    "delivered": 248,
    "failed": 2,
    "openRate": "45.67%",
    "clickRate": "12.34%",
    "lastUsed": "2025-10-23T14:30:00Z",
    "usageCount": 5
  }
}
```

---

### POST /api/email-templates/{id}/clone

**Duplikate Template**

```bash
curl -X POST https://api.club.local/api/email-templates/1/clone \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Tenant: club-123"
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 5,
    "name": "Match Report - Copy",
    "slug": "match-report-copy",
    "createdAt": "2025-10-23T15:00:00Z"
  }
}
```

---

## 🔐 Allgemeine Error Responses

Alle Endpoints können diese Fehler zurückgeben:

### 400 Bad Request
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["This email is already taken."]
  }
}
```

### 429 Too Many Requests
```json
{
  "message": "Too many requests. Please try again later."
}
```

### 500 Internal Server Error
```json
{
  "message": "Server error",
  "error_id": "uuid-error-tracking-id"
}
```

---

## 🔑 Authentication

Alle Endpoints erfordern:

```bash
Authorization: Bearer {ACCESS_TOKEN}
```

Token erhalten über:
```bash
POST /api/login
{
  "email": "user@club.de",
  "password": "password"
}
```

---

## 📝 Pagination

Pagination bei List-Endpoints:

```json
{
  "data": [...],
  "pagination": {
    "currentPage": 1,
    "from": 1,
    "to": 20,
    "total": 250,
    "perPage": 20,
    "lastPage": 13
  },
  "links": {
    "first": "/api/push-notifications?page=1",
    "last": "/api/push-notifications?page=13",
    "next": "/api/push-notifications?page=2"
  }
}
```

---

## 🧪 Beispiele für Programmiersprachen

### JavaScript/Node.js
```javascript
const token = 'your-token';

// Register subscription
const response = await fetch(
  'https://api.club.local/api/push-subscriptions/register',
  {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      endpoint: subscription.endpoint,
      keys: subscription.getKey ? {
        p256dh: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))),
        auth: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth')))),
      } : subscription.keys,
    }),
  }
);

const data = await response.json();
console.log(data);
```

### Python
```python
import requests

headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json',
}

# Get messages
response = requests.get(
    'https://api.club.local/api/messages',
    headers=headers,
    params={'page': 1, 'per_page': 20}
)

messages = response.json()
print(messages)
```

### PHP
```php
<?php
$headers = [
    'Authorization' => "Bearer {$token}",
    'Content-Type' => 'application/json',
];

$client = new \GuzzleHttp\Client();

// Send direct message
$response = $client->post(
    'https://api.club.local/api/messages/direct',
    [
        'headers' => $headers,
        'json' => [
            'recipientId' => 5,
            'subject' => 'Training',
            'body' => 'Trainierst du mit?',
        ]
    ]
);

$message = json_decode($response->getBody(), true);
```

---

**Letzte Aktualisierung:** 2025-10-23
**Version:** 1.0
**Status:** ✅ Production Ready

Alle Endpoints sind deployed und ready to use! 🚀
