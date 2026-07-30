<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->longText('firma_base64')->nullable();
            $table->string('firma_nombre_receptor')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropColumn(['firma_base64', 'firma_nombre_receptor']);
        });
    }
};
