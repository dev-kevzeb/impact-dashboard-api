<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FASE 3: Crear tabla permission para Spatie Permission
     * 
     * Esta tabla almacena todos los permisos del sistema.
     * Cada permiso tiene un scope (ej: donors:read, projects:write)
     */
    public function up(): void
    {
        // Create sequence only for PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE SEQUENCE IF NOT EXISTS permission_seq');
        }

        Schema::create('permission', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                $table->bigInteger('id')->primary();
            } else {
                // For SQLite and others, use auto-increment
                $table->id();
            }
            $table->string('name', 255); // ej: donors:read
            $table->string('guard_name', 125)->default('api');
            $table->string('scope', 50)->nullable(); // ej: donors, projects
            $table->string('module', 50)->nullable(); // Para agrupar permisos
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            // Spatie requiere este constraint
            $table->unique(['name', 'guard_name'], 'uq_permission_name_guard');
        });

        // Set default value for id using sequence (PostgreSQL only)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE permission ALTER COLUMN id SET DEFAULT nextval('permission_seq')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP SEQUENCE IF EXISTS permission_seq');
        }
    }
};
