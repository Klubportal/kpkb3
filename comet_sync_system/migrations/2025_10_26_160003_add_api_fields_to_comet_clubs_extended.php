<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add columns if they don't exist
        $connection = Schema::connection('central');

        if (!$connection->hasColumn('comet_clubs_extended', 'short_name')) {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->string('short_name')->nullable()->after('name');
            });
        }

        if (!$connection->hasColumn('comet_clubs_extended', 'logo_url')) {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->string('logo_url')->nullable()->after('short_name');
            });
        }

        if (!$connection->hasColumn('comet_clubs_extended', 'city')) {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->string('city')->nullable()->after('country');
            });
        }

        if (!$connection->hasColumn('comet_clubs_extended', 'region')) {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->string('region')->nullable()->after('city');
            });
        }

        if (!$connection->hasColumn('comet_clubs_extended', 'facility_fifa_id')) {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->bigInteger('facility_fifa_id')->nullable()->after('stadium_capacity')->comment('Main stadium/facility FIFA ID');
            });
        }

        if (!$connection->hasColumn('comet_clubs_extended', 'website')) {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->string('website')->nullable()->after('club_info');
            });
        }

        if (!$connection->hasColumn('comet_clubs_extended', 'colors')) {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->string('colors')->nullable()->after('website');
            });
        }

        if (!$connection->hasColumn('comet_clubs_extended', 'status')) {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->string('status', 20)->default('ACTIVE')->after('is_synced')->comment('ACTIVE, INACTIVE');
            });
        }

        if (!$connection->hasColumn('comet_clubs_extended', 'local_names')) {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->json('local_names')->nullable()->after('sync_metadata')->comment('Localized club names');
            });
        }

        // Add indexes - with try-catch to avoid errors if they exist
        try {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->index('organisation_fifa_id');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        try {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->index('facility_fifa_id');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        try {
            $connection->table('comet_clubs_extended', function (Blueprint $table) {
                $table->index('status');
            });
        } catch (\Exception $e) {
            // Index already exists
        }
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('comet_clubs_extended', function (Blueprint $table) {
            $table->dropIndex(['comet_clubs_extended_organisation_fifa_id_index']);
            $table->dropIndex(['comet_clubs_extended_status_index']);
            $table->dropIndex(['comet_clubs_extended_facility_fifa_id_index']);

            $table->dropColumn([
                'organisation_fifa_id',
                'short_name',
                'city',
                'region',
                'status',
                'facility_fifa_id',
                'logo_url',
                'website',
                'colors',
                'local_names',
            ]);
        });
    }
};
