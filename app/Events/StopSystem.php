<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StopSystem
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $scriptsEjecutandose;
    public $scriptStopTotal;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        // Cargar los scripts ejecutandose desde el archivo de configuración
        $scriptsReport = include(base_path('pythonScripts/scriptsReport.php'));
        
        // scriptsEjecutandose ya es un array, así que lo asignamos directamente
        $this->scriptsEjecutandose = $scriptsReport['scriptsEjecutandose'];
        $this->scriptStopTotal = $scriptsReport['scriptStopTotal'];
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
