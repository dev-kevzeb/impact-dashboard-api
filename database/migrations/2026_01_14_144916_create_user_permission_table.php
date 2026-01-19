<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FASE 5: Crear tabla user_permission (pivot) para permisos directos a usuarios
     * 
     * Esta tabla permite asignar permisos específicos a usuarios individuales,
     * independientemente de sus roles. Útil para excepciones y permisos temporales.
     * 
     * Casos de uso:
     * - Permisos temporales (ej: acceso especial por 1 semana)
     * - Excepciones de seguridad (ej: revocar 1 permiso específico)
     * - Transiciones de roles (ej: mantener acceso a módulo anterior)
     * 
     * Relación: Many-to-Many polimórfica entre User y Permission
     */
    public function up(): void
    {
        Schema::create('user_permission', function (Blueprint $table) {
            $table->bigInteger('permission_id');
            $table->bigInteger('model_id'); // user_id (llamado model_id por polimorfismo)
            $table->string('model_type', 255); // App\Modules\User\Domain\User

            // Primary key compuesta (evita duplicados)
            $table->primary(['permission_id', 'model_id', 'model_type'], 'user_permission_pkey');

            // Foreign key a permission
            $table->foreign('permission_id')
                ->references('id')
                ->on('permission')
                ->onDelete('cascade');

            // Foreign key a user (via model_id)
            $table->foreign('model_id')
                ->references('id')
                ->on('user')
                ->onDelete('cascade');

            // NO timestamps - tabla pivot simple
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permission');
    }
};
