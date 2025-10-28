@echo off
REM Klubportal Backend - Automatische Resource Generierung
REM Einfach diese Datei ausführen - keine Interaktion nötig

echo.
echo === KLUBPORTAL BACKEND SETUP ===
echo.

REM Resources generieren (ohne --generate um Fehler zu vermeiden)
echo Generiere Filament Resources...

call php artisan make:filament-resource Team --panel=superadmin --simple
call php artisan make:filament-resource Player --panel=superadmin --simple
call php artisan make:filament-resource FootballMatch --panel=superadmin --simple
call php artisan make:filament-resource Training --panel=superadmin --simple
call php artisan make:filament-resource News --panel=superadmin --simple
call php artisan make:filament-resource Member --panel=superadmin --simple
call php artisan make:filament-resource Event --panel=superadmin --simple
call php artisan make:filament-resource Season --panel=superadmin --simple

echo.
echo === Resources erstellt! ===
echo.
echo Jetzt im Browser öffnen: http://localhost:8000/super-admin
echo.
pause
