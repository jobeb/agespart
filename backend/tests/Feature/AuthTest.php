<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_correcto_devuelve_usuario_con_rol(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'password123',
            'rol' => 'admin',
        ]);

        $response = $this->withHeader('Referer', 'http://localhost:5173')
            ->postJson('/api/login', [
                'email' => 'admin@test.com',
                'password' => 'password123',
            ]);

        $response->assertOk()->assertJsonPath('data.rol', 'admin');
    }

    public function test_login_incorrecto_devuelve_error_de_validacion(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'incorrecta',
        ]);

        $response->assertStatus(422);
    }

    public function test_me_devuelve_el_usuario_autenticado_con_rol(): void
    {
        $user = User::factory()->create(['rol' => 'empleado']);

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertOk()->assertJsonPath('data.rol', 'empleado');
    }
}
