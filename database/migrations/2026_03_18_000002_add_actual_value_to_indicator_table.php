<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicator', function (Blueprint $table) {
            $table->decimal('actual_value', 15, 4)->nullable()->after('target');
        });
    }

    public function down(): void
    {
        Schema::table('indicator', function (Blueprint $table) {
            $table->dropColumn('actual_value');
        });
    }
};
