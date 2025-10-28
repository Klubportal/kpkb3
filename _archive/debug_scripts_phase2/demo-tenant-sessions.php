<?php

/**
 * 🔐 TENANT SESSION ISOLATION - Demo Script
 *
 * Demonstriert wie Sessions automatisch pro Tenant getrennt werden
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "   SESSION TENANCY DEMONSTRATION\n";
echo "========================================\n\n";

// Check Session Configuration
$sessionDriver = config('session.driver');
$sessionTenancyActive = ($sessionDriver === 'database');

echo "📋 KONFIGURATION:\n";
echo "   Session Driver: {$sessionDriver}\n";
echo "   Session Isolation: " . ($sessionTenancyActive ? "✅ AKTIV (via DatabaseTenancyBootstrapper)" : "❌ NICHT AKTIV") . "\n";
echo "   Session Table: " . config('session.table') . "\n";
echo "   Session Connection: " . (config('session.connection') ?: 'tenant (dynamisch)') . "\n\n";

if (!$sessionTenancyActive) {
    echo "⚠️  Session Driver ist nicht 'database'!\n";
    echo "   Setze in .env: SESSION_DRIVER=database\n\n";
    exit(1);
}

echo "========================================\n";
echo "   WIE SESSION TENANCY FUNKTIONIERT\n";
echo "========================================\n\n";

echo "📝 AUTOMATISCHE SESSION-TRENNUNG:\n\n";

echo "1️⃣  User öffnet testclub.localhost:8000\n";
echo "   → Tenant: testclub\n";
echo "   → Session DB: tenant_testclub\n";
echo "   → Session Table: sessions\n";
echo "   → Session gespeichert in tenant_testclub.sessions\n\n";

echo "2️⃣  User öffnet liverpool.localhost:8000\n";
echo "   → Tenant: liverpool\n";
echo "   → Session DB: tenant_liverpool\n";
echo "   → Session Table: sessions\n";
echo "   → Session gespeichert in tenant_liverpool.sessions\n\n";

echo "3️⃣  ERGEBNIS: Separate Sessions pro Tenant!\n";
echo "   → Kein Session-Konflikt zwischen Tenants\n";
echo "   → User kann bei mehreren Clubs gleichzeitig eingeloggt sein\n";
echo "   → Jeder Tenant hat eigene Session-Tabelle\n\n";

echo "========================================\n";
echo "   PRAKTISCHES BEISPIEL\n";
echo "========================================\n\n";

$tenant = Tenant::find('testclub');

if ($tenant) {
    echo "🏢 TENANT: {$tenant->id}\n\n";

    $tenant->run(function () use ($tenant) {
        echo "📊 SESSION TABLE STATUS:\n";

        try {
            // Check if sessions table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'sessions'");

            if ($tableExists) {
                $sessionCount = DB::table('sessions')->count();
                echo "   Sessions Table: ✅ Existiert\n";
                echo "   Aktive Sessions: {$sessionCount}\n\n";

                if ($sessionCount > 0) {
                    echo "📋 AKTIVE SESSIONS:\n";
                    $sessions = DB::table('sessions')
                        ->orderBy('last_activity', 'desc')
                        ->limit(5)
                        ->get();

                    foreach ($sessions as $session) {
                        $lastActivity = date('Y-m-d H:i:s', $session->last_activity);
                        $userId = $session->user_id ? "User #{$session->user_id}" : "Guest";
                        echo "   - {$userId} | Last: {$lastActivity} | IP: {$session->ip_address}\n";
                    }
                    echo "\n";
                }
            } else {
                echo "   ⚠️  Sessions Table existiert noch nicht!\n";
                echo "   Migration ausführen:\n";
                echo "   php artisan tenants:migrate --tenants={$tenant->id}\n\n";
            }
        } catch (\Exception $e) {
            echo "   ❌ Fehler: " . $e->getMessage() . "\n\n";
        }
    });
} else {
    echo "❌ Tenant 'testclub' nicht gefunden!\n\n";
}

echo "========================================\n";
echo "   CODE BEISPIELE\n";
echo "========================================\n\n";

echo "📝 SESSION VERWENDEN (funktioniert automatisch):\n\n";

echo "<?php\n";
echo "// In Controller oder Livewire Component\n\n";

echo "// Session speichern (landet automatisch in Tenant DB)\n";
echo "session(['user_preference' => 'dark_mode']);\n\n";

echo "// Session lesen\n";
echo "\$preference = session('user_preference');\n\n";

echo "// Session in Filament\n";
echo "class EditProfile extends Page\n";
echo "{\n";
echo "    protected function mutateFormDataBeforeSave(array \$data): array\n";
echo "    {\n";
echo "        // Session wird im aktuellen Tenant Context gespeichert\n";
echo "        session(['last_profile_update' => now()]);\n";
echo "        return \$data;\n";
echo "    }\n";
echo "}\n\n";

echo "========================================\n";
echo "   SESSION MIGRATION\n";
echo "========================================\n\n";

echo "📁 MIGRATION BEREITS VORHANDEN:\n";
echo "   database/migrations/tenant/0001_01_01_000000_create_sessions_table.php\n\n";

echo "Schema::create('sessions', function (Blueprint \$table) {\n";
echo "    \$table->string('id')->primary();\n";
echo "    \$table->foreignId('user_id')->nullable()->index();\n";
echo "    \$table->string('ip_address', 45)->nullable();\n";
echo "    \$table->text('user_agent')->nullable();\n";
echo "    \$table->longText('payload');\n";
echo "    \$table->integer('last_activity')->index();\n";
echo "});\n\n";

echo "========================================\n";
echo "   MULTI-TENANT LOGIN SCENARIO\n";
echo "========================================\n\n";

echo "🔐 SCENARIO: User bei mehreren Clubs eingeloggt\n\n";

echo "BROWSER TAB 1:\n";
echo "   URL: http://testclub.localhost:8000\n";
echo "   User: admin@testclub.com\n";
echo "   Session: tenant_testclub.sessions (ID: abc123)\n";
echo "   Auth: ✅ Eingeloggt als Admin bei testclub\n\n";

echo "BROWSER TAB 2:\n";
echo "   URL: http://liverpool.localhost:8000\n";
echo "   User: trainer@liverpool.com\n";
echo "   Session: tenant_liverpool.sessions (ID: xyz789)\n";
echo "   Auth: ✅ Eingeloggt als Trainer bei liverpool\n\n";

echo "ERGEBNIS:\n";
echo "   ✅ KEINE Konflikte - Sessions sind komplett getrennt!\n";
echo "   ✅ User kann beide Dashboards parallel nutzen\n";
echo "   ✅ Logout in Tab 1 beeinflusst Tab 2 NICHT\n\n";

echo "========================================\n";
echo "   VORTEILE\n";
echo "========================================\n\n";

echo "✅ ISOLATION:\n";
echo "   - Jeder Tenant hat eigene Sessions-Tabelle\n";
echo "   - Keine Session-Leaks zwischen Tenants\n";
echo "   - Sicher und datenschutzkonform\n\n";

echo "✅ MULTI-LOGIN:\n";
echo "   - User kann bei mehreren Tenants gleichzeitig eingeloggt sein\n";
echo "   - Nützlich für Admins die mehrere Clubs betreuen\n";
echo "   - Jeder Tab hat eigenen Login-Status\n\n";

echo "✅ AUTOMATISCH:\n";
echo "   - Keine Extra-Bootstrapper nötig\n";
echo "   - DatabaseTenancyBootstrapper erledigt Session-Isolation\n";
echo "   - Funktioniert mit allen Session-Methoden\n\n";

echo "========================================\n";
echo "   TESTING\n";
echo "========================================\n\n";

echo "🧪 SESSION TENANCY TESTEN:\n\n";

echo "<?php\n";
echo "use App\\Models\\Central\\Tenant;\n\n";

echo "/** @test */\n";
echo "public function sessions_are_isolated_per_tenant()\n";
echo "{\n";
echo "    \$tenant1 = Tenant::factory()->create(['id' => 'club1']);\n";
echo "    \$tenant2 = Tenant::factory()->create(['id' => 'club2']);\n\n";

