<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\SesionesUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmpleadoController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::where('rol', 'empleado')->orderBy('name')->get());
    }

    public function store(StoreEmpleadoRequest $request)
    {
        $data = $request->validated();

        $empleado = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'rol' => 'empleado',
            'activo' => true,
        ]);

        return new UserResource($empleado);
    }

    public function update(Request $request, User $empleado)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $empleado->update($data);

        return new UserResource($empleado);
    }

    /**
     * Baja definitiva "RGPD-lite": sustituye los datos personales por
     * placeholders, revoca accesos y hace soft-delete. El histórico de
     * incidencias/bitácora no se toca (integridad referencial + snapshot
     * de nombre ya guardado en incidencia_eventos.actor_nombre).
     */
    public function anonimizar(User $empleado)
    {
        $empleado->update([
            'name' => 'Empleado eliminado #'.$empleado->id,
            'email' => 'empleado-eliminado-'.$empleado->id.'@anonimizado.local',
            'password' => Hash::make(Str::random(40)),
            'activo' => false,
        ]);

        $empleado->tokens()->delete();
        SesionesUsuario::invalidarOtras($empleado, null);
        $empleado->pushSubscriptions()->delete();
        $empleado->delete();

        return response()->noContent();
    }
}
