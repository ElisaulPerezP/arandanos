<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RestartEvent
{
    use Dispatchable, SerializesModels;


    /**
     * Create a new event instance.
     */
    public function __construct()
    {
    }
}

