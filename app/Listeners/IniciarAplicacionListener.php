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
        // Ejecutar la lógica de los scripts de base
        if (is_array($event->scriptsDeBase)) {
            $this->iniciarScripts($event->scriptsDeBase);
            Log::info("Los scripts de base han sido iniciados: " . implode(', ', $event->scriptsDeBase));
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
        Log::info("Iniciando scripts");
        $reportFilePath = '/home/elisaul/ws/arandanos/pythonScripts/scriptsReport.php';
        if (!file_exists($reportFilePath)) {
            Log::error("El archivo de reporte no existe: {$reportFilePath}");
            return;
        }

        $report = include($reportFilePath);

        foreach ($scripts as $script) {
            if (!empty($script)) {
                // Separar el script y sus argumentos en una lista, eliminando espacios adicionales y nuevas líneas
                $arguments = array_filter(preg_split('/\s+/', $script));

                // Agregar sudo python3 al inicio de la lista
                array_unshift($arguments, 'sudo', 'python3');

                // Crear el proceso
                $process = new Process($arguments);

                // Iniciar el proceso y esperar su finalización
                try {
                    $process->mustRun();
                    Log::info("Script iniciado exitosamente: {$script}");
                    $report['scriptsEjecutandose'] .= empty($report['scriptsEjecutandose']) ? $script : ', ' . $script;
                } catch (ProcessFailedException $exception) {
                    Log::error("Error al ejecutar el script: {$script}. Error: " . $exception->getMessage());
                }
            }
        }

        $content = "<?php\nreturn " . var_export($report, true) . ";\n";
        file_put_contents($reportFilePath, $content);
    }
}
