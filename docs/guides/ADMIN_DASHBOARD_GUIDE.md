# 🛠️ Admin Dashboard Setup - Club Portal

## Status: ✅ READY FOR USE

Das Admin Dashboard ist für die Verwaltung aller Club Portal Features konfiguriert.

---

## 📍 Zugang zum Admin Panel

### URLs:
```
Haupt-Admin:      /admin
Club Panel:       /admin/club
Superadmin:       /admin/superadmin
Portal Admin:     /admin/portal
```

### Login-Optionen:
- E-Mail: admin@example.com
- Passwort: password

---

## 📊 Dashboard Features

### 1. 🏢 Club Management
**Endpoint:** `/admin/clubs` (oder über Filament)

#### Was kann man tun?
- ✅ Clubs erstellen/bearbeiten/löschen
- ✅ Subscription Plan zuweisen
- ✅ Club Status verwalten (Active, Suspended, Cancelled)
- ✅ Renewal Dates tracken
- ✅ Direkten API Link sehen

#### Datenfelder:
```
- Name, Slug, Description
- Email, Phone, Address
- Subscription Package
- Billing Cycle (Monthly/Yearly)
- Status
- Registration Token
```

---

### 2. 🎨 Design Settings
**Endpoint:** `/admin/club-settings`

#### Funktionen:
- ✅ Primäre/Sekundäre/Akzent-Farbe wählen
- ✅ Font-Familie auswählen
- ✅ Font-Größen konfigurieren
- ✅ Logo & Favicon hochladen
- ✅ Hero Image hochladen
- ✅ SEO Meta Tags bearbeiten (Title, Description, Keywords)

#### Design-Editor:
```html
Live-Vorschau des Designs mit aktuellen Farben/Fonts
```

---

### 3. 📢 Sponsor Management
**Endpoint:** `/admin/sponsors`

#### Sponsoren-Features:
- ✅ Logo hochladen (max 2MB)
- ✅ Banner hochladen (max 5MB)
- ✅ Positionen setzen (Top, Middle, Bottom, Sidebar)
- ✅ Display-Größen: Width x Height (z.B. 300x200px)
- ✅ Jahresgebühr eingeben
- ✅ Contract-Daten: Start & End Date
- ✅ Status: Active/Inactive/Expired
- ✅ Priority-Ordering (welche zuerst angezeigt werden)

#### Beispiel:
```
Name:                Nike
Position:            top
Width x Height:      300 x 200 px
Annual Fee:          €5,000
Contract Duration:   12 months
Start Date:          2025-01-01
End Date:            2025-12-31
```

---

### 4. 📱 Social Links
**Endpoint:** `/admin/social-links`

#### Plattformen:
- ✅ Facebook
- ✅ Instagram
- ✅ X (Twitter)
- ✅ TikTok
- ✅ YouTube
- ✅ LinkedIn
- ✅ Website

#### Pro Link:
- ✅ URL eingeben
- ✅ Display Name (z.B. "@nkprigorje")
- ✅ Aktivieren/Deaktivieren
- ✅ Reihenfolge anpassen (Order)

---

### 5. 🔔 Notifications
**Endpoint:** `/admin/notifications`

#### Notification-Typen:
- 📧 Email
- 💬 SMS
- 🔔 Push (PWA)
- 📝 In-App

#### Recipient Targeting:
```
Optionen:
- Nach Role: Admin, Manager, Coach, Player, Parent, Fan
- Nach User IDs: Spezifische Personen
- Kombinationen: (z.B. "alle Coaches und Spieler")
```

#### Scheduling:
- ✅ Sofort senden
- ✅ Zeitgesteuert (z.B. morgen um 14:00)
- ✅ Status: Draft → Scheduled → Sent

#### Beispiel:
```
Title:          "Match morgen!"
Message:        "Training um 19:00 Uhr"
Type:           Push
Recipients:     Players, Coaches
Schedule:       2025-10-25 18:00
```

---

### 6. 📧 Email Widgets
**Endpoint:** `/admin/email-widgets`

#### Features:
- ✅ HTML/Text Email-Body
- ✅ Recipient Filtering (Roles + User IDs)
- ✅ Subject & Body Editor
- ✅ Scheduling oder Sofort
- ✅ Sent Count Tracking
- ✅ Status: Draft → Scheduled → Sent

#### Beispiel:
```
Subject:        "Neuer Trainingsplan"
Body:           "Der neue Plan ist online..."
Recipients:     Players, Coaches, Parents
Send At:        (leer = sofort)
```

---

### 7. 📲 SMS Widgets
**Endpoint:** `/admin/sms-widgets`

#### SMS-Versand:
- ✅ Message eingeben
- ✅ Recipient Targeting
- ✅ Cost Estimation (7¢ pro SMS)
- ✅ Total Cost berechnen
- ✅ Scheduling
- ✅ Delivery Tracking

#### Kostenberechnung:
```
Kosten pro SMS:     €0,07
Beispiel: 100 Spieler × €0,07 = €7,00
```

