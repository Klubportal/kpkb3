# 🧹 DATENBANK CLEANUP - STRATEGY

## Analyse der aktuellen Tabellen

### ✅ BEHALTEN - KERN-TABELLEN

**1. User Management**
- `users` - Benutzer
- `cache` - Laravel Cache (notwendig)
- `jobs` - Job Queue (notwendig)

**2. Multi-Tenancy**
- `tenants` - Mandanten/Clubs
- `domains` - Domain-Mapping

**3. Club Management (CORE)**
- `clubs` - Haupttabelle
- `club_members` - Mitglieder
- `club_sponsors` - Sponsoren
- `club_banners` - Banner
- `club_extended` - Erweiterte Daten
- `club_social_links` - Social Links

**4. Advertising & Marketing**
- `banners` - Standard-Banner
- `advertising_banners` - Werbebanner
- `sponsors` - Sponsoren
- `sponsor_logos` - Sponsor-Logos
- `sponsor_banners` - Sponsor-Banner

**5. Website Configuration**
- `website_settings` - Website-Einstellungen
- `email_settings` - Email-Konfiguration
- `language_translations` - Mehrsprachigkeit

**6. Comet Integration (nur wenn aktiv)**
- `comet_clubs` - Clubs aus Comet
- `comet_teams` - Teams
- `comet_players` - Spieler
- `comet_matches` - Spiele
- `comet_competitions` - Wettbewerbe
- `cometSync` - Sync-Log

---

### ❌ ENTFERNEN - UNNÖTIGE TABELLEN

Diese Tabellen sind für Core-Funktionalität NICHT nötig:

**Analytics (zu komplex für MVP):**
- analytics_events
- analytics_campaigns
- analytics_aggregation
- funnel_analytics
- conversion_tracking
- user_journey
- user_behavior
- user_location
- user_statistics
- user_attributes
- user_segments
- user_segment_members
- engagement_scores

**Targeting & Campaigns:**
- targeting_rules
- targeting_criteria
- targeting_audience
- targeting_history

**Advanced Messaging (optional):**
- messages (falls nicht genutzt)
- message_conversations
- message_recipients

**Notifications (optional):**
- notifications
- notification_groups
- notification_logs

**Push & SMS (optional):**
- push_notifications
- push_notification_logs
- push_subscriptions
- sms_settings
- sms_templates
- sms_logs
- sms_messages
- sms_conversations
- sms_otp_codes
- sms_campaigns

**AB Testing (optional):**
- ab_tests
- ab_test_variants
- ab_test_results
- ab_test_conversions

**Gaming/Rankings (optional):**
- players
- player_competition_stats
- player_statistics
- competitions
- competition_ranking
- game_matches
- match_events
- match_players
- rankings
- top_scorers

**PWA & Web:**
- websocket_connections
- websocket_events
- websocket_presence
- websocket_typing_indicators
- pwa_installations

**Contact & Misc:**
- contact_form_submissions (optional - kann bleiben für Forms)

---

## 🎯 CLEANUP-PLAN

### Option A: Minimal (nur Core)
**Behalte:** 13 Tabellen
- users, cache, jobs
- tenants, domains
- clubs, club_members, club_sponsors, club_banners, club_extended, club_social_links
- banners, advertising_banners, sponsors, sponsor_logos
- website_settings, email_settings, language_translations
- contact_form_submissions

**Lösche:** Alles andere (Analytics, SMS, Push, AB-Tests, Gaming, WebSocket, etc.)

### Option B: Standard (Core + Integration)
**Behalte:** Option A + Comet
- Zusätzlich: comet_clubs, comet_teams, comet_players, comet_matches, comet_competitions, comet_syncs

### Option C: Full (behalte alles)
**Keine Änderungen** - alle Tabellen bleiben für zukünftige Features

---

## ⚡ EMPFEHLUNG

**Ich empfehle: Option B (Core + Integration)**

**Gründe:**
1. ✅ Sauber & fokussiert (20 Tabellen statt 50+)
2. ✅ Schneller & besser performant
3. ✅ Komet-Integration behält (wichtig!)
4. ✅ Jederzeit erweiterbar

**Removed:** ~35 Tabellen (Analytics, SMS, Push, AB-Tests, Gaming)
**Behalten:** ~20 Tabellen (Core + Comet)

---

## 📋 CLEANUP-SCHRITTE

1. **Neue Migration erstellen:** `cleanup_remove_unused_tables.php`
2. **Migrationen entfernen** (in database/migrations/):
   - analytics_*
   - sms_*
   - push_*
   - ab_test_*
   - websocket_*
   - targeting_*
   - user_segment_*
   - player_*
   - game_*
   - competition_*
   - ranking_*
   - pwa_*
3. **Models löschen** (in app/Models/):
   - Entsprechende 50+ Models
4. **Cache/Config leeren**

---

## 🔄 ROLLBACK

Falls etwas schief geht:
```bash
# Backup ist da!
# cp -r backups/kp_club_management_backup_24-10-2025/* .
```

---

**Frage an dich:**
Welche Option bevorzugst du?
- **A)** Minimal (nur Core)
- **B)** Standard (Core + Comet) ← EMPFEHLSNG
- **C)** Full (alles behalten)

