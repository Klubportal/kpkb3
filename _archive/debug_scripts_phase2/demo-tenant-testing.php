<?php

/**
 * Demo: Tenant Testing Isolation
 *
 * Demonstrates how to run tests with tenant isolation
 * This script shows examples of the testing infrastructure in action
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  TENANT TESTING ISOLATION - DEMO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📋 ÜBERSICHT:\n";
echo "════════════\n\n";

echo "Das Tenant-Testing-System bietet:\n\n";

echo "1️⃣  TESTFÄLLE (tests/Feature/TenantTest.php)\n";
echo "   • test_tenant_can_be_created_with_domain\n";
echo "   • test_tenant_database_can_be_created_and_migrated\n";
echo "   • test_tenant_data_isolation\n";
echo "   • test_tenant_can_access_own_data\n";
echo "   • test_switching_between_tenants_updates_connection\n";
echo "   • test_ending_tenancy_returns_to_central_database\n";
echo "   • test_tenant_users_are_isolated_from_central_users\n";
echo "   • test_tenant_can_be_identified_by_domain\n";
echo "   • test_multiple_domains_can_point_to_same_tenant\n\n";

echo "2️⃣  BASE TEST CLASS (tests/TenantTestCase.php)\n";
echo "   Helper-Methoden:\n";
echo "   • createTestTenant() - Tenant mit DB und Migrations erstellen\n";
echo "   • initializeTenant() - Tenant-Kontext aktivieren\n";
echo "   • endTenancy() - Zurück zu Central\n";
echo "   • switchToTenant() - Zwischen Tenants wechseln\n";
echo "   • actingAsTenant() - Callback im Tenant-Kontext ausführen\n";
echo "   • seedTenantData() - Demo-Daten in Tenant laden\n\n";

echo "   Assertions:\n";
echo "   • assertInTenantContext()\n";
echo "   • assertInCentralContext()\n";
echo "   • assertCurrentTenant()\n";
echo "   • assertTenantDatabase()\n";
echo "   • assertCentralDatabase()\n";
echo "   • assertTenantTableExists()\n\n";

echo "3️⃣  DATA CREATION TRAIT (tests/Traits/CreatesTenantData.php)\n";
echo "   Factory-Methoden:\n";
echo "   • createTenantUser() - Benutzer erstellen\n";
echo "   • createTeam() - Team erstellen\n";
echo "   • createPlayer() - Spieler erstellen\n";
echo "   • createMatch() - Match erstellen\n";
echo "   • createNews() - News-Artikel erstellen\n";
echo "   • createEvent() - Event erstellen\n";
echo "   • createTeamWithPlayers() - Team mit Spielern\n";
echo "   • createCompleteMatch() - Vollständiges Match mit Teams\n\n";

echo "4️⃣  CUSTOM ASSERTIONS (tests/Traits/TenantAssertions.php)\n";
echo "   Spezielle Assertions:\n";
echo "   • assertTenantHasDomain()\n";
echo "   • assertDataIsolatedToTenant()\n";
echo "   • assertTenantDatabaseExists()\n";
echo "   • assertTenantConfig()\n";
echo "   • assertTenantCachePrefix()\n";
echo "   • assertTenantFilesystemDisk()\n\n";

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  VERWENDUNG\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "🔧 TESTS AUSFÜHREN:\n";
echo "══════════════════\n\n";

echo "Alle Tenant-Tests:\n";
echo "  php artisan test tests/Feature/TenantTest.php\n\n";

echo "Einzelner Test:\n";
echo "  php artisan test --filter test_tenant_data_isolation\n\n";

echo "Mit Coverage:\n";
echo "  php artisan test --coverage\n\n";

echo "Verbose Output:\n";
echo "  php artisan test tests/Feature/TenantTest.php -v\n\n";

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  CODE-BEISPIELE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📝 BEISPIEL 1: Einfacher Tenant-Test\n";
echo "════════════════════════════════════\n\n";

echo "<?php\n\n";
echo "use Tests\\TenantTestCase;\n\n";
echo "class MyTenantTest extends TenantTestCase\n";
echo "{\n";
echo "    public function test_tenant_isolation()\n";
echo "    {\n";
echo "        // Tenant erstellen\n";
echo "        \$tenant1 = \$this->createTestTenant('club1');\n";
echo "        \$tenant2 = \$this->createTestTenant('club2');\n\n";
echo "        // In Tenant1 arbeiten\n";
echo "        \$this->initializeTenant(\$tenant1);\n";
echo "        \$this->assertInTenantContext();\n";
echo "        \$this->assertCurrentTenant(\$tenant1);\n\n";
echo "        // Zu Tenant2 wechseln\n";
echo "        \$this->switchToTenant(\$tenant2);\n";
echo "        \$this->assertCurrentTenant(\$tenant2);\n\n";
echo "        // Zurück zu Central\n";
echo "        \$this->endTenancy();\n";
echo "        \$this->assertInCentralContext();\n";
echo "    }\n";
echo "}\n\n";

echo "📝 BEISPIEL 2: Mit Test-Daten\n";
echo "═════════════════════════════\n\n";

echo "<?php\n\n";
echo "use Tests\\TenantTestCase;\n";
echo "use Tests\\Traits\\CreatesTenantData;\n\n";
echo "class DataTest extends TenantTestCase\n";
echo "{\n";
echo "    use CreatesTenantData;\n\n";
echo "    public function test_create_team_with_players()\n";
echo "    {\n";
echo "        \$tenant = \$this->createTestTenant('club1');\n";
echo "        \$this->initializeTenant(\$tenant);\n\n";
echo "        // Team mit 11 Spielern erstellen\n";
echo "        \$team = \$this->createTeamWithPlayers(11);\n\n";
echo "        \$this->assertEquals(11, \$team->players->count());\n";
echo "        \$this->assertEquals('Test Team', \$team->name);\n\n";
echo "        \$this->endTenancy();\n";
echo "    }\n";
echo "}\n\n";

echo "📝 BEISPIEL 3: Callback Pattern\n";
echo "═══════════════════════════════\n\n";

echo "<?php\n\n";
echo "public function test_acting_as_tenant()\n";
echo "{\n";
echo "    \$tenant = \$this->createTestTenant('club1');\n\n";
echo "    // Automatisches Cleanup nach Callback\n";
echo "    \$result = \$this->actingAsTenant(\$tenant, function () {\n";
echo "        \$team = \$this->createTeam(['name' => 'First Team']);\n";
echo "        return \$team->name;\n";
echo "    });\n\n";
echo "    \$this->assertEquals('First Team', \$result);\n";
echo "    \$this->assertInCentralContext(); // Auto zurück zu Central\n";
echo "}\n\n";

echo "📝 BEISPIEL 4: Daten-Isolation testen\n";
echo "═════════════════════════════════════\n\n";

echo "<?php\n\n";
echo "public function test_data_isolation()\n";
echo "{\n";
echo "    \$tenant1 = \$this->createTestTenant('club1');\n";
echo "    \$tenant2 = \$this->createTestTenant('club2');\n\n";
echo "    // Daten in Tenant 1\n";
echo "    \$this->actingAsTenant(\$tenant1, function () {\n";
echo "        \$this->createTeam(['name' => 'Team 1']);\n";
echo "        \$this->assertEquals(1, Team::count());\n";
echo "    });\n\n";
echo "    // Daten in Tenant 2\n";
echo "    \$this->actingAsTenant(\$tenant2, function () {\n";
echo "        \$this->createTeam(['name' => 'Team 2']);\n";
echo "        \$this->assertEquals(1, Team::count()); // Nur 1!\n";
echo "    });\n\n";
echo "    // Tenant 1 sieht nur seine Daten\n";
echo "    \$this->actingAsTenant(\$tenant1, function () {\n";
echo "        \$this->assertEquals(1, Team::count());\n";
echo "        \$this->assertEquals('Team 1', Team::first()->name);\n";
echo "    });\n";
echo "}\n\n";

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  BEST PRACTICES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ DO:\n";
echo "  • Immer TenantTestCase als Basis verwenden\n";
echo "  • tearDown() für Cleanup nutzen (automatisch)\n";
echo "  • actingAsTenant() für automatisches Context-Management\n";
echo "  • Spezifische Assertions verwenden (assertInTenantContext, etc.)\n";
echo "  • Test-Tenants mit 'test_' Prefix erstellen\n\n";

echo "❌ DON'T:\n";
echo "  • Nie tenancy()->initialize() ohne cleanup\n";
echo "  • Nicht zwischen Tests in Tenant-Context bleiben\n";
echo "  • Keine Production-Tenants in Tests verwenden\n";
echo "  • Nicht vergessen Datenbanken zu cleanen\n\n";

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  NÄCHSTE SCHRITTE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "1. Tests ausführen:\n";
echo "   php artisan test tests/Feature/TenantTest.php\n\n";

echo "2. Eigene Tests erstellen:\n";
echo "   Extend TenantTestCase\n";
echo "   Use CreatesTenantData trait\n";
echo "   Use TenantAssertions trait\n\n";

echo "3. Dokumentation lesen:\n";
echo "   TENANT_TESTING.md\n\n";

echo "═══════════════════════════════════════════════════════════════\n\n";

// Optional: Run a simple test demonstration
if (isset($argv[1]) && $argv[1] === '--run-test') {
    echo "🧪 RUNNING TEST DEMONSTRATION...\n\n";

    try {
        Artisan::call('test', [
            '--filter' => 'test_tenant_can_be_created_with_domain',
            'path' => 'tests/Feature/TenantTest.php'
        ]);

        echo Artisan::output();

    } catch (\Exception $e) {
        echo "❌ Error running test: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Demo abgeschlossen!\n\n";
