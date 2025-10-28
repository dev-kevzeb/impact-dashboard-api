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
        Schema::table('kpa', function (Blueprint $table) {
            if (!Schema::hasColumn('kpa', 'created_at') && !Schema::hasColumn('kpa', 'updated_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpa', function (Blueprint $table) {
            if (Schema::hasColumn('kpa', 'created_at') || Schema::hasColumn('kpa', 'updated_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
