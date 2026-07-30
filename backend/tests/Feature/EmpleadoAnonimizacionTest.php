<?php

namespace Tests\Feature;

use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmpleadoAnonimizacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonimizar_borra_pii_revoca_accesos_y_hace_soft_delete(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $empleado = User::factory()->create(['rol' => 'empleado', 'name' => 'Juan Pérez', 'email' => 'juan@test.com']);

        $empleado->createToken('dispositivo-movil');
        DB::table('sessions')->insert(['id' => 'sesion-empleado', 'user_id' => $empleado->id, 'payload' => 'x', 'last_activity' => time()]);

        // Creada por el propio empleado, para que el observer registre su nombre real
        // como snapshot en el evento de creación (igual que en el flujo real de la app).
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id]);
        $this->actingAs($empleado)->postJson("/api/incidencias/{$incidencia->id}/eventos", [
            'uuid_cliente' => (string) \Illuminate\Support\Str::uuid(),
            'comentario' => 'Comentario de prueba antes de anonimizar.',
        ])->assertCreated();

        $this->actingAs($admin)->patchJson("/api/empleados/{$empleado->id}/anonimizar")->assertNoContent();

        $this->assertNull(User::find($empleado->id));
        $anonimizado = User::withTrashed()->find($empleado->id);
        $this->assertNotSame('Juan Pérez', $anonimizado->name);
        $this->assertStringContainsString('anonimizado.local', $anonimizado->email);
        $this->assertFalse((bool) $anonimizado->activo);

        $this->assertSame(0, $empleado->tokens()->count());
        $this->assertSame(0, DB::table('sessions')->where('user_id', $empleado->id)->count());

        // El histórico conserva el nombre real vía el snapshot, aunque el usuario ya esté anonimizado.
        $comentario = $incidencia->eventos()->where('tipo', 'comentario')->first();
        $this->assertSame('Juan Pérez', $comentario->actor_nombre);
    }

    public function test_un_empleado_no_puede_anonimizar(): void
    {
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $otro = User::factory()->create(['rol' => 'empleado']);

        $this->actingAs($empleado)->patchJson("/api/empleados/{$otro->id}/anonimizar")->assertStatus(403);
    }
}
