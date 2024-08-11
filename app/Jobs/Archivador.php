<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Archivador implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $table;
    protected $data;
    protected $action; // Nuevo parámetro para la acción
    protected $identifier; // Identificador para las actualizaciones

    /**
     * Create a new job instance.
     */
    public function __construct($table, $data, $action = 'insert', $identifier = null)
    {
        $this->table = $table;
        $this->data = $data;
        $this->action = $action;
        $this->identifier = $identifier;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Verificar si $this->data es un objeto y convertirlo a array si es necesario
        if (is_object($this->data)) {
            $this->data = $this->data->toArray();
        }

        Log::info("La tabla que se está pasando es: " . gettype($this->table) . " ({$this->table}), y los datos son de tipo: " . gettype($this->data), $this->data);

        if ($this->action === 'update' && $this->identifier) {
            \DB::table($this->table)
                ->where($this->identifier['column'], $this->identifier['value'])
                ->update($this->data);
            Log::info("Acción de actualización sobre la tabla {$this->table}, con los datos: ", $this->data);
        } else {
            \DB::table($this->table)->insert($this->data);
            Log::info("Acción de almacenamiento sobre la tabla {$this->table}, con los datos: ", $this->data);
        }
    }
}
