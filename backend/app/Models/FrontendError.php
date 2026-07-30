<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrontendError extends Model
{
    protected $fillable = [
        'user_id',
        'mensaje',
        'stack',
        'url',
        'user_agent',
        'contexto',
    ];

    protected function casts(): array
    {
        return [
            'contexto' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
