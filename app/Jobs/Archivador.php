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

        if (is_object($this->data)) {
            $dataArray = $this->data->toArray();  // Convertir a array si es un objeto
        } else {
            $dataArray = $this->data;  // Si ya es un array, no se hace nada
        }
        
        //Log::info("La tabla que se está pasando es: " . gettype($this->table) . " ({$this->table}), y los datos son de tipo: " . gettype($dataArray), $dataArray);

        if ($this->action === 'update' && $this->identifier) {
            \DB::table($this->table)
                ->where($this->identifier['column'], $this->identifier['value'])
                ->update($dataArray);
            //Log::info("Acción de actualización sobre la tabla {$this->table}, con los datos: ", $dataArray);
        } else {
            \DB::table($this->table)->insert($dataArray);
            //Log::info("Acción de almacenamiento sobre la tabla {$this->table}, con los datos: ", $dataArray);
        }
    }
}