echo "    // Session in Tenant 1\n";
echo "    \$tenant1->run(function () {\n";
echo "        session(['test_key' => 'tenant1_value']);\n";
echo "        \$this->assertEquals('tenant1_value', session('test_key'));\n";
echo "    });\n\n";

echo "    // Session in Tenant 2 (darf nicht tenant1_value sehen)\n";
echo "    \$tenant2->run(function () {\n";
echo "        \$this->assertNull(session('test_key'));\n";
echo "        session(['test_key' => 'tenant2_value']);\n";
echo "        \$this->assertEquals('tenant2_value', session('test_key'));\n";
echo "    });\n\n";

echo "    // Zurück zu Tenant 1 - Original Session noch da\n";
echo "    \$tenant1->run(function () {\n";
echo "        \$this->assertEquals('tenant1_value', session('test_key'));\n";
echo "    });\n";
echo "}\n\n";

echo "========================================\n";
echo "   TROUBLESHOOTING\n";
echo "========================================\n\n";

echo "❌ PROBLEM: Sessions werden nicht getrennt\n";
echo "✅ LÖSUNG:\n";
echo "   1. Prüfe SESSION_DRIVER=database in .env\n";
echo "   2. DatabaseTenancyBootstrapper muss aktiviert sein\n";
echo "   3. Cache leeren: php artisan config:clear\n\n";

echo "❌ PROBLEM: Session table existiert nicht\n";
echo "✅ LÖSUNG:\n";
echo "   php artisan tenants:migrate --tenants=testclub\n\n";

echo "❌ PROBLEM: Session Konflikte zwischen Tenants\n";
echo "✅ LÖSUNG:\n";
echo "   - Prüfe dass Tenant Context korrekt initialisiert wird\n";
echo "   - Prüfe Domain Routing (InitializeTenancyByDomain)\n";
echo "   - Nie session() in Central Context für Tenant Daten nutzen\n\n";

echo "========================================\n";
echo "   BEFEHLE\n";
echo "========================================\n\n";

echo "# Sessions Migration ausführen\n";
echo "php artisan tenants:migrate\n\n";

echo "# Sessions für spezifischen Tenant\n";
echo "php artisan tenants:migrate --tenants=testclub\n\n";

echo "# Session Tabelle prüfen\n";
echo "php artisan tinker\n";
echo "tenant('testclub')->run(fn() => DB::table('sessions')->count())\n\n";

echo "# Alte Sessions bereinigen (pro Tenant)\n";
echo "php artisan tinker\n";
echo "tenant('testclub')->run(fn() => DB::table('sessions')\n";
echo "    ->where('last_activity', '<', now()->subDays(7)->timestamp)\n";
echo "    ->delete())\n\n";

echo "========================================\n\n";

if ($sessionTenancyActive) {
    echo "✅ SESSION TENANCY IST AKTIVIERT UND BEREIT!\n\n";
} else {
    echo "⚠️  BITTE SESSION_DRIVER=database SETZEN!\n\n";
}
