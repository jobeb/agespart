<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Incidencia::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'uuid_cliente' => ['required', 'uuid'],
            'tipo' => ['required', 'in:reparacion,instalacion'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['nullable', 'in:pendiente,en_curso,resuelta'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'empleado_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
