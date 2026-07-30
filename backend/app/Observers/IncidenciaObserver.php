<?php

namespace App\Observers;

use App\Models\Incidencia;
use App\Models\User;
use App\Notifications\IncidenciaAsignadaNotification;

class IncidenciaObserver
{
    /**
     * Avisa al empleado por push cuando se le asigna (o reasigna) una incidencia,
     * y registra la bitácora de la incidencia (creación / cambios relevantes).
     *
     * created() y updated() por separado porque wasChanged() no es fiable en el
     * evento "saved" tras un insert (Eloquent solo sincroniza $changes en updates).
     */
    public function created(Incidencia $incidencia): void
    {
        $this->registrarEvento($incidencia, 'creacion');

        if ($incidencia->empleado_id) {
            $incidencia->empleado?->notify(new IncidenciaAsignadaNotification($incidencia));
        }
    }

    public function updated(Incidencia $incidencia): void
    {
        if ($incidencia->wasChanged('estado')) {
            $this->registrarEvento($incidencia, 'cambio_estado', [
                'estado' => $incidencia->getOriginal('estado'),
            ], [
                'estado' => $incidencia->estado,
            ]);
        }

        if ($incidencia->wasChanged('prioridad')) {
            $this->registrarEvento($incidencia, 'cambio_prioridad', [
                'prioridad' => $incidencia->getOriginal('prioridad'),
            ], [
                'prioridad' => $incidencia->prioridad,
            ]);
        }

        if ($incidencia->wasChanged('empleado_id')) {
            $anteriorId = $incidencia->getOriginal('empleado_id');
            $this->registrarEvento($incidencia, 'cambio_asignacion', [
                'empleado' => $anteriorId ? User::find($anteriorId)?->name : null,
            ], [
                'empleado' => $incidencia->empleado?->name,
            ]);

            if ($incidencia->empleado_id) {
                $incidencia->empleado?->notify(new IncidenciaAsignadaNotification($incidencia));
            }
        }
    }

    private function registrarEvento(Incidencia $incidencia, string $tipo, ?array $previos = null, ?array $nuevos = null): void
    {
        $actor = auth()->user();

        $incidencia->eventos()->create([
            'tipo' => $tipo,
            'actor_id' => $actor?->id,
            'actor_nombre' => $actor?->name,
            'datos_previos' => $previos,
            'datos_nuevos' => $nuevos,
        ]);
    }
}
