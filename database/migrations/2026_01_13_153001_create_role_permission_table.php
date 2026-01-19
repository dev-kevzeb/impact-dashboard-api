<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FASE 3: Crear tabla role_permission (pivot) para Spatie Permission
     * 
     * Tabla pivot que relaciona roles con permisos.
     * Un rol puede tener muchos permisos.
     * Un permiso puede estar asignado a muchos roles.
     */
    public function up(): void
    {
        Schema::create('role_permission', function (Blueprint $table) {
            $table->bigInteger('permission_id');
            $table->bigInteger('role_id');

            // Primary key compuesta
            $table->primary(['permission_id', 'role_id'], 'role_permission_pkey');

            // Foreign keys
            $table->foreign('permission_id')
                ->references('id')
                ->on('permission')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('role')
                ->onDelete('cascade');

            // NO timestamps - tabla pivot simple
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permission');
    }
};
