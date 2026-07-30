<?php

namespace App\Policies;

use App\Models\Incidencia;
use App\Models\User;

class IncidenciaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Incidencia $incidencia): bool
    {
        return $user->esAdmin() || $incidencia->empleado_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Incidencia $incidencia): bool
    {
        return $user->esAdmin() || $incidencia->empleado_id === $user->id;
    }

    public function delete(User $user, Incidencia $incidencia): bool
    {
        return $user->esAdmin();
    }
}
