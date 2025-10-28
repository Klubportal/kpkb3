# 🏛️ Club Portal Backend - Complete API Documentation

## Overview
Das Club Portal Backend ist ein umfassendes Management-System für Sportvereine mit Multi-Tenant-Unterstützung.

## Features

### 1. Club Management
- **Endpoint:** `/api/clubs`
- **Create/Update Club Settings:**
  - Farben (Primary, Secondary, Accent)
  - Fonts (Familie, Größe)
  - Logo & Favicon Upload
  - SEO Meta Tags

```bash
PUT /api/clubs/{clubId}/settings
Content-Type: application/json

{
  "primary_color": "#2563eb",
  "secondary_color": "#1e40af",
  "accent_color": "#dc2626",
  "font_family": "Inter, sans-serif",
  "font_size_base": 16,
  "font_size_heading": 32,
  "meta_title": "NK Prigorje",
  "meta_description": "Official website of NK Prigorje",
  "meta_keywords": "football, club, sports"
}
```

### 2. Sponsors Management
- **Endpoint:** `/api/clubs/{clubId}/sponsors`
- Features:
  - Logo & Banner Upload
  - Contract Management (Start/End Date)
  - Display Positions (Top, Middle, Bottom, Sidebar)
  - Pricing & Duration
  - Priority Ordering

```bash
POST /api/clubs/{clubId}/sponsors
{
  "name": "Nike",
  "website": "https://nike.com",
  "position": "top",
  "display_width": 300,
  "display_height": 200,
  "annual_fee": 5000,
  "contract_duration_months": 12,
  "contract_start_date": "2025-01-01",
  "contract_end_date": "2025-12-31"
}
```

### 3. Social Media Links
- **Endpoint:** `/api/clubs/{clubId}/social-links`
- Platforms: Facebook, Instagram, X, TikTok, YouTube, LinkedIn, Website
- Features:
  - Add/Edit Links
  - Reorder Links
  - Activate/Deactivate
  - Display Names

```bash
POST /api/clubs/{clubId}/social-links
{
  "platform": "facebook",
  "url": "https://facebook.com/nkprigorje",
  "display_name": "NK Prigorje",
  "is_active": true,
  "order": 0
}
```

### 4. PWA Notifications
- **Endpoint:** `/api/clubs/{clubId}/notifications`
- Send Messages to:
  - All Users
  - Specific Roles (Player, Coach, Admin, etc.)
  - Specific Users
- Types: Email, SMS, Push, In-App

```bash
POST /api/clubs/{clubId}/notifications
{
  "title": "Match Reminder",
  "message": "Tomorrow's match at 15:00",
  "type": "push",
  "recipient_roles": ["player", "coach"],
  "scheduled_at": "2025-10-24 14:00:00"
}
```

### 5. Email Widgets
- **Endpoint:** `/api/clubs/{clubId}/email-widgets`
- Send Bulk Emails to:
  - User Roles
  - Specific Groups
  - Individuals

```bash
POST /api/clubs/{clubId}/email-widgets
{
  "subject": "Training Schedule",
  "body": "New training schedule is available...",
  "recipient_roles": ["player", "coach", "parent"],
  "scheduled_at": null
}
```

### 6. SMS Widgets
- **Endpoint:** `/api/clubs/{clubId}/sms-widgets`
- Cost Management (7 cents per SMS)
- Recipient Filtering
- Cost Estimation

```bash
POST /api/clubs/{clubId}/sms-widgets
{
  "title": "Quick SMS",
  "message": "Match tomorrow at 15:00",
  "recipient_roles": ["player"],
  "cost_per_sms": 7
}

// Estimate cost
POST /api/clubs/{clubId}/sms-widgets/{widgetId}/estimate
```

### 7. Contact Form Management
- **Endpoint:** `/api/contact`
- Features:
  - Public Submissions
  - Reply Management
  - Status Tracking (new, read, replied, spam)
  - Spam Detection

```bash
// Submit form
POST /api/contact
{
  "name": "Max Müller",
  "email": "max@example.com",
  "subject": "Partnership",
  "message": "We'd like to sponsor..."
}

// Admin replies
POST /api/contact/clubs/{clubId}/submissions/{id}/reply
{
  "reply_message": "Thank you for your interest..."
}
```

## Database Schema

### Tables
```
clubs (bereits existierend - Tenant)
club_settings
sponsors
social_links
notifications
email_widgets
sms_widgets
contact_form_submissions
```

## Models

### Club
```php
- extended()
- competitions()
- players()
- matches()
- sponsors()
- socialLinks()
- notifications()
- emailWidgets()
- smsWidgets()
```

### ClubSettings
```php
Colors, Fonts, Images, SEO Meta Tags
Theme Management
```

### Sponsor
```php
- isContractActive()
- getDaysRemaining()
- scopeActive()
- scopeByPosition()
- scopeOrderedByPriority()
```

### SocialLink
```php
- getIconClass()
- scopeActive()
- scopeOrdered()
```

### Notification / EmailWidget / SmsWidget
```php
- send()
- scopeScheduled()
```

### ContactFormSubmission
```php
- reply()
- markAsRead()
- markAsSpam()
```

## API Response Format

### Success Response (201/200)
```json
{
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response (422/404/500)
```json
{
  "message": "Error message",
  "errors": {
    "field": ["Error message"]
  }
}
```

## File Uploads
- **Logos:** max 2MB (jpg, png)
- **Banners:** max 5MB (jpg, png)
- **Favicon:** max 512KB (jpg, png)
- Storage: `/storage/clubs/` / `/storage/sponsors/`

## Authentication
Alle API-Endpoints erfordern authentifizierte Anfragen mit Token oder Session.

## Package Tiers (für Erweiterung)
- **Basic:** Email, bis 50 Users, 5 Sponsors
- **Premium:** Email + SMS, bis 200 Users, 20 Sponsors, Analytics
- **Elite:** Email + SMS + PWA, Unlimited, Premium Support

## Next Steps
1. ✅ Models erstellt
2. ✅ API Routes definiert
3. ✅ Controllers implementiert
4. ⏳ Frontend Dashboard (Vue/React)
5. ⏳ Admin Panel (Filament)
6. ⏳ Email Templates
7. ⏳ Subscription Management
8. ⏳ Billing Integration
