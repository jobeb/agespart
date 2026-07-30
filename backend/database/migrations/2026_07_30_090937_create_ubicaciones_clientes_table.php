<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones_clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('contacto')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_clientes');
    }
};
