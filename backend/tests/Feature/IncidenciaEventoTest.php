<?php

namespace Tests\Feature;

use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IncidenciaEventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_una_incidencia_registra_un_evento_de_creacion(): void
    {
        $empleado = User::factory()->create(['rol' => 'empleado']);

        $this->actingAs($empleado)->postJson('/api/incidencias', [
            'uuid_cliente' => (string) Str::uuid(),
            'tipo' => 'reparacion',
            'descripcion' => 'Prueba',
        ])->assertCreated();

        $incidencia = Incidencia::first();
        $this->assertSame(1, $incidencia->eventos()->where('tipo', 'creacion')->count());
        $this->assertSame($empleado->name, $incidencia->eventos()->first()->actor_nombre);
    }

    public function test_cambiar_el_estado_registra_un_evento_con_los_valores_previos_y_nuevos(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id, 'estado' => 'pendiente']);

        $this->actingAs($empleado)->patchJson("/api/incidencias/{$incidencia->id}", ['estado' => 'en_curso'])->assertOk();

        $evento = $incidencia->eventos()->where('tipo', 'cambio_estado')->first();
        $this->assertNotNull($evento);
        $this->assertSame('pendiente', $evento->datos_previos['estado']);
        $this->assertSame('en_curso', $evento->datos_nuevos['estado']);
    }

    public function test_reasignar_registra_evento_de_cambio_de_asignacion_con_nombres(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $empleadoA = User::factory()->create(['rol' => 'empleado', 'name' => 'Empleado A']);
        $empleadoB = User::factory()->create(['rol' => 'empleado', 'name' => 'Empleado B']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleadoA->id, 'creado_por' => $admin->id]);

        $this->actingAs($admin)->patchJson("/api/incidencias/{$incidencia->id}/asignar", [
            'empleado_id' => $empleadoB->id,
        ])->assertOk();

        $evento = $incidencia->eventos()->where('tipo', 'cambio_asignacion')->first();
        $this->assertSame('Empleado A', $evento->datos_previos['empleado']);
        $this->assertSame('Empleado B', $evento->datos_nuevos['empleado']);
    }

    public function test_un_comentario_duplicado_por_uuid_no_se_duplica(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id]);

        $uuid = (string) Str::uuid();
        $payload = ['uuid_cliente' => $uuid, 'comentario' => 'Cliente ausente, se reintentará mañana.'];

        $this->actingAs($empleado)->postJson("/api/incidencias/{$incidencia->id}/eventos", $payload)->assertCreated();
        $this->actingAs($empleado)->postJson("/api/incidencias/{$incidencia->id}/eventos", $payload)->assertOk();

        $this->assertSame(1, $incidencia->eventos()->where('uuid_cliente', $uuid)->count());
    }

    public function test_un_empleado_ajeno_no_puede_comentar_una_incidencia(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $empleadoA = User::factory()->create(['rol' => 'empleado']);
        $empleadoB = User::factory()->create(['rol' => 'empleado']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleadoB->id, 'creado_por' => $admin->id]);

        $this->actingAs($empleadoA)->postJson("/api/incidencias/{$incidencia->id}/eventos", [
            'uuid_cliente' => (string) Str::uuid(),
            'comentario' => 'No debería poder comentar.',
        ])->assertStatus(403);
    }
}
