<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_cliente')->unique();
            $table->enum('tipo', ['reparacion', 'instalacion']);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['pendiente', 'en_curso', 'resuelta'])->default('pendiente');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('direccion')->nullable();
            $table->foreignId('empleado_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('empleado_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
