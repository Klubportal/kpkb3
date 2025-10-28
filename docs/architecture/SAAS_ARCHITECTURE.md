# 🏗️ SAAS MULTI-TENANCY ARCHITEKTUR - FUSSBALL VEREIN MANAGEMENT

## 📌 SYSTEM ÜBERSICHT

```
HAUPTDOMAIN (localhost:8000 / saas.example.com)
├── PUBLIC AREA
│   ├── Landing Page
│   ├── Features
│   ├── Pricing
│   ├── Blog
│   └── Contact
│
└── ADMIN BACKEND (nur Super Admin)
    ├── Dashboard (Platform Überblick)
    ├── Vereine Management
    ├── User Management
    ├── Subscriptions & Billing
    ├── Support/Tickets
    └── System Settings

───────────────────────────────────────────────────

VEREIN SUBDOMAINS (pro-verein.saas.example.com)
├── FRONTEND (Public Website)
│   ├── Homepage
│   ├── Team Roster
│   ├── Match Schedule
│   ├── Gallery
│   ├── Sponsors
│   ├── News/Blog
│   └── Contact
│
└── BACKEND (Club Admin & Coach)
    ├── Dashboard (Verein-Statistiken)
    ├── Members Management (Spieler, Trainer, Staff)
    ├── Teams Management
    ├── Matches/Events
    ├── Sponsors Management
    ├── Website Settings
    ├── Gallery/Media
    └── Users & Roles
```

---

## 🗄️ DATENBANK-STRUKTUR

### HAUPTDATENBANK: `kp_club_management` (Host)
**Enthält:** Platform-Daten, globale User, Subscriptions

```
TABELLEN:
├── users              ← Super Admins (Platform)
├── companies          ← Verein-Registrierungen
├── subscriptions      ← Abo-Details, Billing
├── domains            ← Subdomains pro Verein
├── tenants            ← Tenant-Konfiguration
├── support_tickets    ← Support-System
├── platform_analytics ← Platform-Analytics
├── email_templates    ← Email-Vorlagen
└── settings           ← System-Einstellungen
```

### TENANT-DATENBANKEN: `kp_club_{verein_id}` (Tenant)
**Pro Verein eine eigene Datenbank!** Vollständig isoliert.

```
TABELLEN PRO VEREIN:
├── club_info          ← Vereinsdaten
├── members            ← Spieler, Trainer, Staff
├── teams              ← Mannschaften
├── matches            ← Spiele/Events
├── match_results      ← Ergebnisse, Statistiken
├── sponsors           ← Sponsoren
├── banners            ← Werbebanner
├── gallery            ← Fotos/Videos
├── website_settings   ← Verein-Website-Config
├── users              ← Club-User (Admin, Coach, etc.)
├── roles              ← Rollen pro Verein
└── ...
```

---

## 🌐 DOMAIN-STRUKTUR

```
HAUPTDOMAIN:
├── localhost:8000                    ← Frontend (Landing Page)
├── localhost:8000/admin              ← Backend (Super Admin)
├── localhost:8000/auth/login         ← Login (Platform)
└── localhost:8000/auth/register      ← Registrierung

TENANT SUBDOMAINS:
├── bvb.localhost:8000                ← Frontend (BVB Website)
├── bvb.localhost:8000/admin          ← Backend (BVB Admin)
├── bvb.localhost:8000/auth/login     ← Login (BVB)
│
├── bayern.localhost:8000             ← Frontend (Bayern Website)
├── bayern.localhost:8000/admin       ← Backend (Bayern Admin)
└── bayern.localhost:8000/auth/login  ← Login (Bayern)
```

---

## 👥 USER-ROLLEN & PERMISSIONS

### 1. PLATFORM LEVEL (Main Database)
```
SUPER ADMIN (kp_club_management)
├── View all clubs
├── Manage subscriptions
├── View analytics
├── Support management
└── System settings
```

### 2. TENANT LEVEL (Tenant Database)
```
CLUB ADMIN (Verein-Chef)
├── Manage all club data
├── Manage members & roles
├── Manage teams & matches
├── View analytics
└── Manage website & sponsors

COACH (Trainer)
├── View team roster
├── Manage match lineups
├── View statistics
└── Report match results

PLAYER (Spieler)
├── View own profile
├── View team schedule
└── View team statistics

STAFF (Mitarbeiter)
├── Manage gallery
├── Manage sponsors
├── Manage news/blog
└── Website management
```

---

## 🔄 WORKFLOW BEI REGISTRIERUNG

