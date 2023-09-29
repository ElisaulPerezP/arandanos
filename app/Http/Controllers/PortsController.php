<?php

namespace App\Http\Controllers;

use Illuminate\View\View;


class PortsController extends Controller
{
    public function onLed(): View
    {
        $scriptPath = '/var/www/onLed.py';
        $command = "sudo python3 $scriptPath";
        $respuesta = shell_exec($command);
        return view('dashboard', [
            'mensaje' => $respuesta ]);
    }

    public function offLed(): View
    {
        $scriptPath = '/var/www/offLed.py';
        $command = "sudo python3 $scriptPath";
        $respuesta = shell_exec($command);
        return view('dashboard', [
            'mensaje' => $respuesta ]);

    }

    public function on_bomba_1(): View
    {
    }

    public function on_bomba_2(): View
    {
    }
    public function on_bomba_1(): View
    {
    }

    public function on_bomba_2(): View
    {
    }

    public function stop(): View
    {
    }

    public function on_1_2(): View
    {
    }

    public function on_3_4(): View
    {
    }


    public function on_5_6(): View
    {
    }

     public function on_7_8): View
    {
    }
public function on_9_10): View
    {
    }
}

