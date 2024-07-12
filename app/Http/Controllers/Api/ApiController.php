<?php

namespace App\Http\Controllers\Api;

use App\Events\CultivoInactivo;
use App\Models\Cultivo;
use App\Models\S1;
use App\Models\S2;
use App\Models\S3;
use App\Models\S4;
use App\Models\S5;
use App\Models\EstadoSistema;
use App\Models\ComandoHardware;
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
        // Obtener el comando actual para el sistema s1, esta tabla solo tiene una entrada
        $estado = EstadoSistema::first();
        $comando = ComandoHardware::find($estado->s1_id);

        if ($comando) {
            return response()->json(['actions' => json_decode($comando->comando)], 200);
        }

        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportTanquesState(Request $request)
    {
        // Actualizar el estado del sistema s1
        $estado = S1::find(1);
        $estado->update($request->all());

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportTanquesShutdown(Request $request)
    {
        // Actualizar el estado del sistema s1 a inactivo
        $estado = S1::find(1);
        $estado->estado = false;
        $estado->save();

        return response()->json(['message' => 'Apagado con exito'], 200);
    }

    public function getSelectorCommand()
    {
        // Obtener el comando actual para el sistema s2
        $estado = EstadoSistema::first();
        $comando = ComandoHardware::find($estado->s2_id);

        if ($comando) {
            return response()->json(['actions' => json_decode($comando->comando)], 200);
        }

        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportState(Request $request)
    {
        // Actualizar el estado del sistema s2
        $estado = S2::find(1);
        $estado->update($request->all());

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportShutdown(Request $request)
    {
        // Actualizar el estado del sistema s2 a inactivo
        $estado = S2::find(1);
        $estado->estado = false;
        $estado->save();

        return response()->json(['message' => 'Apagado con exito'], 200);
    }

    public function getImpulsoresCommand()
    {
        // Obtener el comando actual para el sistema s3
        $estado = EstadoSistema::first();
        $comando = ComandoHardware::find($estado->s3_id);

        if ($comando) {
            return response()->json(['actions' => json_decode($comando->comando)], 200);
        }

        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportImpulsoresState(Request $request)
    {
        // Actualizar el estado del sistema s3
        $estado = S3::find(1);
        $estado->update($request->all());

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportImpulsoresShutdown(Request $request)
    {
        // Actualizar el estado del sistema s3 a inactivo
        $estado = S3::find(1);
        $estado->estado = false;
        $estado->save();

        return response()->json(['message' => 'Apagado con exito'], 200);
    }

    public function getInyectoresCommand()
    {
        // Obtener el comando actual para el sistema s4
        $estado = EstadoSistema::first();
        $comando = ComandoHardware::find($estado->s4_id);

        if ($comando) {
            return response()->json(['actions' => json_decode($comando->comando)], 200);
        }

        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportInyectoresState(Request $request)
    {
        // Actualizar el estado del sistema s4
        $estado = S4::find(1);
        $estado->update($request->all());

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportInyectoresShutdown(Request $request)
    {
        // Actualizar el estado del sistema s4 a inactivo
        $estado = S4::find(1);
        $estado->estado = false;
        $estado->save();

        return response()->json(['message' => 'Apagado con exito'], 200);
    }

    public function reportFlujoConteo(Request $request)
    {
        // Actualizar el conteo de flujo para el sistema s5
        $estado = S5::find(1);
        $estado->update($request->all());

        return response()->json(['message' => 'Conteo reportado exitosamente'], 200);
    }

    public function reportFlujoApagado(Request $request)
    {
        // Actualizar el estado del sistema s5 a inactivo
        $estado = S5::find(1);
        $estado->estado = false;
        $estado->save();

        return response()->json(['message' => 'Apagado con exito'], 200);
    }
}
