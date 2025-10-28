# KLUBPORTAL - BACKUP & RESTORE ANLEITUNG

## 📦 BACKUP ERSTELLEN

### Automatisches Backup (Empfohlen)
```powershell
.\backup.ps1
```

Das Script sichert automatisch:
- ✓ Central Datenbank (central.sqlite)
- ✓ Alle Tenant Datenbanken
- ✓ Storage Files (Uploads, Media Library)
- ✓ Public Files (Images, Logos)
- ✓ .env Konfiguration
- ✓ composer.json & composer.lock

Das Backup wird als ZIP in `backups/backup_DATUM_ZEIT.zip` gespeichert.

---

## 🔄 BACKUP WIEDERHERSTELLEN

### Automatische Wiederherstellung (Empfohlen)
```powershell
.\restore.ps1
```

Das Script:
1. Zeigt alle verfügbaren Backups
2. Fragt welches Backup wiederhergestellt werden soll
3. Warnt vor Datenüberschreibung
4. Stellt alles automatisch wieder her
5. Führt Post-Restore Befehle aus (storage:link, optimize:clear)

---

## 📝 MANUELLES BACKUP

Falls du ein manuelles Backup brauchst:

### 1. Datenbanken
```powershell
# Backup-Ordner erstellen
New-Item -ItemType Directory -Path "manual_backup" -Force

# Central DB kopieren
Copy-Item "database\central.sqlite" "manual_backup\"

# Tenant DBs kopieren
Copy-Item -Path "database\tenant\*" -Destination "manual_backup\tenant\" -Recurse
```

### 2. Storage Files
```powershell
# Storage kopieren
Copy-Item -Path "storage\app\public" -Destination "manual_backup\storage\" -Recurse

# Public Files kopieren
Copy-Item -Path "public\images" -Destination "manual_backup\public_images\" -Recurse
Copy-Item "public\logo.svg" "manual_backup\"
Copy-Item "public\favicon.ico" "manual_backup\"
```

### 3. Konfiguration
```powershell
# .env sichern
Copy-Item ".env" "manual_backup\"

# Composer Files
Copy-Item "composer.json" "manual_backup\"
Copy-Item "composer.lock" "manual_backup\"
```

---

## ⚙️ MANUELLE WIEDERHERSTELLUNG

### 1. Datenbanken zurückspielen
```powershell
Copy-Item "manual_backup\central.sqlite" "database\"
Copy-Item -Path "manual_backup\tenant\*" -Destination "database\tenant\" -Recurse
```

### 2. Files zurückspielen
```powershell
Copy-Item -Path "manual_backup\storage\*" -Destination "storage\app\public\" -Recurse
Copy-Item -Path "manual_backup\public_images\*" -Destination "public\images\" -Recurse
Copy-Item "manual_backup\logo.svg" "public\"
Copy-Item "manual_backup\favicon.ico" "public\"
```

### 3. .env wiederherstellen
```powershell
# VORSICHT: Prüfe vorher die Einstellungen!
Copy-Item "manual_backup\.env" ".env"
```

### 4. Post-Restore Befehle
```powershell
php artisan storage:link
php artisan optimize:clear
```

---

## 📅 BACKUP-STRATEGIE (Empfohlen)

### Tägliche Backups (vor Änderungen)
```powershell
# Vor größeren Änderungen
.\backup.ps1
```

### Wöchentliche Backups
Erstelle jeden Montag ein Backup und speichere es extern (USB, Cloud, etc.)

### Monatliche Backups
Archiviere monatliche Backups langfristig

---

## 🗂️ BACKUP-VERWALTUNG

### Alte Backups löschen
```powershell
# Alle Backups älter als 30 Tage löschen
Get-ChildItem "backups\backup_*.zip" | 
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) } | 
    Remove-Item
```

### Backup extern speichern
```powershell
# Auf USB-Stick kopieren
Copy-Item "backups\backup_*.zip" "E:\Klubportal_Backups\" -Force

# Auf Netzlaufwerk
Copy-Item "backups\backup_*.zip" "\\server\backups\klubportal\" -Force
```

---

## 🚨 NOTFALL-WIEDERHERSTELLUNG

Falls alles schiefgeht:

1. **Neuinstallation Laravel**
   ```bash
   composer install
   ```

2. **Backup wiederherstellen**
   ```powershell
   .\restore.ps1
   ```

3. **Prüfen**
   ```bash
   php artisan migrate:status
   php artisan optimize:clear
   php artisan serve
   ```

---

## ✅ BACKUP TESTEN

Teste regelmäßig ob deine Backups funktionieren:

1. Erstelle Backup
2. Erstelle Kopie deines Projekts in anderem Ordner
3. Restore das Backup dort
4. Teste ob alles läuft

**Wichtig**: Ein Backup ist nur gut, wenn es wiederherstellbar ist!

---

## 📞 SUPPORT

Bei Problemen:
- Prüfe `backups/backup_*/BACKUP_INFO.txt` für Details
- Checke Dateigrößen (sollten nicht 0 KB sein)
- Prüfe .env Einstellungen nach Restore
- Storage Link: `php artisan storage:link`
- Cache: `php artisan optimize:clear`
