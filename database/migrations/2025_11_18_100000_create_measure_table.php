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
        Schema::create('measure', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('strategic_output_id')->constrained('strategic_output');

            $table->unique(['strategic_output_id','name'], 'strategic_output_measure_unique_name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measure');
    }
};
