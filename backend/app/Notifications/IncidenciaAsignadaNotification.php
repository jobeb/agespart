<?php

namespace App\Notifications;

use App\Models\Incidencia;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class IncidenciaAsignadaNotification extends Notification
{
    // Sin ShouldQueue: es un único envío push, no merece la pena depender de tener
    // un worker de cola corriendo siempre para esto.
    public function __construct(protected Incidencia $incidencia)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, self $notification): WebPushMessage
    {
        $tipo = $this->incidencia->tipo === 'reparacion' ? 'Reparación' : 'Instalación';

        return (new WebPushMessage)
            ->title('Nueva incidencia asignada')
            ->icon('/icons/icon-192.png')
            ->body($tipo.($this->incidencia->direccion ? ' — '.$this->incidencia->direccion : ''))
            ->data(['url' => config('app.frontend_url').'/empleado/incidencias/'.$this->incidencia->uuid_cliente]);
    }
}
