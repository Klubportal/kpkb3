# 🎯 Club Portal Backend - Implementation Summary

## ✅ Was wurde erstellt?

### 1. **Backend-Struktur**
```
app/Models/
├── Club (erweitert mit Portal-Features)
├── ClubSettings (Farben, Fonts, Logo, SEO)
├── Sponsor (mit Upload & Contract Management)
├── SocialLink (Facebook, Instagram, X, etc.)
├── Notification (PWA Messages)
├── EmailWidget (Bulk Email)
├── SmsWidget (SMS Versand mit Kostenberechnung)
└── ContactFormSubmission (Kontaktformular Management)

app/Http/Controllers/Api/
├── ClubPortalController
├── SponsorPortalController
├── SocialLinkController
├── NotificationController
├── EmailWidgetController
├── SmsWidgetController
└── ContactFormController
```

### 2. **API Routes** (`routes/api.php`)
```
/api/clubs                          - Club Management
/api/clubs/{clubId}/sponsors        - Sponsor Management
/api/clubs/{clubId}/social-links    - Social Media Links
/api/clubs/{clubId}/notifications   - PWA Notifications
/api/clubs/{clubId}/email-widgets   - Email Versand
/api/clubs/{clubId}/sms-widgets     - SMS Versand
/api/contact                        - Kontaktformular
```

### 3. **Features pro Bereich**

#### 🏢 Club Settings
- ✅ Farben (Primary, Secondary, Accent)
- ✅ Fonts (Familie, Größe)
- ✅ Logo & Favicon Upload
- ✅ SEO Meta Tags (Title, Description, Keywords, OG Image)
- ✅ Theme Export für Frontend

#### 📢 Sponsors
- ✅ Logo & Banner Upload
- ✅ Positionen (Top, Middle, Bottom, Sidebar)
- ✅ Größen-Management (Width x Height)
- ✅ Contract Management (Start/End Datum)
- ✅ Jahresgebühr & Duration
- ✅ Priority Ordering
- ✅ Status Tracking (Active, Inactive, Expired)

#### 📱 Social Links
- ✅ Plattformen: Facebook, Instagram, X, TikTok, YouTube, LinkedIn, Website
- ✅ Add/Edit/Delete Links
- ✅ Aktivieren/Deaktivieren
- ✅ Custom Display Names
- ✅ Reordering

#### 🔔 PWA Notifications
- ✅ Typen: Email, SMS, Push, In-App
- ✅ Recipient Filtering:
  - Nach Role (Admin, Manager, Coach, Player, Parent, Fan)
  - Nach User IDs (Spezifische Personen)
- ✅ Scheduling (Sofort oder zeitgesteuert)
- ✅ Draft/Scheduled/Sent Status
- ✅ Sent Count Tracking

#### 📧 Email Widgets
- ✅ Bulk Email Versand
- ✅ HTML/Text Body
- ✅ Recipient Filtering (Roles + User IDs)
- ✅ Scheduling
- ✅ Sent Tracking

#### 📲 SMS Widgets
- ✅ SMS Versand
- ✅ Kostenberechnung (7 Cent pro SMS)
- ✅ Cost Estimation
- ✅ Recipient Filtering
- ✅ Delivery Tracking

#### 📝 Contact Form
- ✅ Public Submission
- ✅ Admin Dashboard (List all submissions)
- ✅ Reply Management
- ✅ Status Tracking (New, Read, Replied, Spam)
- ✅ Spam Detection (optional)

## 📊 Datenbank Schema

Tabellen (vereinfacht - ohne Foreign Keys zu Central Clubs):

```sql
-- Club Settings
club_settings:
  - club_id (unique)
  - primary_color, secondary_color, accent_color
  - font_family, font_size_base, font_size_heading
  - logo_url, favicon_url, hero_image_url
  - meta_title, meta_description, meta_keywords, meta_og_image

-- Sponsors
sponsors:
  - club_id
  - name, description
  - logo_url, banner_url, website
  - position (top/middle/bottom/sidebar)
  - display_width, display_height
  - annual_fee, contract_duration_months
  - contract_start_date, contract_end_date
  - status (active/inactive/expired)
  - display_priority

-- Social Links
social_links:
  - club_id, platform
  - url, display_name
  - is_active, order

-- Notifications
notifications:
  - club_id, title, message
  - type (email/sms/push/in_app)
  - recipient_roles (JSON array)
  - recipient_user_ids (JSON array)
  - scheduled_at, sent_at
  - status (draft/scheduled/sent/failed)
  - sent_count

-- Email Widgets
email_widgets:
  - club_id, subject, body
  - recipient_roles, recipient_user_ids (JSON)
  - scheduled_at, sent_at
  - status, sent_count

-- SMS Widgets
sms_widgets:
  - club_id, title, message
  - recipient_roles, recipient_user_ids (JSON)
  - scheduled_at, sent_at
  - status, sent_count
  - cost_per_sms

-- Contact Form Submissions
contact_form_submissions:
  - club_id (nullable - für allgemeine Kontakte)
  - name, email, phone
  - subject, message
  - ip_address
  - status (new/read/replied/spam)
  - reply_message, replied_at
```

## 🚀 API Beispiele

### Club Theme abrufen
```bash
curl -X GET http://localhost/api/clubs/598/theme
```

