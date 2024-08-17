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
            // Intentar detener los procesos
            $procesosDetenidos = $this->detenerProcesos($scriptsEjecutandose);
    
            if ($procesosDetenidos) {
                // Actualizar el estado del cultivo
                $cultivo->estado_id = $estadoInactivo->id;
                $cultivo->updated_at = now();
    
                // Actualizar la caché con el cultivo actualizado
                Cache::forever('cultivo_primero', $cultivo);
    
                // Preparar los datos para archivarlos
                $cultivoData = [
                    'nombre' => $cultivo->nombre,
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
                Log::error("No se pudo detener todos los procesos, el cultivo no ha sido marcado como inactivo.");
            }
        } else {
            Log::error('Estado "Inactivo" no encontrado en la base de datos.');
        }
    }
    
    protected function detenerProcesos(array $scripts)
    {
        $reportFilePath = '/var/www/arandanos/pythonScripts/scriptsReport.php';
    
        if (!file_exists($reportFilePath)) {
            Log::error("El archivo de reporte no existe: {$reportFilePath}");
            return false;
        }
    
        $report = include $reportFilePath;
        $allProcessesStopped = true;
    
        foreach ($scripts as $script) {
            if (!empty($script)) {
                $scriptName = explode(' ', $script)[0];
                $pkillCommand = "/usr/bin/pkill " . escapeshellarg($scriptName);
                
                // Log del comando ejecutado
                Log::info("Ejecutando comando pkill: {$pkillCommand}");
                
                exec($pkillCommand, $output, $returnVar);
    
                // Log de la respuesta del sistema
                Log::info("Respuesta de pkill para {$scriptName}: Return Var = {$returnVar}, Output = " . implode("\n", $output));
    
                if ($returnVar !== 0) {
                    Log::error("Error al detener el script: {$scriptName} con pkill. Intentando con kill...");
                    $pgrepCommand = "/usr/bin/pgrep -f '^python3 " . escapeshellarg($scriptName) . "'";
                    
                    // Log del comando ejecutado
                    Log::info("Ejecutando comando pgrep: {$pgrepCommand}");
                    
                    exec($pgrepCommand, $pids, $pgrepReturnVar);
    
                    // Log de la respuesta del sistema
                    Log::info("Respuesta de pgrep para {$scriptName}: Return Var = {$pgrepReturnVar}, PIDs = " . implode(", ", $pids));
    
                    if ($pgrepReturnVar === 0) {
                        foreach ($pids as $pid) {
                            $killCommand = "/usr/bin/kill " . escapeshellarg($pid);
                            
                            // Log del comando ejecutado
                            Log::info("Ejecutando comando kill para PID {$pid}: {$killCommand}");
                            
                            exec($killCommand, $killOutput, $killReturnVar);
    
                            // Log de la respuesta del sistema
                            Log::info("Respuesta de kill para PID {$pid}: Return Var = {$killReturnVar}, Output = " . implode("\n", $killOutput));
    
                            if ($killReturnVar !== 0) {
                                Log::error("Error al detener el proceso con PID: {$pid}. Output: " . implode("\n", $killOutput));
                                $allProcessesStopped = false;
                            } else {
                                Log::info("Proceso con PID {$pid} detenido exitosamente.");
                            }
                        }
                    } else {
                        Log::error("Error al encontrar PIDs para el script: {$scriptName}. Output: " . implode("\n", $pids));
                        $allProcessesStopped = false;
                    }
                }
            }
        }
    
        // Limpia el array `scriptsEjecutandose` después de detener los procesos
        $report['scriptsEjecutandose'] = [];
    
        $content = "<?php\nreturn " . var_export($report, true) . ";\n";
        file_put_contents($reportFilePath, $content);
    
        return $allProcessesStopped;
    }
    
    
    protected function ejecutarStopTotal($scriptStopTotal)
    {
        if (!empty($scriptStopTotal)) {
            $command = "python3 /var/www/arandanos/pythonScripts/{$scriptStopTotal}";
            
            // Log del comando ejecutado
            Log::info("Ejecutando comando stopTotal: {$command}");
            
            exec($command . " > /dev/null 2>&1 &", $output, $returnVar);
            
            // Log de la respuesta del sistema
            Log::info("Respuesta de stopTotal: Return Var = {$returnVar}, Output = " . implode("\n", $output));
            
            if ($returnVar !== 0) {
                Log::error("Error al ejecutar el script stopTotal: {$scriptStopTotal}. Output: " . implode("\n", $output));
            } else {
                Log::info("Script stopTotal iniciado exitosamente: {$scriptStopTotal}");
            }
        }
    }
}
