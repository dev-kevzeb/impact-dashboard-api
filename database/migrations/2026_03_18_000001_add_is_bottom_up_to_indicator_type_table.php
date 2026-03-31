<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicator_type', function (Blueprint $table) {
            $table->boolean('is_bottom_up')->default(true)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('indicator_type', function (Blueprint $table) {
            $table->dropColumn('is_bottom_up');
        });
    }
};
