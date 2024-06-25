<?php

namespace App\Listeners;

use App\Events\CultivoInactivo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Estado;
use Illuminate\Support\Facades\Log;

class ManejarCultivoInactivo
{
    public function __construct()
    {
        //
    }

    public function handle(CultivoInactivo $event)
    {
        $cultivo = $event->cultivo;
        $estadoInactivo = Estado::where('nombre', 'Inactivo')->first();

        if ($estadoInactivo) {
            $cultivo->estado_id = $estadoInactivo->id;
            $cultivo->save();

            // Lógica para detener procesos y scripts
            $this->detenerProcesos();

            Log::info("El cultivo {$cultivo->id} ha sido marcado como inactivo y los procesos han sido detenidos.");
        } else {
            Log::error('Estado "Inactivo" no encontrado en la base de datos.');
        }
    }

    protected function detenerProcesos()
    {
        $reportFilePath = base_path('pythonScripts/scriptsReport.php');

        // Verificar si el archivo de reporte existe
        if (!file_exists($reportFilePath)) {
            Log::error("El archivo de reporte no existe: {$reportFilePath}");
            return;
        }

        // Incluir el archivo de reporte
        $report = include($reportFilePath);

        // Verificar si la clave 'scriptsEjecutandose' está presente en el reporte
        if (!isset($report['scriptsEjecutandose'])) {
            Log::error("La clave 'scriptsEjecutandose' no está presente en el archivo de reporte.");
            return;
        }

        // Obtener la lista de scripts ejecutándose
        $scripts = explode(', ', $report['scriptsEjecutandose']);

        // Detener cada script listado en 'scriptsEjecutandose'
        foreach ($scripts as $script) {
            if (!empty($script)) {
                // Ejecutar el comando pkill para detener el script
                $command = escapeshellcmd("pkill -f $script");
                exec($command, $output, $returnVar);

                if ($returnVar !== 0) {
                    Log::error("Error al detener el script: {$script}. Output: " . implode("\n", $output));
                } else {
                    Log::info("Script detenido exitosamente: {$script}");
                }
            }
        }
//TODO: ESTA LOGICA LA DEBE MANEJAR SOLO UN LISTENER. SOLO UN LISTENER DEBE INTERVENIR ESTE ARCHIVO
        // Limpiar la clave 'scriptsEjecutandose' en el archivo de reporte
        $report['scriptsEjecutandose'] = '';

        // Guardar el reporte actualizado
        $content = "<?php\nreturn " . var_export($report, true) . ";\n";
        file_put_contents($reportFilePath, $content);
    }
}
