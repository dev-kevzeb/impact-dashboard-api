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
        Schema::create('program_country_user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('program')->onDelete('cascade');
            $table->foreignId('country_user_role_id')->constrained('country_user_role')->onDelete('cascade');
            $table->timestamps();

            // A CountryUserRole cannot be assigned to the same Program twice
            $table->unique(['program_id', 'country_user_role_id'], 'uq_program_country_user_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_country_user_role');
    }
};
