<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidenciaResource extends JsonResource
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
            'uuid_cliente' => $this->uuid_cliente,
            'tipo' => $this->tipo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'lat' => $this->lat !== null ? (float) $this->lat : null,
            'lng' => $this->lng !== null ? (float) $this->lng : null,
            'direccion' => $this->direccion,
            'empleado' => new UserResource($this->whenLoaded('empleado')),
            'creado_por' => $this->creado_por,
            'fecha_resolucion' => $this->fecha_resolucion,
            'fotos' => IncidenciaFotoResource::collection($this->whenLoaded('fotos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
