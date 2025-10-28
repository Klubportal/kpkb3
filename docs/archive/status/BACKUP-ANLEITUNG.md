# Klubportal Backup & Restore - Anleitung

## Übersicht

Das System verwendet **Spatie Laravel Backup** mit Filament UI-Integration für vollständige Backup- und Wiederherstellungsfunktionen.

---

## 🎯 Backup-Funktionen

### 1. **Backup über Admin-Panel erstellen**

**Zugriff:**
- URL: http://localhost:8000/admin/backups
- Login: info@klubportal.com / Zagreb123!

**Schritte:**
1. Im Central Admin einloggen
2. Navigation: "Backups" öffnen
3. Button "Create Backup" klicken
4. Backup-Typ wählen:
   - **Only DB** - Nur Datenbank (schnell, ~5 MB)
   - **Only Files** - Nur Dateien (langsam, je nach Projektgröße)
   - **DB and Files** - Vollständig (empfohlen)

**Ergebnis:**
- Backup wird im Hintergrund erstellt
- Speicherort: `storage/app/Klubportal-Admin/`
- Format: `.zip` Datei mit Timestamp
- Anzeige: Tabelle mit Erstellungsdatum, Größe, Disk

---

### 2. **Backup über Terminal erstellen**

```powershell
# Vollständiges Backup (DB + Dateien)
php artisan backup:run

# Nur Datenbank
php artisan backup:run --only-db

# Nur Dateien
php artisan backup:run --only-files
```

**Ausgabe:**
```
Starting backup...
Dumping database klubportal_landlord...
Creating zip archive...
Backup completed successfully.
```

---

## 🔄 Wiederherstellung (Restore)

### **Option 1: Via Terminal (Interaktiv)**

```powershell
php artisan backup:restore
```

**Ablauf:**
1. Zeigt Liste aller verfügbaren Backups:
   ```
   Verfügbare Backups:
   #   Datei                              Größe    Erstellt am
   1   Klubportal-Admin_2025-10-25.zip   4.2 MB   25.10.2025 14:30
   2   Klubportal-Admin_2025-10-24.zip   4.1 MB   24.10.2025 02:00
   ```

2. Nummer eingeben (z.B. `1`)

3. Sicherheitsabfrage bestätigen:
   ```
   WARNUNG: Diese Aktion überschreibt alle aktuellen Daten! Fortfahren? (yes/no)
   ```

4. Wiederherstellung läuft:
   ```
   Starte Wiederherstellung...
   Extrahiere Backup...
   Suche Datenbank-Dump...
   Gefunden: mysql-klubportal_landlord.sql
   Importiere Datenbank...
   Temporäre Dateien bereinigt.
   ✓ Backup erfolgreich wiederhergestellt!
   Bitte neu einloggen.
   ```

### **Option 2: Direkt mit Dateipfad**

```powershell
php artisan backup:restore "C:\xampp\htdocs\Klubportal-Laravel12\storage\app\Klubportal-Admin\Klubportal-Admin_2025-10-25_14-30-00.zip"
```

---

## 📥 Backup herunterladen

**Admin-Panel:**
1. Navigation → "Backups"
2. Bei gewünschtem Backup auf **Download-Icon** klicken
3. ZIP-Datei wird heruntergeladen

**Terminal:**
```powershell
# Backups befinden sich in:
cd storage\app\Klubportal-Admin
dir

# Kopieren nach Desktop
Copy-Item "Klubportal-Admin_2025-10-25.zip" "$env:USERPROFILE\Desktop\"
```

---

## 🗑️ Alte Backups löschen

### **Automatische Bereinigung**

```powershell
php artisan backup:clean
```

**Regeln (config/backup.php):**
- Alle Backups aufbewahren für: **7 Tage**
- Tägliche Backups aufbewahren für: **16 Tage**
- Wöchentliche Backups aufbewahren für: **8 Wochen**
- Monatliche Backups aufbewahren für: **4 Monate**
- Jährliche Backups aufbewahren für: **2 Jahre**

### **Manuell löschen**

**Admin-Panel:**
- Bei Backup auf **Trash-Icon** klicken
- Bestätigen

**Terminal:**
```powershell
Remove-Item "storage\app\Klubportal-Admin\Klubportal-Admin_2025-10-20.zip"
```

---

## ⏰ Automatische Backups einrichten

### **1. Task Scheduler (Windows)**

```powershell
# PowerShell als Administrator öffnen

# Tägliches Backup um 2:00 Uhr
$action = New-ScheduledTaskAction -Execute "php" -Argument "artisan backup:run" -WorkingDirectory "C:\xampp\htdocs\Klubportal-Laravel12"
$trigger = New-ScheduledTaskTrigger -Daily -At 2am
Register-ScheduledTask -TaskName "Klubportal Backup" -Action $action -Trigger $trigger -Description "Tägliches Backup der Klubportal-Datenbank"
```

### **2. Manual Scheduler**

**Datei erstellen:** `backup-scheduler.bat`

