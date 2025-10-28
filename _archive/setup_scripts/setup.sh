#!/bin/bash
# Fußball CMS - Setup Script
# Schnelle Einrichtung des Multi-Tenancy Systems

echo "⚽ Fußball CMS - Multi-Tenancy Setup"
echo "===================================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Are you in the kp_club_management directory?"
    exit 1
fi

echo "✅ Project directory found"
echo ""

# Step 1: Install dependencies
echo "📦 Installing Composer dependencies..."
composer install
if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi
echo "✅ Dependencies installed"
echo ""

# Step 2: Create .env if not exists
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
    echo "✅ .env created with APP_KEY"
else
    echo "✅ .env file already exists"
fi
echo ""

# Step 3: Database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --database=central --force
if [ $? -ne 0 ]; then
    echo "⚠️  Central migrations had issues, continuing..."
fi
echo "✅ Central database migrated"
echo ""

# Step 4: Tenant migrations
echo "🗄️  Preparing tenant migrations..."
php artisan tenants:migrate-fresh
echo "✅ Tenant migrations prepared"
echo ""

# Step 5: Create Super Admin
echo "👤 Creating Super Admin user..."
php artisan tinker << 'EOF'
$admin = App\Models\User::firstOrCreate(
    ['email' => 'admin@example.com'],
    [
        'name' => 'Super Admin',
        'password' => bcrypt('password')
    ]
);
echo "✅ Super Admin created: admin@example.com / password\n";
exit;
EOF
echo ""

# Step 6: Clear cache
echo "🧹 Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
echo "✅ Cache cleared"
echo ""

# Step 7: Summary
echo "=================================="
echo "✅ Setup Complete!"
echo "=================================="
echo ""
echo "🚀 Next steps:"
echo ""
echo "1. Start the development server:"
echo "   php artisan serve"
echo ""
echo "2. Open in browser:"
echo "   Super Admin: http://localhost:8000/super-admin"
echo "   Email: admin@example.com"
echo "   Password: password"
echo ""
echo "3. Create your first club (see GETTING_STARTED.md)"
echo ""
echo "📚 Documentation:"
echo "   - GETTING_STARTED.md   - Quick start guide"
echo "   - DATABASE_SETUP.md    - Database configuration"
echo "   - ARCHITECTURE.md      - System architecture"
echo "   - README_SETUP.md      - Project overview"
echo ""
echo "Happy coding! ⚽"
