<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incidencia extends Model
{
    /** @use HasFactory<\Database\Factories\IncidenciaFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid_cliente',
        'tipo',
        'descripcion',
        'estado',
        'prioridad',
        'lat',
        'lng',
        'direccion',
        'ubicacion_cliente_id',
        'empleado_id',
        'creado_por',
        'fecha_resolucion',
        'version',
        'firma_base64',
        'firma_nombre_receptor',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'fecha_resolucion' => 'datetime',
            'version' => 'integer',
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

    public function ubicacionCliente(): BelongsTo
    {
        return $this->belongsTo(UbicacionCliente::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(IncidenciaFoto::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(IncidenciaEvento::class)->latest();
    }
}
