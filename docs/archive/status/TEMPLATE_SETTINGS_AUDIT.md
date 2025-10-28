# Template Settings - Backend vs Frontend Überprüfung

## ✅ KORREKT VERBUNDEN

### Grundeinstellungen
- ✅ `website_name` - Verwendet in:
  - Layout: Title
  - Navbar: Header
  - Footer: Firmenname
  - Home: Hero-Titel

- ✅ `slogan` - Verwendet in:
  - Navbar: Untertitel unter Logo
  - Home: Hero-Untertitel

- ✅ `logo` - Verwendet in:
  - Navbar: Logo-Bild

- ⚠️ `footer_about` - NICHT VERWENDET (sollte aber sein!)

### Farben
- ✅ `primary_color` - CSS Variable + Klassen
- ✅ `secondary_color` - CSS Variable + Klassen
- ✅ `accent_color` - CSS Variable
- ✅ `header_bg_color` - CSS Variable (.header-bg)
- ✅ `header_text_color` - CSS Variable (.header-text)
- ✅ `badge_bg_color` - CSS Variable (.badge-bg)
- ✅ `badge_text_color` - CSS Variable (.badge-text)
- ✅ `footer_bg_color` - CSS Variable (.footer-bg)
- ✅ `footer_text_color` - CSS Variable (.footer-text)

### Kontakt
- ✅ `footer_email` - Verwendet in Footer
- ✅ `footer_phone` - Verwendet in Footer
- ❌ `footer_address` - NICHT VERWENDET

### Social Media
- ✅ `facebook_url` - Verwendet in Footer
- ✅ `instagram_url` - Verwendet in Footer
- ✅ `youtube_url` - Verwendet in Footer
- ❌ `twitter_url` - NICHT VERWENDET
- ❌ `tiktok_url` - NICHT VERWENDET

## ❌ PROBLEME GEFUNDEN

1. **`footer_about`** - Feld existiert im Backend, wird aber nicht im Frontend angezeigt
   - Sollte in Footer unter "Über uns" erscheinen
   - Aktuell wird ein Fallback-Text angezeigt: "Dein Fußballverein"

2. **`footer_address`** - Feld existiert im Backend, wird aber nicht angezeigt
   - Sollte im Footer unter Kontakt erscheinen

3. **`twitter_url`** - Feld existiert im Backend, wird aber nicht angezeigt
   - Sollte als Social Media Icon im Footer erscheinen

4. **`tiktok_url`** - Feld existiert im Backend, wird aber nicht angezeigt
   - Sollte als Social Media Icon im Footer erscheinen

5. **Falsches Feld im Footer:**
   - Footer verwendet `$settings->club_description` (Zeile 6)
   - Sollte `$settings->footer_about` sein

## 🔧 NOTWENDIGE KORREKTUREN

### 1. Footer: club_description → footer_about
### 2. Footer: footer_address hinzufügen
### 3. Footer: Twitter/X Icon hinzufügen
### 4. Footer: TikTok Icon hinzufügen
