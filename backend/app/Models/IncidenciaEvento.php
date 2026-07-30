<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaEvento extends Model
{
    protected $fillable = [
        'incidencia_id',
        'tipo',
        'actor_id',
        'actor_nombre',
        'datos_previos',
        'datos_nuevos',
        'comentario',
        'uuid_cliente',
    ];

    protected function casts(): array
    {
        return [
            'datos_previos' => 'array',
            'datos_nuevos' => 'array',
        ];
    }

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
