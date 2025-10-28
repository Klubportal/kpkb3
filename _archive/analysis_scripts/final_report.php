<?php
/**
 * 🎯 Final Backend Test & Validation Report
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║   🎯 FINAL BACKEND DEBUG & TEST REPORT 🎯            ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$results = ['pass' => 0, 'warn' => 0, 'fail' => 0];

// 1. Database
echo "1️⃣  DATABASE STATUS\n";
echo str_repeat("─", 60) . "\n";
try {
    $tables = DB::select('SHOW TABLES');
    echo "   ✅ " . count($tables) . " tables found\n";
    $results['pass']++;

    // Check critical tables
    foreach (['users', 'tenants', 'club_members', 'migrations'] as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "   ✅ '$table' table: $count records\n";
            $results['pass']++;
        } else {
            echo "   ❌ '$table' table missing\n";
            $results['fail']++;
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
    $results['fail']++;
}
echo "\n";

// 2. User-Club Relationships
echo "2️⃣  USER-CLUB RELATIONSHIPS\n";
echo str_repeat("─", 60) . "\n";
try {
    if (Schema::hasTable('club_members')) {
        $total = DB::table('club_members')->count();
        $byRole = DB::table('club_members')
            ->selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        echo "   ✅ club_members: $total total assignments\n";
        echo "      Role breakdown:\n";
        foreach ($byRole as $role => $count) {
            echo "      - $role: $count\n";
        }
        $results['pass']++;

        // Check for unassigned users
        $unassigned = DB::table('users')
            ->whereNotIn('id', DB::table('club_members')->select('user_id'))
            ->get();

        if ($unassigned->count() === 0) {
            echo "   ✅ All users assigned to clubs\n";
            $results['pass']++;
        } else {
            echo "   ⚠️  " . $unassigned->count() . " users unassigned\n";
            $results['warn']++;
        }
    } else {
        echo "   ❌ club_members table not found\n";
        $results['fail']++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['fail']++;
}
echo "\n";

// 3. API Endpoints
echo "3️⃣  API ENDPOINTS\n";
echo str_repeat("─", 60) . "\n";
try {
    $apiRoutes = [];
    foreach (Route::getRoutes() as $route) {
        if (strpos($route->uri, 'api') !== false) {
            $apiRoutes[] = $route->uri;
        }
    }
    echo "   ✅ " . count($apiRoutes) . " API routes registered\n";
    $results['pass']++;

    // Check specific endpoints
    $requiredEndpoints = [
        'api/clubs/register',
        'api/clubs/check-availability',
        'api/clubs/{club}'
    ];

    foreach ($requiredEndpoints as $endpoint) {
        $found = false;
        foreach ($apiRoutes as $route) {
            if (strpos($route, str_replace('{club}', '', $endpoint)) !== false) {
                $found = true;
                break;
            }
        }
        if ($found) {
            echo "   ✅ /$endpoint\n";
            $results['pass']++;
        } else {
            echo "   ⚠️  /$endpoint not found\n";
            $results['warn']++;
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['fail']++;
}
echo "\n";

// 4. Tenants & Clubs
echo "4️⃣  TENANTS & CLUBS\n";
echo str_repeat("─", 60) . "\n";
try {
    $clubs = DB::table('tenants')->get();
    echo "   ✅ " . $clubs->count() . " clubs configured:\n";
    $results['pass']++;

    foreach ($clubs as $club) {
        $active = $club->is_active ? '🟢' : '🔴';
        echo "      $active {$club->club_name} ({$club->subscription_plan})\n";
    }

    // Check domains
    $domainCount = DB::table('domains')->count();
    if ($domainCount > 0) {
        echo "   ✅ " . $domainCount . " domains configured\n";
        $results['pass']++;
    } else {
        echo "   ⚠️  No domains configured (may affect multi-tenancy routing)\n";
        $results['warn']++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['fail']++;
}
echo "\n";

// 5. Controllers & Routes
echo "5️⃣  CONTROLLERS & ROUTES\n";
echo str_repeat("─", 60) . "\n";
try {
    $requiredControllers = [
        'ClubApiController',
        'ClubRegistrationController',
        'SuperAdminController'
    ];

    foreach ($requiredControllers as $controller) {
        $path = base_path("app/Http/Controllers/$controller.php");
        if (file_exists($path)) {
            echo "   ✅ $controller\n";
            $results['pass']++;
        } else {
            echo "   ❌ $controller not found\n";
            $results['fail']++;
        }
    }

    // Check route files
    foreach (['web.php', 'tenant.php', 'api.php'] as $file) {
        $path = base_path("routes/$file");
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $routeCount = substr_count($content, 'Route::');
            echo "   ✅ routes/$file ($routeCount routes)\n";
            $results['pass']++;
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['fail']++;
}
echo "\n";

// 6. Filament Admin
echo "6️⃣  FILAMENT ADMIN PANEL\n";
echo str_repeat("─", 60) . "\n";
try {
    $panelProvider = base_path('app/Providers/Filament/SuperAdminPanelProvider.php');
    if (file_exists($panelProvider)) {
        echo "   ✅ SuperAdminPanelProvider configured\n";
        $results['pass']++;

        $resourcePath = base_path('app/Filament/Resources');
        if (is_dir($resourcePath)) {
            $resources = array_filter(scandir($resourcePath), fn($f) => substr($f, -4) === '.php' && $f !== '.gitkeep');
            echo "   ✅ " . count($resources) . " Filament resources\n";
            $results['pass']++;
        }
    } else {
        echo "   ❌ SuperAdminPanelProvider not found\n";
        $results['fail']++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['fail']++;
}
echo "\n";

// SUMMARY
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    📊 TEST SUMMARY 📊                      ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
printf("║ ✅ PASSED:    %-2d                                         ║\n", $results['pass']);
printf("║ ⚠️  WARNINGS: %-2d                                         ║\n", $results['warn']);
printf("║ ❌ FAILED:    %-2d                                         ║\n", $results['fail']);
echo "╠══════════════════════════════════════════════════════════════╣\n";

$total = $results['pass'] + $results['warn'] + $results['fail'];
$healthPercent = ($results['pass'] / $total) * 100;
printf("║ 🏥 Backend Health: %.0f%%                              ║\n", $healthPercent);
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

if ($results['fail'] === 0) {
    echo "✅ Backend is ready for testing!\n";
    echo "   - Server: http://127.0.0.1:8000\n";
    echo "   - Admin: http://127.0.0.1:8000/super-admin\n";
} else {
    echo "❌ Please fix the failed tests above before proceeding.\n";
}

echo "\n";
