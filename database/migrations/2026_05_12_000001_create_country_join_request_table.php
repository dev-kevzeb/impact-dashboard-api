<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_join_request', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('country')->cascadeOnDelete();
            $table->foreignId('requester_user_role_id')->constrained('user_role')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->unique(['country_id', 'requester_user_role_id'], 'uq_country_join_request_country_requester');
            $table->index('status', 'idx_country_join_request_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_join_request');
    }
};
