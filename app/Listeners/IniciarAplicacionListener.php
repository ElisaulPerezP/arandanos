<?php

namespace App\Listeners;

use App\Events\InicioDeAplicacion;
use Illuminate\Support\Facades\Log;
use App\Models\Cultivo;
use App\Models\Estado;
use App\Events\SincronizarSistema;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class IniciarAplicacionListener
{
    public function __construct()
    {
        //
    }

    public function handle(InicioDeAplicacion $event)
    {
        // Ejecutar la lógica de los scripts de base
        if (is_array($event->scriptsDeBase)) {
            $this->iniciarScripts($event->scriptsDeBase);
            //Log::info("Los scripts de base han sido iniciados: " . implode(', ', $event->scriptsDeBase));
        } else {
            Log::error("scriptsDeBase no está definido o no es un array.");
        }

        $cultivo = Cultivo::first();
        $estadoActivo = Estado::where('nombre', 'Activo')->first();
        $cultivo->update([
            'estado_id' => $estadoActivo->id,
        ]);
        event(new SincronizarSistema());
    }

    protected function iniciarScripts(array $scripts)
    {
        $reportFilePath = '/var/www/arandanos/pythonScripts/scriptsReport.php';
        if (!file_exists($reportFilePath)) {
            Log::error("El archivo de reporte no existe: {$reportFilePath}");
            return;
        }
    
        $report = include($reportFilePath);
    
        // Asegúrate de que `scriptsEjecutandose` sea un array
        if (!is_array($report['scriptsEjecutandose'])) {
            $report['scriptsEjecutandose'] = [];
        }
    
        foreach ($scripts as $script) {
            if (!empty($script)) {
                $arguments = array_filter(preg_split('/\s+/', $script));
                array_unshift($arguments, 'sudo python3');
                $command = implode(' ', $arguments) . ' > /dev/null 2>&1 &';
                $process = new Process(['bash', '-c', $command]);
    
                try {
                    $process->mustRun();
                    // Agregar el script al array `scriptsEjecutandose`
                    $report['scriptsEjecutandose'][] = $script;
                } catch (ProcessFailedException $exception) {
                    Log::error("Error al ejecutar el script: {$script}. Error: " . $exception->getMessage());
                }
            }
        }
    
        $content = "<?php\nreturn " . var_export($report, true) . ";\n";
        file_put_contents($reportFilePath, $content);
    }
    
}
