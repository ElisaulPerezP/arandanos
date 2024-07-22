<?php

namespace App\Listeners;

use App\Events\InicioDeAplicacion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Models\Cultivo;
use App\Models\Estado;
use App\Events\SincronizarSistema;



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
        $reportFilePath = base_path('pythonScripts/scriptsReport.php');
        if (!file_exists($reportFilePath)) {
            Log::error("El archivo de reporte no existe: {$reportFilePath}");
            return;
        }

        $report = include($reportFilePath);

        foreach ($scripts as $script) {
            if (!empty($script)) {
                $command = escapeshellcmd("python3 " . base_path("pythonScripts/{$script}"));
                exec($command . " > /dev/null &", $output, $returnVar);
                if ($returnVar !== 0) {
                    Log::error("Error al ejecutar el script: {$script}. Output: " . implode("\n", $output));
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
