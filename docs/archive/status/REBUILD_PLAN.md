# 🏗️ KP CLUB MANAGEMENT - SYSTEM ARCHITEKTUR & REBUILD PLAN

## 📊 EXISTIERENDE DATENBANK-STRUKTUR

### 🔐 Basis-Tabellen (Multi-Tenancy)
- **users** - User-Management (ID, Name, Email, Password, etc.)
- **tenants** - Mandanten/Clubs (Multi-tenancy system)
- **domains** - Domain-Mapping für Tenants

### 🏘️ Club-Management
- **clubs** - Haupttabelle für Clubs (ID, Name, Logo, Beschreibung, etc.)
- **club_members** - Mitglieder pro Club
- **club_sponsors** - Sponsoren für Clubs
- **club_banners** - Banner pro Club
- **club_extended** - Erweiterte Club-Daten
- **club_social_links** - Social-Media Links

### ⚽ Comet-Integration (Externe API/System)
- **comet_clubs** - Clubs aus Comet-System
- **comet_teams** - Teams
- **comet_players** - Spieler
- **comet_matches** - Spiele
- **comet_competitions** - Wettbewerbe
- **comet_match_events** - Match-Events
- **comet_match_stats** - Match-Statistiken
- **comet_player_stats** - Spieler-Statistiken
- **comet_syncs** - Sync-Log

### 📢 Advertising & Marketing
- **advertising_banners** - Werbe-Banner
- **banners** - Standard-Banner
- **sponsors** - Sponsoren-Management
- **sponsor_logos** - Sponsor-Logos
- **sponsor_banners** - Sponsor-Banners
- **ab_tests** - A/B Test-Kampagnen
- **ab_test_variants** - Test-Varianten
- **ab_test_results** - Test-Ergebnisse
- **ab_test_conversions** - Conversions

### 📧 Email & SMS
- **email_settings** - Email-Konfiguration
- **email_templates** - Email-Templates
- **email_widgets** - Email-Widget-Daten
- **sms_settings** - SMS-Konfiguration
- **sms_templates** - SMS-Templates
- **sms_logs** - SMS-Versand-Logs
- **sms_messages** - SMS-Nachrichten
- **sms_conversations** - SMS-Chats
- **sms_otp_codes** - OTP-Codes
- **sms_campaigns** - SMS-Kampagnen

### 💬 Messaging
- **messages** - Interne Nachrichten
- **message_conversations** - Message-Threads
- **message_recipients** - Message-Empfänger
- **notifications** - Benachrichtigungen
- **notification_groups** - Notification-Gruppen
- **notification_logs** - Notification-Logs
- **push_notifications** - Push-Benachrichtigungen
- **push_notification_logs** - Push-Logs
- **push_subscriptions** - Push-Subscriptions

### 📊 Analytics & Tracking
- **analytics_events** - Tracking-Events
- **analytics_campaigns** - Kampagnen-Tracking
- **analytics_aggregation** - Aggregierte Daten
- **funnel_analytics** - Trichter-Analyse
- **conversion_tracking** - Conversion-Tracking
- **user_journey** - User-Journey-Tracking
- **user_behavior** - Nutzungsverhalten
- **user_location** - Benutzer-Standorte
- **user_statistics** - Benutzer-Statistiken
- **user_attributes** - Benutzer-Attribute
- **user_segments** - Benutzer-Segmentierung
- **user_segment_members** - Segment-Zugehörigkeit
- **targeting_rules** - Targeting-Regeln
- **targeting_criteria** - Targeting-Kriterien
- **targeting_audience** - Zielgruppen
- **targeting_history** - Targeting-Historie

### 🎮 Gaming & Rankings
- **players** - Spieler-Profil
- **player_competition_stats** - Spieler-Statistiken pro Wettkampf
- **player_statistics** - Allgemeine Spieler-Statistiken
- **competitions** - Wettbewerbe
- **competition_ranking** - Ranglisten
- **game_matches** - Spiel-Matches
- **match_events** - Match-Events
- **match_players** - Spieler in Match
- **rankings** - Ranglisten
- **top_scorers** - Top-Scorer

### 🔧 System & Konfiguration
- **website_settings** - Website-Einstellungen
- **language_translations** - Mehrsprachigkeit
- **contact_form_submissions** - Kontakt-Formulare
- **cache** - Cache-Tabelle (Laravel)
- **jobs** - Job-Queue
- **websocket_connections** - WebSocket-Verbindungen
- **websocket_events** - WebSocket-Events
- **websocket_presence** - Benutzer-Präsenz
- **websocket_typing_indicators** - Tipp-Indikatoren

### 📱 PWA & Subscriptions
- **pwa_installations** - PWA-Installationen
- **subscription_packages** - Subscription-Pakete

### 📝 Custom Data
- **contact_form_submissions** - Kontaktformular-Einträge
- **engagement_scores** - Engagement-Punkte

---

## 🎯 AKTUELLE MODELS (80+)

### Kern-Models
- User, Club, ClubMember, ClubSponsor, ClubBanner
- Sponsor, Banner, AdvertisingBanner
- SponsorLogo, SponsorBanner

