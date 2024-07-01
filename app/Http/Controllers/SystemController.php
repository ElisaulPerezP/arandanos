<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\InicioDeAplicacion;
use App\Events\StopSystem;

class SystemController extends Controller
{
    public function start()
    {
        // Disparar el evento InicioDeAplicacion
        event(new InicioDeAplicacion());

        // Redirigir al dashboard con un mensaje de éxito
        return redirect()->route('dashboard')->with('success', 'El sistema ha iniciado y los scripts de base están ejecutándose.');
    }
    public function stop()
    {
        // Disparar el evento StopSystem
        event(new StopSystem());

        // Redirigir al dashboard con un mensaje de éxito
        return redirect()->route('dashboard')->with('success', 'El sistema se ha tetenido con exito y los scripts de base han finalizado sin problemas.');
    }
}