### Sponsor erstellen
```bash
curl -X POST http://localhost/api/clubs/598/sponsors \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Nike",
    "position": "top",
    "annual_fee": 5000,
    "contract_start_date": "2025-01-01",
    "contract_end_date": "2025-12-31"
  }'
```

### Social Link hinzufügen
```bash
curl -X POST http://localhost/api/clubs/598/social-links \
  -H "Content-Type: application/json" \
  -d '{
    "platform": "facebook",
    "url": "https://facebook.com/nkprigorje",
    "display_name": "NK Prigorje"
  }'
```

### Notification senden
```bash
curl -X POST http://localhost/api/clubs/598/notifications \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Training morgen!",
    "message": "Training um 19:00 Uhr",
    "type": "push",
    "recipient_roles": ["player", "coach"]
  }'
```

### SMS verschicken
```bash
curl -X POST http://localhost/api/clubs/598/sms-widgets \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Match Info",
    "message": "Morgen Spiel um 15:00",
    "recipient_roles": ["player"]
  }'

# Kosten schätzen
curl -X POST http://localhost/api/clubs/598/sms-widgets/1/estimate
```

### Kontaktformular absenden
```bash
curl -X POST http://localhost/api/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Max Müller",
    "email": "max@example.com",
    "subject": "Sponsorship",
    "message": "Wir möchten NK Prigorje sponsern..."
  }'
```

## 📁 Projekt-Struktur

```
kp_club_management/
├── app/
│   ├── Models/
│   │   ├── Club.php (erweitert)
│   │   ├── ClubSettings.php
│   │   ├── Sponsor.php (erweitert)
│   │   ├── SocialLink.php
│   │   ├── Notification.php
│   │   ├── EmailWidget.php
│   │   ├── SmsWidget.php
│   │   └── ContactFormSubmission.php
│   ├── Http/Controllers/Api/
│   │   ├── ClubPortalController.php
│   │   ├── SponsorPortalController.php
│   │   ├── SocialLinkController.php
│   │   ├── NotificationController.php
│   │   ├── EmailWidgetController.php
│   │   ├── SmsWidgetController.php
│   │   └── ContactFormController.php
│   └── Providers/
│       └── AppServiceProvider.php (routes/api.php geladen)
├── routes/
│   ├── api.php (NEW - Club Portal Routes)
│   ├── web.php
│   └── tenant.php
├── database/migrations/
│   ├── 2025_10_23_club_management_setup.php
│   ├── 2025_10_23_club_design_and_sponsors.php
│   └── 2025_10_23_communications_and_widgets.php
├── resources/js/
│   ├── components/
│   ├── stores/
│   └── services/
├── API_DOCUMENTATION.md (NEW)
├── FRONTEND_SETUP.md (NEW)
└── README.md (this file)
```

## 🔧 Installation

1. **Models & Controllers sind bereits erstellt**
2. **API Routes sind definiert**
3. **AppServiceProvider laden die Routes**

### Testen mit Tinker
```bash
# Club Settings abrufen
php artisan tinker
> $club = Club::find(598);
> $club->primary_color;

# Sponsor erstellen
> Sponsor::create(['club_id' => 598, 'name' => 'Nike', ...]);

# Social Link hinzufügen
> SocialLink::create(['club_id' => 598, 'platform' => 'facebook', ...]);
```

## 📈 Package Tiers (erweiterbar)

### Basic (kostenlos)
- ✅ Club Settings
- ✅ Social Links
- ❌ Sponsors
- ❌ Notifications
- ❌ SMS

### Premium
- ✅ Alles aus Basic
- ✅ Sponsors (5 Stück)
- ✅ Email Widgets
- ✅ Contact Form
- ❌ SMS
- ❌ PWA Notifications

### Elite
- ✅ Alles aus Premium
- ✅ Sponsors (20 Stück)
- ✅ SMS Widgets
- ✅ PWA Notifications
- ✅ Advanced Analytics

## 🎨 Frontend-Integration

Siehe `FRONTEND_SETUP.md` für:
- Vue 3 Component Struktur
- Pinia Store Setup
- API Service Layer
- Beispiel-Komponenten

## 📝 Nächste Schritte

1. **Admin Dashboard** (Filament Integration)
   - Club Settings Editor
   - Sponsor Manager
   - Notification Center
   - Contact Form Admin

2. **Frontend Portal** (Public Website)
   - Theme anwenden
   - Sponsors anzeigen
   - Social Links anzeigen
   - Contact Form

3. **Email Templates**
   - Registration Confirmation
   - Renewal Reminders
   - Newsletter

4. **Subscription Management**
   - Package Selection
   - Payment Gateway Integration
   - Auto-renewal

5. **SMS Integration**
   - SMS Provider (Twillio, Vonage, etc.)
   - Cost Tracking
   - Delivery Reports

## 🔐 Security

- ✅ Validierung auf allen Endpoints
- ✅ File Upload Restrictions
- ✅ CORS ready
- ⏳ Role-based Access Control (RBAC)
- ⏳ Rate Limiting
- ⏳ CSRF Protection

## 📞 Support

Für Fragen oder Erweiterungen:
- Dokumentation: `API_DOCUMENTATION.md`
- Frontend Setup: `FRONTEND_SETUP.md`

---

**Status:** ✅ Backend-Struktur fertig | ⏳ Frontend pending | ⏳ Admin Panel pending
