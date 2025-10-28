<?php
/**
 * Backend Debug & Test Script
 * Run with: php debug_test.php
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DebugTester
{
    protected $results = [];

    public function run()
    {
        echo "🔍 Backend Debug & Test Suite\n";
        echo str_repeat("=", 60) . "\n\n";

        $this->testDatabaseConnection();
        $this->testMigrations();
        $this->testClubsTable();
        $this->testUsersTable();
        $this->testDomainsTable();
        $this->testTenantDatabase();
        $this->testRoutes();

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 Test Summary\n";
        echo str_repeat("=", 60) . "\n";
        $this->printSummary();
    }

    protected function testDatabaseConnection()
    {
        echo "1️⃣  Testing Database Connection...\n";
        try {
            $result = DB::select('SELECT 1 as connected');
            $this->addResult('Database Connection', 'PASS', 'MySQL connection successful');
            echo "   ✅ MySQL connected\n";
        } catch (\Exception $e) {
            $this->addResult('Database Connection', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function testMigrations()
    {
        echo "2️⃣  Testing Migrations...\n";
        try {
            $migrations = DB::table('migrations')->count();
            $this->addResult('Migrations', 'PASS', "$migrations migrations run");
            echo "   ✅ $migrations migrations found\n";
        } catch (\Exception $e) {
            $this->addResult('Migrations', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function testClubsTable()
    {
        echo "3️⃣  Testing Clubs (Tenants) Table...\n";
        try {
            $count = DB::table('tenants')->count();
            $this->addResult('Clubs Count', 'PASS', "$count clubs found");
            echo "   ✅ $count clubs in database\n";

            // Get club details
            $clubs = DB::table('tenants')->select('id', 'club_name', 'email', 'is_active', 'subscription_plan')->get();
            if ($clubs->count() > 0) {
                echo "   📋 Club Details:\n";
                foreach ($clubs as $club) {
                    $status = $club->is_active ? '🟢 Active' : '🔴 Inactive';
                    echo "      - {$club->club_name} ({$club->id}): {$status}, Plan: {$club->subscription_plan}\n";
                }
            }
        } catch (\Exception $e) {
            $this->addResult('Clubs Table', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function testUsersTable()
    {
        echo "4️⃣  Testing Users Table...\n";
        try {
            $count = DB::table('users')->count();
            $this->addResult('Users Count', 'PASS', "$count users found");
            echo "   ✅ $count users in database\n";

            if ($count > 0) {
                $users = DB::table('users')->select('id', 'name', 'email', 'email_verified_at')->limit(5)->get();
                echo "   📋 Sample Users:\n";
                foreach ($users as $user) {
                    $verified = $user->email_verified_at ? '✅' : '❌';
                    echo "      - {$user->name} ({$user->email}) {$verified}\n";
                }
            }
        } catch (\Exception $e) {
            $this->addResult('Users Table', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function testDomainsTable()
    {
        echo "5️⃣  Testing Domains Table...\n";
        try {
            $count = DB::table('domains')->count();
            $this->addResult('Domains Count', 'PASS', "$count domains found");
            echo "   ✅ $count domains configured\n";

            if ($count > 0) {
                $domains = DB::table('domains')->select('id', 'domain', 'tenant_id')->limit(5)->get();
                echo "   📋 Sample Domains:\n";
                foreach ($domains as $domain) {
                    echo "      - {$domain->domain} → Tenant: {$domain->tenant_id}\n";
                }
            }
        } catch (\Exception $e) {
            $this->addResult('Domains Table', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function testTenantDatabase()
    {
        echo "6️⃣  Testing Tenant Database Setup...\n";
        try {
            $firstClub = DB::table('tenants')->first();
            if (!$firstClub) {
                $this->addResult('Tenant Database', 'WARN', 'No clubs to test');
                echo "   ⚠️  No clubs found\n";
                return;
            }

            $dbName = "club_{$firstClub->id}";
            $databases = DB::select("SHOW DATABASES LIKE ?", ["{$dbName}%"]);

            if (!empty($databases)) {
                $this->addResult('Tenant Database', 'PASS', "Tenant database exists: {$dbName}");
                echo "   ✅ Tenant database exists: {$dbName}\n";
            } else {
                $this->addResult('Tenant Database', 'WARN', "Tenant database not found: {$dbName}");
                echo "   ⚠️  Tenant database not found: {$dbName}\n";
            }
        } catch (\Exception $e) {
            $this->addResult('Tenant Database', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function testRoutes()
    {
        echo "7️⃣  Testing Routes Configuration...\n";
        try {
            $routePath = base_path('routes/web.php');
            if (file_exists($routePath)) {
                $this->addResult('Routes File', 'PASS', 'Routes file exists');
                echo "   ✅ routes/web.php exists\n";
            }

            $tenantPath = base_path('routes/tenant.php');
            if (file_exists($tenantPath)) {
                $this->addResult('Tenant Routes', 'PASS', 'Tenant routes file exists');
                echo "   ✅ routes/tenant.php exists\n";
            }
        } catch (\Exception $e) {
            $this->addResult('Routes', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function addResult($test, $status, $message)
    {
        $this->results[] = [
            'test' => $test,
            'status' => $status,
            'message' => $message
        ];
    }

    protected function printSummary()
    {
        $pass = 0;
        $fail = 0;
        $warn = 0;

        foreach ($this->results as $result) {
            if ($result['status'] === 'PASS') $pass++;
            elseif ($result['status'] === 'FAIL') $fail++;
            elseif ($result['status'] === 'WARN') $warn++;
        }

        echo "✅ Passed: {$pass}\n";
        echo "❌ Failed: {$fail}\n";
        echo "⚠️  Warnings: {$warn}\n\n";

        if ($fail > 0) {
            echo "Failed Tests:\n";
            foreach ($this->results as $result) {
                if ($result['status'] === 'FAIL') {
                    echo "  ❌ {$result['test']}: {$result['message']}\n";
                }
            }
        }
    }
}

$tester = new DebugTester();
$tester->run();
