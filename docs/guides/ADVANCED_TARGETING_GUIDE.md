# Advanced Targeting & Segmentation Guide

**Comprehensive user segmentation, engagement scoring, and targeting system**

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Installation & Setup](#installation--setup)
3. [Database Schema](#database-schema)
4. [User Segments](#user-segments)
5. [Targeting Rules](#targeting-rules)
6. [Engagement Scoring](#engagement-scoring)
7. [Targeting Audiences](#targeting-audiences)
8. [API Endpoints](#api-endpoints)
9. [Usage Examples](#usage-examples)
10. [Analytics & Reporting](#analytics--reporting)
11. [Best Practices](#best-practices)

---

## Architecture Overview

### System Flow

```
┌──────────────────────────────────────────────────────────────┐
│                    User Actions                              │
│  (login, email_open, sms_read, event_attendance, etc.)       │
└────────────┬─────────────────────────────────────────────────┘
             │
             ▼
    ┌─────────────────────┐
    │  Behavior Tracking  │
    │  (UserBehavior)     │
    └────────────┬────────┘
                 │
                 ▼
    ┌─────────────────────────┐
    │  Engagement Scoring     │
    │  (EngagementScore)      │
    │  - Email engagement     │
    │  - SMS engagement       │
    │  - App engagement       │
    │  - Event engagement     │
    │  - Total score (0-100)  │
    └────────────┬────────────┘
                 │
    ┌────────────┴────────────┐
    │                         │
    ▼                         ▼
┌──────────────────┐  ┌──────────────────────┐
│ User Segmentation│  │ Targeting Rules      │
│                  │  │                      │
│ Manual segments  │  │ Rules-based targeting│
│ - Team A         │  │ - Role = 'player'    │
│ - VIP members    │  │ - Location = 'Vienna'│
│ - Inactive users │  │ - Age > 18           │
│                  │  │ - Engagement >= 75   │
└────────┬─────────┘  └──────────┬───────────┘
         │                        │
         └────────────┬───────────┘
                      │
                      ▼
         ┌────────────────────────┐
         │ Targeting Audiences    │
         │ (Combined segments &   │
         │  rules for campaigns)  │
         └────────────┬───────────┘
                      │
    ┌─────────────────┼─────────────────┐
    │                 │                 │
    ▼                 ▼                 ▼
┌─────────┐    ┌─────────┐        ┌─────────┐
│ Email   │    │ SMS     │        │ Push    │
│Campaign │    │Campaign │        │Campaign │
└────┬────┘    └────┬────┘        └────┬────┘
     │              │                  │
     ▼              ▼                  ▼
┌──────────────────────────────────────────────┐
│ Targeting History                            │
│ (track performance of each targeting)        │
│ - target_count: 1000                         │
│ - sent_count: 950                            │
│ - delivered_count: 945                       │
│ - engagement_rate: 94.5%                     │
└──────────────────────────────────────────────┘
```

### Data Model

```
Users
  ├─ UserAttribute (location, role, interests)
  ├─ UserBehavior (actions for engagement)
  ├─ EngagementScore (calculated 0-100)
  ├─ UserLocation (geo-targeting)
  └─ UserSegmentMember (segment membership)
      └─ UserSegment (named segments)
           ├─ Manual: created by admin
           ├─ Automatic: rule-based
           └─ Rules-based: complex criteria

Targeting
  ├─ TargetingRule (rule definitions)
  ├─ TargetingAudience (combines segments/rules)
  ├─ TargetingCriteria (available criteria types)
  └─ TargetingHistory (performance audit trail)
```

---

## Installation & Setup

### Step 1: Run Migration

```bash
php artisan migrate
```

Creates tables:
- `user_segments` - Named user groups
- `user_segment_members` - Membership records
- `targeting_criteria` - Available criteria types
- `targeting_rules` - Rule definitions
- `user_attributes` - User metadata for targeting
- `user_behaviors` - User action tracking
- `engagement_scores` - Calculated engagement (0-100)
- `targeting_audiences` - Combined targeting definitions
- `targeting_history` - Performance tracking
- `user_locations` - Geo-location data

### Step 2: Seed Targeting Criteria

```php
// Create available targeting criteria
TargetingCriteria::create([
    'tenant_id' => $tenantId,
    'name' => 'Role',
    'slug' => 'role',
    'criterion_type' => 'role',
    'values' => ['player', 'coach', 'manager', 'admin'],
]);

TargetingCriteria::create([
    'tenant_id' => $tenantId,
    'name' => 'Location',
    'slug' => 'location',
    'criterion_type' => 'location',
    'values' => ['Vienna', 'Graz', 'Salzburg', 'Linz'],
]);

TargetingCriteria::create([
    'tenant_id' => $tenantId,
    'name' => 'Membership Status',
    'slug' => 'membership_status',
    'criterion_type' => 'membership_status',
    'values' => ['active', 'inactive', 'trial', 'cancelled'],
]);

TargetingCriteria::create([
    'tenant_id' => $tenantId,
    'name' => 'Activity Level',
    'slug' => 'activity_level',
    'criterion_type' => 'activity_level',
    'values' => ['active', 'moderate', 'inactive'],
]);
```

### Step 3: Track User Behavior

Whenever users perform actions, record them:

```php
// In a controller or listener
$targetingService = new TargetingService($tenantId);

$targetingService->recordBehavior(
    userId: auth()->id(),
    eventType: 'login',
    source: 'app',
    eventData: ['device' => 'mobile'],
    points: 5
);
```

---

## Database Schema

### `user_segments`
```sql
CREATE TABLE user_segments (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  name varchar(255),
  slug varchar(255) UNIQUE,
  description text,
  type varchar(50),                -- manual, automatic, rules-based
  criteria_rules json,             -- For automatic/rules-based
  member_count int DEFAULT 0,
  is_active boolean DEFAULT true,
  timestamps
);
```

### `user_attributes`
```sql
CREATE TABLE user_attributes (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  user_id bigint UNIQUE,
  location varchar(255),           -- City/region
  role varchar(50),                -- player, coach, manager, admin
  team_id varchar(255),
  age int,
  activity_level varchar(50),      -- active, moderate, inactive
  membership_status varchar(50),   -- active, inactive, trial, cancelled
  lifetime_value decimal(10,2),
  engagement_score int,            -- 0-100
  last_activity_at timestamp,
  joined_at timestamp,
  interests json,                  -- ["football", "coaching"]
  preferences json,
  timestamps
);
```

### `engagement_scores`
```sql
CREATE TABLE engagement_scores (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  user_id bigint UNIQUE,
  score int DEFAULT 0,             -- 0-100 total
  email_engagement int DEFAULT 0,  -- Opens, clicks, etc.
  sms_engagement int DEFAULT 0,    -- Reads, responses
  app_engagement int DEFAULT 0,    -- Logins, interactions
  event_engagement int DEFAULT 0,  -- Attendance, participation
  calculated_at timestamp,
  timestamps
);
```

### `targeting_rules`
```sql
CREATE TABLE targeting_rules (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  created_by bigint,
  name varchar(255),
  slug varchar(255) UNIQUE,
  description text,
  rules json,                      -- [{"field":"role","operator":"=","value":"player"}]
  matching_count int DEFAULT 0,
  is_active boolean DEFAULT true,
  timestamps
);
```

### `targeting_audiences`
```sql
CREATE TABLE targeting_audiences (
  id bigint PRIMARY KEY,
  tenant_id bigint,
  name varchar(255),
  slug varchar(255) UNIQUE,
  description text,
  audience_type varchar(50),       -- segment, rule, custom
  source_segment_id bigint,        -- If type = segment
  source_rule_id bigint,           -- If type = rule
  audience_members json,           -- If type = custom
  member_count int DEFAULT 0,
  usage_count int DEFAULT 0,
  is_active boolean DEFAULT true,
  last_updated_at timestamp,
  timestamps
);
```

---

## User Segments

### Create Manual Segment

```php
$targetingService = new TargetingService($tenantId);

$segment = $targetingService->createSegment(
    name: 'VIP Members',
    type: 'manual',
    description: 'High-value paying members'
);
```

### Add Users to Segment

```php
// Add single user
$targetingService->addUserToSegment($segmentId, $userId);

// Add multiple users
foreach ($userIds as $userId) {
    $targetingService->addUserToSegment($segmentId, $userId);
}
```

### Remove Users from Segment

```php
$targetingService->removeUserFromSegment($segmentId, $userId);
```

### Create Automatic Segment

```php
$segment = $targetingService->createSegment(
    name: 'High Engagement Players',
    type: 'automatic',
    criteriaRules: [
        ['field' => 'role', 'operator' => '=', 'value' => 'player'],
        ['field' => 'engagement_score', 'operator' => '>=', 'value' => 75],
    ]
);
```

---

## Targeting Rules

### Create Targeting Rule

```php
$rule = $targetingService->createTargetingRule(
    name: 'Vienna-based Coaches',
    rules: [
        [
            'field' => 'location',
            'operator' => '=',
            'value' => 'Vienna'
        ],
        [
            'field' => 'role',
            'operator' => '=',
            'value' => 'coach'
        ],
    ],
    createdBy: auth()->id(),
    description: 'All coaches based in Vienna'
);
```

### Rule Operators

- `=` - Equals
- `!=` - Not equals
- `>` - Greater than
- `<` - Less than
- `>=` - Greater than or equal
- `<=` - Less than or equal
- `in` - In array
- `not_in` - Not in array
- `contains` - String contains
- `not_contains` - String not contains

### Get Users Matching Rule

```php
$userIds = $targetingService->getUsersMatchingRule($ruleId);
// Returns: [1, 2, 3, 5, 8]

$count = count($userIds);
// Returns: 5
```

---

## Engagement Scoring

### Automatic Engagement Score Calculation

Engagement score is calculated from user behaviors (0-100):

```
Score = (Email × 0.25) + (SMS × 0.25) + (App × 0.30) + (Event × 0.20)

Examples:
- Email opens/clicks: 10 points
- SMS reads/responses: 10 points
- App logins/interactions: 15 points
- Event attendance: 20 points
```

### Record User Behavior

```php
// Track email open
$targetingService->recordBehavior(
    userId: $userId,
    eventType: 'email_open',
    source: 'email',
    eventData: ['email_id' => 123, 'campaign_id' => 456],
    points: 10
);

// Track SMS read
$targetingService->recordBehavior(
    userId: $userId,
    eventType: 'sms_read',
    source: 'sms',
    points: 10
);

// Track app login
$targetingService->recordBehavior(
    userId: $userId,
    eventType: 'login',
    source: 'app',
    eventData: ['ip' => '192.168.1.1'],
    points: 5
);

// Track event attendance
$targetingService->recordBehavior(
    userId: $userId,
    eventType: 'event_attendance',
    source: 'app',
    eventData: ['event_id' => 789],
    points: 20
);
```

### Get Engagement Score

```php
$score = $targetingService->getEngagementScore($userId);
// Returns: 75 (0-100)

// Classify
if ($score >= 75) {
    echo "High engagement";
} elseif ($score >= 50) {
    echo "Medium engagement";
} else {
    echo "Low engagement";
}
```

### Get High/Low Engagement Users

```php
// High engagement (>=75)
$activeUsers = $targetingService->getHighEngagementUsers(minScore: 75);

// Low engagement (<25)
$atRiskUsers = $targetingService->getLowEngagementUsers(maxScore: 25);
```

---

## Targeting Audiences

### Create Audience from Segment

```php
$audience = $targetingService->createAudience(
    name: 'VIP Campaign Audience',
    type: 'segment',
    sourceSegmentId: $segmentId
);
```

### Create Audience from Rule

```php
$audience = $targetingService->createAudience(
    name: 'Vienna Coaches Audience',
    type: 'rule',
    sourceRuleId: $ruleId
);
```

### Create Custom Audience

```php
$audience = $targetingService->createAudience(
    name: 'Selected Players',
    type: 'custom',
    customMembers: [1, 5, 7, 12, 15] // User IDs
);
```

### Get Audience Members

```php
$memberIds = $audience->getMemberIds();
// Returns: [1, 5, 7, 12, 15]

$count = $audience->member_count;
// Returns: 5
```

---

## API Endpoints

### Create User Segment

```
POST /api/targeting/segments
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "VIP Members",
  "type": "manual",
  "description": "High-value members",
  "criteria_rules": []
}

Response 201:
{
  "data": {
    "segment_id": 1,
    "name": "VIP Members",
    "slug": "vip-members",
    "type": "manual"
  }
}
```

### List User Segments

```
GET /api/targeting/segments
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "id": 1,
      "name": "VIP Members",
      "type": "manual",
      "member_count": 50
    },
    {
      "id": 2,
      "name": "Players",
      "type": "automatic",
      "member_count": 120
    }
  ],
  "total": 2
}
```

### Get Segment Details

```
GET /api/targeting/segments/{segmentId}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "id": 1,
    "name": "VIP Members",
    "type": "manual",
    "description": "High-value members",
    "member_count": 50,
    "is_active": true,
    "created_at": "2025-10-24T10:00:00Z"
  }
}
```

### Add User to Segment

```
POST /api/targeting/segments/{segmentId}/users
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 123
}

Response 200:
{
  "message": "User added to segment",
  "segment_id": 1,
  "user_id": 123
}
```

### Remove User from Segment

```
DELETE /api/targeting/segments/{segmentId}/users/{userId}
Authorization: Bearer {token}

Response 200:
{
  "message": "User removed from segment"
}
```

### Create Targeting Rule

```
POST /api/targeting/rules
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Vienna-based Coaches",
  "description": "All coaches in Vienna",
  "rules": [
    {
      "field": "location",
      "operator": "=",
      "value": "Vienna"
    },
    {
      "field": "role",
      "operator": "=",
      "value": "coach"
    }
  ]
}

Response 201:
{
  "data": {
    "rule_id": 1,
    "name": "Vienna-based Coaches",
    "matching_count": 15
  }
}
```

### Get Targeting Rule

```
GET /api/targeting/rules/{ruleId}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "id": 1,
    "name": "Vienna-based Coaches",
    "description": "All coaches in Vienna",
    "rules": [...],
    "matching_count": 15,
    "is_active": true
  }
}
```

### Get Users Matching Rule

```
GET /api/targeting/rules/{ruleId}/users
Authorization: Bearer {token}

Response 200:
{
  "data": [1, 3, 5, 7, 9, 12],
  "count": 6
}
```

### Create Targeting Audience

```
POST /api/targeting/audiences
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Campaign Audience",
  "audience_type": "segment",
  "source_segment_id": 1
}

Response 201:
{
  "data": {
    "audience_id": 1,
    "name": "Campaign Audience",
    "type": "segment",
    "member_count": 50
  }
}
```

### List Targeting Audiences

```
GET /api/targeting/audiences
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "id": 1,
      "name": "Campaign Audience",
      "type": "segment",
      "member_count": 50
    }
  ],
  "total": 1
}
```

### Get User Engagement Score

```
GET /api/targeting/engagement/{userId}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "user_id": 123,
    "engagement_score": 75,
    "level": "high"
  }
}
```

### Get High Engagement Users

```
GET /api/targeting/users/high-engagement?minScore=75
Authorization: Bearer {token}

Response 200:
{
  "data": [1, 3, 5, 7, 9],
  "count": 5
}
```

### Get Targeting Analytics

```
GET /api/targeting/analytics?days=30
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "total_campaigns": 5,
    "total_targets": 250,
    "total_sent": 240,
    "total_delivered": 235,
    "total_opened": 125,
    "total_clicked": 45,
    "average_engagement_rate": 98.33,
    "by_type": {
      "email": {"count": 2, "targets": 100, "delivered": 98},
      "sms": {"count": 2, "targets": 100, "delivered": 99},
      "push": {"count": 1, "targets": 50, "delivered": 38}
    }
  }
}
```

---

## Usage Examples

### Create Email Campaign Targeting

```php
$targetingService = new TargetingService($tenantId);

// 1. Create rule for target audience
$rule = $targetingService->createTargetingRule(
    name: 'High-value Vienna Players',
    rules: [
        ['field' => 'location', 'operator' => '=', 'value' => 'Vienna'],
        ['field' => 'role', 'operator' => '=', 'value' => 'player'],
        ['field' => 'lifetime_value', 'operator' => '>=', 'value' => 1000],
        ['field' => 'engagement_score', 'operator' => '>=', 'value' => 75],
    ],
    createdBy: auth()->id()
);

// 2. Create audience
$audience = $targetingService->createAudience(
    name: 'VIP Email Campaign',
    type: 'rule',
    sourceRuleId: $rule->id
);

// 3. Get targeting audience members
$targetUsers = $audience->getMemberIds();

// 4. Send campaign
$emailService = new EmailService($tenantId);
foreach ($targetUsers as $userId) {
    $emailService->sendEmail($userId, 'special_offer_template');
}

// 5. Record targeting history
$history = $targetingService->recordTargetingHistory(
    campaignType: 'email',
    targetCount: count($targetUsers),
    campaignId: $campaign->id,
    ruleId: $rule->id,
    targetingCriteria: $rule->rules
);
```

### Track Campaign Performance

```php
// After campaign
$targetingService->updateTargetingHistory(
    historyId: $history->id,
    sentCount: 100,
    deliveredCount: 95,
    openedCount: 45,
    clickedCount: 15
);

// Get analytics
$analytics = $targetingService->getTargetingAnalytics(days: 30);

echo "Average engagement rate: " . $analytics['average_engagement_rate'] . "%";
```

### Segment by Multiple Criteria

```php
$targetingService = new TargetingService($tenantId);

// Get users matching multiple conditions
$activeCoaches = $targetingService->getUsersMatchingRule(
    $rule->id // Rule with role=coach AND location=Vienna AND engagement>=75
);

// Or using service methods directly
$coaches = $targetingService->getUsersByRole('coach');
$viennaUsers = $targetingService->getUsersByLocation('Vienna');
$highEngagement = $targetingService->getHighEngagementUsers(minScore: 75);

// Manual intersection
$targetUsers = array_intersect($coaches, $viennaUsers, $highEngagement);
```

---

## Analytics & Reporting

### Campaign Performance

```php
$analytics = $targetingService->getTargetingAnalytics(days: 30);

// Breakdown by campaign type
foreach ($analytics['by_type'] as $type => $data) {
    echo "$type: " . $data['count'] . " campaigns";
    echo "Targets: " . $data['targets'];
    echo "Delivery rate: " . round(($data['delivered'] / $data['targets']) * 100, 2) . "%";
}
```

### Engagement Trends

```php
// High engagement users over time
for ($days = 30; $days > 0; $days -= 10) {
    $users = $targetingService->getHighEngagementUsers(minScore: 75);
    echo "$days days ago: " . count($users) . " high-engagement users\n";
}
```

---

## Best Practices

### 1. Regular Engagement Recalculation

Schedule daily engagement score updates:

```php
// In Kernel.php
$schedule->command('targeting:recalculate-engagement')
    ->daily()
    ->at('02:00'); // Off-peak time
```

### 2. Archive Old Targeting History

Clean up old performance data:

```php
TargetingHistory::where('created_at', '<', now()->subMonths(6))
    ->delete();
```

### 3. Validate Rules Before Use

```php
$rule->updateMatchingCount();
if ($rule->matching_count === 0) {
    throw new Exception("Rule matches no users");
}
```

### 4. Monitor Engagement Score Distribution

Check if engagement scores are working as expected:

```php
$distribution = EngagementScore::where('tenant_id', $tenantId)
    ->selectRaw('CASE
        WHEN score >= 75 THEN "high"
        WHEN score >= 50 THEN "medium"
        ELSE "low"
    END as level')
    ->groupBy('level')
    ->count();
```

### 5. A/B Test Different Segments

Send campaigns to different segments and compare performance:

```
Segment A: High engagement (>=75)
Segment B: Medium engagement (50-74)
Segment C: Low engagement (<50)

Compare open rates, click rates, and conversions
```

---

**Last Updated**: 2025-10-24  
**Version**: 1.0.0  
**Status**: Production Ready ✅
