<?php

namespace App\Listeners;

use App\Events\StopSystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Estado;
use Illuminate\Support\Facades\Log;
use App\Models\Cultivo;
use App\Events\SincronizarSistema;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StopSystemListener
{
    public function __construct()
    {
        //
    }

    public function handle(StopSystem $event)
    {
        $scriptsEjecutandose = $event->scriptsEjecutandose;
        $scriptStopTotal = $event->scriptStopTotal;

        $cultivo = Cultivo::first();
        $estadoInactivo = Estado::where('nombre', 'Inactivo')->first();

        if ($estadoInactivo) {
            $this->detenerProcesos($scriptsEjecutandose);
            $cultivo->update([
                'estado_id' => $estadoInactivo->id,
            ]);
            $this->ejecutarStopTotal($scriptStopTotal);
            
            event(new SincronizarSistema());

            Log::info("El cultivo {$cultivo->id} ha sido marcado como inactivo y los procesos han sido detenidos.");
        } else {
            Log::error('Estado "Inactivo" no encontrado en la base de datos.');
        }
    }

    protected function detenerProcesos(array $scripts)
    {
        $reportFilePath = '/var/www/arandanos/pythonScripts/scriptsReport.php';

        if (!file_exists($reportFilePath)) {
            Log::error("El archivo de reporte no existe: {$reportFilePath}");
            return;
        }

        $report = include($reportFilePath);

        foreach ($scripts as $script) {
            if (!empty($script)) {
                // Obtener el nombre del script sin parámetros
                $scriptName = explode(' ', $script)[0];
                $command = "pkill -f " . escapeshellarg($scriptName);
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

    protected function ejecutarStopTotal($scriptStopTotal)
    {
        if (!empty($scriptStopTotal)) {
            $command = "python3 /var/www/arandanos/pythonScripts/{$scriptStopTotal}";
            exec($command . " > /dev/null 2>&1 &", $output, $returnVar);
            if ($returnVar !== 0) {
                Log::error("Error al ejecutar el script stopTotal: {$scriptStopTotal}. Output: " . implode("\n", $output));
            } else {
                Log::info("Script stopTotal iniciado exitosamente: {$scriptStopTotal}");
            }
        }
    }
}