```batch
@echo off
cd C:\xampp\htdocs\Klubportal-Laravel12
php artisan backup:run --only-db
php artisan backup:clean
```

**Task Scheduler:**
1. Win + R → `taskschd.msc`
2. "Create Basic Task"
3. Name: "Klubportal Backup"
4. Trigger: Daily, 2:00 AM
5. Action: Start a program
6. Program: `C:\xampp\htdocs\Klubportal-Laravel12\backup-scheduler.bat`

---

## 📊 Backup-Status prüfen

```powershell
# Liste alle Backups
php artisan backup:list

# Backup-Monitor (Dashboard)
# Admin-Panel → Backups → Status-Tabelle zeigt:
# - Disk: local
# - Reachable: ✓ 
# - Healthy: ✓
# - Amount of backups: 5
# - Newest backup: 25.10.2025 14:30
# - Used storage: 21.5 MB
```

---

## ⚙️ Konfiguration

**Datei:** `config/backup.php`

### **Wichtige Einstellungen:**

```php
'backup' => [
    'name' => 'Klubportal-Admin', // Backup-Name
    
    'source' => [
        'databases' => ['mysql'], // Welche DBs sichern
        
        'files' => [
            'include' => [
                base_path(), // Alle Projektdateien
            ],
            'exclude' => [
                base_path('vendor'),      // Vendor nicht
                base_path('node_modules'), // Node nicht
                base_path('storage/logs'), // Logs nicht
            ],
        ],
    ],
    
    'destination' => [
        'disks' => ['local'], // Speicherorte
    ],
],

'cleanup' => [
    'default_strategy' => [
        'keep_all_backups_for_days' => 7,
        'keep_daily_backups_for_days' => 16,
        'keep_weekly_backups_for_weeks' => 8,
        'keep_monthly_backups_for_months' => 4,
        'keep_yearly_backups_for_years' => 2,
        'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
    ],
],
```

---

## 🚨 Fehlerbehandlung

### **Problem: "MySQL command not found"**

**Lösung:**
```powershell
# MySQL zum PATH hinzufügen
$env:Path += ";C:\xampp\mysql\bin"

# Permanent (System Properties → Environment Variables):
# C:\xampp\mysql\bin zu Path hinzufügen
```

### **Problem: "Permission denied"**

**Lösung:**
```powershell
# Rechte für storage-Ordner setzen
icacls "storage" /grant "Benutzer:(OI)(CI)F" /T
```

### **Problem: "Backup too large"**

**Lösung:**
```php
// config/backup.php
'timeout' => 600, // Timeout auf 10 Minuten erhöhen

// Nur DB sichern statt Files
php artisan backup:run --only-db
```

---

## 📦 Backup-Inhalt

**ZIP-Struktur:**
```
Klubportal-Admin_2025-10-25_14-30-00.zip
├── db-dumps/
│   └── mysql-klubportal_landlord.sql  (Datenbank-Dump)
├── manifest.txt                        (Backup-Info)
└── [Optional: Projekt-Dateien]
```

**Datenbank-Dump enthält:**
- ✅ Tenants-Tabelle (11 Einträge)
- ✅ Plans-Tabelle (3 Pläne)
- ✅ Domains-Tabelle
- ✅ Users-Tabelle (Central Admin)
- ✅ Alle anderen Tabellen

---

## 🎯 Best Practices

1. **Vor Updates/Changes:**
   ```powershell
   php artisan backup:run
   ```

2. **Regelmäßige Backups:**
   - Täglich: Nur DB (schnell)
   - Wöchentlich: DB + Files (vollständig)

3. **Off-Site Backup:**
   - Backups regelmäßig auf externe Festplatte kopieren
   - Cloud-Storage nutzen (siehe unten)

4. **Test-Restore:**
   - Monatlich Restore in Test-Umgebung testen

---

## ☁️ Cloud-Backup (Optional)

**AWS S3, Google Drive, Dropbox:**

```powershell
# S3-Treiber installieren
composer require league/flysystem-aws-s3-v3

# config/filesystems.php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
],

# config/backup.php
'destination' => [
    'disks' => ['local', 's3'], // Beide Speicherorte
],
```

---

## 🔗 Nützliche Befehle

```powershell
# Backup erstellen
php artisan backup:run

# Backup-Status
php artisan backup:list

# Alte Backups löschen
php artisan backup:clean

# Backup wiederherstellen
php artisan backup:restore

# Monitor-Benachrichtigung testen
php artisan backup:monitor
```

---

## 📞 Support

Bei Problemen:
1. Logs prüfen: `storage/logs/laravel.log`
2. Backup-Logs: Output des `backup:run` Befehls
3. MySQL-Logs: `C:\xampp\mysql\data\*.err`

**Erfolgreiche Wiederherstellung erkennbar an:**
- ✅ Command zeigt "✓ Backup erfolgreich wiederhergestellt!"
- ✅ Login im Admin-Panel funktioniert
- ✅ Tenant-Daten sind vorhanden
- ✅ Dashboard zeigt korrekte Statistiken
