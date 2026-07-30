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
            'prioridad' => $this->prioridad,
            'lat' => $this->lat !== null ? (float) $this->lat : null,
            'lng' => $this->lng !== null ? (float) $this->lng : null,
            'direccion' => $this->direccion,
            'ubicacion_cliente_id' => $this->ubicacion_cliente_id,
            'ubicacion_cliente' => new UbicacionClienteResource($this->whenLoaded('ubicacionCliente')),
            'empleado' => new UserResource($this->whenLoaded('empleado')),
            'creado_por' => $this->creado_por,
            'fecha_resolucion' => $this->fecha_resolucion,
            'firma_base64' => $this->firma_base64,
            'firma_nombre_receptor' => $this->firma_nombre_receptor,
            'version' => $this->version,
            'fotos' => IncidenciaFotoResource::collection($this->whenLoaded('fotos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
