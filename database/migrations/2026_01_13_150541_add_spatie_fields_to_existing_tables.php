<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * FASE 2: Ajustar tablas existentes para Spatie Permission
     * 
     * Agrega campos requeridos por Spatie a tablas existentes:
     * - role.guard_name (contexto de autenticación)
     * - user_role.model_type (para relación polimórfica)
     */
    public function up(): void
    {
        // 1. Agregar guard_name a tabla role
        Schema::table('role', function (Blueprint $table) {
            $table->string('guard_name', 125)->default('api')->after('name');
        });

        // 2. Actualizar constraint unique en role (name + guard_name)
        // SQLite doesn't support DROP CONSTRAINT, skip for tests
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE role DROP CONSTRAINT IF EXISTS uq_role_name');
            DB::statement('ALTER TABLE role ADD CONSTRAINT uq_role_name_guard UNIQUE(name, guard_name)');
        } else {
            // For SQLite (tests), the unique constraint is handled differently
            // We rely on the unique index created by Laravel's schema builder
        }

        // 3. Poblar guard_name en registros existentes
        DB::table('role')->update(['guard_name' => 'api']);

        // 4. Agregar model_type a tabla user_role
        Schema::table('user_role', function (Blueprint $table) {
            $table->string('model_type', 255)
                ->default('App\\Modules\\User\\Domain\\User')
                ->after('user_id');
        });

        // 5. Poblar model_type en registros existentes
        DB::table('user_role')->update(['model_type' => 'App\\Modules\\User\\Domain\\User']);

        // 6. Mantener PK en user_role.id para compatibilidad con FKs de módulos de dominio
        // y agregar unicidad sobre la combinación usada por Spatie.
        // NOTE: SQLite no requiere este ajuste explícito.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS uq_user_role_spatie_triplet ON user_role(role_id, user_id, model_type)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios en orden inverso

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS uq_user_role_spatie_triplet');
        }

        // 5. Eliminar model_type de user_role
        Schema::table('user_role', function (Blueprint $table) {
            $table->dropColumn('model_type');
        });

        if (DB::getDriverName() === 'pgsql') {
            // 6. Restaurar constraint unique original de role
            DB::statement('ALTER TABLE role DROP CONSTRAINT IF EXISTS uq_role_name_guard');
            DB::statement('ALTER TABLE role ADD CONSTRAINT uq_role_name UNIQUE(name)');
        }

        // 7. Eliminar guard_name de role
        Schema::table('role', function (Blueprint $table) {
            $table->dropColumn('guard_name');
        });
    }
};
