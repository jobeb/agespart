<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaFoto extends Model
{
    protected $fillable = [
        'incidencia_id',
        'uuid_cliente',
        'path',
    ];

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class);
    }
}
