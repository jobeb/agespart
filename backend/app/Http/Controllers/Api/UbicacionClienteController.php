<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUbicacionClienteRequest;
use App\Http\Requests\UpdateUbicacionClienteRequest;
use App\Http\Resources\UbicacionClienteResource;
use App\Models\UbicacionCliente;
use Illuminate\Http\Request;

class UbicacionClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = UbicacionCliente::query()->orderBy('nombre');

        // Cualquier usuario autenticado puede listarlas (para elegirlas al crear
        // una incidencia), pero solo ve las activas salvo que sea admin pidiendo todas.
        if (! $request->user()->esAdmin() || ! $request->boolean('todas')) {
            $query->where('activo', true);
        }

        return UbicacionClienteResource::collection($query->get());
    }

    public function store(StoreUbicacionClienteRequest $request)
    {
        $ubicacion = UbicacionCliente::create($request->validated());

        return new UbicacionClienteResource($ubicacion);
    }

    public function update(UpdateUbicacionClienteRequest $request, UbicacionCliente $ubicacion)
    {
        $ubicacion->update($request->validated());

        return new UbicacionClienteResource($ubicacion);
    }

    public function destroy(UbicacionCliente $ubicacion)
    {
        $ubicacion->update(['activo' => false]);

        return response()->noContent();
    }
}
