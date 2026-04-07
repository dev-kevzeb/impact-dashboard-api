<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permanent cleanup of legacy flow tables.
     */
    public function up(): void
    {
        // Drop child table first to avoid FK issues.
        Schema::dropIfExists('program_user');
        Schema::dropIfExists('country_kpa_user');
    }

    /**
     * Non-reversible migration by product decision.
     */
    public function down(): void
    {
        // Intentionally left empty: permanent removal.
    }
};
