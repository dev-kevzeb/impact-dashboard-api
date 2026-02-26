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
        Schema::create('country_user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('country')->onDelete('cascade');
            $table->foreignId('user_role_id')->constrained('user_role')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint: A user_role cannot be assigned to the same country twice
            $table->unique(['country_id', 'user_role_id'], 'uq_country_user_role_combination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_user_role');
    }
};
