<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidenciaEventoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'actor_nombre' => $this->actor_nombre,
            'datos_previos' => $this->datos_previos,
            'datos_nuevos' => $this->datos_nuevos,
            'comentario' => $this->comentario,
            'uuid_cliente' => $this->uuid_cliente,
            'created_at' => $this->created_at,
        ];
    }
}
