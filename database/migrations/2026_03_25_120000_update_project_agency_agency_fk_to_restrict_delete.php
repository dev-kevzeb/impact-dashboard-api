<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_agency', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->foreign('agency_id')
                ->references('id')
                ->on('agency')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('project_agency', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->foreign('agency_id')
                ->references('id')
                ->on('agency')
                ->onDelete('cascade');
        });
    }
};
