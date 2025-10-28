# Settings & Benachrichtigungen - Dokumentation

## Übersicht

Das Klubportal verfügt über ein umfassendes Settings-System mit folgenden Bereichen:

### 1. **Site Einstellungen** (`/admin/manage-general-settings`)
- **Site Name & Beschreibung**
- **Logo & Favicon** (mit konfigurierbarer Logo-Größe)
- **Farben** (Primär- & Sekundärfarbe)
- **Schriftart & -größe**
- **Kontakt-Informationen**

### 2. **Social Media** (`/admin/manage-social-media-settings`)
- Facebook URL
- Instagram URL
- Twitter/X URL
- YouTube URL
- LinkedIn URL
- TikTok URL

### 3. **Kontakt & Adresse** (`/admin/manage-contact-settings`)
- **Firmeninformationen**
  - Firmenname
- **Adresse**
  - Straße & Hausnummer
  - PLZ & Stadt
  - Land
- **Kontaktdaten**
  - Telefon
  - Mobil
  - Fax
  - E-Mail
- **Google Maps Integration**
  - Google Maps URL
  - Google Maps Embed Code

### 4. **E-Mail Einstellungen** (`/admin/manage-email-settings`)
- **Absender-Informationen**
  - Absender Name
  - Absender E-Mail
  - Reply-To E-Mail
  - Admin-Benachrichtigungs-E-Mail
- **Benachrichtigungen**
  - Benachrichtigung bei neuer Registrierung (Toggle)
  - Benachrichtigung bei Kontaktformular (Toggle)
- **E-Mail Betreffzeilen**
  - Betreff für Registrierungs-E-Mails
  - Betreff für Kontaktformular-E-Mails
- **SMTP Server Konfiguration** ✨ NEU
  - Mail Treiber (SMTP, Sendmail, Mailgun, SES, etc.)
  - SMTP Host (z.B. smtp.gmail.com)
  - SMTP Port (587 für TLS, 465 für SSL)
  - Verschlüsselung (TLS/SSL/Keine)
  - SMTP Benutzername
  - SMTP Passwort (sicher & revealable)
  - Standard Absender-Adresse
  - Standard Absender-Name
  - Info-Box mit gängigen SMTP-Anbietern

## Navigation

Alle Settings-Seiten sind unter dem Menüpunkt **"Einstellungen"** im Central Backend gruppiert:

```
📁 Einstellungen
  ⚙️ Site Einstellungen (navigationSort: 1)
  🔗 Social Media (navigationSort: 2)
  📍 Kontakt & Adresse (navigationSort: 3)
  ✉️ E-Mail Einstellungen (navigationSort: 4)
```

## E-Mail Benachrichtigungen

### Verfügbare Notifications

#### 1. **NewRegistrationNotification**
Wird gesendet wenn sich ein neuer Verein registriert.

**Verwendung:**
```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

$notificationService->sendRegistrationNotification(
    tenantName: 'FC Testclub',
    tenantEmail: 'info@testclub.com',
    tenantDomain: 'testclub.localhost'
);
```

**E-Mail Inhalt:**
- Vereinsname
- E-Mail-Adresse
- Domain
- Link zum Central Backend

#### 2. **ContactFormNotification**
Wird gesendet wenn jemand das Kontaktformular ausfüllt.

**Verwendung:**
```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

$notificationService->sendContactFormNotification(
    name: 'Max Mustermann',
    email: 'max@example.com',
    subject: 'Frage zum Portal',
    message: 'Wie kann ich mich registrieren?'
);
```

**E-Mail Inhalt:**
- Name des Absenders
- E-Mail-Adresse
- Betreff
- Nachricht

### NotificationService

Der `NotificationService` ist ein zentraler Service der:
- Prüft ob Benachrichtigungen aktiviert sind (via EmailSettings)
- **Wendet SMTP-Konfiguration automatisch an** (via MailConfigService)
- E-Mails an die konfigurierte Admin-E-Mail sendet
- Die konfigurierten Betreffzeilen verwendet

### MailConfigService ✨ NEU

Der `MailConfigService` lädt die SMTP-Einstellungen aus der Datenbank und wendet sie auf die Laravel Mail-Konfiguration an.

**Automatische Verwendung:**
```php
use App\Services\NotificationService;

// SMTP-Config wird automatisch angewendet
$service = app(NotificationService::class);
$service->sendContactFormNotification(...);
```

**Manuelle Verwendung:**
```php
use App\Services\MailConfigService;

$mailConfig = app(MailConfigService::class);

// SMTP-Einstellungen anwenden
$mailConfig->applySettings();

// Verbindung testen
try {
    $mailConfig->testConnection();
    echo "SMTP-Verbindung erfolgreich!";
} catch (\Exception $e) {
    echo "Fehler: " . $e->getMessage();
}

// Aktuelle Config abrufen (ohne Passwort)
$config = $mailConfig->getCurrentConfig();

// Anbieter-Vorlagen abrufen
$presets = MailConfigService::getProviderPresets();
// ['gmail' => [...], 'outlook' => [...], ...]
```

## Settings-Klassen

### Dateistruktur
```
app/Settings/
  ├── GeneralSettings.php      (Site-weite Einstellungen)
  ├── SocialMediaSettings.php  (Social Media URLs)
  ├── ContactSettings.php      (Kontakt & Adresse)
  └── EmailSettings.php        (E-Mail Konfiguration)
```

### Settings laden

