<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_state', function(Blueprint $table){
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        // Índice único case-insensitive usando expresión SQL
        DB::statement('CREATE UNIQUE INDEX project_state_name_unique_ci ON project_state (LOWER(name))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS project_state_name_unique_ci');
        Schema::dropIfExists('project_state');
    }
};
