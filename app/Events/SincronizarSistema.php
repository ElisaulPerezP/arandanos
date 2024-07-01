<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Comando;
use App\Models\Mensaje;
use App\Models\Estado;
use App\Models\Programacion;
use App\Models\Cultivo;

class SincronizarSistema
{
    use Dispatchable, SerializesModels;

    public $comandos;
    public $mensajes;
    public $programaciones;
    public $estados;
    public $estadoActual;
    public $token;
    public $cultivo;
    /**
     * Create a new event instance.
     */
    public function __construct($token, Cultivo $cultivo)
    {
        $this->token = $token;
        $this->cultivo = $cultivo;
        // Preparar comandos
        $this->comandos = Comando::all()->pluck('descripcion', 'nombre')->toArray();

        // Preparar mensajes
        $this->mensajes = Mensaje::all()->pluck('contenido')->mapWithKeys(function ($item) {
            return ['mensaje' => $item];
        })->toArray();

        // Preparar programaciones
        $this->programaciones = Programacion::all()->pluck('hora_unix', 'comando_id')->toArray();

        // Preparar estados
        $this->estados = Estado::all()->pluck('descripcion', 'nombre')->toArray();

        // Preparar estado actual (modificar según tu lógica para obtener el estado actual)
        $this->estadoActual = [
            'estadoActual' => $cultivo->estadoActual,
        ];
    }
}
