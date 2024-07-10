<?php

namespace App\Listeners;

use App\Events\StopSystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Estado;
use Illuminate\Support\Facades\Log;
use App\Models\Cultivo;

class StopSystemListener
{
    public function __construct()
    {
        //
    }

    public function handle(StopSystem $event)
    {
        $scriptsEjecutandose = $event->scriptsEjecutandose;
        
        $cultivo = Cultivo::first();
        $estadoInactivo = Estado::where('nombre', 'Inactivo')->first();

        if ($estadoInactivo) {
            // Lógica para detener procesos y scripts
            $this->detenerProcesos($scriptsEjecutandose);
            $cultivo->update([
                'estado_id' => $estadoInactivo->id,
            ]);
            
            Log::info("El cultivo {$cultivo->id} ha sido marcado como inactivo y los procesos han sido detenidos.");
        } else {
            Log::error('Estado "Inactivo" no encontrado en la base de datos.');
        }
    }

    protected function detenerProcesos(array $scripts)
    {
        $reportFilePath = base_path('pythonScripts/scriptsReport.php');
        
        // Verificar si el archivo de reporte existe
        if (!file_exists($reportFilePath)) {
            Log::error("El archivo de reporte no existe: {$reportFilePath}");
            return;
        }

        $report = include($reportFilePath);
        
        foreach ($scripts as $script) {
            if (!empty($script)) {
                // Obtener el nombre del script sin parámetros
                $scriptName = explode(' ', $script)[0];
                $command = escapeshellcmd("pkill -f " . $scriptName);
                exec($command, $output, $returnVar);
                if ($returnVar !== 0) {
                    Log::error("Error al detener el script: {$script}. Output: " . implode("\n", $output));
                } else {
                    Log::info("Script detenido exitosamente: {$script}");
                }
            }
        }

        // Vaciar el campo scriptsEjecutandose
        $report['scriptsEjecutandose'] = '';

        $content = "<?php\nreturn " . var_export($report, true) . ";\n";
        file_put_contents($reportFilePath, $content);
    }
}

