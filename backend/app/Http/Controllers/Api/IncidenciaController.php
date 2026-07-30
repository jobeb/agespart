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

        $query = Incidencia::query()->with(['empleado', 'ubicacionCliente', 'fotos'])->latest();

        if ($request->user()->esAdmin()) {
            $query->when($request->filled('empleado_id'), fn ($q) => $q->where('empleado_id', $request->input('empleado_id')));
        } else {
            $query->where('empleado_id', $request->user()->id);
        }

        $query
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->when($request->filled('prioridad'), fn ($q) => $q->where('prioridad', $request->input('prioridad')))
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
                'prioridad' => $data['prioridad'] ?? 'normal',
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'ubicacion_cliente_id' => $data['ubicacion_cliente_id'] ?? null,
                'empleado_id' => $empleadoId,
                'creado_por' => $request->user()->id,
            ]
        );

        // 'version' se apoya en el DEFAULT de la BD (no se manda aquí a propósito:
        // si se incluyera en $values, un reintento de updateOrCreate sobre una
        // incidencia YA existente resetearía su version a 1 en cada resync offline).
        // refresh() trae el valor real de la fila para que el resource no devuelva null.
        $incidencia->refresh();

        return new IncidenciaResource($incidencia->load(['empleado', 'ubicacionCliente', 'fotos']));
    }

    public function show(Incidencia $incidencia)
    {
        Gate::authorize('view', $incidencia);

        return new IncidenciaResource($incidencia->load(['empleado', 'ubicacionCliente', 'fotos']));
    }

    public function update(UpdateIncidenciaRequest $request, Incidencia $incidencia)
    {
        $data = $request->validated();

        // Concurrencia optimista: solo se exige en el camino online del admin.
        // El empleado edita vía la cola offline (sync.js) y ese flujo sigue
        // resolviendo por last-write-wins + dedupe de uuid, sin version.
        if ($request->user()->esAdmin() && array_key_exists('version', $data)) {
            if ((int) $data['version'] !== $incidencia->version) {
                return response()->json([
                    'message' => 'La incidencia ha sido modificada por otra persona. Recarga los cambios.',
                    'data' => new IncidenciaResource($incidencia->fresh(['empleado', 'ubicacionCliente', 'fotos'])),
                ], 409);
            }
        }
        unset($data['version']);

        if (($data['estado'] ?? null) === 'resuelta' && $incidencia->estado !== 'resuelta') {
            $data['fecha_resolucion'] = now();
        }

        $data['version'] = $incidencia->version + 1;

        $incidencia->update($data);

        return new IncidenciaResource($incidencia->load(['empleado', 'ubicacionCliente', 'fotos']));
    }

    public function asignar(Request $request, Incidencia $incidencia)
    {
        $data = $request->validate([
            'empleado_id' => ['required', 'exists:users,id'],
        ]);

        $incidencia->update(['empleado_id' => $data['empleado_id']]);

        return new IncidenciaResource($incidencia->load(['empleado', 'ubicacionCliente', 'fotos']));
    }

    public function destroy(Incidencia $incidencia)
    {
        Gate::authorize('delete', $incidencia);

        $incidencia->delete();

        return response()->noContent();
    }
}
