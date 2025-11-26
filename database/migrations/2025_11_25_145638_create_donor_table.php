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
        Schema::create('donor', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->decimal('contribution', 5, 2)->default(0);
            $table->foreignId('project_id')->nulleable()->constrained('project');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor');
    }
};
