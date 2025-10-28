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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // z.B. "Modern", "Classic", "Sport"
            $table->string('slug')->unique(); // z.B. "modern", "classic", "sport"
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable(); // Screenshot des Templates
            $table->json('features')->nullable(); // JSON mit verfügbaren Features
            $table->json('colors')->nullable(); // JSON mit Farbschema (primary, secondary, etc.)
            $table->string('layout_path')->default('layouts.frontend'); // Blade Layout Pfad
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Füge template_id zu tenants Tabelle hinzu
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->after('id')->constrained('templates')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn('template_id');
        });

        Schema::dropIfExists('templates');
    }
};
