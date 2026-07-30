<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

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
}
