<?php

namespace App\Providers;

use App\Models\Incidencia;
use App\Observers\IncidenciaObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Incidencia::observe(IncidenciaObserver::class);

        RateLimiter::for('login', function (Request $request) {
            $clave = Str::lower($request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($clave)->response(function () {
                return response()->json([
                    'message' => 'Demasiados intentos. Inténtalo de nuevo en un minuto.',
                ], Response::HTTP_TOO_MANY_REQUESTS);
            });
        });

        RateLimiter::for('password-email', function (Request $request) {
            $clave = Str::lower($request->input('email')).'|'.$request->ip();

            return Limit::perMinute(3)->by($clave)->response(function () {
                return response()->json([
                    'message' => 'Demasiados intentos. Inténtalo de nuevo en un minuto.',
                ], Response::HTTP_TOO_MANY_REQUESTS);
            });
        });
    }
}
