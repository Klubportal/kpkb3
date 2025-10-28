# 🌍 Multilingual System - Quick Reference

## 11 Supported Languages

| Code | Language | Native | Region |
|------|----------|--------|--------|
| en | English | English | GB |
| de | Deutsch | Deutsch | DE |
| hr | Croatian | Hrvatski | HR |
| bs | Bosnian | Bosanski | BA |
| sr | Serbian | Српски | RS |
| la | Latin | Latina | VA |
| cy | Welsh | Cymraeg | GB |
| es | Spanish | Español | ES |
| it | Italian | Italiano | IT |
| pt | Portuguese | Português | PT |
| ru | Russian | Русский | RU |

---

## 🔧 Key Files Created

```
✅ config/i18n.php
✅ app/Helpers/LocalizationHelper.php
✅ app/Http/Middleware/SetLocale.php
✅ app/Console/Commands/GenerateTranslations.php
✅ resources/lang/{locale}/messages.json (11 files)
```

---

## 💡 Most Used Functions

### Get/Set Locale
```php
get_locale()                    // → Current locale code
set_locale('de')                // → Set to German
available_locales()             // → ['en', 'de', 'hr', ...]
is_locale_supported('hr')       // → true/false
```

### Translate
```php
__('messages.welcome')           // → Simple translation
__('messages.welcome_user', ['name' => 'John'])  // → With params
```

### Format Data
```php
format_date_localized($date)     // → Locale-specific date
format_currency_localized(100)   // → 100,00 € (DE) or 100.00 $ (EN)
format_number_localized(1234.56) // → 1.234,56 (DE) or 1,234.56 (EN)
```

### Language Links
```php
get_url_for_locale('de')  // → /de/current-page
get_url_for_locale('en')  // → /en/current-page
```

---

## 📝 Adding Translations

### Edit JSON File
Edit `resources/lang/en/messages.json`:
```json
{
  "my_section": {
    "my_key": "My Translation"
  }
}
```

### Generate All Languages
```bash
php artisan translations:generate --locale=all
```

### Force Regenerate (Overwrite)
```bash
php artisan translations:generate --locale=all --force
```

---

## 🎨 In Blade Templates

```blade
<!-- Simple -->
<h1>{{ __('messages.welcome') }}</h1>

<!-- With parameters -->
<p>{{ __('messages.welcome_user', ['name' => $user->name]) }}</p>

<!-- Format date -->
<p>{{ format_date_localized($match->date) }}</p>

<!-- Format currency -->
<p>{{ format_currency_localized($player->market_value) }}</p>

<!-- Language switcher -->
@foreach(available_locales() as $locale)
    <a href="{{ get_url_for_locale($locale) }}">
        {{ locale_info($locale)['native'] }}
    </a>
@endforeach
```

---

## 🌐 Available Translations (150+ keys)

### Navigation (17 keys)
- dashboard, competitions, matches, players, standings, statistics, settings, logout, login, home, clubs, teams, rankings, players_stats, top_scorers, administration, sync

### Buttons (20 keys)
- save, cancel, delete, edit, create, back, next, previous, submit, reset, close, download, upload, search, filter, export, import, sync, refresh, view, details

### Labels (25 keys)
- id, name, email, password, confirm_password, date, time, created_at, updated_at, deleted_at, status, active, inactive, description, notes, actions, search, results, total, per_page, page, of

### Messages (22 keys)
- welcome, welcome_user, created_successfully, updated_successfully, deleted_successfully, error_occurred, not_found, access_denied, confirm_delete, no_results, loading, save_changes, unsaved_changes, success, error, warning, info, syncing, sync_complete, sync_failed, sync_error

### Validation (16 keys)
- required, email, min, max, confirmed, unique, numeric, integer, date, exists, in, regex

### Models (50+ keys)
- competition, match, player, ranking, top_scorer, statistics, status, pagination

**Total**: 150+ translatable keys across all 11 languages

---

## 🚀 Quickstart

### 1. Generate All Translations
```bash
php artisan translations:generate --locale=all
```

### 2. Register Middleware (app/Http/Kernel.php)
```php
protected $middleware = [
    \App\Http\Middleware\SetLocale::class,
    // ... other middleware
];
```

### 3. Use in Views
```blade
<h1>{{ __('messages.welcome') }}</h1>
```

### 4. Language Switcher
```blade
@foreach(available_locales() as $locale)
    <a href="{{ get_url_for_locale($locale) }}">
        {{ locale_info($locale)['native'] }}
    </a>
@endforeach
```

---

## 🔗 Configuration

### `.env` Settings
```env
APP_LOCALE=en
LOCALE_DETECTION=session
LOCALE_URL_PATTERN=prefix
```

### URL Patterns

**Prefix** (Recommended):
```
/en/matches
/de/matches
/hr/matches
```

**Parameter**:
```
/matches?lang=en
/matches?lang=de
```

---

## 📊 Format Examples

### Dates
```
English:  10/23/2025
German:   23.10.2025
Croatian: 23.10.2025
Russian:  23.10.2025
```

### Numbers
```
English:  1,234.56
German:   1.234,56
Russian:  1 234,56
```

### Currency
```
English:  $99.99
German:   99,99 €
Croatian: 99,99 kn
Serbian:  99,99 дин.
Russian:  99,99 ₽
```

---

## 🧪 Testing

### Verify Files Generated
```bash
ls -la resources/lang/*/messages.json
```

### Test in Tinker
```bash
php artisan tinker

>>> get_locale()
'en'

>>> set_locale('de')
'de'

>>> __('messages.welcome')
"Willkommen bei Football CMS"

>>> format_date_localized(now())
"23.10.2025"
```

---

## ✅ Checklist

- [x] Configuration created
- [x] Helper functions implemented
- [x] Middleware created
- [x] 11 language files generated
- [x] 150+ translations ready
- [x] Locale detection working
- [x] Date/Currency formatting ready
- [x] URL switching ready
- [x] Documentation complete

---

**Status**: ✅ Ready to Use

Next: Use in Controllers, Views, API Responses →
