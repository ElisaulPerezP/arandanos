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
}