### Integration Models
- CometClub, CometTeam, CometPlayer, CometMatch
- CometCompetition, CometMatchEvent, CometMatchStat

### Email/SMS Models
- EmailSetting, EmailTemplate, EmailWidget
- SmsSettings, SmsTemplate, SmsLog, SmsMessage

### Analytics Models
- AnalyticsEvent, AnalyticsAggregation, AnalyticsCampaign
- UserJourney, UserBehavior, UserLocation, UserSegment

### Messaging Models
- Message, MessageConversation, MessageRecipient
- Notification, NotificationGroup, PushNotification

### Other Models
- WebsiteSetting, LanguageTranslation, ContactFormSubmission
- AbTest, AbTestVariant, AbTestResult, ConversionTracking

---

## 🏗️ REBUILD-PLAN (Step-by-Step)

### PHASE 1: AUDIT & DOKUMENTATION ✅
- [x] Alle Models auflisten
- [x] Alle Migrations auflisten
- [x] Datenbank-Struktur dokumentieren

### PHASE 2: FILAMENT-SETUP (Neu)
- [ ] Modernes Red-Theme erstellen (CSS)
- [ ] Filament-Komponenten-Struktur vorbereiten
- [ ] Blade-Templates mit modernem Design

### PHASE 3: SUPER ADMIN PANEL
**19 Pages - mit folgende Struktur:**

#### Club Management (5 Pages)
1. **Clubs Management** - Liste, Filter, Actions
2. **Add/Edit Club** - Form mit Validierung
3. **Club Members** - Mitglieder-Management
4. **Club Sponsors** - Sponsor-Integration
5. **Club Analytics** - Club-Statistiken

#### Website & Settings (4 Pages)
6. **Website Settings** - Logo, Favicon, Farben, Meta
7. **Email Settings** - SMTP, Templates
8. **SMS Settings** - SMS-Provider, Templates
9. **Language Settings** - Übersetzungen

#### Marketing & Ads (4 Pages)
10. **Advertising Banners** - Banner-Management
11. **Sponsors** - Sponsor-Management
12. **AB Tests** - A/B Test-Kampagnen
13. **Analytics Dashboard** - Tracking & Analytics

#### Users & Permissions (3 Pages)
14. **Users Management** - Benutzer-Verwaltung
15. **User Roles** - Rollen & Permissions
16. **User Activity** - Activity-Log

#### Integration (3 Pages)
17. **Comet Sync** - Comet-Integration
18. **API Keys** - API-Management
19. **System Health** - System-Status

### PHASE 4: PORTAL DASHBOARD (User/Club)
- Dashboard mit Statistiken
- Club-Management
- Nachrichten/Notifications
- Profile Management

### PHASE 5: TESTING & VALIDATION
- Datenbank-Integrität
- Performance-Tests
- User-Flow-Tests

---

## 🎨 DESIGN-SYSTEM

### Farben
- **Primary (Red)**: #dc2626
- **Secondary**: #f3f4f6
- **Accent (Blue)**: #2563eb
- **Success (Green)**: #16a34a
- **Warning (Orange)**: #ea580c
- **Danger (Red)**: #dc2626

### Komponenten
- Header mit roten Akzenten
- Cards mit linkem roten Border
- Buttons in Rot
- Forms mit moderner Styling
- Tables mit alternating backgrounds
- Stats-Cards mit Farben

### CSS Datei
- `resources/css/filament-theme.css` - Globale Theme
- Registriert in SuperAdminPanelProvider

---

## 📁 DATEISTRUKTUR

```
app/
  Filament/
    SuperAdmin/
      Resources/        ← Ressourcen für Models
      Pages/           ← 19 Admin Pages
      Widgets/         ← Dashboard-Widgets
    Portal/
      Pages/           ← User Pages
  Http/
    Controllers/       ← API/Web Controller
  Models/              ← 80+ Models (bestehen)
  Providers/
    Filament/SuperAdminPanelProvider.php

database/
  migrations/
    ... bestehende Migrations

resources/
  css/
    filament-theme.css ← Modernes Red-Theme
  views/
    filament/
      pages/
        dashboard.blade.php
        super-admin/
          ... 19 Pages

routes/
  web.php              ← Web Routes
  api.php              ← API Routes
```

---

## ⚙️ SCHRITT-FÜR-SCHRITT IMPLEMENTATION

1. **Theme System** - CSS & Komponenten
2. **Super Admin 19 Pages** - Mit Daten-Bindings
3. **Portal Dashboard** - User-Sicht
4. **Controllers & Actions** - Business Logic
5. **Routes** - Web + API
6. **Testing** - Funktional + Performance
7. **Deployment** - Go-Live

---

## 📝 NÄCHSTE SCHRITTE

1. Soll ich die **19 Super Admin Pages** neu erstellen mit modernem Design?
2. Soll ich die **bestehenden Models** mit Filament **Resources** verbinden?
3. Welche **Priorität**: Club-Management vs. Analytics vs. Settings?

