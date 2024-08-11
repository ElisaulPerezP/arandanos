<?php

namespace App\Listeners;

use App\Events\StopSystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Estado;
use Illuminate\Support\Facades\Log;
use App\Models\Cultivo;
use App\Events\SincronizarSistema;
use Illuminate\Support\Facades\Cache;
use App\Jobs\Archivador;
use Illuminate\Support\Str;

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

        // Obtener el cultivo desde la caché
        $cultivo = Cache::rememberForever('cultivo_primero', function () {
            return Cultivo::first();
        });

        // Obtener el estado inactivo desde la caché
        $estadoInactivo = Cache::rememberForever('estado_inactivo', function () {
            return Estado::where('nombre', 'Inactivo')->first();
        });

        if ($estadoInactivo) {
            $this->detenerProcesos($scriptsEjecutandose);

            // Actualizar el estado del cultivo
            $cultivo->estado_id = $estadoInactivo->id;
            $cultivo->updated_at = now();

            // Actualizar la caché con el cultivo actualizado
            Cache::forever('cultivo_primero', $cultivo);

            // Preparar los datos para archivarlos
            $cultivoData = [
                'nombre' => $cultivo-> nombre,
                'id' => $cultivo->id,
                'estado_id' => $estadoInactivo->id,
                'updated_at' => now(),
                'created_at' => $cultivo->created_at,
                'api_token' => $cultivo->api_token,
            ];

            // Despachar el trabajo para escribir en la base de datos

            Archivador::dispatch('cultivos', $cultivoData, 'update', ['column' => 'id', 'value' => $cultivo->id]);

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
                $pkillCommand = "sudo /usr/bin/pkill " . escapeshellarg($scriptName);
                
                // Ejecutar pkill
                exec($pkillCommand, $output, $returnVar);

                if ($returnVar !== 0) {
                    Log::error("Error al detener el script: {$scriptName} con pkill. Intentando con kill...");

                    // Intentar con kill
                    $pgrepCommand = "/usr/bin/pgrep -f " . escapeshellarg($scriptName);
                    exec("sudo " . $pgrepCommand, $pids, $pgrepReturnVar);

                    if ($pgrepReturnVar === 0) {
                        foreach ($pids as $pid) {
                            $killCommand = "sudo /usr/bin/kill " . escapeshellarg($pid);
                            exec($killCommand, $killOutput, $killReturnVar);

                            if ($killReturnVar !== 0) {
                                Log::error("Error al detener el proceso con PID: {$pid}. Output: " . implode("\n", $killOutput));
                            } else {
                                Log::info("Proceso con PID {$pid} detenido exitosamente.");
                            }
                        }
                    } else {
                        Log::error("Error al encontrar PIDs para el script: {$scriptName}. Output: " . implode("\n", $pids));
                    }
                } else {
                    Log::info("Script detenido exitosamente: {$scriptName}");
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
