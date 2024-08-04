<?php

namespace App\Listeners;

use App\Events\InicioDeAplicacion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
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
        // Obtener los scripts de base desde el evento
        $scriptsDeBase = $event->scriptsDeBase;

        // Ejecutar la lógica de los scripts de base
        $this->iniciarScripts($scriptsDeBase);

        Log::info("Los scripts de base han sido iniciados: " . implode(', ', $scriptsDeBase));

        $cultivo = Cultivo::first();
        $estadoActivo = Estado::where('nombre', 'Activo')->first();
        $cultivo->update([ 
            'estado_id' => $estadoActivo->id, 
        ]);
        event(new SincronizarSistema());
    }

    protected function iniciarScripts(array $scripts)
    {
        Log::info("iniciando");
        $reportFilePath = base_path('/var/www/arandanos/pythonScripts/scriptsReport.php');
        if (!file_exists($reportFilePath)) {
            Log::error("El archivo de reporte no existe: {$reportFilePath}");
            return;
        }

        $report = include($reportFilePath);

        foreach ($scripts as $script) {
            if (!empty($script)) {
                // Separar el script y sus argumentos en una lista
                $arguments = explode(' ', $script);

                // Agregar python3 al inicio de la lista
                array_unshift($arguments, 'sudo python3');

                $process = new Process($arguments);
                $process->start();
                $process->wait(); // Esperar a que el proceso termine

                if (!$process->isSuccessful()) {
                    Log::error("Error al ejecutar el script: {$script}. Output: " . $process->getErrorOutput());
                } else {
                    Log::info("Script iniciado exitosamente: {$script}");
                    $report['scriptsEjecutandose'] .= empty($report['scriptsEjecutandose']) ? $script : ', ' . $script;
                }
            }
        }

        $content = "<?php\nreturn " . var_export($report, true) . ";\n";
        file_put_contents($reportFilePath, $content);
    }
}
