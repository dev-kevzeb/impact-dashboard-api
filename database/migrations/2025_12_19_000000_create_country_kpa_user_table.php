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
        Schema::create('country_kpa_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_kpa_id')->constrained('country_kpa');
            $table->foreignId('user_id')->constrained('user');
            $table->timestamps();
            
            // Unique constraint: a user can only be assigned once to a specific country_kpa
            $table->unique(['country_kpa_id', 'user_id'], 'uq_country_kpa_user_combination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_kpa_user');
    }
};
