<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // API-only: no existe ninguna ruta 'login' web a la que redirigir; el override
        // por defecto de Laravel intenta route('login') y revienta con RouteNotFoundException.
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API-only: todas las respuestas de error deben ser JSON, nunca una redirección/vista HTML.
        $exceptions->shouldRenderJsonWhen(fn () => true);
    })->create();
