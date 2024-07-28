<?php

namespace App\Http\Controllers\Api;

use App\Events\CultivoInactivo;
use App\Events\InicioDeAplicacion;
use App\Models\Cultivo;
use App\Models\S0;
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
    public function reportStop(Request $request)
    {
        // Obtener el estado actual del sistema
        $estadoActual = EstadoSistema::first();

        // Determinar el nuevo estado y el evento a emitir basado en el estado actual
        if ($estadoActual && $estadoActual->s0->estado === 'Parada activada') {
            $nuevoEstado = 'Parada desactivada';
            $evento = new InicioDeAplicacion();
        } else {
            $nuevoEstado = 'Parada activada';
            $evento = new CultivoInactivo();
        }

        // Crear un nuevo registro S0 con el nuevo estado y el comando del antecesor
        $s0 = S0::create([
            'estado' => $nuevoEstado,
            'comando' => $estadoActual->s0->comando ?? null
        ]);

        // Actualizar el estado del sistema con el nuevo s0_id
        if ($estadoActual) {
            $estadoActual->update(['s0_id' => $s0->id]);
        } else {
            EstadoSistema::create(['s0_id' => $s0->id]);
        }

        // Emitir el evento correspondiente
        event($evento);

        return response()->json(['message' => "Estado cambiado a '$nuevoEstado', evento emitido"], 200);
    }

    public function getTanquesCommand()
    {
        // Obtener el estado actual del sistema
        $estado = EstadoSistema::first();

        // Verificar si existe el estado y la relación s1
        if ($estado && $estado->s1) {
            // Obtener el comando desde la relación s1
            $comando = $estado->s1->comando;

            // Retornar el comando si existe
            if ($comando) {
                return response()->json(['command' => $comando], 200);
            }
        }

        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportTanquesState(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema
        $estadoSistema = EstadoSistema::first();

        // Verificar si existe el estadoSistema y la relación s1
        if ($estadoSistema && $estadoSistema->s1) {
            // Obtener la entrada s1 relacionada
            $s1Actual = $estadoSistema->s1;

            // Crear una nueva entrada s1 con la información nueva y la faltante
            $s1Nueva = S1::create([
                'estado' => $request->input('estado', $s1Actual->estado),
                'sensor1' => $request->input('sensor1', $s1Actual->sensor1),
                'sensor2' => $request->input('sensor2', $s1Actual->sensor2),
                'valvula14' => $request->input('valvula14', $s1Actual->valvula14),
                'comando' => $s1Actual->comando
            ]);

            // Actualizar el EstadoSistema con la nueva entrada s1
            $estadoSistema->update(['s1_id' => $s1Nueva->id]);

            return response()->json(['message' => 'Estado reportado exitosamente'], 200);
        }

        // Retornar un mensaje de error si no se encuentra el estadoSistema o la relación s1
        return response()->json(['message' => 'Estado del sistema no encontrado'], 404);
    }

    public function reportTanquesShutdown(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema
        $estadoSistema = EstadoSistema::first();

        // Verificar si existe el estadoSistema y la relación s1
        if ($estadoSistema && $estadoSistema->s1) {
            // Obtener la entrada s1 relacionada
            $s1Actual = $estadoSistema->s1;

            // Crear una nueva entrada s1 con el estado inactivo y copiar la información faltante
            $s1Nueva = S1::create([
                'estado' => false,
                'sensor1' => $s1Actual->sensor1,
                'sensor2' => $s1Actual->sensor2,
                'valvula14' => $s1Actual->valvula14,
                'comando' => $s1Actual->comando
            ]);

            // Actualizar el EstadoSistema con la nueva entrada s1
            $estadoSistema->update(['s1_id' => $s1Nueva->id]);

            return response()->json(['message' => 'Apagado con exito'], 200);
        }

        // Retornar un mensaje de error si no se encuentra el estadoSistema o la relación s1
        return response()->json(['message' => 'Estado del sistema no encontrado'], 404);
    }

    public function getSelectorCommand()
    {
        // Obtener el estado actual del sistema
        $estado = EstadoSistema::first();

        // Verificar si existe el estado y la relación s2
        if ($estado && $estado->s2) {
            // Obtener el comando desde la relación s2
            $comando = $estado->s2->comando;

            // Retornar el comando si existe
            if ($comando) {
                return response()->json(['actions' => json_decode($comando)], 200);
            }
        }

        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportState(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::firstOrCreate();

        // Obtener la entrada s2 actual si existe
        $s2Actual = $estadoSistema->s2;

        // Crear una nueva entrada s2 con la información proporcionada en el request y el comando del antecesor
        $s2Nueva = S2::create(array_merge(
            $request->all(),
            ['comando_id' => $s2Actual ? $s2Actual->comando_id : null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s2
        $estadoSistema->update(['s2_id' => $s2Nueva->id]);

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportShutdown(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::firstOrCreate();

        // Obtener la entrada s2 actual si existe
        $s2Actual = $estadoSistema->s2;

        // Crear una nueva entrada s2 con el estado inactivo y el comando del antecesor
        $s2Nueva = S2::create(array_merge(
            ['estado' => 'apagado'],
            $request->except('estado'),
            ['comando_id' => $s2Actual ? $s2Actual->comando_id : null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s2
        $estadoSistema->update(['s2_id' => $s2Nueva->id]);

        return response()->json(['message' => 'Apagado con exito'], 200);
    }

    public function getImpulsoresCommand()
    {
        // Obtener el estado actual del sistema
        $estado = EstadoSistema::first();

        // Verificar si existe el estado y la relación s3
        if ($estado && $estado->s3) {
            // Obtener el comando desde la relación s3
            $comando = $estado->s3->comando;

            // Retornar el comando si existe
            if ($comando) {
                return response()->json(['actions' => json_decode($comando)], 200);
            }
        }

        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportImpulsoresState(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::firstOrCreate();

        // Obtener la entrada s3 actual si existe
        $s3Actual = $estadoSistema->s3;

        // Crear una nueva entrada s3 con la información proporcionada en el request y el comando del antecesor
        $s3Nueva = S3::create(array_merge(
            $request->all(),
            ['comando' => $s3Actual ? $s3Actual->comando : null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s3
        $estadoSistema->update(['s3_id' => $s3Nueva->id]);

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportImpulsoresShutdown(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::firstOrCreate();

        // Obtener la entrada s3 actual si existe
        $s3Actual = $estadoSistema->s3;

        // Crear una nueva entrada s3 con el estado inactivo y el comando del antecesor
        $s3Nueva = S3::create(array_merge(
            ['estado' => false],
            $request->except('estado'),
            ['comando' => $s3Actual ? $s3Actual->comando : null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s3
        $estadoSistema->update(['s3_id' => $s3Nueva->id]);

        return response()->json(['message' => 'Apagado con exito'], 200);
    }

    public function getInyectoresCommand()
    {
        // Obtener el estado actual del sistema
        $estado = EstadoSistema::first();

        // Verificar si existe el estado y la relación s4
        if ($estado && $estado->s4) {
            // Obtener el comando desde la relación s4
            $comando = $estado->s4->comando->comando;

            // Retornar el comando si existe
            if ($comando) {
                return response()->json(['actions' => json_decode($comando)], 200);
            }
        }

        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportInyectoresState(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::firstOrCreate();

        // Obtener la entrada s4 actual si existe
        $s4Actual = $estadoSistema->s4;

        // Crear una nueva entrada s4 con la información proporcionada en el request y el comando del antecesor
        $s4Nueva = S4::create(array_merge(
            $request->all(),
            ['comando_id' => $s4Actual ? $s4Actual->comando_id : null],
            ['estado' => $s4Actual ? $s4Actual->estado : null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s4
        $estadoSistema->update(['s4_id' => $s4Nueva->id]);

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportInyectoresShutdown(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::firstOrCreate();

        // Obtener la entrada s4 actual si existe
        $s4Actual = $estadoSistema->s4;

        // Crear una nueva entrada s4 con el estado inactivo y el comando del antecesor
        $s4Nueva = S4::create(array_merge(
            ['estado' => 'apagado'],
            $request->except('estado'),
            ['comando_id' => $s4Actual ? $s4Actual->comando_id : null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s4
        $estadoSistema->update(['s4_id' => $s4Nueva->id]);

        return response()->json(['message' => 'Apagado con exito'], 200);
    }

    public function reportFlujoConteo(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::firstOrCreate();

        // Obtener la entrada s5 actual si existe
        $s5Actual = $estadoSistema->s5;

        // Crear una nueva entrada s5 con la información proporcionada en el request y el comando del antecesor
        $s5Nueva = S5::create(array_merge(
            $request->all(),
            ['comando' => $s5Actual ? $s5Actual->comando : null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s5
        $estadoSistema->update(['s5_id' => $s5Nueva->id]);

        return response()->json(['message' => 'Conteo reportado exitosamente'], 200);
    }

    public function reportFlujoApagado(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::firstOrCreate();

        // Obtener la entrada s5 actual si existe
        $s5Actual = $estadoSistema->s5;

        // Crear una nueva entrada s5 con el estado inactivo y el comando del antecesor
        $s5Nueva = S5::create(array_merge(
            ['estado' => false],
            $request->except('estado'),
            ['comando' => $s5Actual ? $s5Actual->comando : null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s5
        $estadoSistema->update(['s5_id' => $s5Nueva->id]);

        return response()->json(['message' => 'Apagado con exito'], 200);
    }
}
