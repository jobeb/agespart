<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal')->after('estado');
            $table->index('prioridad');
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropColumn('prioridad');
        });
    }
};
