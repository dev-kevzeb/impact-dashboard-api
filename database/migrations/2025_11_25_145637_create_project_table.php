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
        Schema::create('project', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('description',2000);
            $table->string('project_url')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('progress', 5, 2)->default(0);
            $table->string('comments', 1000)->nullable();
            $table->decimal('project_budget', 15, 2);

            // Relaciones (FKs)
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('beneficiary_id')->nullable();
            $table->unsignedBigInteger('project_state_id')->nullable();

            // Foreign keys
            $table->foreign('contact_id')->references('id')->on('contact');
            $table->foreign('beneficiary_id')->references('id')->on('beneficiary');
            $table->foreign('project_state_id')->references('id')->on('project_state');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project');
    }
};
