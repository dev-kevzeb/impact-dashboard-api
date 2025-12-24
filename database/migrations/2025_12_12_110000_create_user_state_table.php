<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE SEQUENCE IF NOT EXISTS user_state_seq');
        }

        Schema::create('user_state', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                $table->bigInteger('id')->primary();
            } else {
                $table->id();
            }

            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE user_state ALTER COLUMN id SET DEFAULT nextval('user_state_seq')");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_state');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP SEQUENCE IF EXISTS user_state_seq');
        }
    }
};
