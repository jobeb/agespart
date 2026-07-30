<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\SesionesUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        // Respuesta siempre genérica: no revelamos si el email existe o no.
        return response()->json([
            'message' => 'Si el email existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña.',
        ]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                // Sin "sesión actual" en este flujo (llega por email): se cierran todas.
                SesionesUsuario::invalidarOtras($user, null);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'El enlace de restablecimiento no es válido o ha caducado.',
            ], 422);
        }

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
