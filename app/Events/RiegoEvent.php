<?php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use App\Jobs\Archivador;

class RiegoEvent
{
    use Dispatchable, SerializesModels;

    public $programacion;

    /**
     * Create a new event instance.
     */
    public function __construct($programacion)
    {
        // Almacenar $programacion como un array para mantener flexibilidad
        $this->programacion = $programacion->toArray();
    }

    /**
     * Manejar la actualización de estado sin afectar la base de datos directamente.
     */
    public function actualizarEstado(string $nuevoEstado)
    {
        // Actualizar el estado y la fecha de actualización en el array
        $this->programacion['estado'] = $nuevoEstado;
        $this->programacion['updated_at'] = now();

        // Actualizar la caché
        Cache::put("programacion_{$this->programacion['id']}", $this->programacion, 60);

        // Despachar la actualización a la base de datos
        Archivador::dispatch('programaciones', $this->programacion, 'update', ['column' => 'id', 'value' => $this->programacion['estado']]);
    }
}
