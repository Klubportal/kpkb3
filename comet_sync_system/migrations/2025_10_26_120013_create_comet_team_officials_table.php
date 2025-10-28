<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Team Officials (Trainer, Physios, etc. - Team Staff)
     */
    public function up(): void
    {
        Schema::connection('central')->create('comet_team_officials', function (Blueprint $table) {
            $table->id();

            // FIFA IDs
            $table->bigInteger('team_fifa_id')->comment('FK to comet_clubs_extended');
            $table->bigInteger('person_fifa_id')->comment('Official person FIFA ID');
            $table->bigInteger('competition_fifa_id')->nullable()->comment('FK to comet_competitions (if competition-specific)');

            // Person Info
            $table->string('person_name')->nullable();
            $table->string('local_person_name')->nullable();
            $table->string('international_first_name')->nullable();
            $table->string('international_last_name')->nullable();

            // Official Role
            $table->enum('role', [
                'COACH',
                'ASSISTANT_COACH',
                'GOALKEEPER_COACH',
                'PHYSICAL_TRAINER',
                'TEAM_DOCTOR',
                'PHYSIOTHERAPIST',
                'DIRECTOR',
                'TECHNICAL_DIRECTOR',
                'MANAGER',
                'SPORTS_DIRECTOR',
                'SCOUT',
                'ANALYST',
                'OTHER'
            ])->comment('Official role in team');

            $table->string('role_description')->nullable();
            $table->string('comet_role_name')->nullable()->comment('COMET label key');
            $table->string('comet_role_name_key')->nullable()->comment('Translation key');

            // Status
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'SUSPENDED'])->default('ACTIVE');

            // Contract Info
            $table->date('valid_from')->nullable()->comment('Contract start date');
            $table->date('valid_to')->nullable()->comment('Contract end date');

            // Personal Info
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('nationality_code', 3)->nullable();
            $table->string('place_of_birth')->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('photo_url')->nullable();

            // License Info
            $table->string('license_type')->nullable()->comment('Trainer license (e.g., UEFA Pro)');
            $table->string('license_number')->nullable();
            $table->date('license_valid_until')->nullable();

            // Metadata
            $table->json('additional_info')->nullable()->comment('Extra official data');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('team_fifa_id');
            $table->index('person_fifa_id');
            $table->index('competition_fifa_id');
            $table->index('status');
            $table->index(['team_fifa_id', 'status']);
            $table->index(['team_fifa_id', 'role']);
            $table->unique(['team_fifa_id', 'person_fifa_id', 'role'], 'team_official_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_team_officials');
    }
};
