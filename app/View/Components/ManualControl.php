<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ManualControl extends Component
{
    public $buttons;

    /**
     * Create a new component instance.
     */
    public function __construct($buttons)
    {
        $this->buttons = $buttons;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.manual-control');
    }
}
