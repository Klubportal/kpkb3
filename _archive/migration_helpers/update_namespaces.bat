@echo off
REM Update namespaces for Core Models
cd /d C:\xampp\htdocs\kp_club_management\app\Models\Core

for %%f in (*.php) do (
    powershell -Command "(Get-Content '%%f') -replace 'namespace App\\Models;', 'namespace App\Models\Core;' | Set-Content '%%f'"
)

echo ✅ Core namespaces updated

REM Update namespaces for Marketing Models
cd /d C:\xampp\htdocs\kp_club_management\app\Models\Marketing

for %%f in (*.php) do (
    powershell -Command "(Get-Content '%%f') -replace 'namespace App\\Models;', 'namespace App\Models\Marketing;' | Set-Content '%%f'"
)

echo ✅ Marketing namespaces updated

REM Update namespaces for Integration Models
cd /d C:\xampp\htdocs\kp_club_management\app\Models\Integration

for %%f in (*.php) do (
    powershell -Command "(Get-Content '%%f') -replace 'namespace App\\Models;', 'namespace App\Models\Integration;' | Set-Content '%%f'"
)

echo ✅ Integration namespaces updated

REM Update namespaces for System Models
cd /d C:\xampp\htdocs\kp_club_management\app\Models\System

for %%f in (*.php) do (
    powershell -Command "(Get-Content '%%f') -replace 'namespace App\\Models;', 'namespace App\Models\System;' | Set-Content '%%f'"
)

echo ✅ System namespaces updated

echo.
echo ======================================
echo ✅ ALL NAMESPACES UPDATED
echo ======================================
