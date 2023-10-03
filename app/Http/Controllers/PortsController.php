<?php

namespace App\Http\Controllers;

use App\Http\Actions\Control;
use Illuminate\View\View;


class PortsController extends Controller
{
    public function stop(): View
    {
        $control = new Control();
        $buttons = $control->stopAction();
        return view('dashboard', $buttons);
    }
    public function start(): View
    {
        $control = new Control();
        $buttons = $control->startAction();
        return view('dashboard', $buttons);
    }
    public function onLed(): View
    {
        $control = new Control();
        $buttons = $control->onLedAction();
        return view('dashboard', $buttons);

    }

    public function offLed(): View
    {
        $control = new Control();
        $buttons = $control->offLedAction();
        return view('dashboard', $buttons);
    }

    public function on_bomba_1(): View
    {
        $control = new Control();
        $buttons = $control->on_bomba_1Action();
        return view('dashboard', $buttons);
    }

    public function off_bomba_1(): View
    {
        $control = new Control();
        $buttons = $control->off_bomba_1Action();
        return view('dashboard', $buttons);
    }

    public function on_bomba_2(): View
    {
        $control = new Control();
        $buttons = $control->on_bomba_2Action();
        return view('dashboard', $buttons);
    }


    public function off_bomba_2(): View
    {
        $control = new Control();
        $buttons = $control->off_bomba_2Action();
        return view('dashboard', $buttons);
    }



    public function on_1_2(): View
    {
        $control = new Control();
        $buttons = $control->on_1_2Action();
        return view('dashboard', $buttons);
    }

    public function on_3_4(): View
    {
        $control = new Control();
        $buttons = $control->on_3_4Action();
        return view('dashboard', $buttons);
    }

    public function on_5_6(): View
    {
        $control = new Control();
        $buttons = $control->on_5_6Action();
        return view('dashboard', $buttons);
    }

    public function on_7_8(): View
    {
        $control = new Control();
        $buttons = $control->on_7_8Action();
        return view('dashboard', $buttons);
    }

    public function on_9_10(): View
    {
        $control = new Control();
        $buttons = $control->on_9_10Action();
        return view('dashboard', $buttons);
    }

    public function off_1_2(): View
    {
        $control = new Control();
        $buttons = $control->off_1_2Action();
        return view('dashboard', $buttons);
    }

    public function off_3_4(): View
    {
        $control = new Control();
        $buttons = $control->off_3_4Action();
        return view('dashboard', $buttons);
    }

    public function off_5_6(): View
    {
        $control = new Control();
        $buttons = $control->off_5_6Action();
        return view('dashboard', $buttons);
    }

    public function off_7_8(): View
    {
        $control = new Control();
        $buttons = $control->off_7_8Action();
        return view('dashboard', $buttons);
    }

    public function off_9_10(): View
    {
        $control = new Control();
        $buttons = $control->off_9_10Action();
        return view('dashboard', $buttons);
    }
}

