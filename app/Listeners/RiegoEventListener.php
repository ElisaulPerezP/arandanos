<?php

namespace App\Listeners;

use App\Events\RiegoEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RiegoEventListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(RiegoEvent $event)
    {
        // Implementa la lógica para el evento Riego
        Log::info('Riego event handled', ['descripcion' => $event->descripcion]);

        // Aquí puedes agregar la lógica que necesitas para manejar el evento Riego
    }
}
