<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiegoEvent
{
    use Dispatchable, SerializesModels;

    public $descripcion;

    /**
     * Create a new event instance.
     */
    public function __construct($descripcion)
    {
        $this->descripcion = $descripcion;
    }
}
