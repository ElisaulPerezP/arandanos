<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;




class PortsController extends Controller
{
    public function onLed(): JsonResponse
    {
        $scriptPath = '/var/www/onLed.py';
        $command = "python3 $scriptPath";

        $output = shell_exec($command);

        if ($output !== null) {
            return new JsonResponse(['message' => 'Script de Python ejecutado correctamente', 'output' => $output]);
        } else {
            return new JsonResponse(['message' => 'Error al ejecutar el script de Python']);
        }
    }

    public function offLed(): JsonResponse
    {
        $scriptPath = '/var/www/offLed.py';
        $command = "python3 $scriptPath";

        $output = shell_exec($command);

        if ($output !== null) {
            return new JsonResponse(['message' => 'Script de Python ejecutado correctamente', 'output' => $output]);
        } else {
            return new JsonResponse(['message' => 'Error al ejecutar el script de Python']);
        }
    }
}
