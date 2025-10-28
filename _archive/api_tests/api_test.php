<?php
/**
 * API Endpoint Tester
 * Tests critical API endpoints
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class APITester
{
    protected $results = [];

    public function run()
    {
        echo "🌐 API Endpoint & Route Tester\n";
        echo str_repeat("=", 60) . "\n\n";

        $this->testRegistrationAPI();
        $this->testClubAPIEndpoints();
        $this->testUserAssignment();
        $this->testFilamentRoutes();
        $this->testTenantRoutes();

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 API Test Summary\n";
        echo str_repeat("=", 60) . "\n";
        $this->printSummary();
    }

    protected function testRegistrationAPI()
    {
        echo "1️⃣  Testing Club Registration API...\n";
        try {
            // Check if endpoint exists
            $routeExists = false;
            foreach (Route::getRoutes() as $route) {
                if (strpos($route->uri, 'api/clubs/register') !== false) {
                    $routeExists = true;
                    break;
                }
            }

            if ($routeExists) {
                $this->addResult('Registration API Route', 'PASS', 'Route found');
                echo "   ✅ /api/clubs/register route exists\n";
            } else {
                $this->addResult('Registration API Route', 'FAIL', 'Route not found');
                echo "   ❌ /api/clubs/register route not found\n";
            }

            // Check availability endpoint
            $availabilityExists = false;
            foreach (Route::getRoutes() as $route) {
                if (strpos($route->uri, 'api/clubs/check-availability') !== false) {
                    $availabilityExists = true;
                    break;
                }
            }

            if ($availabilityExists) {
                $this->addResult('Availability Check', 'PASS', 'Route found');
                echo "   ✅ /api/clubs/check-availability route exists\n";
            } else {
                $this->addResult('Availability Check', 'FAIL', 'Route not found');
                echo "   ❌ /api/clubs/check-availability route not found\n";
            }
        } catch (\Exception $e) {
            $this->addResult('Registration API', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function testClubAPIEndpoints()
    {
        echo "2️⃣  Testing Club Management API Endpoints...\n";
        try {
            $routes = [];
            foreach (Route::getRoutes() as $route) {
                if (strpos($route->uri, 'api/clubs') !== false) {
                    $routes[] = [
                        'uri' => $route->uri,
                        'methods' => implode(', ', $route->methods)
                    ];
                }
            }

            if (count($routes) > 0) {
                $this->addResult('Club API Endpoints', 'PASS', count($routes) . ' endpoints found');
                echo "   ✅ " . count($routes) . " club API endpoints found:\n";
                foreach ($routes as $route) {
                    echo "      - {$route['methods']}: {$route['uri']}\n";
                }
            } else {
                $this->addResult('Club API Endpoints', 'FAIL', 'No endpoints found');
                echo "   ❌ No club API endpoints found\n";
            }
        } catch (\Exception $e) {
            $this->addResult('Club API Endpoints', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function testUserAssignment()
    {
        echo "3️⃣  Testing User-Club Assignment...\n";
        try {
            // Check if user_club table exists
            $userClubCount = DB::table('user_club')->count();
            $this->addResult('User-Club Table', 'PASS', "$userClubCount assignments found");
            echo "   ✅ user_club table exists with $userClubCount assignments\n";

            // Get user assignments
            $assignments = DB::table('user_club')
                ->join('users', 'user_club.user_id', '=', 'users.id')
                ->join('tenants', 'user_club.club_id', '=', 'tenants.id')
                ->select('users.email', 'tenants.club_name', 'user_club.role')
                ->limit(5)
                ->get();

            if ($assignments->count() > 0) {
                echo "   📋 User Assignments:\n";
                foreach ($assignments as $assignment) {
                    echo "      - {$assignment->email} → {$assignment->club_name} ({$assignment->role})\n";
                }
            }
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'user_club') !== false) {
                $this->addResult('User-Club Table', 'WARN', 'Table might not exist');
                echo "   ⚠️  user_club table may need to be created\n";
            } else {
                $this->addResult('User Assignment', 'FAIL', $e->getMessage());
                echo "   ❌ {$e->getMessage()}\n";
            }
        }
        echo "\n";
    }

    protected function testFilamentRoutes()
    {
        echo "4️⃣  Testing Filament Admin Routes...\n";
        try {
            $filamentRoutes = [];
            foreach (Route::getRoutes() as $route) {
                if (strpos($route->uri, 'super-admin') !== false || strpos($route->uri, 'filament') !== false) {
                    $filamentRoutes[] = $route->uri;
                }
            }

            $this->addResult('Filament Routes', 'PASS', count($filamentRoutes) . ' routes found');
            echo "   ✅ " . count($filamentRoutes) . " Filament routes found\n";

            // Show some sample routes
            $samples = array_slice($filamentRoutes, 0, 5);
            foreach ($samples as $route) {
                echo "      - {$route}\n";
            }
            if (count($filamentRoutes) > 5) {
                echo "      ... and " . (count($filamentRoutes) - 5) . " more\n";
            }
        } catch (\Exception $e) {
            $this->addResult('Filament Routes', 'FAIL', $e->getMessage());
            echo "   ❌ {$e->getMessage()}\n";
        }
        echo "\n";
    }

    protected function testTenantRoutes()
    {
        echo "5️⃣  Testing Tenant Routes...\n";
        try {
            // Check if tenant.php routes are loaded
            $file = base_path('routes/tenant.php');
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $routeCount = substr_count($content, 'Route::');

                $this->addResult('Tenant Routes File', 'PASS', "$routeCount route definitions found");
                echo "   ✅ routes/tenant.php has $routeCount route definitions\n";

                // Extract sample routes
                preg_match_all('/Route::\w+\([\'"]([^\'"]+)/', $content, $matches);
                if (isset($matches[1]) && count($matches[1]) > 0) {
                    $samples = array_slice($matches[1], 0, 5);
                    echo "   📋 Sample Routes:\n";
                    foreach ($samples as $route) {
                        echo "      - {$route}\n";
                    }
                }
            } else {
                $this->addResult('Tenant Routes', 'FAIL', 'File not found');
                echo "   ❌ routes/tenant.php not found\n";
            }
        } catch (\Exception $e) {
            $this->addResult('Tenant Routes', 'FAIL', $e->getMessage());
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

        if ($warn > 0) {
            echo "\nWarnings:\n";
            foreach ($this->results as $result) {
                if ($result['status'] === 'WARN') {
                    echo "  ⚠️  {$result['test']}: {$result['message']}\n";
                }
            }
        }
    }
}

$tester = new APITester();
$tester->run();
