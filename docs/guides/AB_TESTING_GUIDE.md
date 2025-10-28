# A/B Testing System Guide

**Comprehensive A/B testing framework for email, SMS, push, web, and landing pages**

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Installation & Setup](#installation--setup)
3. [Database Schema](#database-schema)
4. [Core Concepts](#core-concepts)
5. [Creating A/B Tests](#creating-ab-tests)
6. [Statistical Significance](#statistical-significance)
7. [API Endpoints](#api-endpoints)
8. [Usage Examples](#usage-examples)
9. [Analytics & Reporting](#analytics--reporting)
10. [Best Practices](#best-practices)
11. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

### System Flow

```
┌──────────────────────────────────────┐
│      Create A/B Test                 │
│ - Name, Type, Hypothesis             │
│ - Confidence level (95%)             │
│ - Min sample size (100 users)        │
└────────────┬──────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│   Create Variants                    │
│ - Control (original)                 │
│ - Variant A                          │
│ - Variant B                          │
│ - Traffic allocation (%)             │
└────────────┬──────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│   Activate Test                      │
│ - Status: draft → active             │
│ - Record start time                  │
└────────────┬──────────────────────────┘
             │
    ┌────────┴────────┐
    │                 │
    ▼                 ▼
┌──────────┐    ┌──────────────┐
│ Campaign │    │ Email/SMS    │
│ Send     │    │ Landing Page │
└────┬─────┘    └────┬─────────┘
     │               │
     └───────┬───────┘
             │
             ▼
    ┌─────────────────────┐
    │  User Experiences   │
    │  Variant            │
    │  (Traffic 50/50)    │
    │                     │
    │ Control Group:      │
    │ - 50 users          │
    │                     │
    │ Variant Group:      │
    │ - 50 users          │
    └────────┬────────────┘
             │
    ┌────────┴─────────────┐
    │                      │
    ▼                      ▼
┌──────────────┐    ┌──────────────┐
│   Track      │    │   Record     │
│ - Views      │    │ - Conversions│
│ - Clicks     │    │ - Revenue    │
│ - Events     │    │ - Revenue/EU │
└──────┬───────┘    └──────┬───────┘
       │                   │
       └───────────┬───────┘
                   │
                   ▼
        ┌────────────────────────┐
        │   Analysis Phase       │
        │ - Calculate stats      │
        │ - Chi-square test      │
        │ - P-value calculation  │
        │ - Determine winner     │
        └────────────┬───────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │    Declare Winner      │
        │ - Statistically sig.   │
        │ - Confidence >= 95%    │
        │ - Apply to all users   │
        └────────────────────────┘
```

### Data Model

```
┌─────────────────────────────────────┐
│           ab_tests                  │
│─────────────────────────────────────│
│ - id: primary key                   │
│ - tenant_id, created_by             │
│ - name, slug, description           │
│ - test_type: email|sms|push|web     │
│ - status: draft|active|completed    │
│ - started_at, ended_at              │
│ - confidence_level: 0.95 (95%)      │
│ - minimum_sample_size: 100          │
│ - is_winner_declared, winning_*     │
└──────────────────┬──────────────────┘
                   │ 1
                   │
                   │ * (1 = control, 2+ = variants)
                   │
┌──────────────────▼──────────────────┐
│      ab_test_variants               │
│──────────────────────────────────────│
│ - id: primary key                   │
│ - ab_test_id: foreign key           │
│ - name: "Control", "Variant A", etc │
│ - traffic_allocation: 50.0%         │
│ - assigned_participants: 500        │
│ - views, clicks, conversions        │
│ - conversion_rate: 12.5%            │
│ - revenue, average_order_value      │
│ - is_control: true/false            │
└──────────────────┬──────────────────┘
                   │ *
                   │
        ┌──────────┴──────────┐
        │ 1:n relationship   │
        │                    │
        ▼                    ▼
┌──────────────────┐  ┌──────────────────┐
│ ab_test_results  │  │ab_test_conversions
│ (user tracking)  │  │ (purchase/signup)
├──────────────────┤  ├──────────────────┤
│ - user_id        │  │ - user_id        │
│ - session_id     │  │ - conversion_type│
│ - views: 5       │  │ - revenue: 1999  │
│ - clicks: 2      │  │ - currency: EUR  │
│ - conversions: 1 │  │ - external_id    │
│ - event_log      │  │ - timestamp      │
└──────────────────┘  └──────────────────┘
```

---

## Installation & Setup

### Step 1: Run Migration

```bash
php artisan migrate
```

Creates tables:
- `ab_tests` - Test configurations
- `ab_test_variants` - Control and variant versions
- `ab_test_results` - User-level tracking
- `ab_test_conversions` - Conversion events

### Step 2: Seed Initial Variants (Optional)

```php
// Pre-seed test with control + variants
$test = AbTest::create([...]);

AbTestVariant::create([
    'ab_test_id' => $test->id,
    'name' => 'Control',
    'slug' => 'control',
    'traffic_allocation' => 50,
    'is_control' => true,
]);

AbTestVariant::create([
    'ab_test_id' => $test->id,
    'name' => 'Variant A',
    'slug' => 'variant-a',
    'traffic_allocation' => 50,
    'is_control' => false,
]);
```

---

## Database Schema

### `ab_tests`

Main test configuration table:

```sql
CREATE TABLE ab_tests (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  created_by bigint,
  name varchar(255),
  slug varchar(255) UNIQUE,
  description text,
  test_type varchar(50),              -- email, sms, push, web, landing_page
  status varchar(50) DEFAULT 'draft',  -- draft, active, paused, completed
  started_at timestamp,
  ended_at timestamp,
  scheduled_end_at timestamp,
  duration_days int,                  -- Auto-end after N days
  total_participants int,
  minimum_sample_size int DEFAULT 100,
  confidence_level decimal(5,3),      -- 0.95 = 95%
  hypothesis varchar(255),
  test_config json,
  is_winner_declared boolean,
  winning_variant_id bigint,
  timestamps
);
```

### `ab_test_variants`

Variant definitions (control + variations):

```sql
CREATE TABLE ab_test_variants (
  id bigint PRIMARY KEY,
  ab_test_id bigint,
  name varchar(255),                  -- "Control", "Variant A", "Variant B"
  slug varchar(255),
  traffic_allocation decimal(5,2),    -- 50.00 = 50%
  assigned_participants int,
  views int,
  clicks int,
  conversions int,
  conversion_rate decimal(5,2),       -- 12.50 = 12.5%
  revenue int,                        -- Cents: 19999 = €199.99
  average_order_value decimal(8,2),
  variant_content json,               -- Email subject, body, CTA
  is_control boolean DEFAULT false,
  timestamps
);
```

### `ab_test_results`

Per-user test results:

```sql
CREATE TABLE ab_test_results (
  id bigint PRIMARY KEY,
  ab_test_id bigint,
  variant_id bigint,
  user_id bigint,
  session_id varchar(255),            -- For anonymous tracking
  identifier varchar(255),            -- Email, phone for linking
  views int,
  clicks int,
  conversions int,
  first_seen_at timestamp,
  last_seen_at timestamp,
  converted_at timestamp,
  event_log json,                     -- [{event: 'view', at: '2025-10-24T10:00:00'}, ...]
  metadata json,
  timestamps
);
```

### `ab_test_conversions`

Conversion events:

```sql
CREATE TABLE ab_test_conversions (
  id bigint PRIMARY KEY,
  ab_test_id bigint,
  variant_id bigint,
  user_id bigint,
  conversion_type varchar(50),        -- purchase, signup, email_open, sms_read
  conversion_source varchar(50),      -- email, sms, push, web, api
  revenue int,                        -- Cents
  currency varchar(3),                -- EUR, USD, GBP
  conversion_data json,               -- Order details, product info
  external_id varchar(255),           -- Order ID, transaction ID
  notes text,
  timestamps
);
```

---

## Core Concepts

### Variants

Each test has multiple variants:
- **Control**: Original version (baseline for comparison)
- **Variants**: Alternative versions (A, B, C, etc.)

```
Test Structure:
├── Control (50% traffic)
│   └── Current email subject
├── Variant A (25% traffic)
│   └── New email subject v1
└── Variant B (25% traffic)
    └── New email subject v2
```

### Traffic Allocation

Distribution of users to variants:

```
Control:   50.0%  ← Gets 50% of users
Variant A: 30.0%  ← Gets 30% of users
Variant B: 20.0%  ← Gets 20% of users (new feature test)
```

### Conversion Rate

Percentage of users who convert:

```
Control:   12 conversions / 1000 users = 1.2%
Variant A: 18 conversions / 1000 users = 1.8% ← +50% improvement
Variant B: 8 conversions / 1000 users  = 0.8%
```

### Statistical Significance

Determines if results are real or due to chance.

**Requirements**:
- Minimum sample size: 100+ users per variant
- Confidence level: 95% (α = 0.05)
- P-value < 0.05

**Test**: Chi-square goodness of fit

```
H₀ (Null): No difference between variants
H₁ (Alt):  Variants perform differently

If p-value < 0.05: Reject H₀ (variants ARE different)
If p-value ≥ 0.05: Accept H₀ (variants NOT different)
```

### P-Value

Probability that observed results occurred by chance.

- **p = 0.01**: 1% chance results are random → Very significant ✅
- **p = 0.05**: 5% chance results are random → Significant ✅
- **p = 0.10**: 10% chance results are random → Not significant ❌
- **p = 0.50**: 50% chance results are random → Definitely random ❌

---

## Creating A/B Tests

### Step 1: Create Test

```php
$testingService = new AbTestingService(auth()->user()->current_tenant_id);

$test = $testingService->createTest(
    name: 'Email Subject A/B Test',
    testType: 'email',
    hypothesis: 'Adding emoji to subject increases open rate by 10%',
    minimumSampleSize: 200,
    confidenceLevel: 0.95, // 95%
    durationDays: 14,      // Auto-end after 14 days
    createdBy: auth()->id()
);
```

### Step 2: Create Variants

```php
// Control variant
$control = $testingService->createVariant(
    testId: $test->id,
    name: 'Control',
    trafficAllocation: 50,
    isControl: true,
    variantContent: [
        'subject' => 'Weekly Update',
        'body' => 'Check out this week\'s news',
        'cta_text' => 'Read More'
    ]
);

// Test variant with emoji
$variantA = $testingService->createVariant(
    testId: $test->id,
    name: 'Variant A - With Emoji',
    trafficAllocation: 50,
    isControl: false,
    variantContent: [
        'subject' => '📰 Weekly Update',
        'body' => 'Check out this week\'s news',
        'cta_text' => 'Read More'
    ]
);
```

### Step 3: Start Test

```php
$test = $testingService->startTest($test->id);
// Status: active, started_at: now()
```

### Step 4: Assign Users & Track

```php
// Assign user to test
$variant = $testingService->assignVariant($test->id, $userId);
// Returns: assigned variant

// Record events
$testingService->recordView($test->id, $variant->id, $userId);
$testingService->recordClick($test->id, $variant->id, $userId);

// Record conversion with revenue
$testingService->recordConversionEvent(
    testId: $test->id,
    variantId: $variant->id,
    conversionType: 'purchase',
    userId: $userId,
    revenueInCents: 9999, // €99.99
    externalId: 'ORDER-12345'
);
```

### Step 5: Analyze Results

```php
// Get analytics
$analytics = $testingService->getAnalytics($test->id);

// Check statistical significance
$significance = $testingService->calculateSignificance($test->id);

// Determine winner
$winner = $testingService->getWinner($test->id);

if ($winner) {
    $testingService->declareWinner($test->id, $winner->id);
}
```

---

## Statistical Significance

### Chi-Square Test Implementation

Simplif Chi-square test for A/B testing:

```
χ² = Σ((Observed - Expected)² / Expected)

For each variant:
  Observed = actual conversions
  Expected = variant participants × overall conversion rate
  
Example (2 variants, 95% confidence):
  Control:   12 conversions / 1000 participants = 1.2%
  Variant A: 18 conversions / 1000 participants = 1.8%
  
  Overall rate = (12 + 18) / 2000 = 1.5%
  
  χ² = ((12 - 15)² / 15) + ((18 - 15)² / 15)
     = (9/15) + (9/15)
     = 1.2
  
  Critical value (95% confidence) = 3.841
  1.2 < 3.841 → NOT significant
```

### When to Declare Winner

1. **Minimum participants reached**
   - Both variants: ≥100 users
   - Overall: ≥200 users (configurable)

2. **Statistical significance achieved**
   - Chi-square > critical value
   - P-value < 0.05 (95% confidence)

3. **One variant clearly winning**
   - Conversion rate difference > threshold
   - No overlap in confidence intervals

### Sample Size Calculator

Estimate participants needed:

```
For 2 variants (control + variant):
- Current conversion rate: 1%
- Desired improvement: +30% (1% → 1.3%)
- Confidence: 95%
- Statistical power: 80%

Result: ~3,200 participants per variant needed
```

---

## API Endpoints

### Create A/B Test

```
POST /api/ab-testing/tests
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Email Subject Test",
  "test_type": "email",
  "description": "Testing emoji in subject lines",
  "hypothesis": "Emoji increases open rate by 10%",
  "minimum_sample_size": 200,
  "confidence_level": 0.95,
  "duration_days": 14
}

Response 201:
{
  "message": "A/B test created",
  "data": {
    "test_id": 1,
    "name": "Email Subject Test",
    "slug": "email-subject-test-1729772400",
    "type": "email",
    "status": "draft"
  }
}
```

### Get Test Details

```
GET /api/ab-testing/tests/{testId}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "id": 1,
    "name": "Email Subject Test",
    "type": "email",
    "status": "active",
    "hypothesis": "Emoji increases open rate by 10%",
    "started_at": "2025-10-24T10:00:00Z",
    "summary": {
      "status": "active",
      "total_participants": 450,
      "total_conversions": 25,
      "overall_conversion_rate": 5.56,
      "variant_count": 2
    }
  }
}
```

### List All Tests

```
GET /api/ab-testing/tests?status=active&type=email
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "id": 1,
      "name": "Email Subject Test",
      "type": "email",
      "status": "active",
      "total_participants": 450,
      "started_at": "2025-10-24T10:00:00Z"
    }
  ],
  "total": 1
}
```

### Create Variant

```
POST /api/ab-testing/tests/{testId}/variants
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Variant A - With Emoji",
  "traffic_allocation": 50,
  "is_control": false,
  "variant_content": {
    "subject": "📰 Weekly Update",
    "body": "Check out this week's news",
    "cta_text": "Read More"
  }
}

Response 201:
{
  "message": "Variant created",
  "data": {
    "variant_id": 2,
    "name": "Variant A - With Emoji",
    "traffic_allocation": 50,
    "is_control": false
  }
}
```

### Assign User to Variant

```
POST /api/ab-testing/tests/{testId}/assign
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 123
}

Response 200:
{
  "data": {
    "test_id": 1,
    "user_id": 123,
    "variant_id": 2,
    "variant_name": "Variant A - With Emoji"
  }
}
```

### Record View

```
POST /api/ab-testing/tests/{testId}/variants/{variantId}/view
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 123,
  "session_id": null
}

Response 200:
{
  "message": "View recorded"
}
```

### Record Click

```
POST /api/ab-testing/tests/{testId}/variants/{variantId}/click
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 123
}

Response 200:
{
  "message": "Click recorded"
}
```

### Record Conversion

```
POST /api/ab-testing/tests/{testId}/variants/{variantId}/conversion
Authorization: Bearer {token}
Content-Type: application/json

{
  "conversion_type": "purchase",
  "user_id": 123,
  "revenue_cents": 9999,
  "conversion_source": "email",
  "external_id": "ORDER-12345",
  "conversion_data": {
    "product_id": 456,
    "quantity": 2
  }
}

Response 200:
{
  "message": "Conversion recorded",
  "data": {
    "conversion_id": 10,
    "type": "purchase",
    "revenue": 99.99
  }
}
```

### Get Analytics

```
GET /api/ab-testing/tests/{testId}/analytics
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "test_id": 1,
    "test_name": "Email Subject Test",
    "test_type": "email",
    "status": "active",
    "total_participants": 1000,
    "variants": [
      {
        "id": 1,
        "name": "Control",
        "assigned_participants": 500,
        "views": 450,
        "clicks": 90,
        "conversions": 36,
        "conversion_rate": 7.2,
        "click_through_rate": 20,
        "revenue_eur": 359.91,
        "is_control": true
      },
      {
        "id": 2,
        "name": "Variant A",
        "assigned_participants": 500,
        "views": 480,
        "clicks": 115,
        "conversions": 50,
        "conversion_rate": 10.4,
        "click_through_rate": 23.96,
        "revenue_eur": 500.50,
        "is_control": false
      }
    ],
    "significance": {
      "chi_square": 5.67,
      "significant": true,
      "confidence_level": 0.95
    },
    "winner": {
      "id": 2,
      "name": "Variant A",
      "conversion_rate": 10.4
    }
  }
}
```

### Get Variant Performance

```
GET /api/ab-testing/tests/{testId}/variants/{variantId}/performance
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "variant_id": 2,
    "name": "Variant A - With Emoji",
    "assigned_participants": 500,
    "views": 480,
    "clicks": 115,
    "conversions": 50,
    "conversion_rate": 10.4,
    "click_through_rate": 23.96,
    "revenue": 500.50,
    "average_order_value": 10.01
  }
}
```

### Get Statistical Significance

```
GET /api/ab-testing/tests/{testId}/significance
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "chi_square": 5.67,
    "significant": true,
    "confidence_level": 0.95
  }
}
```

### Declare Winner

```
POST /api/ab-testing/tests/{testId}/declare-winner
Authorization: Bearer {token}
Content-Type: application/json

{
  "winner_variant_id": 2  // Optional - auto-detect if omitted
}

Response 200:
{
  "message": "Winner declared",
  "data": {
    "test_id": 1,
    "winner_variant_id": 2,
    "winner_name": "Variant A - With Emoji",
    "conversion_rate": 10.4
  }
}
```

### Start Test

```
POST /api/ab-testing/tests/{testId}/start
Authorization: Bearer {token}

Response 200:
{
  "message": "Test started",
  "data": {
    "test_id": 1,
    "status": "active",
    "started_at": "2025-10-24T10:00:00Z"
  }
}
```

### End Test

```
POST /api/ab-testing/tests/{testId}/end
Authorization: Bearer {token}

Response 200:
{
  "message": "Test ended",
  "data": {
    "test_id": 1,
    "status": "completed",
    "ended_at": "2025-10-24T14:00:00Z"
  }
}
```

### Get Conversions

```
GET /api/ab-testing/tests/{testId}/conversions?variant_id=2&type=purchase&page=1
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "id": 10,
      "test_id": 1,
      "variant_id": 2,
      "user_id": 123,
      "type": "purchase",
      "source": "email",
      "revenue": 99.99,
      "currency": "EUR",
      "data": {...},
      "external_id": "ORDER-12345",
      "timestamp": "2025-10-24T10:30:00Z"
    }
  ],
  "pagination": {
    "total": 50,
    "per_page": 20,
    "current_page": 1
  }
}
```

---

## Usage Examples

### Email Subject A/B Test

```php
$testingService = new AbTestingService($tenantId);

// 1. Create test
$test = $testingService->createTest(
    name: 'Email Subject Line Test',
    testType: 'email',
    hypothesis: 'Adding emoji increases open rate',
    minimumSampleSize: 500,
    confidenceLevel: 0.95,
    durationDays: 7
);

// 2. Create variants
$control = $testingService->createVariant(
    testId: $test->id,
    name: 'Control: Plain Subject',
    trafficAllocation: 50,
    isControl: true,
    variantContent: ['subject' => 'New Update']
);

$variant = $testingService->createVariant(
    testId: $test->id,
    name: 'Variant: With Emoji',
    trafficAllocation: 50,
    isControl: false,
    variantContent: ['subject' => '🆕 New Update']
);

// 3. Start test
$testingService->startTest($test->id);

// 4. Send emails and track
$users = User::where('tenant_id', $tenantId)->limit(1000)->get();

foreach ($users as $user) {
    $assignedVariant = $testingService->assignVariant($test->id, $user->id);
    
    // Get variant subject
    $subject = $assignedVariant->variant_content['subject'];
    
    // Send email
    Mail::to($user)->queue(new WelcomeEmail($subject));
    
    // Record view
    $testingService->recordView($test->id, $assignedVariant->id, $user->id);
}

// 5. Track opens/clicks via email webhooks
// (Webhook: POST /api/webhooks/email-open)
$testingService->recordClick($test->id, $variantId, $userId);

// 6. After 7 days, analyze
$analytics = $testingService->getAnalytics($test->id);

if ($analytics['significance']['significant']) {
    $winner = $testingService->getWinner($test->id);
    $testingService->declareWinner($test->id, $winner->id);
}
```

### SMS Call-to-Action A/B Test

```php
// Test different CTA messages
$test = $testingService->createTest(
    name: 'SMS CTA Test',
    testType: 'sms',
    hypothesis: 'Shorter CTA increases click rate',
    minimumSampleSize: 200
);

$control = $testingService->createVariant(
    testId: $test->id,
    name: 'Control: Long CTA',
    trafficAllocation: 50,
    isControl: true,
    variantContent: [
        'message' => 'Join our event! Click here to register: https://myclub.at/event/123'
    ]
);

$variant = $testingService->createVariant(
    testId: $test->id,
    name: 'Variant: Short CTA',
    trafficAllocation: 50,
    isControl: false,
    variantContent: [
        'message' => 'Join us! Register: https://myclub.at/e/123'
    ]
);

$testingService->startTest($test->id);

// Send SMS and track clicks
// Track via link clicks (affiliate/UTM parameter)
```

### Landing Page A/B Test

```php
// Test different layouts
$test = $testingService->createTest(
    name: 'Landing Page Layout Test',
    testType: 'web',
    hypothesis: 'Hero image above fold increases signups',
    minimumSampleSize: 1000
);

// Variants with different content
$testingService->createVariant(
    testId: $test->id,
    name: 'Control: Standard',
    trafficAllocation: 50,
    isControl: true
);

$testingService->createVariant(
    testId: $test->id,
    name: 'Variant: Hero Above Fold',
    trafficAllocation: 50,
    isControl: false
);

// Frontend code:
$testId = request('test_id');
$userId = auth()->id();
$variant = $testingService->getVariantByIdentifier($testId, $userId);

// Render variant layout
@if($variant->slug === 'control')
    @include('landing.standard')
@elseif($variant->slug === 'hero-above-fold')
    @include('landing.hero')
@endif

// Track conversion
Route::post('/signup', function () {
    $testingService->recordConversionEvent(
        testId: request('test_id'),
        variantId: request('variant_id'),
        conversionType: 'signup',
        userId: auth()->id()
    );
});
```

---

## Analytics & Reporting

### Generate Report

```php
$test = AbTest::find($testId);
$analytics = $testingService->getAnalytics($testId);

$report = [
    'test_name' => $analytics['test_name'],
    'duration' => $test->started_at->diff($test->ended_at)->days . ' days',
    'variants' => $analytics['variants'],
    'winner' => $analytics['winner'],
    'significance' => $analytics['significance']['significant'] ? 'YES' : 'NO',
    'confidence' => $analytics['significance']['confidence_level'] * 100 . '%',
];

return view('report.ab-test', $report);
```

### Export to CSV

```php
$conversions = AbTestConversion::where('ab_test_id', $testId)->get();

$csv = "Variant,Type,Revenue,Date\n";
foreach ($conversions as $conv) {
    $csv .= "{$conv->variant->name},{$conv->conversion_type},{$conv->revenue/100},{$conv->created_at}\n";
}

return response()->streamDownload(
    fn() => print($csv),
    "test-$testId-conversions.csv"
);
```

---

## Best Practices

### 1. Plan Before Testing

Write hypothesis before starting:

```
❌ "Test if this works"
✅ "Adding emoji to subject line increases open rate by 10%"
```

### 2. Minimum Sample Size

Use calculator to estimate participants needed:

```
- Current conversion rate
- Desired improvement percentage
- Statistical power (typically 80%)
- Confidence level (typically 95%)

Result: Run test until reaching minimum
```

### 3. Test One Variable

Change only one element per test:

```
❌ Change subject + body + CTA
✅ Change only subject line
```

### 4. Avoid Peeking

Don't declare winner before sufficient data:

```
❌ "After 1 day, Variant A is winning 60/40"
✅ "After 14 days with 1000+ per variant, Variant A wins"
```

### 5. Statistical Significance Required

Only declare winner if:
- Chi-square > critical value
- P-value < 0.05 (95% confidence)
- Minimum sample size reached

### 6. Document Results

Keep test documentation:

```
Test: Email Subject A/B Test
Control: "New Update" (7.2% conversion)
Variant: "📰 New Update" (10.4% conversion)
Winner: Variant (44% improvement)
Confidence: 95% (p = 0.03)
Duration: 14 days
Participants: 2,000
```

---

## Troubleshooting

### Test shows no winner

**Cause**: Insufficient data or no statistical difference

**Solution**:
- Continue running test longer
- Increase traffic allocation to each variant
- Check if variants are actually different enough
- Review hypothesis for realism

### One variant gets all traffic

**Cause**: Traffic allocation not respected

**Solution**:
- Verify traffic_allocation values sum to ~100
- Check assignment logic in assignVariant()
- Ensure random number generation works correctly

### Conversions not recording

**Cause**: Events not being tracked

**Solution**:
- Verify webhook endpoints are receiving data
- Check recordConversionEvent() is being called
- Verify variant_id is passed correctly
- Review error logs

### Winner not declared

**Cause**: Test not statistically significant

**Solution**:
- Run test longer (need more data)
- Increase minimum_sample_size if too conservative
- Verify chi-square calculation is correct
- Check confidence_level threshold (0.95 is standard)

---

**Last Updated**: 2025-10-24  
**Version**: 1.0.0  
**Status**: Production Ready ✅
