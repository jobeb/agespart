<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SesionesUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalidar_otras_borra_solo_las_sesiones_de_ese_usuario_salvo_la_actual(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();

        DB::table('sessions')->insert([
            ['id' => 'sesion-actual', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time()],
            ['id' => 'sesion-otro-dispositivo', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time()],
            ['id' => 'sesion-de-otro-usuario', 'user_id' => $otro->id, 'payload' => 'x', 'last_activity' => time()],
        ]);

        SesionesUsuario::invalidarOtras($user, 'sesion-actual');

        $this->assertDatabaseHas('sessions', ['id' => 'sesion-actual']);
        $this->assertDatabaseMissing('sessions', ['id' => 'sesion-otro-dispositivo']);
        $this->assertDatabaseHas('sessions', ['id' => 'sesion-de-otro-usuario']);
    }

    public function test_invalidar_otras_sin_sesion_actual_borra_todas(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            ['id' => 'sesion-1', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time()],
            ['id' => 'sesion-2', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time()],
        ]);

        SesionesUsuario::invalidarOtras($user, null);

        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
    }

    public function test_cambiar_password_invoca_la_invalidacion_sin_romper_la_peticion(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $response = $this->actingAs($user)
            ->withHeader('Referer', 'http://localhost:5173')
            ->patchJson('/api/me/password', [
                'current_password' => 'password123',
                'password' => 'nuevaPassword456',
                'password_confirmation' => 'nuevaPassword456',
            ]);

        $response->assertNoContent();
    }

    public function test_resetear_password_por_email_borra_todas_las_sesiones(): void
    {
        $user = User::factory()->create(['email' => 'admin@test.com']);

        DB::table('sessions')->insert([
            ['id' => 'sesion-1', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time()],
            ['id' => 'sesion-2', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time()],
        ]);

        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'admin@test.com',
            'password' => 'nuevaPassword456',
            'password_confirmation' => 'nuevaPassword456',
        ])->assertOk();

        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
    }
}
