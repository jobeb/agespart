<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class IncidenciaFotoResource extends JsonResource
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
            'url' => $this->path ? Storage::disk('public')->url($this->path) : null,
            'created_at' => $this->created_at,
        ];
    }
}
