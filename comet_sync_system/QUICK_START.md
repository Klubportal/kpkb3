# QUICK START GUIDE

## 🚀 Schnellstart in 5 Schritten

### 1. Konfiguration kopieren

```bash
# In .env einfügen:
COMET_API_URL=https://api-hns.analyticom.de/api/export/comet
COMET_USERNAME=nkprigorje
COMET_PASSWORD=3c6nR$dS
```

### 2. Models kopieren

```bash
# Von comet_sync_system/models/ nach app/Models/Comet/
cp comet_sync_system/models/*.php app/Models/Comet/
```

### 3. Service kopieren

```bash
# Von comet_sync_system/services/ nach app/Services/
cp comet_sync_system/services/CometApiService.php app/Services/
```

### 4. Migrationen kopieren

```bash
# Von comet_sync_system/migrations/ nach database/migrations/
cp comet_sync_system/migrations/*.php database/migrations/
```

### 5. Migrationen ausführen

```bash
# Alle Comet-Migrationen ausführen
php artisan migrate
```

### 6. Sync ausführen

```bash
# Script kopieren
cp comet_sync_system/scripts/sync_nk_prigorje_comet_data.php .

# Ausführen
php sync_nk_prigorje_comet_data.php
```

---

## ✅ Erwartetes Ergebnis

```
╔═══════════════════════════════════════════════════════════════╗
║                       SYNC SUMMARY                            ║
╠═══════════════════════════════════════════════════════════════╣
║ Competitions                                               11 ║
║ Teams                                                       0 ║
║ Players                                                   254 ║
║ Matches                                                     0 ║
║ Top Scorers                                                53 ║
╚═══════════════════════════════════════════════════════════════╝

✅ SYNC COMPLETED SUCCESSFULLY!
```

---

## 📋 Die 11 Competitions

1. ⚽ PRVA ZAGREBAČKA LIGA - SENIORI 25/26
2. 🎯 1. ZNL JUNIORI 25/26 **(WICHTIG!)**
3. 👦 1. ZNL KADETI 25/26
4. 🧒 2. ZNL PIONIRI 25/26
5. 👶 2. ZNL MLAĐI PIONIRI 25/26
6. 🏃 2. "B1"ZNL LIMAĆI grupa "A" 25/26
7. 🏃 2. "B2"ZNL LIMAĆI grupa "A" 25/26
8. 🏃 2. "B1"ZNL ZAGIĆI grupa "A" 25/26
9. 🏃 2. "B2"ZNL ZAGIĆI grupa "A" 25/26
10. 👴 57. prvenstvo veterana ZNS, 1. liga skupina B
11. 🏆 KUP ZNS-a - SENIORI 25/26

---

## 🔍 Verifikation

Nach dem Sync kannst du prüfen:

```bash
# Competitions zählen
php artisan tinker --execute="echo DB::connection('central')->table('comet_club_competitions')->count();"
# Erwartung: 11

# Spieler zählen
php artisan tinker --execute="echo DB::connection('central')->table('comet_players')->where('club_fifa_id', 598)->count();"
# Erwartung: 254

# Top Scorers zählen
php artisan tinker --execute="echo DB::connection('central')->table('comet_top_scorers')->count();"
# Erwartung: 53
```

---

## ⚠️ WICHTIG

**Team FIFA ID = 598** (NICHT 618!)

Die korrekte ID ist **598** - damit bekommst du genau die 11 Competitions inkl. JUNIORI!
