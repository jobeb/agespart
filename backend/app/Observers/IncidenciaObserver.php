<?php

namespace App\Observers;

use App\Models\Incidencia;
use App\Notifications\IncidenciaAsignadaNotification;

class IncidenciaObserver
{
    /**
     * Avisa al empleado por push cuando se le asigna (o reasigna) una incidencia,
     * tanto al crearla directamente con empleado_id como al usar /asignar.
     *
     * created() y updated() por separado porque wasChanged() no es fiable en el
     * evento "saved" tras un insert (Eloquent solo sincroniza $changes en updates).
     */
    public function created(Incidencia $incidencia): void
    {
        if ($incidencia->empleado_id) {
            $incidencia->empleado?->notify(new IncidenciaAsignadaNotification($incidencia));
        }
    }

    public function updated(Incidencia $incidencia): void
    {
        if ($incidencia->wasChanged('empleado_id') && $incidencia->empleado_id) {
            $incidencia->empleado?->notify(new IncidenciaAsignadaNotification($incidencia));
        }
    }
}
