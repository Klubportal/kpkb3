# SMS Gateway Integration Guide

**Complete SMS messaging system with multi-provider support (Twilio, MessageBird, Nexmo)**

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Installation & Setup](#installation--setup)
3. [Database Schema](#database-schema)
4. [Configuration](#configuration)
5. [API Endpoints](#api-endpoints)
6. [Usage Examples](#usage-examples)
7. [Provider Integration](#provider-integration)
8. [Webhooks & Delivery](#webhooks--delivery)
9. [OTP & 2FA](#otp--2fa)
10. [Best Practices](#best-practices)
11. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

### System Components

```
┌─────────────────────────────────────────────────────────────────┐
│                     Frontend / API Clients                       │
└────────────────┬──────────────────────────────────────────────────┘
                 │ REST API Requests
                 ▼
        ┌────────────────────────┐
        │  SmsController         │
        │  - send()              │
        │  - sendFromTemplate()  │
        │  - sendCampaign()      │
        │  - verifyOtp()         │
        └────────────┬───────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │  SmsService            │
        │  - sendMessage()       │
        │  - sendCampaign()      │
        │  - sendOtp()           │
        │  - getStats()          │
        └────────────┬───────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
        ▼                         ▼
   ┌─────────────┐          ┌─────────────────┐
   │  Database   │          │  Job Queue      │
   │             │          │                 │
   │ - Templates │          │ - SendSmsJob    │
   │ - Messages  │          │ - CheckDeliv...│
   │ - Logs      │          │ - SendCampaign  │
   │ - Contacts  │          └────────┬────────┘
   │ - Configs   │                   │
   └─────────────┘                   ▼
                            ┌─────────────────────────┐
                            │  Multi-Provider Layer   │
                            │                         │
                            │ ┌─────────────────────┐ │
                            │ │ Twilio              │ │
                            │ │ - Account SID       │ │
                            │ │ - Auth Token        │ │
                            │ │ - From Number       │ │
                            │ └─────────────────────┘ │
                            │                         │
                            │ ┌─────────────────────┐ │
                            │ │ MessageBird         │ │
                            │ │ - API Key           │ │
                            │ │ - From ID           │ │
                            │ └─────────────────────┘ │
                            │                         │
                            │ ┌─────────────────────┐ │
                            │ │ Nexmo/Vonage        │ │
                            │ │ - API Key           │ │
                            │ │ - API Secret        │ │
                            │ │ - From Number       │ │
                            │ └─────────────────────┘ │
                            └─────────────┬───────────┘
                                          │
                     ┌────────────────────┴────────────────────┐
                     │                                         │
                     ▼                                         ▼
            ┌─────────────────────┐              ┌──────────────────────┐
            │  SMS Delivery       │              │  Webhook Receiver    │
            │  (Messages sent)    │              │  (Delivery receipts) │
            └─────────────────────┘              │                      │
                                                 │  /api/sms-webhook... │
                                                 └──────────────────────┘
                                                          ▲
                                                          │
                              ┌───────────────────────────┼───────────────────────┐
                              │                           │                       │
                    ┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
                    │  Twilio         │    │  MessageBird    │    │  Nexmo          │
                    │  (External)     │    │  (External)     │    │  (External)     │
                    └──────────────────┘    └──────────────────┘    └──────────────────┘
```

### Message Lifecycle

1. **Message Creation**: Via REST API or programmatically
2. **Queue**: Dispatched to job queue (Redis)
3. **Send**: Job processes and sends via active provider
4. **Delivery Check**: Periodic polling/webhook updates status
5. **Log**: All events recorded in audit trail

---

## Installation & Setup

### Step 1: Install PHP Dependencies

```bash
composer require twilio/sdk
composer require messagebird/php-rest-api
composer require vonage/client
```

### Step 2: Run Migration

```bash
php artisan migrate
```

Creates tables:
- `sms_templates` - Message templates with variables
- `sms_messages` - All sent/received messages
- `sms_campaigns` - Bulk SMS campaigns
- `sms_provider_configs` - Provider credentials
- `sms_logs` - Event audit trail
- `sms_conversations` - Two-way SMS conversations
- `sms_otp_codes` - One-time passwords for 2FA
- `sms_contacts` - Contact list management
- `sms_statistics` - Daily aggregated stats

### Step 3: Configure Environment Variables

Add to `.env`:

```env
# SMS Provider - Twilio
SMS_PROVIDER_TWILIO=true
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890

# SMS Provider - MessageBird
SMS_PROVIDER_MESSAGEBIRD=true
MESSAGEBIRD_API_KEY=your_api_key
MESSAGEBIRD_FROM_NUMBER=YourSenderId

# SMS Provider - Nexmo/Vonage
SMS_PROVIDER_NEXMO=true
NEXMO_API_KEY=your_api_key
NEXMO_API_SECRET=your_secret
NEXMO_FROM_NUMBER=YourBrand

# Queue
QUEUE_CONNECTION=redis
```

### Step 4: Register Providers in Database

```php
// In a seeder or command
SmsProviderConfig::create([
    'tenant_id' => $tenantId,
    'provider' => 'twilio',
    'is_active' => true,
    'priority' => 10,
    'credentials' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
    ],
    'from_number' => env('TWILIO_FROM_NUMBER'),
    'cost_per_sms' => 0.0075,
]);

SmsProviderConfig::create([
    'tenant_id' => $tenantId,
    'provider' => 'messagebird',
    'is_active' => true,
    'priority' => 5,
    'credentials' => [
        'api_key' => env('MESSAGEBIRD_API_KEY'),
    ],
    'from_number' => env('MESSAGEBIRD_FROM_NUMBER'),
    'cost_per_sms' => 0.012,
]);
```

---

## Database Schema

### `sms_templates`
```sql
CREATE TABLE sms_templates (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  name varchar(255),
  slug varchar(255) UNIQUE,
  content text,
  variables json,                    -- {"name": "User's name", "code": "Verification code"}
  max_length int DEFAULT 160,
  category varchar(50),              -- notification, reminder, alert, broadcast, general
  is_active boolean DEFAULT true,
  usage_count int DEFAULT 0,
  timestamps
);
```

### `sms_messages`
```sql
CREATE TABLE sms_messages (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  user_id bigint,
  recipient_phone varchar(20),       -- E.164 format: +43123456789
  content text,
  status varchar(20),                -- pending, queued, sent, delivered, failed, bounced
  provider varchar(50),
  provider_message_id varchar(255),  -- Reference from SMS provider
  error_code varchar(50),
  error_message text,
  retry_count int DEFAULT 0,
  max_retries int DEFAULT 3,
  message_type varchar(50),          -- transactional, marketing, otp, reminder
  metadata json,
  sent_at timestamp,
  delivered_at timestamp,
  failed_at timestamp,
  timestamps
);
```

### `sms_campaigns`
```sql
CREATE TABLE sms_campaigns (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  created_by bigint,
  name varchar(255),
  description text,
  content text,
  status varchar(50),                -- draft, scheduled, running, paused, completed, failed
  recipient_list json,               -- ["+43123456789", "+43987654321"]
  recipient_count int,
  targeting_criteria varchar(255),   -- segment_id, role, etc.
  scheduled_for timestamp,
  started_at timestamp,
  completed_at timestamp,
  sent_count int DEFAULT 0,
  delivered_count int DEFAULT 0,
  failed_count int DEFAULT 0,
  cost decimal(12,4),
  cost_currency varchar(3),
  timestamps
);
```

### `sms_provider_configs`
```sql
CREATE TABLE sms_provider_configs (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  provider varchar(50),              -- twilio, messagebird, nexmo
  is_active boolean DEFAULT false,
  priority int DEFAULT 0,            -- Higher = try first (failover)
  credentials json,                  -- Encrypted
  from_number varchar(50),
  from_name varchar(50),
  cost_per_sms decimal(10,6),
  daily_limit int,
  daily_sent int DEFAULT 0,
  daily_limit_reset_at timestamp,
  webhook_url text,
  webhook_secret varchar(255),
  timestamps
);
```

---

## Configuration

### Multi-Provider Strategy

Create command to configure providers:

```php
// php artisan sms:configure-provider

php artisan sms:configure-provider --provider=twilio --active
php artisan sms:configure-provider --provider=messagebird --active
php artisan sms:configure-provider --provider=nexmo --active
```

### Provider Priority

Providers are tried in order of priority. If one fails, system automatically tries the next:

```
Priority 10: Twilio (primary)
Priority 5:  MessageBird (fallback)
Priority 0:  Nexmo (last resort)
```

### Rate Limiting

Each provider can have daily limits:

```php
// Configure limits
SmsProviderConfig::where('provider', 'twilio')
    ->update(['daily_limit' => 1000]); // 1000 SMS per day
```

---

## API Endpoints

### Send Single SMS

```
POST /api/sms/send
Authorization: Bearer {token}
Content-Type: application/json

{
  "phone": "+43123456789",
  "content": "Hello John! Your verification code is 123456",
  "type": "transactional",
  "metadata": {
    "user_id": 123,
    "verification_id": "abc123"
  }
}

Response 201:
{
  "data": {
    "message_id": 456,
    "status": "pending",
    "recipient": "+43123456789",
    "content": "Hello John! Your verification code is 123456"
  }
}
```

### Send from Template

```
POST /api/sms/send-template
Authorization: Bearer {token}
Content-Type: application/json

{
  "template_slug": "event-reminder",
  "phone": "+43123456789",
  "variables": {
    "name": "John",
    "event": "Team Meeting",
    "time": "3:00 PM"
  }
}

Response 201:
{
  "data": {
    "message_id": 457,
    "status": "pending"
  }
}
```

### Get Message Status

```
GET /api/sms/{messageId}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "id": 456,
    "recipient": "+43123456789",
    "status": "delivered",
    "content": "Hello John! Your verification code is 123456",
    "sent_at": "2025-10-24T10:00:00Z",
    "delivered_at": "2025-10-24T10:00:05Z",
    "error_message": null
  }
}
```

### List SMS Messages

```
GET /api/sms?status=delivered&type=transactional&from=2025-10-01&to=2025-10-31
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "id": 456,
      "recipient": "+43123456789",
      "status": "delivered",
      "created_at": "2025-10-24T10:00:00Z"
    },
    ...
  ],
  "pagination": {
    "total": 1500,
    "per_page": 50,
    "current_page": 1,
    "last_page": 30
  }
}
```

### Create SMS Campaign

```
POST /api/sms/campaigns
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "October Marketing Campaign",
  "description": "Promotional SMS for October",
  "content": "Special offer! Get 20% off. Code: OCT20",
  "recipient_list": ["+43123456789", "+43987654321", "+43555666777"],
  "scheduled_for": "2025-10-25T10:00:00Z",
  "targeting_criteria": "members"
}

Response 201:
{
  "data": {
    "campaign_id": 1,
    "status": "scheduled",
    "recipient_count": 3
  }
}
```

### Send Campaign

```
POST /api/sms/campaigns/{campaignId}/send
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "campaign_id": 1,
    "status": "started"
  }
}
```

### Get Campaign Stats

```
GET /api/sms/campaigns/{campaignId}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "id": 1,
    "name": "October Marketing Campaign",
    "status": "running",
    "recipient_count": 3,
    "sent_count": 3,
    "delivered_count": 2,
    "failed_count": 1,
    "delivery_rate": 66.67,
    "failure_rate": 33.33,
    "cost": 0.0285
  }
}
```

### Get SMS Statistics

```
GET /api/sms/stats?from=2025-10-01&to=2025-10-31
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "total_sent": 5420,
    "total_delivered": 5380,
    "total_failed": 40,
    "delivery_rate": 99.26,
    "total_cost": 40.65,
    "by_type": {
      "transactional": {"sent": 3000, "delivered": 2990, "failed": 10},
      "marketing": {"sent": 2000, "delivered": 1980, "failed": 20},
      "otp": {"sent": 400, "delivered": 400, "failed": 0},
      "reminder": {"sent": 20, "delivered": 10, "failed": 10}
    },
    "by_provider": {
      "twilio": 3000,
      "messagebird": 2000,
      "nexmo": 420
    }
  }
}
```

### Create SMS Template

```
POST /api/sms-templates
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Event Reminder",
  "content": "Hi {name}, reminder: {event} at {time}. See you there!",
  "category": "reminder",
  "variables": ["name", "event", "time"]
}

Response 201:
{
  "data": {
    "template_id": 1,
    "slug": "event-reminder",
    "name": "Event Reminder"
  }
}
```

### Send OTP

```
POST /api/sms/send-otp
Authorization: Bearer {token}
Content-Type: application/json

{
  "phone": "+43123456789",
  "type": "2fa"
}

Response 201:
{
  "data": {
    "otp_id": 1,
    "expires_at": "2025-10-24T10:10:00Z"
  }
}
```

### Verify OTP

```
POST /api/sms/verify-otp
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "123456",
  "type": "2fa"
}

Response 200:
{
  "data": {
    "verified": true
  }
}
```

---

## Usage Examples

### Send Transactional SMS

```php
use App\Services\SmsService;

$smsService = new SmsService($tenantId = 1);

$message = $smsService->sendMessage(
    recipientPhone: '+43123456789',
    content: 'Your verification code is: 123456',
    type: 'transactional',
    userId: auth()->id(),
    metadata: ['verification_id' => 'v123']
);

echo "Message ID: " . $message->id;
echo "Status: " . $message->status; // 'pending'
```

### Send from Template

```php
$smsService->sendFromTemplate(
    templateSlug: 'welcome-message',
    recipientPhone: '+43123456789',
    variables: [
        'name' => 'John',
        'club_name' => 'FC Austria',
    ],
    userId: auth()->id()
);
```

### Send Bulk Campaign

```php
$campaign = SmsCampaign::create([
    'tenant_id' => $tenantId,
    'created_by' => auth()->id(),
    'name' => 'Season Announcement',
    'content' => 'New season starts! Get your tickets now.',
    'recipient_list' => ['+43123456789', '+43987654321'],
    'recipient_count' => 2,
    'status' => 'draft',
]);

$smsService->sendCampaign($campaign);
```

### Schedule Campaign

```php
$campaign = SmsCampaign::create([
    'tenant_id' => $tenantId,
    'created_by' => auth()->id(),
    'name' => 'Reminder',
    'content' => 'Game tomorrow at 3 PM!',
    'recipient_list' => $phoneNumbers,
    'recipient_count' => count($phoneNumbers),
    'status' => 'scheduled',
    'scheduled_for' => now()->addHours(24),
]);

$smsService->scheduleCampaign($campaign);
```

### Send OTP for 2FA

```php
// Generate and send OTP
$otp = $smsService->sendOtp(
    userId: auth()->id(),
    phone: '+43123456789',
    type: '2fa',
    expiresIn: 600 // 10 minutes
);

// Later, verify the code
$verified = $smsService->verifyOtp(
    userId: auth()->id(),
    code: '123456',
    type: '2fa'
);

if ($verified) {
    // 2FA successful, enable 2FA on account
    auth()->user()->enable2fa();
}
```

### Get Statistics

```php
$stats = $smsService->getStats(
    startDate: now()->subMonth(),
    endDate: now()
);

echo "Delivery Rate: " . $stats['delivery_rate'] . "%";
echo "Total Cost: €" . $stats['total_cost'];
echo "By Provider: " . json_encode($stats['by_provider']);
```

### Retry Failed Messages

```php
// Manually retry failed SMS
$retryCount = $smsService->retryFailedMessages();
echo "Retried $retryCount messages";

// Or use as scheduled job
\Schedule::call(function () {
    (new SmsService(tenancy()->tenant()->id))->retryFailedMessages();
})->hourly();
```

---

## Provider Integration

### Twilio Setup

1. Create Twilio account at https://www.twilio.com/console
2. Get Account SID and Auth Token
3. Purchase phone number
4. Set webhook URL in Twilio console: `https://yourapp.com/api/sms-webhook/twilio`

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

### MessageBird Setup

1. Create MessageBird account at https://dashboard.messagebird.com
2. Get API Key
3. Set From ID (Sender ID)
4. Configure webhook: `https://yourapp.com/api/sms-webhook/messagebird`

```env
MESSAGEBIRD_API_KEY=your_api_key
MESSAGEBIRD_FROM_NUMBER=YourSenderId
```

### Nexmo/Vonage Setup

1. Create account at https://dashboard.nexmo.com
2. Get API Key and API Secret
3. Configure webhook: `https://yourapp.com/api/sms-webhook/nexmo`

```env
NEXMO_API_KEY=your_api_key
NEXMO_API_SECRET=your_secret
NEXMO_FROM_NUMBER=YourBrand
```

---

## Webhooks & Delivery

### Webhook Validation

```php
// In SmsController@webhook

protected function validateWebhookSignature(string $provider, Request $request): void
{
    match ($provider) {
        'twilio' => $this->validateTwilioSignature($request),
        'messagebird' => $this->validateMessageBirdSignature($request),
        'nexmo' => $this->validateNexmoSignature($request),
    };
}
```

### Webhook Events

**Twilio Delivery Status**:
```json
{
  "MessageSid": "SMxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "MessageStatus": "delivered",
  "Timestamp": "2025-10-24T10:00:05Z"
}
```

**MessageBird Delivery Status**:
```json
{
  "id": "1234567890123456789",
  "status": 20,
  "statusDatetime": "2025-10-24 10:00:05",
  "type": "mt"
}
```

**Nexmo Delivery Status**:
```json
{
  "messageId": "02000000971A4F1E",
  "to": "43123456789",
  "status": "0",
  "timestamp": "2025-10-24T10:00:05Z"
}
```

---

## OTP & 2FA

### Generate OTP

```php
// 6-digit code, auto-expires in 10 minutes
$otp = SmsService::sendOtp($userId, $phone, '2fa');

// OTP record
$otp->code;        // '123456'
$otp->expires_at;  // Carbon timestamp
$otp->attempts;    // 0
$otp->verified;    // false
```

### Verify OTP

```php
// Verify within timeout and max attempts
$verified = $otp->verify('123456');

if ($verified) {
    // OTP verified, proceed
    $otp->verified_at; // Current timestamp
} else {
    // Invalid code
    $otp->attempts;    // Incremented
}
```

### OTP Types

- `2fa` - Two-factor authentication
- `password_reset` - Password reset verification
- `email_verification` - Email verification

---

## Best Practices

### 1. Phone Number Normalization

Always normalize to E.164 format:

```php
$phone = '+43123456789'; // ✅ Correct
$phone = '0123456789';   // ❌ Will be normalized to +430123456789

$normalized = $smsService->normalizePhoneNumber($phone);
```

### 2. Template Variables

Use clear placeholder syntax:

```
// ❌ Bad
Hello {user_name}, your code is {code}

// ✅ Good
Hello {name}, your code is {code}
```

### 3. Message Length

SMS has 160-character limit per message (multi-part SMS costs more):

```php
// Check before sending
if (!$template->validateLength($renderedContent)) {
    throw new Exception("Message too long");
}
```

### 4. Retry Logic

Failed messages automatically retry up to 3 times:

```php
// Manual retry
if ($message->canRetry()) {
    dispatch(new SendSmsJob($message->id));
}
```

### 5. Cost Tracking

Track SMS costs per provider:

```php
$stats = $smsService->getStats();
echo "Total cost: €" . $stats['total_cost'];
echo "Provider costs: " . json_encode($stats['by_provider']);
```

### 6. Rate Limiting

Configure daily limits:

```php
SmsProviderConfig::where('provider', 'twilio')
    ->update([
        'daily_limit' => 1000,
        'daily_limit_reset_at' => now()->addDay()
    ]);
```

---

## Troubleshooting

### SMS Not Sending

**Check**:
1. Provider credentials configured: `SmsProviderConfig::where('is_active', true)->first()`
2. Queue worker running: `php artisan queue:work`
3. Daily limit not exceeded: `provider->getDailyRemaining()`
4. Phone number valid: `smsService->validatePhoneNumber($phone)`

### High Failure Rate

**Causes**:
- Invalid phone numbers
- Provider account suspended
- Daily limit reached
- Network timeout

**Solutions**:
- Validate phone numbers before sending
- Check provider account status
- Increase daily limit or add backup provider
- Increase retry timeout

### Webhook Not Receiving Delivery Status

**Check**:
1. Webhook URL public and accessible
2. Webhook secret configured
3. Firewall allows provider IP
4. Signature validation passing

**Test**:
```bash
# Test webhook manually
curl -X POST https://yourapp.com/api/sms-webhook/twilio \
  -d '{"MessageSid":"test","MessageStatus":"delivered"}'
```

### OTP Code Not Received

**Debug**:
```php
// Check OTP was created
$otp = SmsOtpCode::where('user_id', $userId)->latest()->first();

// Check SMS message was sent
$message = SmsMessage::where('id', $otp->metadata['sms_message_id'])->first();

// Check message status
echo $message->status;  // Should be 'delivered'
```

---

## Monitoring

### Daily Statistics

Automatically calculated and stored in `sms_statistics` table:

```php
// Query stats
$stats = SmsStatistic::where('date', today())->first();

echo "Sent: " . $stats->total_sent;
echo "Delivered: " . $stats->total_delivered;
echo "Cost: €" . $stats->total_cost;
```

### Log Monitoring

All SMS events logged in `sms_logs`:

```php
// Query logs
$logs = SmsLog::where('sms_message_id', $messageId)
    ->orderBy('created_at', 'desc')
    ->get();

foreach ($logs as $log) {
    echo $log->event_type;  // 'sent', 'delivered', 'failed'
    echo $log->created_at;
}
```

---

**Last Updated**: 2025-10-24  
**Version**: 1.0.0  
**Status**: Production Ready ✅
