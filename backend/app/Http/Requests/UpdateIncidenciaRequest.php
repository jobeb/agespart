<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('incidencia'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['sometimes', 'in:reparacion,instalacion'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'estado' => ['sometimes', 'in:pendiente,en_curso,resuelta'],
            'lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'direccion' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
