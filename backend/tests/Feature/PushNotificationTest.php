<?php

namespace Tests\Feature;

use App\Models\Incidencia;
use App\Models\User;
use App\Notifications\IncidenciaAsignadaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_asignar_una_incidencia_notifica_al_empleado(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['rol' => 'admin']);
        $empleado = User::factory()->create(['rol' => 'empleado']);
        $incidencia = Incidencia::factory()->create(['empleado_id' => null, 'creado_por' => $admin->id]);

        $this->actingAs($admin)->patchJson("/api/incidencias/{$incidencia->id}/asignar", [
            'empleado_id' => $empleado->id,
        ])->assertOk();

        Notification::assertSentTo($empleado, IncidenciaAsignadaNotification::class);
    }

    public function test_crear_incidencia_con_empleado_asignado_directamente_tambien_notifica(): void
    {
        Notification::fake();

        $empleado = User::factory()->create(['rol' => 'empleado']);
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->postJson('/api/incidencias', [
            'uuid_cliente' => (string) \Illuminate\Support\Str::uuid(),
            'tipo' => 'reparacion',
            'descripcion' => 'Prueba',
            'empleado_id' => $empleado->id,
        ])->assertCreated();

        Notification::assertSentTo($empleado, IncidenciaAsignadaNotification::class);
    }

    public function test_registrar_y_eliminar_una_suscripcion_push(): void
    {
        $empleado = User::factory()->create(['rol' => 'empleado']);

        $this->actingAs($empleado)->postJson('/api/push-subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'keys' => ['p256dh' => 'clave-publica', 'auth' => 'token-auth'],
        ])->assertNoContent();

        $this->assertDatabaseHas('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);

        $this->actingAs($empleado)->deleteJson('/api/push-subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ])->assertNoContent();

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);
    }
}
