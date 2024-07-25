<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\StopSystem;
use App\Events\InicioDeAplicacion;

class RestartEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
         // Instanciar y llamar al listener StopSystemListener
         $stopListener = new StopSystemListener();
         $stopListener->handle(new StopSystem());
 
         // Instanciar y llamar al listener IniciarAplicacionListener
         $startListener = new IniciarAplicacionListener();
         $startListener->handle(new InicioDeAplicacion());
    }
}
