@echo off
REM Backup-Script für Klubportal mit korrektem MySQL-Pfad

echo ========================================
echo  KLUBPORTAL BACKUP SCRIPT
echo ========================================
echo.

REM Setze MySQL-Pfad
set PATH=%PATH%;C:\xampp\mysql\bin

REM Wechsle ins Projektverzeichnis
cd /d %~dp0

echo [1/3] Pruefe MySQL-Verbindung...
mysqldump --version >nul 2>&1
if errorlevel 1 (
    echo FEHLER: mysqldump nicht gefunden!
    echo Bitte pruefen Sie den MySQL-Pfad in diesem Script.
    pause
    exit /b 1
)
echo      OK - mysqldump gefunden

echo.
echo [2/3] Erstelle Datenbank-Backup...
php artisan backup:run --only-db

if errorlevel 1 (
    echo FEHLER: Backup fehlgeschlagen!
    pause
    exit /b 1
)

echo.
echo [3/3] Liste vorhandene Backups...
php artisan backup:list

echo.
echo ========================================
echo  BACKUP ERFOLGREICH ABGESCHLOSSEN!
echo ========================================
echo.
echo Backups gespeichert in: storage\app\private\Klubportal\
echo.

pause
