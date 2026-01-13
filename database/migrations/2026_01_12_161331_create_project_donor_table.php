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
        Schema::create('project_donor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('donor_id');
            $table->decimal('contribution', 5, 2)->default(0);

            $table->foreign('project_id')->references('id')->on('project')->onDelete('cascade');
            $table->foreign('donor_id')->references('id')->on('donor')->onDelete('cascade');

            $table->unique(['project_id','donor_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_donor');
    }
};
