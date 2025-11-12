<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Habilita la extensión unaccent de PostgreSQL para permitir
     * comparaciones de strings sin distinción de acentos/tildes.
     * 
     */
    public function up(): void
    {
        // Solo ejecutar en PostgreSQL (no en SQLite para tests)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Solo ejecutar en PostgreSQL (no en SQLite para tests)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP EXTENSION IF EXISTS unaccent');
        }
    }
};
