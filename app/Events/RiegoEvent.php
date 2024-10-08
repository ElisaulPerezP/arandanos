<?php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use App\Jobs\Archivador;
use Illuminate\Support\Facades\Log;


class RiegoEvent
{
    use Dispatchable, SerializesModels;

    public $programacion;

    /**
     * Create a new event instance.
     */
    public function __construct($programacion)
    {
        $this->programacion = $programacion;
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
        Cache::put("programacion_{$this->programacion['id']}", $this->programacion, 600);

        // Despachar la actualización a la base de datos
        Archivador::dispatch('programacions', $this->programacion, 'update', ['column' => 'id', 'value' => $this->programacion['estado']]);
    }
}
