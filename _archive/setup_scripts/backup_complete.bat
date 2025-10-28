@echo off
echo ========================================
echo  KLUBPORTAL COMPLETE BACKUP
echo ========================================
echo.

set TIMESTAMP=%date:~-4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set BACKUP_DIR=C:\xampp\htdocs\kpkb3\backups\backup_%TIMESTAMP%

echo Creating backup directory: %BACKUP_DIR%
mkdir "%BACKUP_DIR%"
mkdir "%BACKUP_DIR%\database"
mkdir "%BACKUP_DIR%\project"

echo.
echo ========================================
echo  1. BACKING UP DATABASES
echo ========================================
echo.

REM Backup central database (kpkb3)
echo [1/3] Backing up kpkb3 (central)...
C:\xampp\mysql\bin\mysqldump.exe -u root kpkb3 > "%BACKUP_DIR%\database\kpkb3.sql"
if %ERRORLEVEL% EQU 0 (
    echo      OK - kpkb3.sql created
) else (
    echo      ERROR backing up kpkb3
)

REM Backup tenant database
echo [2/3] Backing up tenant_nkprigorjem...
C:\xampp\mysql\bin\mysqldump.exe -u root tenant_nkprigorjem > "%BACKUP_DIR%\database\tenant_nkprigorjem.sql"
if %ERRORLEVEL% EQU 0 (
    echo      OK - tenant_nkprigorjem.sql created
) else (
    echo      ERROR backing up tenant_nkprigorjem
)

REM Backup all databases list
echo [3/3] Creating database list...
C:\xampp\mysql\bin\mysql.exe -u root -e "SHOW DATABASES;" > "%BACKUP_DIR%\database\databases_list.txt"

echo.
echo ========================================
echo  2. BACKING UP PROJECT FILES
echo ========================================
echo.

echo Copying project files (this may take a while)...
xcopy "C:\xampp\htdocs\kpkb3" "%BACKUP_DIR%\project\" /E /I /H /Y /EXCLUDE:backup_exclude.txt

echo.
echo ========================================
echo  3. CREATING BACKUP SUMMARY
echo ========================================
echo.

REM Create backup summary
(
echo KLUBPORTAL BACKUP SUMMARY
echo ========================
echo.
echo Backup Date: %date% %time%
echo Backup Location: %BACKUP_DIR%
echo.
echo DATABASE BACKUPS:
echo ----------------
dir "%BACKUP_DIR%\database\*.sql" /B
echo.
echo PROJECT FILES:
echo --------------
echo Full project copied to: %BACKUP_DIR%\project\
echo.
echo IMPORTANT FILES:
echo ---------------
echo - .env configuration
echo - database migrations
echo - uploaded files in storage/app
echo - tenant data
echo.
) > "%BACKUP_DIR%\BACKUP_INFO.txt"

echo.
echo ========================================
echo  BACKUP COMPLETE!
echo ========================================
echo.
echo Backup saved to:
echo %BACKUP_DIR%
echo.
echo Database backups:
dir "%BACKUP_DIR%\database\*.sql"
echo.
echo To restore:
echo 1. Databases: mysql -u root DATABASE_NAME ^< backup.sql
echo 2. Project: Copy files from backup\project\
echo.
pause
