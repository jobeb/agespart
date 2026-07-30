<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncidenciaEventoResource;
use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IncidenciaEventoController extends Controller
{
    public function index(Incidencia $incidencia)
    {
        Gate::authorize('view', $incidencia);

        return IncidenciaEventoResource::collection($incidencia->eventos()->with('actor')->get());
    }

    public function store(Request $request, Incidencia $incidencia)
    {
        Gate::authorize('view', $incidencia);

        $data = $request->validate([
            'uuid_cliente' => ['required', 'uuid'],
            'comentario' => ['required', 'string', 'max:2000'],
        ]);

        // Un comentario nunca se edita tras crearse: firstOrCreate (no updateOrCreate)
        // es la primitiva correcta para el dedupe de reintentos offline.
        $evento = $incidencia->eventos()->firstOrCreate(
            ['uuid_cliente' => $data['uuid_cliente']],
            [
                'tipo' => 'comentario',
                'comentario' => $data['comentario'],
                'actor_id' => $request->user()->id,
                'actor_nombre' => $request->user()->name,
            ]
        );

        return new IncidenciaEventoResource($evento->load('actor'));
    }
}
