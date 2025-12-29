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
        Schema::create('program_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('program')->onDelete('cascade');
            $table->foreignId('country_kpa_user_id')->constrained('country_kpa_user')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint: A CountryKpaUser cannot be assigned to the same Program twice
            $table->unique(['program_id', 'country_kpa_user_id'], 'uq_program_country_kpa_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_user');
    }
};