```
1. Verein registriert sich auf hauptdomain.com
   ├── Email: admin@bvb.de
   ├── Verein: Borussia Dortmund
   ├── Wählt: Subdomain = "bvb"
   └── Wählt: Abo-Plan

2. System erstellt automatisch:
   ├── Domain-Eintrag: bvb.hauptdomain.com
   ├── Neue Datenbank: kp_club_bvb
   ├── Tenant-Konfiguration
   └── Admin-Benutzer für Verein

3. Email an admin@bvb.de:
   ├── "Willkommen bei Fussball-Manager"
   ├── Link: https://bvb.hauptdomain.com
   ├── Backend-Link: https://bvb.hauptdomain.com/admin
   └── Login-Daten

4. Admin loggt sich ein:
   ├── Sieht sein Backend
   ├── Kann Spieler hinzufügen
   ├── Kann Website anpassen
   └── Sieht nur seine Daten
```

---

## 🛠️ TECHNISCHE IMPLEMENTIERUNG

### Laravel Tenancy Library
```
Nutzen: Spatie/Laravel-Tenancy
├── Automatic Tenant Detection (via Domain/Subdomain)
├── Middleware für Tenant-Isolation
├── Database Switching Pro Request
└── Tenant-Specific Cache
```

### Middleware-Stack
```
Request kommt an: bvb.localhost:8000/admin
    ↓
Middleware: DetectTenant
    ├── Extrahiert: "bvb" aus Subdomain
    ├── Findet: Tenant ID
    └── Switched zu: Database kp_club_bvb
    ↓
Request verarbeitet mit Tenant-Datenbank
    ↓
Response gesendet
```

---

## 📁 PROJECT-STRUKTUR

```
/app
  /Http
    /Controllers
      /Admin              ← Platform Admin Pages
      /Tenant             ← Tenant (Verein) Pages
      /Auth               ← Authentication
      /Api                ← REST API
  /Models
    /Platform             ← Subscriptions, Users, etc.
    /Core                 ← Club, Members, Teams
    /Marketing            ← Sponsors, Banners
    /Integration          ← Comet, etc.
    /System               ← Settings, etc.
  /Filament              
    /Resources            ← Admin Resources
    /Pages                ← Admin Pages

/resources/views
  /layouts
    /platform.blade.php   ← Main Admin Layout
    /tenant.blade.php     ← Verein Backend Layout
    /frontend.blade.php   ← Public Website Layout
  /platform              ← Platform Pages
  /tenant                ← Tenant Pages
  /frontend              ← Public Pages

/routes
  /web.php               ← Alle Web Routes (mit Tenancy Middleware)
  /api.php               ← API Routes
  /tenant.php            ← Tenant-Only Routes

/config
  /tenancy.php           ← Tenancy Konfiguration
```

---

## 🚀 IMPLEMENTIERUNGS-PHASEN

### PHASE 1: PLATFORM SETUP ✅
- [x] Multi-Database Support
- [x] Tenant Detection
- [x] Domain/Subdomain Routing
- [x] Authentication

### PHASE 2: PLATFORM BACKEND (In Arbeit)
- [ ] Dashboard
- [ ] Club Management
- [ ] Subscription Management
- [ ] User Management
- [ ] Support System

### PHASE 3: TENANT BACKEND
- [ ] Club Admin Dashboard
- [ ] Members Management
- [ ] Teams Management
- [ ] Match Management
- [ ] Settings

### PHASE 4: TENANT FRONTEND
- [ ] Public Website
- [ ] Team Roster
- [ ] Match Schedule
- [ ] Gallery
- [ ] Sponsors Page

### PHASE 5: API & MOBILE
- [ ] REST API
- [ ] Mobile App (React Native / Flutter)

---

## 📝 DATENBANK-MIGRATIONS

### Host (kp_club_management)
```
Migrations:
├── users
├── companies
├── subscriptions
├── domains
├── tenants
├── support_tickets
└── platform_settings
```

### Tenant (kp_club_{id})
```
Migrations (Pro Verein):
├── club_info
├── members
├── teams
├── matches
├── sponsors
├── website_settings
└── users (Tenant Users)
```

---

## ✅ NÄCHSTE SCHRITTE

1. **Database + Tenant Middleware Setup** (Laravel Tenancy)
2. **Host & Tenant Routes** konfigurieren
3. **Platform Admin Backend** erstellen
4. **Verein Backend** erstellen
5. **Verein Frontend** erstellen
6. **API** entwickeln

---

## 🎯 PLATTFORM-NAME

Vorschläge (für hosting + branding):
1. **Fussball Manager** (einfach, klar)
2. **ClubFlow** (modern)
3. **TeamHub** (fokussiert)
4. **VereinVerwaltung** (deutsch)
5. **KickManager** (spielerisch)

**Welchen magst du?**

