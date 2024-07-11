<?php

namespace App\Http\Controllers\Api;

use App\Events\CultivoInactivo;
use App\Models\Cultivo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ApiController extends Controller
{

    public function reportStop()
    {
        event(new CultivoInactivo());
        return response()->json(['message' => 'Parada reportada exitosamente, evento emitido'], 200);
    }

    public function getTanquesCommand()
    {
        // Lógica para obtener comandos de selección
        return response()->json([
            'actions' => [
                'on:valvula1',
                'off:valvula2'
            ]
        ], 200);
    }

    public function reportTanquesState(Request $request)
    {
        // Lógica para manejar el reporte de estado
        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportTanquesShutdown(Request $request)
    {
        // Lógica para manejar el reporte de apagado
        return response()->json(['message' => 'Apagado con exito'], 200);
    }


    public function getSelectorCommand()
    {
        // Lógica para obtener comandos de selección
        return response()->json([
            'actions' => [
                'on:valvula1',
                'off:valvula2'
            ]
        ], 200);
    }

    public function reportState(Request $request)
    {
        // Lógica para manejar el reporte de estado
        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportShutdown(Request $request)
    {
        // Lógica para manejar el reporte de apagado
        return response()->json(['message' => 'Apagado con exito'], 200);
    }
    public function getImpulsoresCommand()
    {
        // Lógica para obtener comandos de selección
        return response()->json([
            'actions' => [
                'on:bomba1',
                'off:bomba2'
            ]
        ], 200);
    }

    public function reportImpulsoresState(Request $request)
    {
        // Lógica para manejar el reporte de estado
        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportImpulsoresShutdown(Request $request)
    {
        // Lógica para manejar el reporte de apagado
        return response()->json(['message' => 'Apagado con exito'], 200);
    }
    public function getInyectoresCommand()
    {
        // Lógica para obtener comandos de selección
        return response()->json([
            'actions' => [
                'on:bomba1, 18',
                'off:bomba2'
            ]
        ], 200);
    }
    
    public function reportInyectoresState(Request $request)
    {
        // Lógica para manejar el reporte de estado
        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }
    
    public function reportInyectoresShutdown(Request $request)
    {
        // Lógica para manejar el reporte de apagado
        return response()->json(['message' => 'Apagado con exito'], 200);
    }
    public function reportFlujoConteo(Request $request)
    {
        // Lógica para manejar el reporte de conteo
        return response()->json(['message' => 'Conteo reportado exitosamente'], 200);
    }

    public function reportFlujoApagado(Request $request)
    {
        // Lógica para manejar el reporte de apagado
        return response()->json(['message' => 'Apagado con exito'], 200);
    }

}
