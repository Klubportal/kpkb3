# SMTP E-Mail Konfiguration - Anleitung

## Übersicht

Das Klubportal verfügt über eine vollständige SMTP-Konfiguration direkt im Backend. Sie können alle E-Mail-Einstellungen über die UI konfigurieren, ohne die `.env`-Datei bearbeiten zu müssen.

## Zugriff

**URL:** `/admin/manage-email-settings`

Navigieren Sie zu: **Einstellungen** → **E-Mail Einstellungen**

## Verfügbare Packages

Das System verwendet folgende Laravel/Symfony Packages:
- `symfony/mailer` (v7.3.4) - Core Mail-System
- `egulias/email-validator` (v4.0.4) - E-Mail Validierung
- `filament/notifications` (v4.1.10) - Notification-System
- `spatie/laravel-newsletter` (v5.3.1) - Newsletter-Funktionen
- `laravel-notification-channels/webpush` (v10.2.0) - Push-Benachrichtigungen

## SMTP-Konfiguration

### Felder in der UI

| Feld | Beschreibung | Beispiel |
|------|--------------|----------|
| **Mail Treiber** | Art des E-Mail-Versands | SMTP (empfohlen) |
| **SMTP Host** | Server-Adresse | smtp.gmail.com |
| **SMTP Port** | Port-Nummer | 587 (TLS), 465 (SSL) |
| **Verschlüsselung** | Sicherheitsprotokoll | TLS (empfohlen) |
| **SMTP Benutzername** | Login-Name | ihre-email@domain.com |
| **SMTP Passwort** | Passwort | ••••••••••• |
| **Absender-Adresse** | Von-Adresse | noreply@klubportal.com |
| **Absender-Name** | Von-Name | Klubportal |

## Gängige SMTP-Anbieter

### 1. Gmail

```
Host: smtp.gmail.com
Port: 587
Verschlüsselung: TLS
Benutzername: ihre-email@gmail.com
Passwort: App-spezifisches Passwort
```

