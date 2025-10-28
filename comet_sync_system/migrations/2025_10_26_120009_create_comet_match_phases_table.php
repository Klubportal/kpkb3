<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Match Phases (Halbzeiten, Verlängerung, Elfmeterschießen)
     */
    public function up(): void
    {
        Schema::connection('central')->create('comet_match_phases', function (Blueprint $table) {
            $table->id();

            // FIFA IDs
            $table->bigInteger('match_fifa_id')->comment('FK to comet_matches');

            // Phase Info
            $table->enum('phase', [
                'FIRST_HALF',
                'SECOND_HALF',
                'FIRST_ET',
                'SECOND_ET',
                'PEN',
                'BEFORE_THE_MATCH',
                'DURING_THE_BREAK',
                'AFTER_THE_MATCH',
                'PER_1',
                'PER_2',
                'PER_3'
            ])->comment('Match phase type');

            // Scores
            $table->integer('home_score')->nullable()->comment('Home team score in this phase');
            $table->integer('away_score')->nullable()->comment('Away team score in this phase');

            // Timing
            $table->integer('regular_time')->nullable()->comment('Regular time in minutes');
            $table->integer('stoppage_time')->nullable()->comment('Stoppage/injury time in minutes');
            $table->integer('phase_length')->nullable()->comment('Total phase length (regular + stoppage)');

            // Timestamps
            $table->dateTime('start_date_time')->nullable()->comment('Phase start (local time)');
            $table->dateTime('end_date_time')->nullable()->comment('Phase end (local time)');
            $table->dateTime('start_date_time_utc')->nullable()->comment('Phase start (UTC)');
            $table->dateTime('end_date_time_utc')->nullable()->comment('Phase end (UTC)');

            // Metadata
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('match_fifa_id');
            $table->unique(['match_fifa_id', 'phase'], 'match_phase_unique');
            $table->index(['match_fifa_id', 'start_date_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_match_phases');
    }
};
