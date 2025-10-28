<?php
/**
 * Fix: Populate User-Club Assignments
 * Assigns each user to a club to fix 403 Authorization errors
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔧 Fixing User-Club Assignments\n";
echo str_repeat("=", 60) . "\n\n";

try {
    $users = DB::table('users')->get();
    $clubs = DB::table('tenants')->get();

    if ($clubs->count() === 0) {
        echo "❌ No clubs found. Cannot assign users.\n";
        exit(1);
    }

    echo "Starting assignment process...\n";
    echo "Users to assign: " . $users->count() . "\n";
    echo "Clubs available: " . $clubs->count() . "\n\n";

    $assignmentCount = 0;

    foreach ($users as $user) {
        // Get or create club assignment
        // For simplicity, assign each user to all clubs they should have access to
        // Or assign to first club with admin role

        foreach ($clubs as $club) {
            $exists = DB::table('club_users')
                ->where('user_id', $user->id)
                ->where('club_id', $club->id)
                ->exists();

            if (!$exists) {
                DB::table('club_users')->insert([
                    'user_id' => $user->id,
                    'club_id' => $club->id,
                    'role' => 'admin', // Assign admin role by default
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $assignmentCount++;
                echo "✅ Assigned {$user->email} to {$club->club_name} as admin\n";
            }
        }
    }

    echo "\n✅ Total assignments created: $assignmentCount\n";

    // Verify
    $totalAssignments = DB::table('club_users')->count();
    echo "Total assignments in DB: $totalAssignments\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ User-Club assignments fixed!\n";
