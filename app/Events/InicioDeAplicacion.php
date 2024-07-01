<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class InicioDeAplicacion
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $scriptsDeBase;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        // Cargar los scripts de base desde el archivo de configuración
        $scriptsReport = include(base_path('pythonScripts/scriptsReport.php'));
        $this->scriptsDeBase = explode(', ', $scriptsReport['scritpsDeBase']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
