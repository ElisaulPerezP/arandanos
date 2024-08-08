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
use Illuminate\Support\Facades\Log;
use App\Jobs\Archivador;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    public function reportStop(Request $request)
    {
        
            // Obtener el estado actual del sistema desde la caché
        $estadosDelSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::find(1);
        });

        $s0Actual = Cache::rememberForever('estado_s0_actual', function () use ($estadosDelSistema) {
            return S0::find($estadoSistema->s0_id);

        });

        // Determinar el nuevo estado y el evento a emitir basado en el estado actual
        if ($s0Actual && $s0Actual->estado === 'Parada activada') {
            $nuevoEstado = 'Parada desactivada';
            $evento = new InicioDeAplicacion();
        } else {
            $nuevoEstado = 'Parada activada';
            $evento = new CultivoInactivo();
        }


        // Crear un nuevo registro S0 con el nuevo estado y el comando del antecesor
        $s0Final = [
            'estado' => $nuevoEstado,
            'comando_id' => $s0Actual->comando_id ?? null,
            'sensor3' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ];


        // Actualizar el estado del sistema con el nuevo s0_id
        $estadoSistemaActualizado = $estadosDelSistema->toArray();
        $estadoSistemaActualizado['s0_id'] = $s0Final['id'];

        // Actualizar la caché con los nuevos valores
        Cache::forever('estado_sistema', $estadoSistemaActualizado);
        Cache::forever('estado_s0_actual', $s0Final);

        // Despachar los trabajos para escribir en la base de datos
        Archivador::dispatch('s0', $s0Final);
        Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado);

        // Emitir el evento correspondiente
        event($evento);
        Log::info('Evento emitido ', [$nuevoEstado]);

        return response()->json(['message' => "Estado cambiado a '$nuevoEstado', evento emitido"], 200);
    }

    public function getTanquesCommand()
    {
        // Obtener el estado actual del sistema desde la caché
        $estado = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::find(1);
        });

        // Obtener el s1 actual desde la caché
        $s1Actual = Cache::rememberForever('estado_s1_actual', function () use ($estado) {
            return S1::find($estado->s1_id);
        });

        // Obtener el comando hardware desde la caché cargada en el AppServiceProvider
        $comandosHardware = Cache::get('comandos_hardware');

        // Obtener el comando hardware con el comando_id desde la caché
        $comandoHardware = null;
        if ($s1Actual && isset($s1Actual->comando_id)) {
            $comandoHardware = $comandosHardware->firstWhere('id', $s1Actual->comando_id);
        }

        // Si no se encuentra el comando hardware, usar el comando por defecto 'esperar'
        if (!$comandoHardware) {
            $comandoHardware = $comandosHardware->firstWhere('comando', 'esperar');
        }

        // Verificar si existe el comando
        if ($comandoHardware) {
            // Obtener el comando desde la relación s1
            $comandoExplicito = $comandoHardware->comando;

            // Retornar el comando si existe
            if ($comandoExplicito) {
                Log::info('Comando de tanques entregado por el controlador', [$comandoExplicito]);
                return response()->json(['command' => $comandoExplicito], 200);
            }
        }

        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportTanquesState(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::find(1);
        });

        // Obtener los comandos hardware desde la caché
        $comandosHardware = Cache::get('comandos_hardware');

        // Buscar el comando "esperar" en la caché
        $comandoEsperar = $comandosHardware->firstWhere('comando', 'esperar');

         // Verificar si existe el estadoSistema y la relación s1 en la caché
        if ($estadoSistema) {
            $s1Actual = Cache::rememberForever('estado_s1_actual', function () use ($estadoSistema) {
                return S1::find($estadoSistema->s1_id);
            });
        
        
            // Crear una nueva entrada s1 con la información nueva y la faltante
            $s1Nueva = [
                'estado' => $request->input('estado', $s1Actual->estado),
                'sensor1' => $request->input('sensor1', $s1Actual->sensor1),
                'sensor2' => $request->input('sensor2', $s1Actual->sensor2),
                'valvula14' => $request->input('valvula14', $s1Actual->valvula14),
                'comando_id' => $s1Actual->comando_id ?? $comandoEsperar->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Actualizar el estado del sistema con la nueva entrada s1
            $estadoSistemaActualizado = $estadoSistema->toArray();
            $estadoSistemaActualizado['s1_id'] = $s1Nueva['id'];

            // Actualizar la caché con los nuevos valores
            Cache::forever('estado_sistema', $estadoSistemaActualizado);
            Cache::forever('estado_s1_actual', $s1Nueva);

            // Despachar los trabajos para escribir en la base de datos
            Archivador::dispatch('s1', $s1Nueva);
            Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado);

            return response()->json(['message' => 'Estado reportado exitosamente'], 200);
        }

        // Retornar un mensaje de error si no se encuentra el estadoSistema o la relación s1
        return response()->json(['message' => 'Estado del sistema no encontrado'], 404);
    }

    public function reportTanquesShutdown(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema
        $estadoSistema = EstadoSistema::find(1);

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
                'comando_id' => $s1Actual->comando->id
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
        $estado = EstadoSistema::find(1);

        // Verificar si existe el estado y la relación s2
        if ($estado && $estado->s2) {
            // Obtener el comando desde la relación s2
            $comando = $estado->s2->comando;

            // Retornar el comando si existe
            if ($comando) {
                $action = $comando->comando;  // Aquí el comando es una cadena, no un JSON
                Log::info('Comando de selector entregado por el controlador es: ', ['action' => $action]);
                return response()->json(['actions' => [$action]], 200);  // Empaquetar en un array
            }
        }

        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }
    

    public function reportState(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::find(1);

        if (!$estadoSistema) {
            $estadoSistema = EstadoSistema::create(['s2_id' => null]);
        }

        // Obtener la entrada s2 actual si existe
        $s2Actual = $estadoSistema->s2;
        Log::info('Request received for s2:', $request->all());

        // Desglosar el campo 'status' del request
        $status = $request->input('status', []);
        $valvulas = [];
        foreach ($status as $key => $value) {
            $valvulas[$key] = ($value == 'encendida') ? 1 : 0;
        }

        // Crear una nueva entrada s2 con la información proporcionada en el request y el comando del antecesor
        $s2Nueva = S2::create(array_merge(
            ['estado' => 'funcionando'],
            $valvulas,
            ['comando_id' => $s2Actual ? $s2Actual->comando_id : null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s2
        $estadoSistema->update(['s2_id' => $s2Nueva->id]);

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportShutdown(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::find(1);

        // Obtener la entrada s2 actual si existe
        $s2Actual = $estadoSistema->s2;

        // Crear una nueva entrada s2 con el estado inactivo y el comando del antecesor
        $s2Nueva = S2::create(array_merge(
            ['estado' => 'apagado'],
            $request->except('estado'),
            ['comando_id' => null]
        ));

        // Actualizar el EstadoSistema con la nueva entrada s2
        $estadoSistema->update(['s2_id' => $s2Nueva->id]);

        return response()->json(['message' => 'Apagado con exito'], 200);
    }

    public function getImpulsoresCommand()
    {
        // Obtener el estado actual del sistema
        $estado = EstadoSistema::find(1);
    
        // Verificar si existe el estado y la relación s3
        if ($estado && $estado->s3) {
            // Obtener el comando desde la relación s3
            $comando = $estado->s3->comando;
    
            // Retornar el comando si existe
            if ($comando) {
                Log::info('Request sent by getImpulsoresCommand:', ['actions' => json_decode($comando->comando, true)]);
                return response()->json(['actions' => json_decode($comando->comando)], 200);
            }
        }
    
        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }
    

    public function reportImpulsoresState(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::find(1);

        // Obtener la entrada s3 actual si existe
        $s3Actual = $estadoSistema->s3;

        $validatedData = $request->validate([
            'pump1' => 'required|string',
            'pump2' => 'required|string',
            // Agrega aquí otros campos que sean necesarios
        ]);

        $data = array_merge($validatedData, [
            'comando_id' => $s3Actual ? $s3Actual->comando_id : null,
            'estado' => 'funcionando'
        ]);

       $s3Nueva = S3::create($data);

        // Actualizar el EstadoSistema con la nueva entrada s3
        $estadoSistema->update(['s3_id' => $s3Nueva->id]);
        Log::info('Request received for reportImpulsoresState:', $data);

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

public function reportImpulsoresShutdown(Request $request)
{
    Log::info('Request received for reportImpulsoresSHutdown:', $request->all());

    // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
    $estadoSistema = EstadoSistema::find(1);

    // Obtener la entrada s3 actual si existe
    $s3Actual = $estadoSistema->s3;

    // Buscar el comando en la tabla comando_hardware
    $comandoAccion = '{"actions":["pump1:off","pump2:off"]}';
    $comando = ComandoHardware::where('comando', $comandoAccion)->first();

    // Crear una nueva entrada s3 con el estado inactivo y el comando del antecesor
    $s3Nueva = S3::create(array_merge(
        $request->all(),
        ['comando_id' => $comando ? $comando->id : ($s3Actual ? $s3Actual->comando_id : null)],
    ));

    // Actualizar el EstadoSistema con la nueva entrada s3
    $estadoSistema->update(['s3_id' => $s3Nueva->id]);

    return response()->json(['message' => 'Apagado con exito'], 200);
}


    public function getInyectoresCommand()
    {
        // Obtener el estado actual del sistema
        $estado = EstadoSistema::find(1);

        // Verificar si existe el estado y la relación s4
        if ($estado && $estado->s4) {
            // Obtener el comando desde la relación s4
            $comando = $estado->s4->comando;

            // Retornar el comando si existe
            if ($comando) {
                $actions = json_decode($comando->comando, true)['actions'];
                return response()->json(['actions' => $actions], 200);
            }
        }
        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportInyectoresState(Request $request)
    {
        

        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::find(1);

        // Obtener la entrada s4 actual si existe
        $s4Actual = $estadoSistema->s4;

        $validatedData = $request->validate([
                'pump3' => 'required|string',
                'pump4' => 'required|string',
                // Agrega aquí otros campos que sean necesarios
            ]);

        $data = array_merge($validatedData, [
            'comando_id' => $s4Actual ? $s4Actual->comando_id : null,
            'estado' => 'funcionando'
        ]);

        $s4Nueva = S4::create($data);

        // Actualizar el EstadoSistema con la nueva entrada s4
        $estadoSistema->update(['s4_id' => $s4Nueva->id]);

        Log::info('Request received for ReportInyectoresSstate:', $request->all());

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportInyectoresShutdown(Request $request)
    {
        Log::info('Request received for reportInyectoresShutdown:', $request->all());

        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::find(1);
        // Obtener la entrada s4 actual si existe
        $s4Actual = $estadoSistema->s4;

        // Crear una nueva entrada s4 con el estado inactivo y el comando del antecesor
        $s4Nueva = S4::create(array_merge(
            ['estado' => $request->input('status', 'Apagado con exito')],
            ['comando_id' => $s4Actual ? $s4Actual->comando_id : null],
            ['pump3' => 'apagado'],
            ['pump4' => 'apagado'],

        ));

        // Actualizar el EstadoSistema con la nueva entrada s4
        $estadoSistema->update(['s4_id' => $s4Nueva->id]);

        return response()->json(['message' => 'Apagado con exito'], 200);
    }

    public function reportFlujoConteo(Request $request)
    {
        Log::info('Request received for reportFlujoCOnteo:', $request->all());

        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::find(1);

        // Obtener la entrada s5 actual si existe
        $s5Actual = $estadoSistema->s5;

        // Crear una nueva entrada s5 con la información proporcionada en el request y el comando del antecesor
        $s5Nueva = S5::create(array_merge(
            $request->all(),
            ['estado' => $request->input('status', 'Apagado con exito')],
            ['comando_id' => $s5Actual ? $s5Actual->comando_id : null],
        ));

        // Actualizar el EstadoSistema con la nueva entrada s5
        $estadoSistema->update(['s5_id' => $s5Nueva->id]);

        return response()->json(['message' => 'Conteo reportado exitosamente'], 200);
    }

    public function reportFlujoApagado(Request $request)
    {
        Log::info('Request received for reportFLujoApagado:', $request->all());

        // Buscar la entrada en la tabla EstadoSistema o crear una nueva si no existe
        $estadoSistema = EstadoSistema::find(1);

        // Obtener la entrada s5 actual si existe
        $s5Actual = $estadoSistema->s5;

        // Crear una nueva entrada s5 con el estado inactivo y el comando del antecesor
        $s5Nueva = S5::create(array_merge(
            ['estado' => $request->input('status', 'Apagado con exito')],
            ['comando_id' => $s5Actual ? $s5Actual->comando_id : null],
            ['flux1' => '0'],
            ['flux2' => '0'],
        ));

        // Actualizar el EstadoSistema con la nueva entrada s5
        $estadoSistema->update(['s5_id' => $s5Nueva->id]);

        return response()->json(['message' => 'Apagado con exito'], 200);
    }
}
