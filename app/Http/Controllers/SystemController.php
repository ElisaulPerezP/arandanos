<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\InicioDeAplicacion;
use App\Events\StopSystem;
use App\Models\Cultivo;
use App\Events\SincronizarSistema;


class SystemController extends Controller
{
    public function start()
    {
        // Disparar el evento InicioDeAplicacion
        event(new InicioDeAplicacion());
        event(new SincronizarSistema());
        // Redirigir al dashboard con un mensaje de éxito
        return redirect()->route('dashboard')->with('success', 'El sistema ha iniciado y los scripts de base están ejecutándose.');
    }
    public function stop()
    {
        // Disparar el evento StopSystem
        event(new StopSystem());
        event(new SincronizarSistema());
        // Redirigir al dashboard con un mensaje de éxito
        return redirect()->route('dashboard')->with('success', 'El sistema se ha tetenido con exito y los scripts de base han finalizado sin problemas.');
    }

    public function sync()
    {
        event(new SincronizarSistema());
        return redirect()->route('dashboard')->with('success', 'El sistema se ha encolado para sincronizacion, en breve las bases de datos se igualaran a las de la nuve');
    }

}
