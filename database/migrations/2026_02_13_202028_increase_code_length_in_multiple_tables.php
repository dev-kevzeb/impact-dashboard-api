<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpa', function (Blueprint $table) {
            $table->string('name', 300)->change();
        });

        Schema::table('strategic_output', function (Blueprint $table) {
            $table->string('name', length: 300)->change();
        });

        Schema::table('measure', function (Blueprint $table) {
            $table->string('name', 300)->change();
        });
        
        Schema::table('indicator', function (Blueprint $table) {
            $table->string('name', length: 300)->change();
        });
    }

    public function down(): void
    {
        Schema::table('kpa', function (Blueprint $table) {
            $table->string('name', 100)->change();
        });

        Schema::table('strategic_output', function (Blueprint $table) {
            $table->string('name', 255)->change();
        });

        Schema::table('measure', function (Blueprint $table) {
            $table->string('name', 100)->change();
        });

        Schema::table('indicator', function (Blueprint $table) {
            $table->string('name', 100)->change();
        });
    }
};
