<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_solicitar_enlace_devuelve_mensaje_generico_exista_o_no_el_email(): void
    {
        $respuestaExistente = $this->postJson('/api/forgot-password', ['email' => 'nadie@test.com']);
        $respuestaExistente->assertOk();

        User::factory()->create(['email' => 'admin@test.com']);
        $respuestaOtro = $this->postJson('/api/forgot-password', ['email' => 'admin@test.com']);
        $respuestaOtro->assertOk();

        $this->assertSame($respuestaExistente->json('message'), $respuestaOtro->json('message'));
    }

    public function test_reset_con_token_valido_cambia_la_contrasena(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'admin@test.com', 'password' => 'password123']);

        $this->postJson('/api/forgot-password', ['email' => 'admin@test.com'])->assertOk();

        $token = null;
        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use (&$token) {
            $url = (fn () => $this->url)->call($notification);
            parse_str(parse_url($url, PHP_URL_QUERY), $params);
            $token = $params['token'];

            return true;
        });

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'admin@test.com',
            'password' => 'nuevaPassword123',
            'password_confirmation' => 'nuevaPassword123',
        ]);

        $response->assertOk();

        $this->withHeader('Referer', 'http://localhost:5173')->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'nuevaPassword123',
        ])->assertOk();
    }

    public function test_reset_con_token_invalido_falla(): void
    {
        User::factory()->create(['email' => 'admin@test.com']);

        $response = $this->postJson('/api/reset-password', [
            'token' => 'token-invalido',
            'email' => 'admin@test.com',
            'password' => 'nuevaPassword123',
            'password_confirmation' => 'nuevaPassword123',
        ]);

        $response->assertStatus(422);
    }
}
