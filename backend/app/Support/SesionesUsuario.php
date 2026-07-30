<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SesionesUsuario
{
    /**
     * Borra las sesiones activas de un usuario en la tabla 'sessions'
     * (SESSION_DRIVER=database), opcionalmente conservando la actual.
     * Se usa al cambiar contraseña (se conserva la sesión desde la que se
     * cambió) y al resetear por email (se cierran todas, no hay "actual").
     */
    public static function invalidarOtras(User $user, ?string $sessionIdActual = null): void
    {
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->when($sessionIdActual, fn ($query) => $query->where('id', '!=', $sessionIdActual))
            ->delete();
    }
}
