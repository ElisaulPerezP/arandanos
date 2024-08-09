<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Comando;
use App\Models\Mensaje;
use App\Models\Estado;
use App\Models\Programacion;
use App\Models\Cultivo;
use Illuminate\Support\Facades\Log;


class SincronizarSistema
{
    use Dispatchable, SerializesModels;

    public $comandos;
    public $mensajes;
    public $programaciones;
    public $estados;
    public $estadoActual;
    public $cultivo;
    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->cultivo = Cultivo::first();
        // Preparar comandos
        $this->comandos = Comando::all()->map(function ($comando) {
            return [
                'nombre' => $comando->nombre,
                'descripcion' => $comando->descripcion,
            ];
        })->toArray();

        // Preparar mensajes
        $this->mensajes = Mensaje::all()->pluck('contenido')->mapWithKeys(function ($item) {
            return ['mensaje' => $item];
        })->toArray();

        $this->programaciones = Programacion::all()->map(function ($programacion) {
            return [
                'comando_id' => $programacion->comando_id,
                'hora_unix' => $programacion->hora_unix,
                'estado' => $programacion->estado,
            ];
        })->toArray();

        // Preparar estados
        $this->estados = Estado::all()->pluck('descripcion', 'nombre')->toArray();

        // Preparar estado actual para regresar a la base
        $this->estadoActual = [
            'estado_id' => $this->cultivo->estadoActual,
        ];
    }
}
