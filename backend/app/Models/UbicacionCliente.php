<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UbicacionCliente extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones_clientes';

    protected $fillable = [
        'nombre',
        'direccion',
        'lat',
        'lng',
        'contacto',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'activo' => 'boolean',
        ];
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }
}
