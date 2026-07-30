<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FrontendErrorResource extends JsonResource
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
            'mensaje' => $this->mensaje,
            'stack' => $this->stack,
            'url' => $this->url,
            'user_agent' => $this->user_agent,
            'contexto' => $this->contexto,
            'usuario' => $this->user?->name,
            'created_at' => $this->created_at,
        ];
    }
}
