# Analytics Dashboard Guide

**Comprehensive analytics, tracking, and conversion attribution system**

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Installation & Setup](#installation--setup)
3. [Database Schema](#database-schema)
4. [Core Concepts](#core-concepts)
5. [Event Tracking](#event-tracking)
6. [User Journeys](#user-journeys)
7. [Conversion Attribution](#conversion-attribution)
8. [Funnel Analytics](#funnel-analytics)
9. [API Endpoints](#api-endpoints)
10. [Usage Examples](#usage-examples)
11. [Dashboard Metrics](#dashboard-metrics)
12. [Best Practices](#best-practices)

---

## Architecture Overview

### System Flow

```
┌──────────────────────────────────┐
│   User Interaction Events        │
│ - View page                      │
│ - Click button                   │
│ - Open email                     │
│ - Read SMS                       │
│ - Make purchase                  │
└──────────────┬───────────────────┘
               │
               ▼
    ┌──────────────────────┐
    │  trackEvent()        │
    │  AnalyticsService    │
    │                      │
    │ - Store event        │
    │ - Update journey     │
    │ - Categorize event   │
    └──────────┬───────────┘
               │
        ┌──────┴──────┐
        │             │
        ▼             ▼
┌──────────────┐  ┌──────────────────┐
│ analytics_   │  │ user_journeys    │
│ events       │  │ (session state)  │
│ (raw data)   │  │                  │
└──────────────┘  └──────────┬───────┘
        │                    │
        └──────────┬─────────┘
                   │
                   ▼
        ┌─────────────────────┐
        │ Conversion Events   │
        │ (purchase, signup)  │
        └──────────┬──────────┘
                   │
                   ▼
        ┌──────────────────────────┐
        │ recordConversion()        │
        │                          │
        │ - Track touch points     │
        │ - Calculate attribution  │
        │ - Record in conversion   │
        │   _tracking table        │
        └──────────┬───────────────┘
                   │
                   ▼
        ┌──────────────────────────┐
        │ conversion_tracking      │
        │ (with attribution data)  │
        └──────────────────────────┘
```

### Data Model

```
Multiple event sources converge:
├── Web events (view, click, scroll)
├── Email events (open, click, unsubscribe)
├── SMS events (read, click, reply)
├── Push events (open, click)
└── API events (custom business events)

All tracked in:
└─ AnalyticsEvent (granular, real-time)
   └─ AnalyticsAggregation (hourly/daily summaries)
      └─ AnalyticsCampaign (campaign-level metrics)

User journeys link events:
└─ UserJourney (session flow)
   └─ Touch points track conversion path
      └─ ConversionTracking (attribution model)

Funnels track multi-step conversions:
└─ FunnelAnalytics (signup flow, purchase flow)
   └─ Dropoff analysis (where users abandon)
```

---

## Installation & Setup

### Step 1: Run Migration

```bash
php artisan migrate
```

Creates tables:
- `analytics_events` - Raw event data
- `analytics_aggregations` - Hourly/daily rollups
- `analytics_campaigns` - Campaign performance
- `user_journeys` - Session tracking
- `funnel_analytics` - Multi-step funnels
- `conversion_tracking` - Attribution models

### Step 2: Initialize Analytics Service

```php
// In a controller or listener
$analyticsService = new AnalyticsService($tenantId);

// Start tracking user
$journey = $analyticsService->startJourney(
    userId: auth()->id(),
    firstSource: 'email',
    deviceType: 'mobile',
    country: 'AT'
);

// Store session ID in cookie/session for tracking
session(['analytics_session' => $journey->session_id]);
```

### Step 3: Track Events

```php
$analyticsService->trackEvent(
    eventType: 'view',
    eventSource: 'web',
    userId: auth()->id(),
    sessionId: session('analytics_session'),
    deviceType: 'mobile'
);
```

---

## Database Schema

### `analytics_events`

Raw granular event data:

```sql
CREATE TABLE analytics_events (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  user_id bigint,
  session_id varchar(255),           -- Links to user_journeys
  event_type varchar(50),             -- view, click, purchase, email_open, etc.
  event_source varchar(50),           -- web, email, sms, push, api
  category varchar(100),              -- conversion, engagement, acquisition
  action varchar(255),                -- Button clicked, form submitted
  label varchar(255),                 -- Product name, campaign name
  value int,                          -- Revenue cents, quantity
  currency varchar(3),                -- EUR, USD
  campaign_id varchar(255),           -- Email campaign 123
  variant_id varchar(255),            -- A/B test variant
  device_type varchar(50),            -- mobile, tablet, desktop
  browser varchar(100),               -- Chrome, Firefox
  country varchar(2),                 -- AT, DE, US
  city varchar(100),                  -- Vienna, Graz
  ip_address varchar(45),
  metadata json,
  created_at timestamp INDEX          -- Critical for queries
);

Indexes:
- event_type, created_at
- user_id, created_at
- session_id
- campaign_id
```

### `analytics_aggregations`

Time-bucketed rollups:

```sql
CREATE TABLE analytics_aggregations (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  period_type varchar(50),            -- hourly, daily, weekly, monthly
  period_start timestamp INDEX,
  period_end timestamp,
  dimension varchar(100),             -- event_type, source, device_type, campaign_id
  dimension_value varchar(255),       -- view, email, mobile, campaign_123
  
  -- Counts
  total_events int,
  unique_users int,
  unique_sessions int,
  
  -- Conversions
  conversion_count int,
  conversion_rate decimal(5,2),       -- 12.50 = 12.5%
  
  -- Revenue
  total_revenue int,                  -- Cents
  average_order_value decimal(8,2),
  
  -- Engagement
  bounce_rate decimal(5,2),
  avg_session_duration int,           -- Seconds
  pages_per_session int,
  
  -- By type
  views int,
  clicks int,
  purchases int,
  signups int,
  
  unique: (tenant_id, period_type, period_start, dimension, dimension_value)
);
```

### `analytics_campaigns`

Campaign-level metrics:

```sql
CREATE TABLE analytics_campaigns (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  campaign_id varchar(255) UNIQUE,    -- Email campaign ID
  campaign_type varchar(50),          -- email, sms, push, social
  campaign_name varchar(255),
  
  -- Performance
  target_audience_size int,
  sent_count int,
  delivered_count int,
  open_count int,
  open_rate decimal(5,2),             -- 45.50 = 45.5%
  click_count int,
  click_through_rate decimal(5,2),
  conversion_count int,
  conversion_rate decimal(5,2),
  
  -- Revenue
  revenue int,                        -- Cents
  
  -- Engagement
  unsubscribe_count int,
  complaint_count int
);
```

### `user_journeys`

Session-level user flow:

```sql
CREATE TABLE user_journeys (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  user_id bigint,
  session_id varchar(255) UNIQUE,
  
  started_at timestamp,
  ended_at timestamp,
  duration_seconds int,               -- Total time in session
  
  first_source varchar(100),          -- email, sms, direct, social
  referrer_url varchar(500),
  device_type varchar(50),
  
  pages_viewed int,
  interactions int,                   -- Clicks, form submissions
  
  converted boolean,
  conversion_type varchar(50),        -- purchase, signup
  conversion_value int,               -- Cents
  
  event_sequence json,                -- [{type: 'view', at: '...'}...]
  
  INDEX: (tenant_id, started_at)
);
```

### `funnel_analytics`

Multi-step conversion tracking:

```sql
CREATE TABLE funnel_analytics (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  funnel_name varchar(255),           -- Signup funnel, Purchase funnel
  funnel_slug varchar(255) UNIQUE,
  
  -- Funnel steps
  steps json,                         -- [{step: 1, name: 'Landing', event_type: 'view'}...]
  total_entered int,                  -- Users who started funnel
  total_completed int,                -- Users who completed all steps
  completion_rate decimal(5,2),
  
  -- Step metrics
  step_metrics json,                  -- [{step: 1, entered: 1000, completed: 800}...]
  
  -- Dropoff
  dropoff_analysis json               -- Step with highest abandonment
);
```

### `conversion_tracking`

Attribution and conversion paths:

```sql
CREATE TABLE conversion_tracking (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  user_id bigint,
  session_id varchar(255),
  
  -- Conversion info
  conversion_type varchar(50),        -- purchase, signup, email_signup
  conversion_value int,               -- Cents
  external_id varchar(255) UNIQUE,    -- Order ID
  
  -- Attribution (which source gets credit?)
  first_touch_source varchar(100),    -- First campaign/source user saw
  first_touch_campaign varchar(255),
  last_touch_source varchar(100),     -- Last source before conversion
  last_touch_campaign varchar(255),
  
  -- All interactions
  touch_points json,                  -- [{source: 'email', campaign_id: '123', at: '...'}...]
  touch_point_count int,              -- Total interactions (1 = direct)
  
  -- Timeline
  first_interaction_at timestamp,
  conversion_at timestamp,
  days_to_conversion int,             -- How long from first touch to purchase
  
  INDEX: (first_touch_source, last_touch_source)
);
```

---

## Core Concepts

### Event Types

```
Engagement:
- view: Page/email viewed
- click: Link clicked
- scroll: User scrolled
- form_submit: Form completed

Conversion:
- purchase: Product purchased
- signup: Account created
- email_signup: Newsletter signup
- event_registration: Event registered

Platform-specific:
- email_open: Email opened
- email_click: Email link clicked
- sms_read: SMS message read
- push_open: Push notification opened
- login: User logged in
- logout: User logged out
```

### Event Sources

Where events originate:
- `web` - Website/app
- `email` - Email campaign
- `sms` - SMS campaign
- `push` - Push notification
- `api` - Direct API call
- `direct` - Direct traffic

### Attribution Models

**First Touch**: Give all credit to first source user encountered
```
User journey: Email → Direct → Website → Purchase
Attribution: Email gets 100% credit
```

**Last Touch**: Give all credit to source immediately before conversion
```
User journey: Email → Direct → Website → Purchase
Attribution: Website gets 100% credit
```

**Linear**: Split credit equally among all touches
```
User journey: Email → Direct → Website → Purchase (3 touches)
Attribution: Each source gets 33.33% credit
```

### Funnel Concept

Multi-step conversion path:

```
Purchase Funnel:
1. Landing Page (view)
   ├─ 10,000 entered
   │
2. Product Page (view)
   ├─ 6,000 entered (40% dropoff)
   │
3. Add to Cart (click)
   ├─ 3,000 entered (50% dropoff)
   │
4. Checkout (view)
   ├─ 2,500 entered (17% dropoff)
   │
5. Payment (conversion)
   ├─ 2,000 completed (20% dropoff)

Overall completion rate: 2,000 / 10,000 = 20%
Highest dropoff: Step 2-3 (50%)
```

---

## Event Tracking

### Track Page View

```php
$analyticsService->trackEvent(
    eventType: 'view',
    eventSource: 'web',
    label: 'Homepage',
    userId: auth()->id(),
    sessionId: session('analytics_session'),
    deviceType: 'mobile',
    country: 'AT'
);
```

### Track Button Click

```php
$analyticsService->trackEvent(
    eventType: 'click',
    eventSource: 'web',
    action: 'Button clicked',
    label: 'Subscribe Button',
    userId: auth()->id(),
    sessionId: session('analytics_session')
);
```

### Track Email Open

```php
// Webhook from email service provider
$analyticsService->trackEvent(
    eventType: 'email_open',
    eventSource: 'email',
    campaignId: 'campaign_123',
    userId: $userId,
    metadata: [
        'email_id' => 'msg_456',
        'opened_at' => '2025-10-24T10:30:00Z',
    ]
);
```

### Track Purchase

```php
$analyticsService->trackEvent(
    eventType: 'purchase',
    eventSource: 'web',
    category: 'conversion',
    label: 'Product: Football Jersey',
    value: 9999, // €99.99 in cents
    userId: auth()->id(),
    sessionId: session('analytics_session'),
    metadata: [
        'product_id' => 123,
        'quantity' => 2,
        'order_id' => 'ORDER-456',
    ]
);
```

---

## User Journeys

### Start Journey

```php
$journey = $analyticsService->startJourney(
    userId: auth()->id(),
    firstSource: 'email',      // Email campaign
    deviceType: 'mobile',
    country: 'AT'
);

session(['analytics_session' => $journey->session_id]);
```

### Track Journey Events

```php
// User views page
$analyticsService->trackEvent(
    eventType: 'view',
    eventSource: 'web',
    sessionId: session('analytics_session')
);

// User clicks link
$analyticsService->trackEvent(
    eventType: 'click',
    eventSource: 'web',
    sessionId: session('analytics_session')
);
```

### End Journey

```php
$analyticsService->endJourney(
    sessionId: session('analytics_session'),
    converted: true,
    conversionType: 'purchase',
    conversionValue: 9999
);
```

### Get Journey Summary

```php
$journey = UserJourney::where('session_id', $sessionId)->first();
$summary = $journey->getJourneySummary();

// Output:
[
    'session_id' => 'abc123...',
    'duration_minutes' => 15.5,
    'pages_viewed' => 5,
    'interactions' => 12,
    'first_source' => 'email',
    'converted' => true,
    'conversion_value_eur' => 99.99,
]
```

---

## Conversion Attribution

### Record Purchase with Attribution

```php
$conversion = $analyticsService->recordConversion(
    conversionType: 'purchase',
    userId: auth()->id(),
    sessionId: session('analytics_session'),
    value: 9999, // €99.99
    externalId: 'ORDER-123',
    touchPoints: [
        [
            'source' => 'email',
            'campaign_id' => 'welcome_series_1',
            'at' => '2025-10-20T10:00:00Z',
        ],
        [
            'source' => 'web',
            'campaign_id' => 'direct',
            'at' => '2025-10-24T14:30:00Z',
        ],
    ]
);
```

### Get Attribution Models

```php
$conversion = ConversionTracking::find($conversionId);

// First touch: Give credit to email campaign
$firstTouchAttribution = $conversion->getFirstTouchAttribution();
// Output: {source: 'email', campaign: 'welcome_series_1', credit_percent: 100}

// Last touch: Give credit to direct traffic
$lastTouchAttribution = $conversion->getLastTouchAttribution();
// Output: {source: 'web', campaign: 'direct', credit_percent: 100}

// Linear: Split equally
$linearAttribution = $conversion->getLinearAttribution();
// Output: [
//   {source: 'email', credit_percent: 50},
//   {source: 'web', credit_percent: 50}
// ]
```

### Analyze Conversion Path

```php
$conversion = ConversionTracking::find($conversionId);
$journey = $conversion->getConversionJourney();

// Output shows complete path
[
    'first_touch' => ['source' => 'email', 'campaign' => 'welcome_1'],
    'last_touch' => ['source' => 'web', 'campaign' => 'direct'],
    'days_to_conversion' => 4,
    'total_touch_points' => 2,
    'touch_points' => [...],
]
```

---

## Funnel Analytics

### Create Funnel

```php
$funnel = FunnelAnalytics::create([
    'tenant_id' => $tenantId,
    'funnel_name' => 'Purchase Funnel',
    'funnel_slug' => 'purchase-funnel',
    'steps' => [
        [
            'step' => 1,
            'name' => 'Landing Page',
            'event_type' => 'view',
            'label' => 'product_page',
        ],
        [
            'step' => 2,
            'name' => 'Add to Cart',
            'event_type' => 'click',
            'label' => 'add_to_cart',
        ],
        [
            'step' => 3,
            'name' => 'Checkout',
            'event_type' => 'view',
            'label' => 'checkout_page',
        ],
        [
            'step' => 4,
            'name' => 'Payment',
            'event_type' => 'purchase',
            'label' => null,
        ],
    ],
    'total_entered' => 10000,
    'total_completed' => 2000,
    'completion_rate' => 20,
]);
```

### Analyze Funnel

```php
$funnel = FunnelAnalytics::where('funnel_slug', 'purchase-funnel')->first();

// Get step-by-step breakdown
$breakdown = $funnel->getStepBreakdown();
// [
//   [step: 1, entered: 10000, completed: 10000, completion_rate: 100],
//   [step: 2, entered: 10000, completed: 6000, completion_rate: 60],
//   [step: 3, entered: 6000, completed: 2500, completion_rate: 41.67],
//   [step: 4, entered: 2500, completed: 2000, completion_rate: 80],
// ]

// Get dropoff analysis
$dropoffs = $funnel->getDropoffAnalysis();
// [
//   [between_step: '1→2', users_lost: 4000, dropoff_rate: 40],
//   [between_step: '2→3', users_lost: 3500, dropoff_rate: 58.33],
//   [between_step: '3→4', users_lost: 500, dropoff_rate: 20],
// ]

// Identify highest dropoff
$worstStep = $dropoffs[0];
// 58.33% dropout between Add to Cart and Checkout
// → Focus optimization efforts on checkout page
```

---

## API Endpoints

### Get Dashboard

```
GET /api/analytics/dashboard?days=7
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "period_days": 7,
    "total_events": 50000,
    "unique_users": 2500,
    "total_conversions": 150,
    "conversion_rate": 6.0,
    "total_revenue_eur": 14250.50,
    "average_order_value": 95.0,
    "events_by_type": {
      "view": 30000,
      "click": 15000,
      "purchase": 5000
    },
    "events_by_source": {
      "web": 25000,
      "email": 20000,
      "sms": 5000
    }
  }
}
```

### Track Event

```
POST /api/analytics/events
Authorization: Bearer {token}
Content-Type: application/json

{
  "event_type": "purchase",
  "event_source": "web",
  "category": "conversion",
  "user_id": 123,
  "session_id": "abc123...",
  "label": "Football Jersey",
  "value": 9999,
  "campaign_id": null,
  "device_type": "mobile",
  "country": "AT"
}

Response 201:
{
  "message": "Event tracked",
  "data": {
    "event_id": 10000,
    "type": "purchase"
  }
}
```

### Start Journey

```
POST /api/analytics/journeys/start
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 123,
  "first_source": "email",
  "device_type": "mobile",
  "country": "AT"
}

Response 201:
{
  "message": "Journey started",
  "data": {
    "session_id": "abc123...",
    "started_at": "2025-10-24T10:00:00Z"
  }
}
```

### Get Journey

```
GET /api/analytics/journeys/{sessionId}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "session_id": "abc123...",
    "user_id": 123,
    "started_at": "2025-10-24T10:00:00Z",
    "duration_minutes": 15.5,
    "pages_viewed": 5,
    "interactions": 12,
    "first_source": "email",
    "device_type": "mobile",
    "converted": true,
    "conversion_type": "purchase",
    "conversion_value_eur": 99.99
  }
}
```

### End Journey

```
POST /api/analytics/journeys/{sessionId}/end
Authorization: Bearer {token}
Content-Type: application/json

{
  "converted": true,
  "conversion_type": "purchase",
  "conversion_value": 9999
}

Response 200:
{
  "message": "Journey ended"
}
```

### Record Conversion

```
POST /api/analytics/conversions
Authorization: Bearer {token}
Content-Type: application/json

{
  "conversion_type": "purchase",
  "user_id": 123,
  "session_id": "abc123...",
  "value": 9999,
  "external_id": "ORDER-123",
  "touch_points": [
    {"source": "email", "campaign_id": "welcome_1"},
    {"source": "web", "campaign_id": "direct"}
  ]
}

Response 201:
{
  "message": "Conversion recorded",
  "data": {
    "conversion_id": 5000,
    "type": "purchase",
    "value_eur": 99.99
  }
}
```

### Get Top Converting Sources

```
GET /api/analytics/top-sources?limit=10&days=30
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "source": "email",
      "conversions": 450,
      "revenue_eur": 42500.50,
      "avg_order_value": 94.44
    },
    {
      "source": "web",
      "conversions": 200,
      "revenue_eur": 19000.00,
      "avg_order_value": 95.00
    },
    {
      "source": "sms",
      "conversions": 85,
      "revenue_eur": 8000.00,
      "avg_order_value": 94.12
    }
  ]
}
```

### Get Funnel Analytics

```
GET /api/analytics/funnels/{funnelSlug}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "funnel_name": "Purchase Funnel",
    "total_entered": 10000,
    "total_completed": 2000,
    "completion_rate": 20.0,
    "steps": 4,
    "step_details": [
      {
        "step": 1,
        "name": "Landing Page",
        "entered": 10000,
        "completed": 10000,
        "completion_rate": 100.0
      },
      {
        "step": 2,
        "name": "Add to Cart",
        "entered": 10000,
        "completed": 6000,
        "completion_rate": 60.0
      },
      ...
    ],
    "highest_dropoff_step": {
      "between_step": "2 → 3",
      "users_lost": 3500,
      "dropoff_rate": 58.33
    },
    "efficiency_score": 5.0
  }
}
```

### Get Conversion Attribution

```
GET /api/analytics/conversions/{externalId}/attribution
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "conversion_type": "purchase",
    "value_eur": 99.99,
    "first_touch": {
      "source": "email",
      "campaign": "welcome_1"
    },
    "last_touch": {
      "source": "web",
      "campaign": "direct"
    },
    "days_to_conversion": 4,
    "total_touch_points": 2,
    "touch_points": [
      {"source": "email", "campaign_id": "welcome_1", "at": "2025-10-20T10:00:00Z"},
      {"source": "web", "campaign_id": "direct", "at": "2025-10-24T14:30:00Z"}
    ]
  }
}
```

### List Campaigns

```
GET /api/analytics/campaigns?type=email&limit=20
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "campaign_id": "email_campaign_1",
      "campaign_name": "Welcome Series",
      "type": "email",
      "audience_size": 5000,
      "delivered": 4900,
      "delivery_rate": 98.0,
      "open_rate": 45.5,
      "click_through_rate": 15.2,
      "conversions": 125,
      "conversion_rate": 2.5,
      "revenue_eur": 11750.50,
      "roi_percent": 87.5
    }
  ],
  "total": 5
}
```

### Export Data

```
GET /api/analytics/export?days=30
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "period_from": "2025-09-24",
    "period_to": "2025-10-24",
    "total_events": 500000,
    "total_revenue": 475250.00,
    "unique_users": 15000,
    "events": [
      {
        "type": "view",
        "source": "web",
        "value": 0,
        "user_id": 123,
        "timestamp": "2025-10-24T10:00:00Z"
      }
    ]
  }
}
```

---

## Usage Examples

### E-commerce Analytics

```php
// 1. User arrives from email campaign
$journey = $analyticsService->startJourney(
    userId: null,
    firstSource: 'email',
    deviceType: 'mobile',
    country: 'AT'
);
session(['analytics_session' => $journey->session_id]);

// 2. Track page views
$analyticsService->trackEvent(
    eventType: 'view',
    eventSource: 'web',
    label: 'Product Page',
    sessionId: session('analytics_session')
);

// 3. Track add to cart click
$analyticsService->trackEvent(
    eventType: 'click',
    eventSource: 'web',
    label: 'Add to Cart',
    sessionId: session('analytics_session')
);

// 4. Track purchase
$analyticsService->trackEvent(
    eventType: 'purchase',
    eventSource: 'web',
    value: 9999,
    sessionId: session('analytics_session'),
    metadata: ['product_id' => 123, 'quantity' => 2]
);

// 5. Record conversion with attribution
$conversion = $analyticsService->recordConversion(
    conversionType: 'purchase',
    userId: auth()->id(),
    sessionId: session('analytics_session'),
    value: 9999,
    externalId: 'ORDER-456',
    touchPoints: [
        ['source' => 'email', 'campaign_id' => 'welcome_1'],
    ]
);

// 6. End journey
$analyticsService->endJourney(
    sessionId: session('analytics_session'),
    converted: true,
    conversionType: 'purchase',
    conversionValue: 9999
);
```

### Campaign Attribution Analysis

```php
// Analyze which campaigns drive conversions
$topSources = $analyticsService->getTopConvertingSources(limit: 5, days: 30);

foreach ($topSources as $source) {
    echo $source['source'] . ': ';
    echo $source['conversions'] . ' conversions, ';
    echo '€' . $source['revenue_eur'] . ' revenue';
}

// Output:
// email: 450 conversions, €42500.50 revenue
// web: 200 conversions, €19000.00 revenue
// sms: 85 conversions, €8000.00 revenue
```

### Funnel Optimization

```php
// Find conversion bottleneck
$funnel = FunnelAnalytics::where('funnel_slug', 'purchase-funnel')->first();
$dropoffs = $funnel->getDropoffAnalysis();

$worstStep = $dropoffs[0];
echo "Highest dropoff: {$worstStep['between_step']}";
echo "Users lost: {$worstStep['users_lost']}";
echo "Dropoff rate: {$worstStep['dropoff_rate']}%";

// → Focus optimization on checkout page (58.33% dropout)
```

---

## Dashboard Metrics

### Key Metrics

**Conversion Rate** = Conversions / Unique Users × 100
```
150 purchases / 2500 unique users = 6% conversion rate
```

**Average Order Value** = Total Revenue / Number of Orders
```
€14,250.50 / 150 orders = €95.00 AOV
```

**Click-Through Rate** = Clicks / Views × 100
```
500 clicks / 10,000 views = 5% CTR
```

**Days to Conversion** = Average time from first touch to purchase
```
User 1: 4 days
User 2: 7 days
User 3: 2 days
Average: 4.33 days
```

**Bounce Rate** = Single-page sessions / Total sessions × 100
```
250 bounces / 5000 sessions = 5% bounce rate
```

---

## Best Practices

### 1. Use Consistent Session IDs

```php
// Generate once at session start
if (!session('analytics_session')) {
    $journey = $analyticsService->startJourney(...);
    session(['analytics_session' => $journey->session_id]);
}

// Use same session ID in all events
$analyticsService->trackEvent(
    sessionId: session('analytics_session'),
    ...
);
```

### 2. Track Both Views and Conversions

```php
// Good: Track all interactions
trackEvent('view', 'Landing page');
trackEvent('click', 'CTA button');
trackEvent('purchase', 'Order placed');

// Bad: Only track purchases
trackEvent('purchase', 'Order placed');
```

### 3. Record Revenue in Cents

```php
// Good: €99.99 stored as 9999 cents
trackEvent(eventType: 'purchase', value: 9999);

// Bad: Float values cause precision issues
trackEvent(eventType: 'purchase', value: 99.99);
```

### 4. Aggregate Daily for Performance

```php
// Schedule nightly aggregation
$schedule->command('analytics:aggregate-daily')
    ->daily()
    ->at('02:00'); // Off-peak time
```

### 5. Clean Old Data

```php
// Archive events older than 1 year
AnalyticsEvent::where('created_at', '<', now()->subYear())
    ->delete();
```

---

**Last Updated**: 2025-10-24  
**Version**: 1.0.0  
**Status**: Production Ready ✅
