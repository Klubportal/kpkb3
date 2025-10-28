<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Match Officials (Schiedsrichter und Assistenten)
     */
    public function up(): void
    {
        Schema::connection('central')->create('comet_match_officials', function (Blueprint $table) {
            $table->id();

            // FIFA IDs
            $table->bigInteger('match_fifa_id')->comment('FK to comet_matches');
            $table->bigInteger('person_fifa_id')->comment('Official person FIFA ID');

            // Person Info
            $table->string('person_name')->nullable();
            $table->string('local_person_name')->nullable();

            // Official Role
            $table->enum('role', [
                'REFEREE',
                'ASSISTANT_REFEREE',
                'FOURTH_OFFICIAL',
                'VAR',
                'AVAR',
                'DELEGATE',
                'OBSERVER',
                'ADDITIONAL_ASSISTANT_REFEREE'
            ])->comment('Official role in match');

            $table->string('role_description')->nullable();
            $table->string('comet_role_name')->nullable()->comment('COMET label key');
            $table->string('comet_role_name_key')->nullable()->comment('Translation key');

            // Official Details
            $table->string('nationality')->nullable();
            $table->string('nationality_code', 3)->nullable();

            // Metadata
            $table->json('additional_info')->nullable()->comment('Extra official data');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('match_fifa_id');
            $table->index('person_fifa_id');
            $table->index('role');
            $table->unique(['match_fifa_id', 'person_fifa_id', 'role'], 'match_official_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_match_officials');
    }
};
