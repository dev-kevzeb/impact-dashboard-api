<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_invite_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('project')->cascadeOnDelete();
            $table->foreignId('country_user_role_id')->constrained('country_user_role')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'country_user_role_id'], 'uq_project_invite_user_project_country_user_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invite_user');
    }
};