<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_dashboard_share', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('country')->cascadeOnDelete();
            $table->foreignId('owner_country_user_role_id')->constrained('country_user_role')->cascadeOnDelete();
            $table->foreignId('shared_user_role_id')->constrained('user_role')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['country_id', 'shared_user_role_id'], 'uq_country_dashboard_share_country_shared_user_role');
            $table->index('owner_country_user_role_id', 'idx_country_dashboard_share_owner_cur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_dashboard_share');
    }
};
