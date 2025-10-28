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
        Schema::connection('central')->create('comet_competitions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('comet_id')->unique()->comment('Comet API Competition ID');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('country', 3)->nullable()->comment('ISO 3166-1 alpha-3 country code');
            $table->string('logo_url')->nullable();
            $table->enum('type', ['league', 'cup', 'tournament', 'friendly'])->default('league');
            $table->string('season', 20)->nullable()->comment('e.g., 2024/25');
            $table->enum('status', ['upcoming', 'active', 'finished', 'cancelled'])->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('settings')->nullable()->comment('Additional competition settings');
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index(['country', 'status']);
            $table->index('season');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_competitions');
    }
};
