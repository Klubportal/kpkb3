@echo off
REM Fußball CMS - Setup Script für Windows
REM Schnelle Einrichtung des Multi-Tenancy Systems

title Fußball CMS - Setup
color 0A
cls

echo ========================================
echo   ⚽ Fußball CMS - Multi-Tenancy Setup
echo ========================================
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo ❌ Error: artisan file not found.
    echo Are you in the kp_club_management directory?
    pause
    exit /b 1
)

echo ✅ Project directory found
echo.

REM Step 1: Install dependencies
echo 📦 Installing Composer dependencies...
call composer install
if errorlevel 1 (
    echo ❌ Composer install failed
    pause
    exit /b 1
)
echo ✅ Dependencies installed
echo.

REM Step 2: Create .env if not exists
if not exist ".env" (
    echo 📝 Creating .env file...
    copy .env.example .env >nul
    call php artisan key:generate
    echo ✅ .env created with APP_KEY
) else (
    echo ✅ .env file already exists
)
echo.

REM Step 3: Database migrations
echo 🗄️  Running database migrations...
call php artisan migrate --database=central --force
echo ✅ Central database migrated
echo.

REM Step 4: Tenant migrations
echo 🗄️  Preparing tenant migrations...
call php artisan tenants:migrate-fresh
echo ✅ Tenant migrations prepared
echo.

REM Step 5: Create Super Admin
echo 👤 Creating Super Admin user...
(
    echo $admin = App\Models\User::firstOrCreate(
    echo     ['email' ^=> 'admin@example.com'],
    echo     [
    echo         'name' ^=> 'Super Admin',
    echo         'password' ^=> bcrypt('password')
    echo     ]
    echo ^);
    echo echo "✅ Super Admin created: admin@example.com / password\n";
    echo exit;
) | call php artisan tinker
echo.

REM Step 6: Clear cache
echo 🧹 Clearing cache...
call php artisan cache:clear
call php artisan config:clear
call php artisan view:clear
echo ✅ Cache cleared
echo.

REM Step 7: Summary
cls
echo ========================================
echo ✅ Setup Complete!
echo ========================================
echo.
echo 🚀 Next steps:
echo.
echo 1. Start the development server:
echo    php artisan serve
echo.
echo 2. Open in browser:
echo    Super Admin: http://localhost:8000/super-admin
echo    Email: admin@example.com
echo    Password: password
echo.
echo 3. Create your first club ^(see GETTING_STARTED.md^)
echo.
echo 📚 Documentation:
echo    - GETTING_STARTED.md   - Quick start guide
echo    - DATABASE_SETUP.md    - Database configuration
echo    - ARCHITECTURE.md      - System architecture
echo    - README_SETUP.md      - Project overview
echo.
echo Happy coding! ⚽
echo.
pause
