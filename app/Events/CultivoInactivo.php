<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Cultivo;

class CultivoInactivo
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cultivo;

    /**
     * Create a new event instance.
     */
    public function __construct(Cultivo $cultivo)
    {
        $this->cultivo = $cultivo;
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
