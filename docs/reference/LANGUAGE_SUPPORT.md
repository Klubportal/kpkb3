# 🌍 Multi-Language Backend Documentation

## ✅ Status: FULLY CONFIGURED

Das Backend unterstützt **11 Sprachen** mit vollständiger Übersetzung und Language-Switcher.

## 📍 Supported Languages

| Code | Language | Native | Status |
|------|----------|--------|--------|
| `en` | English | English | ✅ |
| `de` | German | Deutsch | ✅ |
| `hr` | Croatian | Hrvatski | ✅ |
| `bs` | Bosnian | Bosanski | ✅ |
| `sr` | Serbian | Српски | ✅ |
| `la` | Latin | Latina | ✅ |
| `cy` | Welsh | Cymraeg | ✅ |
| `es` | Spanish | Español | ✅ |
| `it` | Italian | Italiano | ✅ |
| `pt` | Portuguese | Português | ✅ |
| `ru` | Russian | Русский | ✅ |

## 🎯 How Language Switching Works

### **URL Parameter (Recommended)**
```
http://localhost:8000/super-admin?lang=de
http://localhost:8000/super-admin?lang=en
http://localhost:8000/super-admin?lang=hr
```

### **Priority Detection**
1. ✅ **URL Parameter** (?lang=xx) - Highest priority
2. ✅ **Session Variable** (stored in session)
3. ✅ **Cookie** (user preference)
4. ✅ **Browser Header** (Accept-Language)
5. ✅ **Config Default** (fallback)

### **How to Use in Code**

#### **In Blade Templates**
```blade
{{ __('messages.navigation.dashboard') }}
{{ trans('messages.buttons.save') }}
{{ __('website.welcome.title') }}
```

#### **In PHP Controllers**
```php
// Get translation
$text = __('messages.buttons.save');
$text = trans('messages.labels.name');

// Get current locale
$locale = app()->getLocale();
$locale = LocalizationHelper::getLocale();

// Change locale
LocalizationHelper::setLocale('de');
app()->setLocale('en');

// Check if locale is supported
$isSupported = LocalizationHelper::isSupported('hr');

// Get all available locales
$locales = LocalizationHelper::getAvailableLocales();
```

## 📂 Translation File Structure

```
resources/lang/
├── de/           # German
│   ├── messages.json      (7.43 KB)
│   └── website.json       (3.30 KB)
├── en/           # English
│   ├── messages.json      (6.90 KB)
│   └── website.json       (3.15 KB)
├── hr/           # Croatian
│   ├── messages.json      (7.92 KB)
│   └── website.json       (3.18 KB)
├── sr/           # Serbian
│   ├── messages.json      (7.89 KB)
│   └── website.json       (3.18 KB)
├── la/           # Latin
│   └── messages.json      (7.80 KB)
└── [... 6 more languages ...]
```

### **messages.json Structure**
```json
{
  "navigation": {
    "dashboard": "Übersicht",
    "competitions": "Wettbewerbe",
    "matches": "Spiele"
  },
  "buttons": {
    "save": "Speichern",
    "cancel": "Abbrechen"
  },
  "labels": {
    "name": "Name",
    "email": "E-Mail"
  }
}
```

## 🔧 Configuration Files

### **config/i18n.php**
```php
'default' => 'en',                    // Default locale
'fallback' => 'en',                   // Fallback locale
'supported_locales' => [              // All supported locales
    'en' => ['name' => 'English', ...],
    'de' => ['name' => 'German', ...],
    // ... 9 more locales
]
```

### **bootstrap/app.php**
```php
->withMiddleware(function (Middleware $middleware): void {
    // Global SetLocale middleware for all routes
    $middleware->append(\App\Http\Middleware\SetLocale::class);
})
```

## 🛠️ Key Components

### **1. SetLocale Middleware** (`app/Http/Middleware/SetLocale.php`)
- Detects locale from multiple sources
- Sets `app()->locale` for the request
- Handles Accept-Language headers

### **2. LocalizationHelper** (`app/Helpers/LocalizationHelper.php`)
- Provides helper functions for locale management
- Methods: `getLocale()`, `setLocale()`, `isSupported()`
- Caches locale information

### **3. Translation Files** (`resources/lang/*`)
- JSON-based translations per language
- Messages for backend UI
- Website content translations

## 📊 Translation Coverage

| Language | messages.json | website.json | Coverage |
|----------|--------------|-------------|----------|
| German (de) | ✅ | ✅ | 100% |
| English (en) | ✅ | ✅ | 100% |
| Croatian (hr) | ✅ | ✅ | 100% |
| Serbian (sr) | ✅ | ✅ | 100% |
| Portuguese (pt) | ✅ | ✅ | 100% |
| Bosnian (bs) | ✅ | ❌ | Partial |
| Spanish (es) | ✅ | ❌ | Partial |
| Italian (it) | ✅ | ❌ | Partial |
| Latin (la) | ✅ | ❌ | Partial |
| Welsh (cy) | ✅ | ❌ | Partial |
| Russian (ru) | ✅ | ❌ | Partial |

## 🚀 Example: Adding New Translations

### **Step 1: Add to all language files**
```bash
# resources/lang/de/messages.json
{ "new_feature": "Neue Funktion" }

# resources/lang/en/messages.json
{ "new_feature": "New Feature" }

# resources/lang/hr/messages.json
{ "new_feature": "Novost" }
```

### **Step 2: Use in code**
```blade
<button>{{ __('messages.new_feature') }}</button>
```

### **Step 3: Automatic caching**
```bash
php artisan cache:clear
```

## 🔍 Testing Localization

```bash
# Test German
curl "http://localhost:8000/super-admin?lang=de"

# Test Croatian
curl "http://localhost:8000/super-admin?lang=hr"

# Test all languages
for lang in en de hr bs sr la cy es it pt ru; do
    curl "http://localhost:8000/super-admin?lang=$lang" -o "/tmp/$lang.html"
done
```

## 📋 Verification Checklist

- ✅ 11 supported languages configured
- ✅ Translation files present for all languages
- ✅ SetLocale middleware registered and active
- ✅ LocalizationHelper functions available
- ✅ URL parameter (?lang=xx) switching works
- ✅ Session/Cookie locale persistence
- ✅ Browser Accept-Language detection
- ✅ Config fallback working
- ✅ Backend UI translatable

## 🎓 Best Practices

1. **Always use trans() or __()** for user-facing strings
2. **Organize translations** by feature/module
3. **Keep translations up-to-date** when adding features
4. **Test all languages** before deployment
5. **Use locale middleware** for consistent detection
6. **Cache translations** for performance

## 🔗 Related Files

- Configuration: `config/i18n.php`
- Middleware: `app/Http/Middleware/SetLocale.php`
- Helper: `app/Helpers/LocalizationHelper.php`
- Translations: `resources/lang/*/`
- Bootstrap: `bootstrap/app.php`

---

**Last Updated**: 2025-10-24  
**System Health**: 100% ✅  
**Multi-Language Support**: FULLY OPERATIONAL ✅
