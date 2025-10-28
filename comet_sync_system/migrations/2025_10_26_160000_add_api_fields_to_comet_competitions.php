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
        Schema::connection('central')->table('comet_competitions', function (Blueprint $table) {
            // FIFA Organization ID (WICHTIG für Filterung)
            $table->bigInteger('organisation_fifa_id')->nullable()->after('comet_id')->comment('Parent organization FIFA ID');

            // Active Status
            $table->boolean('active')->default(true)->after('status')->index();

            // Age Category
            $table->string('age_category', 20)->nullable()->after('active')->comment('SENIORS, U_21, U_19, etc.');

            // Team Character
            $table->string('team_character', 20)->nullable()->after('age_category')->comment('CLUB or NATIONAL');

            // Competition Nature
            $table->string('nature', 50)->nullable()->after('team_character')->comment('ROUND_ROBIN, KNOCK_OUT, etc.');

            // Gender
            $table->string('gender', 10)->nullable()->after('nature')->comment('MALE, FEMALE, MIXED');

            // Match Type
            $table->string('match_type', 20)->nullable()->after('gender')->comment('OFFICIAL, FRIENDLY');

            // Number of Participants
            $table->integer('participants')->nullable()->after('match_type');

            // Image ID from Comet
            $table->bigInteger('image_id')->nullable()->after('logo_url');

            // Local Names JSON
            $table->json('local_names')->nullable()->after('settings')->comment('Localized competition names');

            // Add index for organisation_fifa_id
            $table->index('organisation_fifa_id');
            $table->index(['organisation_fifa_id', 'active', 'season']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('comet_competitions', function (Blueprint $table) {
            $table->dropIndex(['comet_competitions_organisation_fifa_id_index']);
            $table->dropIndex(['comet_competitions_organisation_fifa_id_active_season_index']);

            $table->dropColumn([
                'organisation_fifa_id',
                'active',
                'age_category',
                'team_character',
                'nature',
                'gender',
                'match_type',
                'participants',
                'image_id',
                'local_names',
            ]);
        });
    }
};
