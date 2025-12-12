<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create sequence only for PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE SEQUENCE user_role_seq');
        }

        Schema::create('user_role', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                $table->bigInteger('id')->primary();
            } else {
                // For SQLite and others, use auto-increment
                $table->id();
            }
            $table->string('name', 50)->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        // Set default value for id using sequence (PostgreSQL only)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE user_role ALTER COLUMN id SET DEFAULT nextval('user_role_seq')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_role');
        
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP SEQUENCE IF EXISTS user_role_seq');
        }
    }
};
