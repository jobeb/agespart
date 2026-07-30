<?php

namespace Tests\Feature;

use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class IncidenciaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_empleado_no_ve_incidencias_de_otro_empleado(): void
    {
        $empleadoA = User::factory()->create(['rol' => 'empleado']);
        $empleadoB = User::factory()->create(['rol' => 'empleado']);
        $admin = User::factory()->create(['rol' => 'admin']);

        Incidencia::factory()->create(['empleado_id' => $empleadoA->id, 'creado_por' => $admin->id]);
        Incidencia::factory()->create(['empleado_id' => $empleadoB->id, 'creado_por' => $admin->id]);

        $response = $this->actingAs($empleadoA)->getJson('/api/incidencias');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($empleadoA->id, $response->json('data.0.empleado.id'));
    }

    public function test_un_admin_ve_y_filtra_todas_las_incidencias(): void
    {
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $admin = User::factory()->create(['rol' => 'admin']);

        Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id, 'estado' => 'pendiente']);
        Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id, 'estado' => 'resuelta']);

        $response = $this->actingAs($admin)->getJson('/api/incidencias?estado=resuelta');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('resuelta', $response->json('data.0.estado'));
    }

    public function test_crear_la_misma_incidencia_dos_veces_con_el_mismo_uuid_no_duplica(): void
    {
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $uuid = (string) Str::uuid();

        $payload = [
            'uuid_cliente' => $uuid,
            'tipo' => 'reparacion',
            'descripcion' => 'Fuga de agua',
            'lat' => 40.4168,
            'lng' => -3.7038,
        ];

        $this->actingAs($empleado)->postJson('/api/incidencias', $payload)->assertCreated();
        // Reintento (p. ej. tras perder la respuesta offline): mismo uuid_cliente, ya existe -> 200, no 201.
        $this->actingAs($empleado)->postJson('/api/incidencias', $payload)->assertOk();

        $this->assertSame(1, Incidencia::where('uuid_cliente', $uuid)->count());
    }

    public function test_un_empleado_puede_cambiar_el_estado_de_su_propia_incidencia(): void
    {
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $admin = User::factory()->create(['rol' => 'admin']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id]);

        $response = $this->actingAs($empleado)->patchJson("/api/incidencias/{$incidencia->id}", [
            'estado' => 'en_curso',
        ]);

        $response->assertOk()->assertJsonPath('data.estado', 'en_curso');
    }

    public function test_un_empleado_no_puede_editar_incidencias_ajenas(): void
    {
        $empleadoA = User::factory()->create(['rol' => 'empleado']);
        $empleadoB = User::factory()->create(['rol' => 'empleado']);
        $admin = User::factory()->create(['rol' => 'admin']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleadoB->id, 'creado_por' => $admin->id]);

        $response = $this->actingAs($empleadoA)->patchJson("/api/incidencias/{$incidencia->id}", [
            'estado' => 'en_curso',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_no_puede_editar_con_una_version_desactualizada(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id]);

        $response = $this->actingAs($admin)->patchJson("/api/incidencias/{$incidencia->id}", [
            'descripcion' => 'Cambio con version vieja',
            'version' => $incidencia->version + 5,
        ]);

        $response->assertStatus(409);
        $this->assertSame($incidencia->descripcion, $incidencia->fresh()->descripcion);
    }

    public function test_admin_puede_editar_con_la_version_correcta_y_esta_avanza(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id]);

        $response = $this->actingAs($admin)->patchJson("/api/incidencias/{$incidencia->id}", [
            'descripcion' => 'Cambio correcto',
            'version' => $incidencia->version,
        ]);

        $response->assertOk()->assertJsonPath('data.version', $incidencia->version + 1);
    }

    public function test_un_empleado_no_esta_sujeto_al_control_de_version(): void
    {
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $admin = User::factory()->create(['rol' => 'admin']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id]);

        $response = $this->actingAs($empleado)->patchJson("/api/incidencias/{$incidencia->id}", [
            'estado' => 'en_curso',
        ]);

        $response->assertOk();
    }

    public function test_subida_de_foto_asocia_correctamente_el_path_a_la_incidencia(): void
    {
        Storage::fake('public');

        $empleado = User::factory()->create(['rol' => 'empleado']);
        $admin = User::factory()->create(['rol' => 'admin']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id]);

        $response = $this->actingAs($empleado)->postJson("/api/incidencias/{$incidencia->id}/fotos", [
            'uuid_cliente' => (string) Str::uuid(),
            'file' => UploadedFile::fake()->image('reparacion.jpg'),
        ]);

        $response->assertCreated();
        $this->assertSame(1, $incidencia->fotos()->count());
        Storage::disk('public')->assertExists($incidencia->fotos()->first()->path);
    }

    public function test_borrar_una_incidencia_es_un_soft_delete_no_un_borrado_definitivo(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => $empleado->id, 'creado_por' => $admin->id]);

        $this->actingAs($admin)->deleteJson("/api/incidencias/{$incidencia->id}")->assertNoContent();

        $this->assertSoftDeleted($incidencia);
        $this->assertNotNull(Incidencia::withTrashed()->find($incidencia->id));
        $this->assertNull(Incidencia::find($incidencia->id));
    }
}
