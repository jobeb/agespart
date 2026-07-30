<?php

namespace Tests\Feature;

use App\Models\UbicacionCliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UbicacionClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_admin_puede_crear_una_ubicacion(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/ubicaciones-clientes', [
            'nombre' => 'Oficina Central',
            'direccion' => 'Calle Mayor 1',
            'lat' => 40.4168,
            'lng' => -3.7038,
        ]);

        $response->assertCreated()->assertJsonPath('data.nombre', 'Oficina Central');
    }

    public function test_un_empleado_no_puede_crear_una_ubicacion(): void
    {
        $empleado = User::factory()->create(['rol' => 'empleado']);

        $this->actingAs($empleado)->postJson('/api/ubicaciones-clientes', ['nombre' => 'Oficina'])
            ->assertStatus(403);
    }

    public function test_un_empleado_puede_listar_ubicaciones_activas(): void
    {
        $empleado = User::factory()->create(['rol' => 'empleado']);
        UbicacionCliente::factory()->create(['nombre' => 'Activa', 'activo' => true]);
        UbicacionCliente::factory()->create(['nombre' => 'Inactiva', 'activo' => false]);

        $response = $this->actingAs($empleado)->getJson('/api/ubicaciones-clientes');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Activa', $response->json('data.0.nombre'));
    }

    public function test_desactivar_una_ubicacion_no_la_borra(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $ubicacion = UbicacionCliente::factory()->create(['activo' => true]);

        $this->actingAs($admin)->deleteJson("/api/ubicaciones-clientes/{$ubicacion->id}")->assertNoContent();

        $this->assertDatabaseHas('ubicaciones_clientes', ['id' => $ubicacion->id, 'activo' => false]);
    }
}
