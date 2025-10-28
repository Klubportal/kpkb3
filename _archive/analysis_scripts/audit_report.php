<?php
/**
 * Comprehensive Backend Audit Report
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackendAudit
{
    protected $issues = [];
    protected $warnings = [];
    protected $successes = [];

    public function run()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║         🔍 BACKEND COMPREHENSIVE AUDIT REPORT 🔍          ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        $this->auditDatabaseSetup();
        $this->auditDataIntegrity();
        $this->auditUserClubRelationships();
        $this->auditTenantSystem();
        $this->auditAPIEndpoints();
        $this->auditFilamentAdmin();
        $this->auditConfigFiles();

        $this->printReport();
    }

    protected function auditDatabaseSetup()
    {
        echo "1. Database Setup\n";
        echo "───────────────────────────────────────────────────────────\n";

        try {
            $tables = DB::select('SHOW TABLES');
            $this->addSuccess("Database connected with " . count($tables) . " tables");

            $critical = ['users', 'tenants', 'domains', 'migrations'];
            foreach ($critical as $table) {
                if (Schema::hasTable($table)) {
                    $this->addSuccess("Table '$table' exists");
                } else {
                    $this->addIssue("Critical table '$table' is missing!");
                }
            }
        } catch (\Exception $e) {
            $this->addIssue("Database connection failed: " . $e->getMessage());
        }
        echo "\n";
    }

    protected function auditDataIntegrity()
    {
        echo "2. Data Integrity\n";
        echo "───────────────────────────────────────────────────────────\n";

        $usersCount = DB::table('users')->count();
        $tenantsCount = DB::table('tenants')->count();
        $domainsCount = DB::table('domains')->count();
        $contactsCount = DB::table('contact_form_submissions')->count();

        $this->addSuccess("$usersCount users registered");
        $this->addSuccess("$tenantsCount clubs configured");
        $this->addSuccess("$contactsCount contact form submissions");

        if ($domainsCount === 0) {
            $this->addWarning("No domains configured - multi-tenancy routing may not work");
        } else {
            $this->addSuccess("$domainsCount domains configured");
        }

        // Check for unverified users
        $unverified = DB::table('users')->whereNull('email_verified_at')->count();
        if ($unverified > 0) {
            $this->addWarning("$unverified users have unverified email addresses");
        } else {
            $this->addSuccess("All users have verified email addresses");
        }

        // Check clubs status
        $inactiveCl = DB::table('tenants')->where('is_active', false)->count();
        if ($inactiveCl > 0) {
            $this->addWarning("$inactiveCl clubs are marked as inactive");
        }

        echo "\n";
    }

    protected function auditUserClubRelationships()
    {
        echo "3. User-Club Relationships\n";
        echo "───────────────────────────────────────────────────────────\n";

        // Check for user_club table
        if (!Schema::hasTable('user_club')) {
            $this->addWarning("'user_club' pivot table missing - using 'club_users' instead");
        }

        if (Schema::hasTable('club_users')) {
            $clubUsersCount = DB::table('club_users')->count();
            if ($clubUsersCount > 0) {
                $this->addSuccess("$clubUsersCount user-club relationships configured");
            } else {
                $this->addWarning("'club_users' table exists but is empty - no user-club assignments");
            }

            // List unassigned users
            $usersCount = DB::table('users')->count();
            if ($clubUsersCount === 0 && $usersCount > 0) {
                $unassignedUsers = DB::table('users')->pluck('email')->implode(', ');
                $this->addWarning("Users have no club assignments: $unassignedUsers");
            }
        } else {
            $this->addIssue("No pivot table found for user-club relationships!");
        }

        echo "\n";
    }

    protected function auditTenantSystem()
    {
        echo "4. Multi-Tenancy System\n";
        echo "───────────────────────────────────────────────────────────\n";

        try {
            $tenants = DB::table('tenants')->get(['id', 'club_name', 'database']);

            foreach ($tenants as $tenant) {
                $dbExists = false;
                try {
                    $databases = DB::select("SELECT 1 FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$tenant->database]);
                    $dbExists = count($databases) > 0;
                } catch (\Exception $e) {
                    // Database check failed
                }

                if ($dbExists) {
                    $this->addSuccess("Tenant DB exists: {$tenant->database} (Club: {$tenant->club_name})");
                } else {
                    $this->addWarning("Tenant DB missing: {$tenant->database} (Club: {$tenant->club_name})");
                }
            }
        } catch (\Exception $e) {
            $this->addWarning("Could not verify tenant databases: " . $e->getMessage());
        }

        echo "\n";
    }

    protected function auditAPIEndpoints()
    {
        echo "5. API Endpoints\n";
        echo "───────────────────────────────────────────────────────────\n";

        try {
            // Check for route file
            if (file_exists(base_path('routes/web.php'))) {
                $this->addSuccess("Web routes file exists");
            } else {
                $this->addIssue("Web routes file missing");
            }

            if (file_exists(base_path('routes/tenant.php'))) {
                $this->addSuccess("Tenant routes file exists");
            } else {
                $this->addWarning("Tenant routes file missing");
            }

            if (file_exists(base_path('routes/api.php'))) {
                $this->addSuccess("API routes file exists");
            } else {
                $this->addWarning("API routes file missing");
            }

            // Check controllers
            $controllers = [
                'ClubApiController',
                'ClubRegistrationController',
                'SuperAdminController',
            ];

            foreach ($controllers as $controller) {
                $path = base_path("app/Http/Controllers/{$controller}.php");
                if (file_exists($path)) {
                    $this->addSuccess("Controller '$controller' exists");
                } else {
                    $this->addWarning("Controller '$controller' not found");
                }
            }
        } catch (\Exception $e) {
            $this->addWarning("API audit failed: " . $e->getMessage());
        }

        echo "\n";
    }

    protected function auditFilamentAdmin()
    {
        echo "6. Filament Admin Panel\n";
        echo "───────────────────────────────────────────────────────────\n";

        try {
            if (file_exists(base_path('app/Providers/Filament/SuperAdminPanelProvider.php'))) {
                $this->addSuccess("Filament SuperAdminPanelProvider configured");
            } else {
                $this->addWarning("Filament SuperAdminPanelProvider not found");
            }

            $resourcePath = base_path('app/Filament/Resources');
            if (is_dir($resourcePath)) {
                $resources = array_filter(scandir($resourcePath), fn($f) => substr($f, -4) === '.php');
                $this->addSuccess(count($resources) . " Filament resources configured");
            }
        } catch (\Exception $e) {
            $this->addWarning("Filament audit failed: " . $e->getMessage());
        }

        echo "\n";
    }

    protected function auditConfigFiles()
    {
        echo "7. Configuration Files\n";
        echo "───────────────────────────────────────────────────────────\n";

        $configs = [
            '.env' => 'Environment variables',
            'config/app.php' => 'App configuration',
            'config/database.php' => 'Database configuration',
            'config/tenancy.php' => 'Tenancy configuration',
        ];

        foreach ($configs as $file => $desc) {
            if (file_exists(base_path($file))) {
                $this->addSuccess("$desc ($file) exists");
            } else {
                $this->addIssue("$desc ($file) missing!");
            }
        }

        echo "\n";
    }

    protected function addSuccess($message)
    {
        $this->successes[] = $message;
        echo "   ✅ " . $message . "\n";
    }

    protected function addWarning($message)
    {
        $this->warnings[] = $message;
        echo "   ⚠️  " . $message . "\n";
    }

    protected function addIssue($message)
    {
        $this->issues[] = $message;
        echo "   ❌ " . $message . "\n";
    }

    protected function printReport()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║                    📊 AUDIT SUMMARY 📊                    ║\n";
        echo "╠════════════════════════════════════════════════════════════╣\n";
        printf("║ ✅ Passed:    %2d                                         ║\n", count($this->successes));
        printf("║ ⚠️  Warnings: %2d                                         ║\n", count($this->warnings));
        printf("║ ❌ Issues:    %2d                                         ║\n", count($this->issues));
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        if (count($this->issues) > 0) {
            echo "🔴 CRITICAL ISSUES TO FIX:\n";
            foreach ($this->issues as $i => $issue) {
                echo "   " . ($i + 1) . ". " . $issue . "\n";
            }
            echo "\n";
        }

        if (count($this->warnings) > 0) {
            echo "🟡 RECOMMENDATIONS:\n";
            foreach ($this->warnings as $i => $warning) {
                echo "   " . ($i + 1) . ". " . $warning . "\n";
            }
            echo "\n";
        }

        if (count($this->issues) === 0 && count($this->warnings) === 0) {
            echo "🎉 Backend is fully configured and ready for testing!\n\n";
        }
    }
}

$audit = new BackendAudit();
$audit->run();
