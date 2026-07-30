<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\IncidenciaController;
use App\Http\Controllers\Api\IncidenciaFotoController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:password-email');
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me/password', [ProfileController::class, 'updatePassword']);
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store']);
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy']);

    Route::get('/incidencias', [IncidenciaController::class, 'index']);
    Route::post('/incidencias', [IncidenciaController::class, 'store']);
    Route::get('/incidencias/{incidencia}', [IncidenciaController::class, 'show']);
    Route::patch('/incidencias/{incidencia}', [IncidenciaController::class, 'update']);
    Route::delete('/incidencias/{incidencia}', [IncidenciaController::class, 'destroy']);

    Route::get('/incidencias/{incidencia}/fotos', [IncidenciaFotoController::class, 'index']);
    Route::post('/incidencias/{incidencia}/fotos', [IncidenciaFotoController::class, 'store']);
    Route::delete('/fotos/{foto}', [IncidenciaFotoController::class, 'destroy']);

    Route::middleware('role:admin')->group(function () {
        Route::patch('/incidencias/{incidencia}/asignar', [IncidenciaController::class, 'asignar']);
        Route::get('/empleados', [EmpleadoController::class, 'index']);
        Route::post('/empleados', [EmpleadoController::class, 'store']);
        Route::patch('/empleados/{empleado}', [EmpleadoController::class, 'update']);
    });
});
