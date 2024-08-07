<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;

class InicioDeAplicacion
{
    public $scriptsDeBase;

    public function __construct()
    {
        $configFilePath = '/var/www/arandanos/pythonScripts/scriptsReport.php';
        if (!file_exists($configFilePath)) {
            Log::error("El archivo de configuración no existe: {$configFilePath}");
            return;
        }

        $config = include($configFilePath);

        // Verificar si 'scriptsDeBase' está definido en la configuración
        if (!isset($config['scriptsDeBase'])) {
            Log::error("La clave 'scriptsDeBase' no está definida en la configuración");
            return;
        }

        $this->scriptsDeBase = $config['scriptsDeBase'];
    }
}