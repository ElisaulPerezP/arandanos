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

    /**
     * Create a new job instance.
     */
    public function __construct($table, $data)
    {
        $this->table = $table;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        \DB::table($this->table)->insert($this->data);
        Log::info("Acción de almacenamiento sobre la tabla {$this->table}, con los datos: ", $this->data);
    }
}
