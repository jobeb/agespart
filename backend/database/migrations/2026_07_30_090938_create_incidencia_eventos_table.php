<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencia_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incidencia_id')->constrained('incidencias')->cascadeOnDelete();
            $table->string('tipo'); // creacion, cambio_estado, cambio_asignacion, cambio_prioridad, comentario
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_nombre')->nullable(); // snapshot: sobrevive a la anonimización del empleado
            $table->json('datos_previos')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->text('comentario')->nullable();
            $table->uuid('uuid_cliente')->nullable()->unique(); // dedupe de comentarios creados offline
            $table->timestamps();

            $table->index('incidencia_id');
            $table->index('tipo');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencia_eventos');
    }
};
