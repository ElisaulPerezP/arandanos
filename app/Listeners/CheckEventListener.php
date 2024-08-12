<?php

namespace App\Listeners;

use App\Events\RestartEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RestartEventListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(RestartEvent $event)
    {
        // Implementa la lógica para el evento Restart
        // Aquí puedes agregar la lógica que necesitas para manejar el evento Restart
    }
}
