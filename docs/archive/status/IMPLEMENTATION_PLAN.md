# 🎯 IMPLEMENTATION PLAN - SAAS BACKEND STRUKTUR

## PHASE 0: SETUP (JETZT)
✅ **Multi-Database System** aktivieren
✅ **Tenant Detection** via Subdomain
✅ **Middleware** für Tenant-Isolation

---

## PHASE 1: PLATFORM BACKEND (Host)
### Datenbank: `kp_club_management`

**Tabellen:**
```
✅ users               - Super Admin Users
✅ companies           - Registrierte Vereine
✅ subscriptions       - Abo-Verwaltung
✅ domains             - Subdomains
✅ tenants             - Tenant-Konfig (mit Database Name)
✅ support_tickets     - Support-System
✅ email_templates     - Email-Vorlagen
✅ settings            - Platform-Settings
```

**Models (App\Models\Platform):**
```
User                - Super Admin
Company             - Verein (Global)
Subscription        - Abo
Domain              - Domain
Tenant              - Tenant Config
SupportTicket       - Support
EmailTemplate       - Email
Setting             - Settings
```

**Controllers (App\Http\Controllers\Admin):**
```
DashboardController
- platform_analytics
- subscription_summary
- user_activity
- system_health

ClubManagementController
- list_all_clubs
- create_club
- edit_club
- delete_club
- activate/deactivate
- manage_subscription

UserManagementController
- list_super_admins
- create_admin
- edit_admin
- permissions
- audit_log

SubscriptionController
- view_plans
- manage_subscriptions
- billing
- invoices

SettingsController
- system_settings
- email_config
- smtp_settings
- branding
```

**Pages (App\Filament\Pages\Admin):**
```
- Dashboard
- Club Management
- User Management  
- Subscriptions
- Settings
- Support Tickets
- Analytics
```

---

## PHASE 2: TENANT BACKEND (Pro Verein)
### Datenbank: `kp_club_{tenant_id}`

**Tabellen:**
```
club_info           - Vereinsdaten
members             - Spieler/Trainer/Staff
teams               - Mannschaften
matches             - Spiele/Events
match_results       - Ergebnisse
sponsors            - Sponsoren
banners             - Banner/Werbung
gallery             - Fotos/Videos
website_settings    - Website-Config
users               - Club-User (Admin, Coach, Player)
roles               - Rollen/Permissions
notifications       - Benachrichtigungen
logs                - Audit-Log
```

**Models (App\Models\Core, etc.):**
```
// Core
Club                - Vereinsdaten
Member              - Spieler/Trainer/Staff
Team                - Mannschaften
Match               - Spiele
MatchResult         - Ergebnisse

// Marketing
Sponsor             - Sponsoren
Banner              - Banner/Werbung
Gallery             - Fotos

// System
WebsiteSetting      - Website-Config
User                - Club-User
Role                - Rollen
```

**Controllers (App\Http\Controllers\Tenant):**
```
DashboardController
- club_statistics
- recent_matches
- upcoming_events
- team_roster

MembersController
- list_members
- add_member
- edit_member
- assign_roles
- delete_member

TeamsController
- list_teams
- create_team
- edit_team
- manage_players
- statistics

MatchesController
- schedule_match
- record_result
- manage_lineup
- statistics

SponsorsController
- list_sponsors
- add_sponsor
- manage_banner
- sponsorship_details

SettingsController
- website_settings
- club_info
- users_management
- roles_permissions
```

**Pages (App\Filament\Pages\Tenant):**
```
- Dashboard
- Members Management
- Teams Management
- Matches Management
- Sponsors Management
- Website Settings
- Users & Roles
```

---

## PHASE 3: TENANT FRONTEND (Public Website)
### URL: `{tenant}.localhost:8000`

**Pages:**
```
- Landing / Homepage
- Team Roster (Spielerliste)
- Match Schedule (Spielplan)
- Gallery (Fotos/Videos)
- Sponsors (Sponsorenseite)
- News / Blog
- Contact
- About
```

**Features:**
```
- Responsive Design
- Match/Score Widget
- Team Stats
- Player Profiles
- Social Media Integration
- Contact Form
```

---

## 🔧 TECHNISCHE REQUIREMENTS

### Libraries & Packages
```bash
# Tenancy
composer require spatie/laravel-tenancy

# Admin UI
composer require filament/filament

# Database
composer require laravel/sanctum  (für API)

# Frontend
npm install vue@3
npm install tailwindcss

# Email
composer require symfony/mailer

# Payment (später)
composer require laravel/cashier
```

---

## 📊 NEUE DATEISTRUKTUR

```
/app
  /Models
    /Platform           ← Company, Subscription, etc.
    /Core               ← Club, Members, Teams
    /Marketing          ← Sponsors, Banners
    /System             ← Settings, Users
  
  /Http/Controllers
    /Admin              ← Platform Admin
    /Tenant             ← Club Admin
    /Frontend           ← Public Pages
    /Api                ← REST API (später)
  
  /Filament
    /Resources
      /Admin            ← Admin Resources
      /Tenant           ← Tenant Resources
    /Pages
      /Admin            ← Admin Pages
      /Tenant           ← Tenant Pages

/resources/views
  /platform             ← Admin Pages
  /tenant               ← Club Admin Pages
  /frontend             ← Public Website
  /layouts
    /admin.blade.php
    /tenant.blade.php
    /website.blade.php

/routes
  /web.php              ← Alle Routes (mit Tenancy)
  /api.php              ← API Routes
  /tenant.php           ← Tenant Routes (Middleware)

/database/migrations
  /host                 ← Platform Migrations
  /tenant               ← Tenant Migrations
```

---

## 🚀 START-STRATEGIE

### 1. Heute: Backend Setup
- [ ] Tenancy Middleware konfigurieren
- [ ] Tenant Detection aktivieren
- [ ] Database Switching testen

### 2. Morgen: Platform Admin
- [ ] Dashboard erstellen
- [ ] Club Management (CRUD)
- [ ] User Management

### 3. Übermorgen: Tenant Backend
- [ ] Members Management
- [ ] Teams/Matches
- [ ] Settings

### 4. Später: Frontend
- [ ] Public Website
- [ ] Mobile API

---

## 📝 NÄCHSTER BEFEHL

**Sollen wir jetzt starten?** Step-by-Step:

1. ✅ Tenancy Middleware Setup
2. ✅ Database Config für Multi-Tenant
3. ✅ Platform Admin Backend
4. ✅ Tenant Backend
5. ✅ Frontend

