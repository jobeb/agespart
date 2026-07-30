<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->foreignId('ubicacion_cliente_id')->nullable()->after('direccion')->constrained('ubicaciones_clientes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ubicacion_cliente_id');
        });
    }
};
