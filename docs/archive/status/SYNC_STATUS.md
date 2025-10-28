# ✅ Comet API Automatisierung - Vollständig eingerichtet

## 📊 Aktueller Status

🟢 **Scheduler läuft** - Automatische Synchronisierung ist aktiv

---

## ⏰ Automatischer Zeitplan

| Task | Intervall | Beschreibung |
|------|-----------|--------------|
| `comet:sync-all` | **Alle 5 Minuten** | Syncet Matches, Rankings & Top Scorers von Comet API → Landlord DB |
| `tenant:sync-comet --all` | **Alle 10 Minuten** | Syncet Daten von Landlord DB → Alle Tenant-DBs |

**Zeitzone:** Europe/Zagreb

---

## 🔄 Datenfluss

```
Comet API 
   ↓ (alle 5 Min)
Landlord DB (klubportal_landlord)
   ↓ (alle 10 Min)
Tenant DBs (tenant_nkprigorjem, etc.)
```

---

## 📋 Verfügbare Commands

### Landlord Syncs (Comet API → Landlord DB)
```bash
php artisan comet:sync-matches      # Nur Matches
php artisan comet:sync-rankings     # Nur Rankings  
php artisan comet:sync-topscorers   # Nur Top Scorers
php artisan comet:sync-all          # Alles auf einmal
```

### Tenant Syncs (Landlord DB → Tenant DBs)
```bash
php artisan tenant:sync-comet nkprigorjem   # Nur NK Prigorje
php artisan tenant:sync-comet --all         # Alle Tenants
```

---

## 🚀 Scheduler Verwaltung

### Lokale Entwicklung
```bash
# Scheduler starten (läuft dauerhaft)
php artisan schedule:work

# Mit verbose Output
php artisan schedule:work --verbose

# Zeitplan anzeigen
php artisan schedule:list
```

### Webspace (Produktion)
Cron Job einrichten (einmalig):
```bash
* * * * * cd /pfad/zu/klubportal && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📈 Was wird synchronisiert?

### Landlord Database (klubportal_landlord)
- ✅ **1500+ Matches** (alle Wettbewerbe)
- ✅ **137 Rankings** (mit Team-Namen & Logos)
- ✅ **800+ Top Scorers** (mit Vereins-Logos)
- ✅ **178 Team-Logos** automatisch zugeordnet

### Tenant Databases (z.B. tenant_nkprigorjem)
- ✅ **Matches** (nur Spiele des eigenen Vereins)
- ✅ **Rankings** (komplette Tabellen aller Wettbewerbe)
- ✅ **Top Scorers** (Spieler des eigenen Vereins)
- ✅ **Alle Team-Daten** (Namen & Logos aller Gegner)

---

## 💡 Besondere Features

### Smart Updates
- ✅ Nur **geänderte Daten** werden geschrieben
- ✅ `upsert_if_changed()` erkennt identische Records
- ✅ Minimale Datenbank-Last

### Progress Tracking
- ✅ Progress Bars für alle Syncs
- ✅ Statistiken (Inserted/Updated/Skipped)
- ✅ Fehlerbehandlung & Logging

### Team Logo Loading
- ✅ Automatisches Laden aus `public/images/kp_team_logo_images/`
- ✅ Bevorzugt PNG Format
- ✅ Fallback auf JPG/GIF

---

## 🎯 Nächste Ausführungen

- **Comet Sync:** In ca. 3 Minuten
- **Tenant Sync:** In ca. 8 Minuten

Danach alle 5 bzw. 10 Minuten automatisch!

---

## 🛠️ Konfiguration anpassen

### Zeitplan ändern
Bearbeiten Sie `routes/console.php`:

```php
// Beispiele:

// Alle 15 Minuten
Schedule::command('comet:sync-all')->everyFifteenMinutes();

// Jede Stunde
Schedule::command('comet:sync-all')->hourly();

// Nur zu bestimmten Zeiten
Schedule::command('comet:sync-all')->hourlyAt(15); // :15 jeder Stunde

// Nur an Spieltagen (z.B. Samstag/Sonntag)
Schedule::command('comet:sync-all')
    ->everyFiveMinutes()
    ->when(function () {
        return now()->isWeekend();
    });
```

### Tenant Mapping erweitern
Bearbeiten Sie `app/Console/Commands/SyncTenantData.php`:

```php
private function getTeamFifaIdForTenant($tenantId, $landlord)
{
    $mapping = [
        'nkprigorjem' => 598,  // NK Prigorje Markuševec
        'nksava' => 619,       // NK Sava Zagreb
        'nkzet' => 624,        // NK ZET
        // Weitere Vereine hier hinzufügen
    ];
    
    return $mapping[$tenantId] ?? null;
}
```

---

## 📊 Monitoring

### Logs prüfen
```bash
# Laravel Log
tail -f storage/logs/laravel.log

# Scheduler Output (wenn mit --verbose)
# Läuft im separaten Terminal-Fenster
```

### Manuell testen
```bash
# Kompletten Sync durchführen
php artisan comet:sync-all

# Tenant-Sync testen
php artisan tenant:sync-comet nkprigorjem
```

---

## ✅ Vorteile der Automatisierung

| Vorher | Nachher |
|--------|---------|
| ❌ Manuelle PHP-Skripte | ✅ Laravel Commands |
| ❌ Manuelles Ausführen | ✅ Automatisch alle 5/10 Min |
| ❌ Keine Übersicht | ✅ Progress Bars & Statistiken |
| ❌ Fehleranfällig | ✅ Try-Catch & Logging |
| ❌ Nur lokal | ✅ Lokal & Webspace ready |

---

## 🔍 Troubleshooting

### "Command not found"
```bash
php artisan optimize:clear
```

### Scheduler läuft nicht
```bash
# Prüfen ob Tasks geplant sind
php artisan schedule:list

# Manuell ausführen (Test)
php artisan schedule:run
```

### Keine Daten in Tenant-DB
1. Prüfen Sie das Team-Mapping in `SyncTenantData.php`
2. Tenant muss in `tenants` Tabelle existieren
3. Tenant-Datenbank muss existieren

---

**Status:** ✅ Produktionsbereit
**Scheduler:** 🟢 Aktiv
**Erstellt:** 27. Oktober 2025
