<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing NULLs to empty string before removing nullable
        DB::table('agency')->whereNull('url')->update(['url' => '']);

        Schema::table('agency', function (Blueprint $table) {
            $table->string('url', 255)->default('')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->string('url', 255)->nullable()->change();
        });

        // Restore empty strings back to NULL on rollback
        DB::table('agency')->where('url', '')->update(['url' => null]);
    }
};
