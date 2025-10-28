<?php
/**
 * Create club_members table migration
 * Since club_members table is missing but ClubMember model expects it
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "🔧 Creating club_members table...\n\n";

if (Schema::hasTable('club_members')) {
    echo "✅ club_members table already exists\n";
} else {
    echo "Creating club_members table...\n";

    Schema::create('club_members', function ($table) {
        $table->id();
        $table->string('club_id')->index(); // Changed to string for UUID support
        $table->unsignedBigInteger('user_id')->index();
        $table->enum('role', ['admin', 'manager', 'coach', 'player', 'parent', 'fan'])->default('fan');
        $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
        $table->string('phone')->nullable();
        $table->dateTime('joined_at')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();

        // Foreign keys
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });

    echo "✅ club_members table created\n";
}

// Now populate it from club_users if data exists there
echo "\nPopulating club_members from club_users...\n";

$clubUsersCount = DB::table('club_users')->count();
if ($clubUsersCount > 0) {
    $clubUsers = DB::table('club_users')->get();
    $count = 0;

    foreach ($clubUsers as $cu) {
        // Get the UUID of the club
        $club = DB::table('tenants')->where('id', $cu->club_id)->first();

        if ($club) {
            $exists = DB::table('club_members')
                ->where('club_id', $club->id)
                ->where('user_id', $cu->user_id)
                ->exists();

            if (!$exists) {
                DB::table('club_members')->insert([
                    'club_id' => $club->id,
                    'user_id' => $cu->user_id,
                    'role' => $cu->role,
                    'status' => $cu->is_active ? 'active' : 'inactive',
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }
    }

    echo "✅ Migrated $count records from club_users\n";
} else {
    echo "⚠️  club_users is empty, nothing to migrate\n";
}

// Finally, assign ALL users to clubs if not already done
echo "\nEnsuring all users are assigned to clubs...\n";

$users = DB::table('users')->get();
$clubs = DB::table('tenants')->get();

if ($clubs->count() > 0) {
    $newAssignments = 0;

    foreach ($users as $user) {
        foreach ($clubs as $club) {
            $exists = DB::table('club_members')
                ->where('club_id', $club->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$exists) {
                DB::table('club_members')->insert([
                    'club_id' => $club->id,
                    'user_id' => $user->id,
                    'role' => 'admin', // Default to admin for testing
                    'status' => 'active',
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $newAssignments++;
            }
        }
    }

    echo "✅ Created $newAssignments new user-club assignments\n";
}

$finalCount = DB::table('club_members')->count();
echo "\n📊 Final club_members count: $finalCount\n";

echo "\n✅ All fixes applied successfully!\n";
