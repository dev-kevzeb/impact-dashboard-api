<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invite_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_country_user_role_id')
                ->constrained('program_country_user_role')
                ->cascadeOnDelete();
            $table->foreignId('invited_user_role_id')
                ->constrained('user_role')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['program_country_user_role_id', 'invited_user_role_id'],
                'uq_invite_program_target'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invite_program');
    }
};
