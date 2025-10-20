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
        Schema::create('country_kpas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_country')->constrained('countries');
            $table->foreignId('id_kpa')->constrained('kpas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_kpas');
    }
};
