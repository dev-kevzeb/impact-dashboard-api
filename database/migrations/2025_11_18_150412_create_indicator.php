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
        Schema::create('indicator', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('target');
            $table->foreignId('type_id')->constrained('indicator_type');
            $table->foreignId('measure_id')->nullable()->constrained('measure')->onDelete('set null');

            $table->unique(['measure_id','name'], 'measure_indicator_unique_name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicator');
    }
};