#### Beispiel:
```
Title:          "Quick SMS"
Message:        "Morgen 15:00 Anpfiff"
Recipients:     Players (est. 50)
Cost:           €3,50
```

---

### 8. 📝 Contact Form Admin
**Endpoint:** `/admin/contact-form-submissions`

#### Features:
- ✅ Alle Submissions anzeigen
- ✅ Filter: New, Read, Replied, Spam
- ✅ Nachricht anschauen
- ✅ Reply verfassen
- ✅ As Spam markieren
- ✅ Export (optional)

#### Status-Workflow:
```
New → Read → Replied (oder Spam)
```

#### Submission Details:
```
- Sender: Name, Email, Phone
- Subject & Message
- IP Address (für Spam-Detection)
- Timestamp
- Reply (Admin)
```

---

## 🔧 Verwaltungs-Aufgaben

### A. Club Onboarding (für neuen Verein)

1. **Create Club:**
   - Name: "NK Prigorje"
   - Email: "admin@nkprigorje.hr"
   - Package: Premium
   - Billing: Yearly
   - Generate Registration Token

2. **Setup Design:**
   - Primary Color: #FF0000 (Rot)
   - Secondary Color: #FFFFFF (Weiß)
   - Font: Roboto
   - Upload Logo & Favicon

3. **Add Sponsors:**
   - Nike (top position)
   - Adidas (middle)
   - Local Bank (bottom)

4. **Social Links:**
   - Facebook: facebook.com/nkprigorje
   - Instagram: @nkprigorje
   - YouTube: youtube.com/nkprigorje

### B. Send Bulk Message

1. **Create Notification:**
   - Title: "Training Reminder"
   - Message: "Don't forget training tomorrow 7 PM"
   - Type: Push
   - Recipients: Players, Coaches
   - Send: Immediately

2. **Track:**
   - Siehe "Sent Count"
   - Check Status: Sent

### C. Contact Form Management

1. **Review Submissions:**
   - Go to Contact Form
   - Filter: New
   - Open & Read

2. **Reply:**
   - Click "Reply"
   - Write Response
   - Send
   - Status → "Replied"

### D. Sponsor Contract Management

1. **Add Sponsor:**
   - Name, Website, Fees
   - Contract: Jan 1 - Dec 31, 2025
   - Position: Top
   - Upload Logo & Banner

2. **Track Contract:**
   - See "Days Remaining"
   - Auto-expire when date passed
   - Set status → Inactive

---

## 📈 Reports & Analytics (Optional)

Können hinzugefügt werden:

### Dashboard Stats:
```
- Total Clubs: 24
- Active Subscriptions: 20
- Pending Renewals: 4
- Total Sponsors: 145
- Messages Sent: 3,521
- Contact Submissions: 287
```

### Export Options:
```
- Club List (CSV/Excel)
- Sponsor Contracts (PDF)
- Contact Submissions (CSV)
- Message Logs (CSV)
```

---

## 🔐 Sicherheit & Berechtigungen

### Admin Rollen:
- **Super Admin:** Alles
- **Admin:** Club Management, Notifications
- **Manager:** Settings, Sponsors, Contacts
- **Moderator:** Contacts only

### API Security:
```
✅ Token-based Authentication
✅ Rate Limiting: 60 requests/minute
✅ CORS enabled
✅ Input Validation
✅ File Upload Restrictions
```

---

## 📱 Mobile Admin (Optional)

Die API ist vollständig mobile-friendly:

```bash
# Beispiel: Sponsor auf Mobile hinzufügen
POST /api/clubs/598/sponsors
{
  "name": "Nike",
  "position": "top",
  "annual_fee": 5000,
  ...
}
```

---

## 🚀 Best Practices

### ✅ DO:
- Regelmäßig Backups machen
- Colors & Fonts testen bevor live
- Sponsor-Verträge vor Ablauf erneuern
- Contact Forms täglich prüfen
- Notifications gezielt senden (nicht Spam)

### ❌ DON'T:
- Zu viele Sponsors gleichzeitig (max 20)
- SMS an alle Users (sparsam mit Kosten)
- Alte Notifications nicht löschen
- Falsches Logo-Format uploadenzen (PNG/JPG nur)

---

## 📞 Support & Troubleshooting

### Problem: Club nicht angezeigt
**Lösung:** 
- Registration Token prüfen
- Status nicht "suspended"
- Subscription Package aktiv

### Problem: Logo wird nicht angezeigt
**Lösung:**
- Format: PNG oder JPG
- Größe: max 2MB
- Dimensions: mindestens 200x100px

### Problem: SMS wird nicht gesendet
**Lösung:**
- Recipient Count prüfen
- SMS-Provider konfiguriert?
- Budget/Credits ausreichend?

---

## 📚 Dokumentation Links

- 📖 [API Dokumentation](../API_DOCUMENTATION.md)
- 🎨 [Frontend Setup](../FRONTEND_SETUP.md)
- 📋 [Complete README](../CLUB_PORTAL_README.md)

---

**Erstellt:** October 24, 2025 | **Status:** ✅ Production Ready