**Im Backend (Central oder Tenant):**
```php
use App\Settings\GeneralSettings;
use App\Settings\SocialMediaSettings;
use App\Settings\ContactSettings;
use App\Settings\EmailSettings;

// Dependency Injection
public function __construct(
    private GeneralSettings $generalSettings,
    private SocialMediaSettings $socialMediaSettings,
    private ContactSettings $contactSettings,
    private EmailSettings $emailSettings
) {}

// Oder via app()
$generalSettings = app(GeneralSettings::class);
$socialSettings = app(SocialMediaSettings::class);
$contactSettings = app(ContactSettings::class);
$emailSettings = app(EmailSettings::class);

// Werte abrufen
echo $generalSettings->site_name;
echo $socialSettings->facebook_url;
echo $contactSettings->email;
echo $emailSettings->sender_name;
```

## Datenbank-Struktur

Alle Settings werden in der `settings`-Tabelle der **Central Database** (`klubportal_landlord`) gespeichert:

```sql
-- Beispiel-Einträge
SELECT * FROM settings WHERE `group` = 'general';
SELECT * FROM settings WHERE `group` = 'social_media';
SELECT * FROM settings WHERE `group` = 'contact';
SELECT * FROM settings WHERE `group` = 'email';
```

## Integration in Tenant-Backend

Das Tenant-Backend kann auf die Settings des Central-Backends zugreifen via Cross-Database-Query (siehe `AdminPanelProvider.php`).

## Beispiel: Kontaktformular mit E-Mail-Benachrichtigung

```php
<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Benachrichtigung an Admin senden
        $this->notificationService->sendContactFormNotification(
            name: $validated['name'],
            email: $validated['email'],
            subject: $validated['subject'],
            message: $validated['message']
        );

        return redirect()->back()->with('success', 'Ihre Nachricht wurde gesendet!');
    }
}
```

## Beispiel: Registrierung mit E-Mail-Benachrichtigung

```php
<?php

namespace App\Http\Controllers;

use App\Models\Central\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TenantRegistrationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function register(Request $request)
    {
        // ... Tenant erstellen ...

        $tenant = Tenant::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $domain = $tenant->domains()->create([
            'domain' => $request->subdomain . '.localhost',
        ]);

        // Benachrichtigung an Admin senden
        $this->notificationService->sendRegistrationNotification(
            tenantName: $tenant->name,
            tenantEmail: $tenant->email,
            tenantDomain: $domain->domain
        );

        return redirect()->route('registration.success');
    }
}
```

## Konfiguration

### E-Mail-Versand aktivieren

**Option 1: Über UI (empfohlen)** ✨ NEU

Gehe zu `/admin/manage-email-settings` → Scrolle zu "SMTP Server Konfiguration":
1. Wähle Mail Treiber: **SMTP**
2. SMTP Host: z.B. `smtp.gmail.com`
3. SMTP Port: `587` (für TLS)
4. Verschlüsselung: **TLS**
5. Benutzername: Ihre vollständige E-Mail
6. Passwort: Ihr E-Mail-Passwort (bei Gmail: App-Passwort!)
7. Absender-Adresse: `noreply@klubportal.com`
8. Absender-Name: `Klubportal`
9. **Speichern**

Die Einstellungen werden automatisch bei jedem E-Mail-Versand angewendet.

**Option 2: Via .env (Legacy)**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@klubportal.com"
MAIL_FROM_NAME="Klubportal"
```

**Hinweis:** UI-Einstellungen überschreiben .env-Werte!

### Gängige SMTP-Anbieter

Siehe **SMTP_CONFIGURATION_GUIDE.md** für detaillierte Anleitungen zu:
- Gmail (mit App-Passwort)
- Outlook/Office 365
- IONOS, Strato, All-Inkl, HostEurope
- Mailgun (für hohe Volumen)
- Mailtrap (für Tests)

### Admin-E-Mail konfigurieren

Gehe zu `/admin/manage-email-settings` und setze:
- **Admin-Benachrichtigungs-E-Mail**: Die E-Mail-Adresse die alle Benachrichtigungen erhält
- **Absender Name & E-Mail**: Für ausgehende System-E-Mails
- **Reply-To E-Mail**: Für Antworten auf System-E-Mails

## Erweiterung

### Neue Notification hinzufügen

1. **Notification-Klasse erstellen:**
```php
php artisan make:notification YourNotification
```

2. **In NotificationService erweitern:**
```php
public function sendYourNotification(array $data): void
{
    if (!$this->emailSettings->notify_on_your_event) {
        return;
    }

    Notification::route('mail', $this->emailSettings->admin_notification_email)
        ->notify(new YourNotification($data));
}
```

3. **EmailSettings erweitern:**
```php
// app/Settings/EmailSettings.php
public bool $notify_on_your_event;
public ?string $your_event_subject;
```

4. **UI aktualisieren:**
Füge Toggle und Betreff-Feld in `ManageEmailSettings.php` hinzu.

## Zusammenfassung

✅ 4 Settings-Bereiche vollständig implementiert
✅ Navigation unter "Einstellungen" gruppiert
✅ E-Mail-Benachrichtigungen für Kontaktformular & Registrierung
✅ NotificationService für einfache Integration
✅ **SMTP vollständig über UI konfigurierbar** ✨ NEU
✅ **MailConfigService für dynamische Mail-Konfiguration** ✨ NEU
✅ **Unterstützung für alle gängigen Anbieter** ✨ NEU
✅ Alle Settings in Central Database
✅ Cross-Database-Zugriff für Tenant-Backends möglich
✅ Vollständig konfigurierbar über UI

## Weitere Dokumentation

- **SMTP_CONFIGURATION_GUIDE.md** - Detaillierte SMTP-Anleitung mit allen Anbietern
- **BRANDING_SETUP.md** - Logo, Favicon und Branding-Konfiguration
