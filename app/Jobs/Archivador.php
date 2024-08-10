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
