<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidenciaRequest;
use App\Http\Requests\UpdateIncidenciaRequest;
use App\Http\Resources\IncidenciaResource;
use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IncidenciaController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Incidencia::class);

        $query = Incidencia::query()->with(['empleado', 'fotos'])->latest();

        if ($request->user()->esAdmin()) {
            $query->when($request->filled('empleado_id'), fn ($q) => $q->where('empleado_id', $request->input('empleado_id')));
        } else {
            $query->where('empleado_id', $request->user()->id);
        }

        $query
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->input('tipo')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('hasta')));

        $perPage = min((int) $request->input('per_page', 50), 500);

        return IncidenciaResource::collection($query->paginate($perPage));
    }

    public function store(StoreIncidenciaRequest $request)
    {
        $data = $request->validated();

        $empleadoId = $data['empleado_id'] ?? ($request->user()->esAdmin() ? null : $request->user()->id);

        $incidencia = Incidencia::updateOrCreate(
            ['uuid_cliente' => $data['uuid_cliente']],
            [
                'tipo' => $data['tipo'],
                'descripcion' => $data['descripcion'] ?? null,
                'estado' => $data['estado'] ?? 'pendiente',
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'empleado_id' => $empleadoId,
                'creado_por' => $request->user()->id,
            ]
        );

        return new IncidenciaResource($incidencia->load(['empleado', 'fotos']));
    }

    public function show(Incidencia $incidencia)
    {
        Gate::authorize('view', $incidencia);

        return new IncidenciaResource($incidencia->load(['empleado', 'fotos']));
    }

    public function update(UpdateIncidenciaRequest $request, Incidencia $incidencia)
    {
        $data = $request->validated();

        if (($data['estado'] ?? null) === 'resuelta' && $incidencia->estado !== 'resuelta') {
            $data['fecha_resolucion'] = now();
        }

        $incidencia->update($data);

        return new IncidenciaResource($incidencia->load(['empleado', 'fotos']));
    }

    public function asignar(Request $request, Incidencia $incidencia)
    {
        $data = $request->validate([
            'empleado_id' => ['required', 'exists:users,id'],
        ]);

        $incidencia->update(['empleado_id' => $data['empleado_id']]);

        return new IncidenciaResource($incidencia->load(['empleado', 'fotos']));
    }

    public function destroy(Incidencia $incidencia)
    {
        Gate::authorize('delete', $incidencia);

        $incidencia->delete();

        return response()->noContent();
    }
}
