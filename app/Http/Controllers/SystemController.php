<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\InicioDeAplicacion;

class SystemController extends Controller
{
    public function start()
    {
        // Disparar el evento InicioDeAplicacion
        event(new InicioDeAplicacion());

        // Redirigir al dashboard con un mensaje de éxito
        return redirect()->route('dashboard')->with('success', 'El sistema ha iniciado y los scripts de base están ejecutándose.');
    }
}
