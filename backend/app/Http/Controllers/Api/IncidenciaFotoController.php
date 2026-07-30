<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncidenciaFotoResource;
use App\Models\Incidencia;
use App\Models\IncidenciaFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class IncidenciaFotoController extends Controller
{
    public function index(Incidencia $incidencia)
    {
        Gate::authorize('view', $incidencia);

        return IncidenciaFotoResource::collection($incidencia->fotos);
    }

    public function store(Request $request, Incidencia $incidencia)
    {
        Gate::authorize('update', $incidencia);

        $data = $request->validate([
            'uuid_cliente' => ['required', 'uuid'],
            'file' => ['required', 'image', 'max:10240'],
        ]);

        $foto = IncidenciaFoto::where('uuid_cliente', $data['uuid_cliente'])->first();

        if ($foto) {
            return new IncidenciaFotoResource($foto);
        }

        $path = $request->file('file')->store("incidencias/{$incidencia->id}", 'public');

        $foto = $incidencia->fotos()->create([
            'uuid_cliente' => $data['uuid_cliente'],
            'path' => $path,
        ]);

        return new IncidenciaFotoResource($foto);
    }

    public function destroy(IncidenciaFoto $foto)
    {
        Gate::authorize('update', $foto->incidencia);

        if ($foto->path) {
            Storage::disk('public')->delete($foto->path);
        }

        $foto->delete();

        return response()->noContent();
    }
}
