<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiegoEvent
{
    use Dispatchable, SerializesModels;

    public $programacion;

    /**
     * Create a new event instance.
     */
    public function __construct($programacion)
    {
        $this->programacion = $programacion;
    }
}
