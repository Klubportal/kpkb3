<?php

/**
 * Demo: Automatic Tenant Registration
 *
 * Demonstrates the automatic tenant creation pipeline:
 * 1. Create Tenant
 * 2. Automatic Database Creation
 * 3. Automatic Migrations
 * 4. Automatic Seeding
 * 5. Create Default Settings
 * 6. Create Default Admin User
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  AUTOMATIC TENANT REGISTRATION - DEMO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📋 ÜBERSICHT:\n";
echo "════════════\n\n";

echo "Beim Erstellen eines neuen Tenants werden automatisch ausgeführt:\n\n";
echo "  1️⃣  CREATE DATABASE      - Neue Datenbank erstellen\n";
echo "  2️⃣  MIGRATE DATABASE     - Alle Migrations ausführen\n";
echo "  3️⃣  SEED DATABASE        - Demo-Daten einfügen\n";
echo "  4️⃣  DEFAULT SETTINGS     - Theme, Club, Notifications\n";
echo "  5️⃣  ADMIN USER           - Erster Admin-User erstellen\n\n";

echo "═══════════════════════════════════════════════════════════════\n\n";

// Test tenant data
$testTenantId = 'demo_club_' . time();
$testDomain = $testTenantId . '.localhost';

echo "🆕 NEUEN TENANT ERSTELLEN:\n";
echo "═════════════════════════\n\n";

echo "Tenant ID: {$testTenantId}\n";
echo "Domain: {$testDomain}\n";
echo "Name: Demo Football Club\n";
echo "Email: admin@democlub.com\n\n";

echo "Erstelle Tenant...\n";

try {
    // Create tenant (triggers automatic pipeline)
    $tenant = Tenant::create([
        'id' => $testTenantId,
        'name' => 'Demo Football Club',
        'email' => 'admin@democlub.com',
    ]);

    echo "✅ Tenant erstellt!\n\n";

    // Create domain
    $domain = $tenant->domains()->create([
        'domain' => $testDomain,
    ]);

    echo "✅ Domain erstellt!\n\n";

    echo "⏳ JobPipeline wird ausgeführt...\n";
    echo "   (Database → Migrations → Seeding → Settings → Admin User)\n\n";

    // Give jobs a moment to complete (if running synchronously)
    sleep(2);

    echo "═══════════════════════════════════════════════════════════════\n\n";
    echo "📊 ERGEBNIS PRÜFEN:\n";
    echo "══════════════════\n\n";

    // Check if database exists
    $dbName = $tenant->tenancy_db_name;
    $databases = DB::select("SHOW DATABASES LIKE '{$dbName}'");

    if (count($databases) > 0) {
        echo "✅ Datenbank erstellt: {$dbName}\n\n";
    } else {
        echo "❌ Datenbank nicht gefunden: {$dbName}\n\n";
    }

    // Initialize tenant to check data
    tenancy()->initialize($tenant);

    // Check tables
    echo "📋 TABELLEN:\n";
    $tables = DB::select('SHOW TABLES');
    $tableCount = count($tables);
    echo "   {$tableCount} Tabellen erstellt\n";

    $importantTables = ['users', 'teams', 'settings'];
    foreach ($importantTables as $table) {
        $exists = DB::getSchemaBuilder()->hasTable($table);
        echo "   " . ($exists ? "✅" : "❌") . " {$table}\n";
    }
    echo "\n";

    // Check settings
    echo "⚙️  SETTINGS:\n";
    $settingsCount = DB::table('settings')->count();
    echo "   {$settingsCount} Settings erstellt\n";

    if ($settingsCount > 0) {
        $sampleSettings = DB::table('settings')->limit(5)->get();
        foreach ($sampleSettings as $setting) {
            $value = json_decode($setting->payload);
            echo "   • {$setting->group}.{$setting->name} = {$value}\n";
        }
        if ($settingsCount > 5) {
            echo "   ... und " . ($settingsCount - 5) . " weitere\n";
        }
    }
    echo "\n";

    // Check users
    echo "👥 BENUTZER:\n";
    $usersCount = DB::table('users')->count();
    echo "   {$usersCount} Benutzer erstellt\n";

    if ($usersCount > 0) {
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            echo "   • {$user->first_name} {$user->last_name} ({$user->email})\n";
        }
    }
    echo "\n";

    // Check teams (from seeder)
    echo "⚽ TEAMS (aus Seeder):\n";
    $teamsCount = DB::table('teams')->count();
    echo "   {$teamsCount} Teams erstellt\n";

    if ($teamsCount > 0) {
        $teams = DB::table('teams')->get();
        foreach ($teams as $team) {
            echo "   • {$team->name} ({$team->age_group})\n";
        }
    }
    echo "\n";

    tenancy()->end();

    echo "═══════════════════════════════════════════════════════════════\n\n";
    echo "🔑 LOGIN-INFORMATIONEN:\n";
    echo "══════════════════════\n\n";

    echo "⚠️  WICHTIG: Admin-Passwort prüfen in Log!\n\n";
    echo "Das generierte Passwort findest du in:\n";
    echo "   storage/logs/laravel.log\n\n";
    echo "Suche nach: 'Admin user created for tenant: {$testTenantId}'\n\n";

    echo "URL: http://{$testDomain}:8000/login\n";
    echo "Email: admin@democlub.com (oder siehe Log)\n";
    echo "Passwort: [siehe Log-Datei]\n\n";

    echo "═══════════════════════════════════════════════════════════════\n\n";
    echo "🧹 AUFRÄUMEN:\n";
    echo "════════════\n\n";

    echo "Möchtest du den Demo-Tenant löschen? (j/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $answer = trim(strtolower($line));

    if ($answer === 'j' || $answer === 'y') {
        echo "\nLösche Tenant...\n";

        // Delete tenant (triggers automatic database deletion)
        $tenant->delete();

        echo "✅ Tenant gelöscht!\n";
        echo "✅ Datenbank automatisch gelöscht!\n\n";
    } else {
        echo "\nTenant bleibt erhalten.\n";
        echo "Manuell löschen mit:\n";
        echo "  php artisan tinker\n";
        echo "  Tenant::find('{$testTenantId}')->delete();\n\n";
    }

} catch (\Exception $e) {
    echo "\n❌ FEHLER: " . $e->getMessage() . "\n";
    echo "\nStacktrace:\n";
    echo $e->getTraceAsString() . "\n\n";

    // Try to clean up
    if (tenancy()->initialized) {
        tenancy()->end();
    }
}

echo "═══════════════════════════════════════════════════════════════\n\n";
echo "📚 WEITERE INFORMATIONEN:\n";
echo "════════════════════════\n\n";

echo "Dokumentation:\n";
echo "  TENANT_REGISTRATION.md\n\n";

echo "Code-Dateien:\n";
echo "  • app/Providers/TenancyServiceProvider.php\n";
echo "  • app/Jobs/CreateDefaultClubSettings.php\n";
echo "  • app/Jobs/CreateDefaultAdminUser.php\n\n";

echo "Pipeline anpassen:\n";
echo "  Bearbeite TenancyServiceProvider::events()\n";
echo "  Füge eigene Jobs hinzu oder entferne welche\n\n";

echo "✅ Demo abgeschlossen!\n\n";
