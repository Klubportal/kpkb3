@echo off
setlocal enabledelayedexpansion

echo ========================================
echo  KLUBPORTAL VEREIN BACKUP
echo ========================================
echo.

REM Check if tenant ID was provided
if "%1"=="" (
    echo Verfügbare Vereine:
    echo.
    C:\xampp\mysql\bin\mysql -u root -e "USE kpkb3; SELECT CONCAT('ID: ', id, ' - Name: ', name) as 'Vereine' FROM tenants;"
    echo.
    echo Verwendung: backup_verein.bat [VEREIN_ID]
    echo Beispiel:   backup_verein.bat nkprigorjem
    echo.
    pause
    exit /b 1
)

set TENANT_ID=%1
set TIMESTAMP=%date:~-4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set BACKUP_DIR=C:\xampp\htdocs\kpkb3\backups\verein_%TENANT_ID%_%TIMESTAMP%

echo Erstelle Backup für Verein: %TENANT_ID%
echo Zeitstempel: %TIMESTAMP%
echo.

REM Create backup directory
echo Erstelle Backup-Verzeichnis: %BACKUP_DIR%
mkdir "%BACKUP_DIR%"
mkdir "%BACKUP_DIR%\database"
mkdir "%BACKUP_DIR%\uploads"

echo.
echo ========================================
echo  1. DATENBANK BACKUP
echo ========================================
echo.

REM Check if tenant database exists
C:\xampp\mysql\bin\mysql -u root -e "USE tenant_%TENANT_ID%;" 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo FEHLER: Datenbank tenant_%TENANT_ID% existiert nicht!
    echo.
    pause
    exit /b 1
)

REM Backup tenant database
echo [1/3] Sichere Vereins-Datenbank tenant_%TENANT_ID%...
C:\xampp\mysql\bin\mysqldump.exe -u root --single-transaction --routines --triggers tenant_%TENANT_ID% > "%BACKUP_DIR%\database\tenant_%TENANT_ID%.sql"
if %ERRORLEVEL% EQU 0 (
    echo      ✓ OK - tenant_%TENANT_ID%.sql erstellt
) else (
    echo      ✗ FEHLER beim Backup der Vereins-Datenbank
    pause
    exit /b 1
)

REM Backup central database tenant entry
echo [2/3] Sichere Vereins-Eintrag in zentraler Datenbank...
C:\xampp\mysql\bin\mysqldump.exe -u root --single-transaction --where="id='%TENANT_ID%'" kpkb3 tenants > "%BACKUP_DIR%\database\tenant_entry.sql"
if %ERRORLEVEL% EQU 0 (
    echo      ✓ OK - tenant_entry.sql erstellt
) else (
    echo      ✗ FEHLER beim Backup des Vereins-Eintrags
)

REM Export tenant configuration
echo [3/3] Exportiere Vereins-Konfiguration...
C:\xampp\mysql\bin\mysql -u root -e "USE kpkb3; SELECT * FROM tenants WHERE id='%TENANT_ID%';" > "%BACKUP_DIR%\database\tenant_config.txt"

echo.
echo ========================================
echo  2. DATEIEN BACKUP
echo ========================================
echo.

REM Backup uploaded files for this tenant
echo Sichere hochgeladene Dateien für Verein %TENANT_ID%...
if exist "storage\app\tenant_%TENANT_ID%" (
    xcopy "storage\app\tenant_%TENANT_ID%" "%BACKUP_DIR%\uploads\tenant_%TENANT_ID%\" /E /I /H /Y
    echo      ✓ Upload-Dateien gesichert
) else (
    echo      - Keine Upload-Dateien gefunden
)

REM Backup tenant-specific media files
if exist "storage\app\public\tenants\%TENANT_ID%" (
    xcopy "storage\app\public\tenants\%TENANT_ID%" "%BACKUP_DIR%\uploads\public\%TENANT_ID%\" /E /I /H /Y
    echo      ✓ Öffentliche Medien-Dateien gesichert
) else (
    echo      - Keine öffentlichen Medien-Dateien gefunden
)

echo.
echo ========================================
echo  3. BACKUP ZUSAMMENFASSUNG
echo ========================================
echo.

REM Get database size
for /f "tokens=*" %%a in ('C:\xampp\mysql\bin\mysql -u root -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size MB' FROM information_schema.tables WHERE table_schema='tenant_%TENANT_ID%';" ^| findstr /V "DB Size MB"') do set DB_SIZE=%%a

REM Get file sizes
for /f "tokens=3" %%a in ('dir "%BACKUP_DIR%" /s /-c ^| findstr /E "Datei(en)"') do set TOTAL_SIZE=%%a

REM Create backup summary
(
echo KLUBPORTAL VEREIN BACKUP ZUSAMMENFASSUNG
echo ========================================
echo.
echo Verein ID: %TENANT_ID%
echo Backup Datum: %date% %time%
echo Backup Ort: %BACKUP_DIR%
echo.
echo DATENBANK BACKUP:
echo ----------------
echo - Vereins-DB: tenant_%TENANT_ID%.sql
echo - DB Größe: %DB_SIZE% MB
echo - Vereins-Eintrag: tenant_entry.sql
echo - Konfiguration: tenant_config.txt
echo.
echo DATEIEN BACKUP:
echo --------------
if exist "%BACKUP_DIR%\uploads\tenant_%TENANT_ID%" (
    echo - Upload-Dateien: ✓ Gesichert
) else (
    echo - Upload-Dateien: - Keine gefunden
)
if exist "%BACKUP_DIR%\uploads\public\%TENANT_ID%" (
    echo - Medien-Dateien: ✓ Gesichert
) else (
    echo - Medien-Dateien: - Keine gefunden
)
echo.
echo WIEDERHERSTELLUNG:
echo -----------------
echo 1. Datenbank: mysql -u root -e "CREATE DATABASE tenant_%TENANT_ID%;"
echo 2. Import: mysql -u root tenant_%TENANT_ID% ^< tenant_%TENANT_ID%.sql
echo 3. Verein-Eintrag: mysql -u root kpkb3 ^< tenant_entry.sql
echo 4. Dateien: Kopiere uploads\ zurück nach storage\app\
echo.
echo BACKUP ERFOLGREICH ABGESCHLOSSEN!
echo Gesamtgröße: %TOTAL_SIZE% Bytes
) > "%BACKUP_DIR%\BACKUP_INFO.txt"

echo.
echo ========================================
echo  ✓ VEREIN BACKUP ERFOLGREICH!
echo ========================================
echo.
echo Backup gespeichert in:
echo %BACKUP_DIR%
echo.
echo Backup enthält:
dir "%BACKUP_DIR%\database\*.sql" /B
echo.
echo Für Wiederherstellung siehe: BACKUP_INFO.txt
echo.
pause
