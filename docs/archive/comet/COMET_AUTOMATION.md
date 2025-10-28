# Comet API Automatisierung

## ✅ Einrichtung abgeschlossen!

Alle Comet API Syncs sind jetzt **vollautomatisch** über Laravel Artisan Commands verfügbar.

---

## 📋 Verfügbare Commands

### Einzelne Syncs
```bash
# Matches synchronisieren
php artisan comet:sync-matches

# Rankings synchronisieren
php artisan comet:sync-rankings

# Top Scorers synchronisieren
php artisan comet:sync-topscorers
```

### Alles auf einmal
```bash
# ALLE Daten synchronisieren (Matches + Rankings + Top Scorers)
php artisan comet:sync-all
```

---

## ⏰ Automatische Ausführung

### Lokal (Entwicklung)
Laravel Scheduler läuft im Hintergrund. Starten Sie:

```bash
php artisan schedule:work
```

Dies startet den Scheduler, der automatisch **täglich um 3:00 Uhr** alle Daten syncet.

**Oder** für Tests jede Minute prüfen:
```bash
php artisan schedule:work --verbose
```

### Auf dem Webspace (Produktion)

#### Option 1: Cron Job (Empfohlen)
Richten Sie einen Cron Job ein, der jede Minute Laravel's Scheduler prüft:

```bash
* * * * * cd /pfad/zu/klubportal && php artisan schedule:run >> /dev/null 2>&1
```

Der Scheduler entscheidet dann selbst, welche Tasks ausgeführt werden müssen.

#### Option 2: Direkter Cron Job
Wenn Sie den Sync zu einer bestimmten Zeit haben möchten:

```bash
# Täglich um 3:00 Uhr
0 3 * * * cd /pfad/zu/klubportal && php artisan comet:sync-all >> /dev/null 2>&1

# Alle 6 Stunden
0 */6 * * * cd /pfad/zu/klubportal && php artisan comet:sync-all >> /dev/null 2>&1
```

---

## 📅 Aktueller Zeitplan

| Task | Zeitplan | Beschreibung |
|------|----------|--------------|
| `comet:sync-all` | Täglich 03:00 | Syncet Matches, Rankings & Top Scorers |

**Zeitzone:** Europe/Zagreb

---

## 🔄 Zeitplan anpassen

Bearbeiten Sie `routes/console.php`:

```php
// Beispiele für verschiedene Zeitpläne:

// Alle 3 Stunden
Schedule::command('comet:sync-all')->everyThreeHours();

// Zweimal täglich (morgens und abends)
Schedule::command('comet:sync-all')->twiceDaily(3, 15);

// Nur an Wochentagen
Schedule::command('comet:sync-all')->weekdays()->at('03:00');

// Jeden Montag
Schedule::command('comet:sync-all')->weekly()->mondays()->at('03:00');
```

Mehr Optionen: https://laravel.com/docs/scheduling

---

## 📊 Logs prüfen

Alle Commands schreiben in Laravel Logs:

```bash
# Log-Datei anzeigen
tail -f storage/logs/laravel.log
```

---

## ✅ Was wurde automatisiert?

- ✅ **Matches Sync** (1500+ Spiele)
- ✅ **Rankings Sync** (137 Rankings mit Teams & Logos)
- ✅ **Top Scorers Sync** (800+ Torjäger)
- ✅ **Logo Loading** (178 Team-Logos)
- ✅ **Smart Update** (Nur geänderte Daten werden geschrieben)
- ✅ **Progress Bar** (Visuelles Feedback)
- ✅ **Statistiken** (Inserted/Updated/Skipped)

---

## 🚀 Schnellstart

### Lokale Entwicklung
```bash
# 1. Scheduler im Hintergrund starten (einmalig)
php artisan schedule:work &

# 2. Fertig! Läuft automatisch täglich um 3:00 Uhr
```

### Webspace (Produktion)
```bash
# 1. Cron Job einrichten (siehe oben)
# 2. Fertig! Laravel kümmert sich um den Rest
```

---

## 🛠️ Manueller Sync

Falls Sie sofort syncen möchten (nicht warten bis 3:00 Uhr):

```bash
php artisan comet:sync-all
```

---

## 📈 Vorteile

✅ **Keine manuellen PHP-Skripte mehr nötig**
✅ **Laravel-native Integration**
✅ **Funktioniert lokal & auf Webspace**
✅ **Automatische Zeitplanung**
✅ **Bessere Fehlerbehandlung**
✅ **Progress Bars & Statistiken**
✅ **Log-Integration**

---

## 🔍 Troubleshooting

### Scheduler läuft nicht?
```bash
# Prüfen ob Tasks geplant sind
php artisan schedule:list

# Schedule manuell ausführen (für Tests)
php artisan schedule:run
```

### Command findet sync_helpers.php nicht?
Die Datei `lib/sync_helpers.php` muss existieren. Pfad wird automatisch über `base_path()` gefunden.

### Datenbankverbindung fehlgeschlagen?
Commands verwenden hardcoded `localhost`, `root`, `''`. Für Webspace in den Commands anpassen oder .env verwenden.

---

**Erstellt:** 27. Oktober 2025
**Status:** ✅ Produktionsbereit