**Wichtig:** Google erfordert ein [App-spezifisches Passwort](https://myaccount.google.com/apppasswords)
- Gehen Sie zu Google-Konto → Sicherheit
- Aktivieren Sie 2-Faktor-Authentifizierung
- Erstellen Sie ein App-Passwort für "Mail"
- Verwenden Sie dieses 16-stellige Passwort (nicht Ihr normales Gmail-Passwort)

### 2. Outlook / Office 365 / Hotmail

```
Host: smtp.office365.com
Port: 587
Verschlüsselung: TLS
Benutzername: ihre-email@outlook.com
Passwort: Ihr Outlook-Passwort
```

**Funktioniert mit:**
- @outlook.com
- @hotmail.com
- @live.com
- Office 365 Business-Konten

### 3. IONOS (1&1)

```
Host: smtp.ionos.de
Port: 587
Verschlüsselung: TLS
Benutzername: ihre-email@ihre-domain.de
Passwort: Ihr E-Mail-Passwort
```

**Hinweis:** Verwenden Sie die vollständige E-Mail-Adresse als Benutzername

### 4. Strato

```
Host: smtp.strato.de
Port: 465
Verschlüsselung: SSL
Benutzername: ihre-email@ihre-domain.de
Passwort: Ihr E-Mail-Passwort
```

### 5. All-Inkl

```
Host: smtp.all-inkl.com
Port: 587
Verschlüsselung: TLS
Benutzername: ihre-email@ihre-domain.de
Passwort: Ihr E-Mail-Passwort
```

### 6. HostEurope

```
Host: smtp.hosteurope.de
Port: 587
Verschlüsselung: TLS
Benutzername: ihre-email@ihre-domain.de
Passwort: Ihr E-Mail-Passwort
```

### 7. Mailgun (für hohe Versandvolumen)

```
Host: smtp.mailgun.org
Port: 587
Verschlüsselung: TLS
Benutzername: [Ihre Mailgun SMTP-Credentials]
Passwort: [Ihr Mailgun SMTP-Passwort]
```

**Vorteile:**
- API-basiert
- Hohe Zustellraten
- Detaillierte Analytics
- Kostenlos bis 5.000 E-Mails/Monat

### 8. Mailtrap (nur für Tests/Entwicklung)

```
Host: smtp.mailtrap.io
Port: 2525
Verschlüsselung: TLS
Benutzername: [Ihr Mailtrap Username]
Passwort: [Ihr Mailtrap Passwort]
```

**Hinweis:** E-Mails werden NICHT wirklich versendet, sondern nur im Mailtrap-Posteingang angezeigt. Perfekt für Tests!

## Port & Verschlüsselung

| Port | Verschlüsselung | Verwendung | Empfohlen |
|------|----------------|------------|-----------|
| 25 | Keine | Legacy, oft blockiert | ❌ Nein |
| 465 | SSL | Alte SSL-Verbindung | ⚠️ Funktioniert, aber veraltet |
| 587 | TLS | Moderner Standard | ✅ **Ja** |
| 2525 | TLS | Alternative (Mailtrap) | ✅ Für Tests |

**Empfehlung:** Port **587** mit **TLS**-Verschlüsselung

## Dynamische Mail-Konfiguration

### MailConfigService

Der `MailConfigService` lädt die SMTP-Einstellungen aus der Datenbank und wendet sie auf die Laravel Mail-Konfiguration an.

**Automatische Verwendung:**
```php
use App\Services\NotificationService;

$service = app(NotificationService::class);

// SMTP-Config wird automatisch angewendet vor dem Versand
$service->sendContactFormNotification(...);
$service->sendRegistrationNotification(...);
```

**Manuelle Verwendung:**
```php
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Mail;

$mailConfig = app(MailConfigService::class);
$mailConfig->applySettings();

// Jetzt können Sie Mails mit den konfigurierten Einstellungen senden
Mail::to('user@example.com')->send(new YourMailable());
```

**Verbindungstest:**
```php
use App\Services\MailConfigService;

$mailConfig = app(MailConfigService::class);

try {
    $mailConfig->testConnection();
    echo "SMTP-Verbindung erfolgreich!";
} catch (\Exception $e) {
    echo "Fehler: " . $e->getMessage();
}
```

## Fehlerbehebung

### Problem: "Connection could not be established"

**Lösungen:**
1. Prüfen Sie Host und Port
2. Stellen Sie sicher, dass Firewall Port 587/465 erlaubt
3. Bei Gmail: App-Passwort verwenden
4. Verschlüsselung korrekt? (TLS für 587, SSL für 465)

### Problem: "Authentication failed"

**Lösungen:**
1. Benutzername = vollständige E-Mail-Adresse
2. Passwort korrekt? (Bei Gmail: App-Passwort!)
3. 2-Faktor-Auth deaktiviert oder App-Passwort verwendet?

### Problem: "Mails kommen nicht an"

**Lösungen:**
1. Spam-Ordner prüfen
2. Absender-Adresse muss zu Domain passen (SPF/DKIM)
3. Bei deutschen Hostern: Domain verifizieren
4. Versandlimits des Providers beachten

### Problem: "SSL certificate problem"

**Lösungen:**
1. PHP OpenSSL-Extension installiert?
2. Aktuelle CA-Zertifikate im System?
3. Temporär: TLS statt SSL verwenden

## Sicherheit

### Best Practices

✅ **DO:**
- App-spezifische Passwörter verwenden (Gmail, Outlook)
- TLS-Verschlüsselung aktivieren
- Starke Passwörter für E-Mail-Konten
- Absender-Domain verifizieren (SPF, DKIM, DMARC)

❌ **DON'T:**
- Haupt-Passwörter in Konfiguration speichern
- Unverschlüsselte Verbindungen (Port 25)
- SMTP-Credentials in .env committen
- Produktions-Credentials in Tests verwenden

### SMTP-Credentials Sicherheit

Die SMTP-Credentials werden:
- In der Datenbank gespeichert (verschlüsselt empfohlen)
- Nicht im Code oder .env
- Nur über Backend-UI zugänglich
- Mit Passwort-Feld (revealable) verwaltet

## Testing

### Test-Mail senden

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Test-Nachricht', function ($message) {
    $message->to('test@example.com')
            ->subject('SMTP Test');
});
```

### Mit Mailtrap testen

1. Registrieren Sie sich bei [Mailtrap.io](https://mailtrap.io)
2. Erstellen Sie ein Inbox
3. Kopieren Sie die SMTP-Credentials
4. Konfigurieren Sie in `/admin/manage-email-settings`
5. Senden Sie Test-E-Mails
6. Prüfen Sie Mailtrap Inbox

## Versandlimits

Beachten Sie die Limits Ihres SMTP-Anbieters:

| Anbieter | Limit | Kosten |
|----------|-------|--------|
| Gmail | 500/Tag | Kostenlos |
| Outlook | 300/Tag | Kostenlos |
| IONOS | Unbegrenzt | Im Hosting enthalten |
| Mailgun | 5.000/Monat | Kostenlos, dann $35/Monat |
| Deutsche Hoster | Unterschiedlich | Im Hosting enthalten |

## Empfehlungen für Produktivbetrieb

### Für kleine Vereine (< 100 E-Mails/Tag)
- **Gmail** oder **Outlook** mit App-Passwort
- Kostenlos und zuverlässig
- Einfache Einrichtung

### Für mittlere Vereine (100-1000 E-Mails/Tag)
- **Deutscher Webhoster** (IONOS, Strato, All-Inkl)
- Unbegrenzte E-Mails
- Deutscher Support
- DSGVO-konform

### Für große Vereine (> 1000 E-Mails/Tag)
- **Mailgun** oder **SendGrid**
- API-basiert
- Hohe Zustellraten
- Detaillierte Analytics
- Professionelles Bounce-Management

## Zusammenfassung

✅ SMTP vollständig über UI konfigurierbar
✅ Unterstützung für alle gängigen Anbieter
✅ Automatische Anwendung bei Benachrichtigungen
✅ Sichere Passwort-Verwaltung
✅ Flexible Konfiguration (SMTP, Mailgun, SES, etc.)
✅ Test-Modus mit Mailtrap
