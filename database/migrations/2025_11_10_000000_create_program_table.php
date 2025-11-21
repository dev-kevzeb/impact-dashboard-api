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
        Schema::create('program', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description');
            $table->string('banner_img', 500)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('program_url', 500)->nullable();
            
            // Foreign keys
            $table->foreignId('contact_id')->constrained('contact')->onDelete('restrict');
            $table->foreignId('program_state_id')->constrained('program_state')->onDelete('restrict');
            $table->foreignId('country_id')->constrained('country')->onDelete('restrict');
            
            $table->timestamps();
            
            // Índices para búsquedas frecuentes
            $table->index('name');
            $table->index('country_id');
            $table->index('program_state_id');
        });

        // Tabla pivot para Program-SDG (Many-to-Many)
        Schema::create('program_sdg', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('program')->onDelete('cascade');
            $table->foreignId('sdg_id')->constrained('sdg')->onDelete('cascade');
            $table->timestamps();
            
            // Índice único compuesto para evitar duplicados
            $table->unique(['program_id', 'sdg_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_sdg');
        Schema::dropIfExists('program');
    }
};
