<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incidencia extends Model
{
    /** @use HasFactory<\Database\Factories\IncidenciaFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid_cliente',
        'tipo',
        'descripcion',
        'estado',
        'lat',
        'lng',
        'direccion',
        'empleado_id',
        'creado_por',
        'fecha_resolucion',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'fecha_resolucion' => 'datetime',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'empleado_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(IncidenciaFoto::class);
    }
}
