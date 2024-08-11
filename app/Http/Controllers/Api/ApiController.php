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
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function reportStop(Request $request)
    {

        // Obtener el estado actual del sistema desde la caché
        $estadosDelSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::find(1);
        });

        $s0Actual = Cache::rememberForever('estado_s0_actual', function () use ($estadosDelSistema) {
            return S0::find($estadosDelSistema['s0_id']);
        });

        // Determinar el nuevo estado y el evento a emitir basado en el estado actual
        if ($s0Actual && $s0Actual['estado'] === 'Parada activada') {
            $nuevoEstado = 'Parada desactivada';
            $evento = new InicioDeAplicacion();
        } else {
            $nuevoEstado = 'Parada activada';
            $evento = new CultivoInactivo();
        }

        // Generar un nuevo UUID para el nuevo registro S0
        $nuevoS0Id = Str::uuid();

        // Crear un nuevo registro S0 con el nuevo estado y el comando del antecesor
        $s0Final = [
            'id' => $nuevoS0Id,
            'estado' => $nuevoEstado,
            'comando_id' => $s0Actual['comando_id'] ?? null,
            'sensor3' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ];

        // Actualizar el estado del sistema con el nuevo s0_id
        $estadoSistemaActualizado = $estadosDelSistema;
        $estadoSistemaActualizado['s0_id'] = $nuevoS0Id;

        // Actualizar la caché con los nuevos valores
        Cache::forever('estado_sistema', $estadoSistemaActualizado);
        Cache::forever('estado_s0_actual', $s0Final);

        // Despachar los trabajos para escribir en la base de datos
        Archivador::dispatch('s0', $s0Final);

        Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadosDelSistema['id']]);

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
            return S1::find($estado['s1_id']);
        });

        // Obtener el comando hardware desde la caché cargada en el AppServiceProvider
        $comandosHardware = Cache::get('comandos_hardware');

        // Obtener el comando hardware con el comando_id desde la caché
        $comandoHardware = null;
        if ($s1Actual && isset($s1Actual['comando_id'])) {
            $comandoHardware = $comandosHardware->firstWhere('id', $s1Actual['comando_id']);
        }

        // Si no se encuentra el comando hardware, usar el comando por defecto 'esperar'
        if (!$comandoHardware) {
            $comandoHardware = $comandosHardware->firstWhere('comando', 'esperar');
        }

        // Verificar si existe el comando
        if ($comandoHardware) {
            // Obtener el comando desde la relación s1
            $comandoExplicito = $comandoHardware['comando'];

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
                return S1::find($estadoSistema['s1_id']);
            });

            // Generar un nuevo UUID para la nueva entrada s1
            $s1NuevaId = (string) Str::uuid();

            // Crear una nueva entrada s1 con la información nueva y la faltante
            $s1Nueva = [
                'id' => $s1NuevaId,
                'estado' => $request->input('estado', $s1Actual['estado']),
                'sensor1' => $request->input('sensor1', $s1Actual['sensor1']),
                'sensor2' => $request->input('sensor2', $s1Actual['sensor2']),
                'valvula14' => $request->input('valvula14', $s1Actual['valvula14']),
                'comando_id' => $s1Actual['comando_id'] ?? $comandoEsperar['id'],
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Actualizar el estado del sistema con la nueva entrada s1
            $estadoSistemaActualizado = $estadoSistema;
            $estadoSistemaActualizado['s1_id'] = $s1NuevaId;

            // Actualizar la caché con los nuevos valores
            Cache::forever('estado_sistema', $estadoSistemaActualizado);
            Cache::forever('estado_s1_actual', $s1Nueva);

            // Despachar los trabajos para escribir en la base de datos
            Archivador::dispatch('s1', $s1Nueva);
            Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);

            return response()->json(['message' => 'Estado reportado exitosamente'], 200);
        }

        // Retornar un mensaje de error si no se encuentra el estadoSistema o la relación s1
        return response()->json(['message' => 'Estado del sistema no encontrado'], 404);
    }

    public function reportTanquesShutdown(Request $request)
    {
        // Buscar la entrada en la tabla EstadoSistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::find(1);
        });

        // Verificar si existe el estadoSistema y la relación s1 en la caché
        if ($estadoSistema) {
            $s1Actual = Cache::rememberForever('estado_s1_actual', function () use ($estadoSistema) {
                return S1::find($estadoSistema['s1_id']);
            });
            
            // Obtener los comandos hardware desde la caché
            $comandosHardware = Cache::get('comandos_hardware');

            // Buscar el comando "esperar" en la caché
            $comandoEsperar = $comandosHardware->firstWhere('comando', 'esperar');

            // Generar un nuevo UUID para la nueva entrada s1
            $s1NuevaId = (string) Str::uuid();

            // Crear una nueva entrada s1 con el estado inactivo y copiar la información faltante
            $s1Nueva = [
                'id' => $s1NuevaId,
                'estado' => false,
                'sensor1' => $s1Actual['sensor1'],
                'sensor2' => $s1Actual['sensor2'],
                'valvula14' => $s1Actual['valvula14'],
                'comando_id' => $comandoEsperar['id'],
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Actualizar el estado del sistema con la nueva entrada s1
            $estadoSistemaActualizado = $estadoSistema;
            $estadoSistemaActualizado['s1_id'] = $s1NuevaId;

            // Actualizar la caché con los nuevos valores
            Cache::forever('estado_sistema', $estadoSistemaActualizado);
            Cache::forever('estado_s1_actual', $s1Nueva);

            // Despachar los trabajos para escribir en la base de datos
            Archivador::dispatch('s1', $s1Nueva);
            Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


            return response()->json(['message' => 'Apagado con éxito'], 200);
        }

        // Retornar un mensaje de error si no se encuentra el estadoSistema o la relación s1
        return response()->json(['message' => 'Estado del sistema no encontrado'], 404);
    }

    public function getSelectorCommand()
    {
        // Obtener el estado actual del sistema desde la caché
        $estado = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::find(1);
        });

        if ($estado) {
            $s2Actual = Cache::rememberForever('estado_s2_actual', function () use ($estado) {
                return S2::find($estado['s2_id']);
            });

            // Obtener el comando hardware desde la caché cargada en el AppServiceProvider
            $comandosHardware = Cache::get('comandos_hardware');

            $comandoHardware = null;
            if ($s2Actual && isset($s2Actual['comando_id'])) {
                $comandoHardware = $comandosHardware->firstWhere('id', $s2Actual['comando_id']);
            }

            // Si no se encuentra el comando hardware, usar el comando por defecto 'off:valvula1'
            if (!$comandoHardware) {
                $comandoHardware = $comandosHardware->firstWhere('comando', 'off:valvula1');
            }

            // Verificar si existe el comando
            if ($comandoHardware) {
                // Obtener el comando desde la relación s2
                $comandoExplicito = $comandoHardware['comando'];

                // Retornar el comando si existe
                if ($comandoExplicito) {
                    Log::info('Comando de selector entregado por el controlador', ['action' => $comandoExplicito]);
                    return response()->json(['actions' => [$comandoExplicito]], 200); // Empaquetar en un array
                }
            }
        }

        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }
    

    public function reportState(Request $request)
    {
       // Obtener el estado actual del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });


        if ($estadoSistema) {
            // Obtener la entrada s2 actual si existe en la caché
            $s2Actual = Cache::rememberForever('estado_s2_actual', function () use ($estadoSistema) {
                return S2::find($estadoSistema['s2_id']);
            });

            // Obtener los comandos hardware desde la caché
            $comandosHardware = Cache::get('comandos_hardware');

            $comandoHardware = null;
            if ($s2Actual && isset($s2Actual['comando_id'])) {
                $comandoHardware = $comandosHardware->firstWhere('id', $s2Actual['comando_id']);
            }

            // Si no se encuentra el comando hardware, usar el comando por defecto 'off:valvula1'
            if (!$comandoHardware) {
                $comandoHardware = $comandosHardware->firstWhere('comando', 'off:valvula1');
            }

            // Desglosar el campo 'status' del request
            $status = $request->input('status', []);
            $valvulas = [];
            foreach ($status as $key => $value) {
                $valvulas[$key] = ($value == 'encendida') ? 1 : 0;
            }

            // Crear una nueva entrada s2 con la información proporcionada en el request y el comando del antecesor
            $s2Nueva = [
                'id' => (string) Str::uuid(), // Asignar un UUID
                'estado' => 'funcionando',
                'comando_id' => $s2Actual['comando_id'] ?? $comandoHardware,
                'created_at' => now(),
                'updated_at' => now()
            ] + $valvulas;

            // Actualizar el estado del sistema con la nueva entrada s2
            $estadoSistemaActualizado = $estadoSistema;
            $estadoSistemaActualizado['s2_id'] = $s2Nueva['id'];

            // Actualizar la caché con los nuevos valores
            Cache::forever('estado_sistema', $estadoSistemaActualizado);
            Cache::forever('estado_s2_actual', $s2Nueva);

            // Despachar los trabajos para escribir en la base de datos
            Archivador::dispatch('s2', $s2Nueva);
            Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


            return response()->json(['message' => 'Estado reportado exitosamente'], 200);
        }

        // Retornar un mensaje de error si no se encuentra el estadoSistema
        return response()->json(['message' => 'Estado del sistema no encontrado'], 404);
    }

    public function reportShutdown(Request $request)
    {
        // Obtener el estado actual del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });
            
        // Obtener los comandos hardware desde la caché
        $comandosHardware = Cache::get('comandos_hardware');

        // Verificar si existe el estadoSistema en la caché
        if ($estadoSistema) {
            // Obtener la entrada s2 actual si existe en la caché
            $s2Actual = Cache::rememberForever('estado_s2_actual', function () use ($estadoSistema) {
                return S2::find($estadoSistema['s2_id']);
            });

            $comandoHardware = null;
            if ($s2Actual && isset($s2Actual['comando_id'])) {
                $comandoHardware = $comandosHardware->firstWhere('id', $s2Actual['comando_id']);
            }

            // Si no se encuentra el comando hardware, usar el comando por defecto 'off:valvula1'
            if (!$comandoHardware) {
                $comandoHardware = $comandosHardware->firstWhere('comando', 'off:valvula1');
            }

        // Crear una nueva entrada s2 con el estado inactivo y el comando del antecesor
        $s2Nueva = [
            'id' => (string) Str::uuid(), // Asignar un UUID
            'estado' => 'apagado',
            'comando_id' => $comandoHardware['id'],
            'created_at' => now(),
            'updated_at' => now()
        ] + $request->except('estado');

        // Actualizar el estado del sistema con la nueva entrada s2
        $estadoSistemaActualizado = $estadoSistema;
        $estadoSistemaActualizado['s2_id'] = $s2Nueva['id'];

        // Actualizar la caché con los nuevos valores
        Cache::forever('estado_sistema', $estadoSistemaActualizado);
        Cache::forever('estado_s2_actual', $s2Nueva);

        // Despachar los trabajos para escribir en la base de datos
        Archivador::dispatch('s2', $s2Nueva);
        Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);

        return response()->json(['message' => 'Apagado con exito'], 200);
        }
    }

    public function getImpulsoresCommand()
    {
        // Obtener el estado actual del sistema desde la caché
        $estado = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });
        // Verificar si existe el estado y la relación s3 en la caché
        if ($estado) {
            $s3Actual = Cache::rememberForever('estado_s3_actual', function () use ($estado) {
                return S3::find($estado['s3_id']);
            });

            // Obtener los comandos hardware desde la caché
            $comandosHardware = Cache::get('comandos_hardware');

            // Obtener el comando desde la caché utilizando el comando_id de s3
            $comando = null;
            if ($s3Actual && isset($s3Actual['comando_id'])) {
                $comando = $comandosHardware->firstWhere('id', $s3Actual['comando_id']);
            }

            // Verificar si existe el comando
            if ($comando) {
                $action = json_decode($comando['comando'], true);
                Log::info('Request sent by getImpulsoresCommand:', ['actions' => $action]);
                return response()->json(['actions' => $action], 200);
            }
        } 
        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }
    

    public function reportImpulsoresState(Request $request)
    {
        // Obtener el estado actual del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });

        // Obtener la entrada s3 actual desde la caché
        $s3Actual = Cache::rememberForever('estado_s3_actual', function () use ($estadoSistema) {
            return S3::find($estadoSistema['s3_id']);
        });

        // Validar los datos del request
        $validatedData = $request->validate([
            'pump1' => 'required|string',
            'pump2' => 'required|string',
            // Agrega aquí otros campos que sean necesarios
        ]);

        // Obtener los comandos hardware desde la caché
        $comandosHardware = Cache::get('comandos_hardware');

        $comandoHardware = null;
        if ($s3Actual && isset($s2Actual['comando_id'])) {
            $comandoHardware = $comandosHardware->firstWhere('id', $s3Actual['comando_id']);
        }

        // Si no se encuentra el comando hardware, usar el comando por defecto 'off:valvula1'
        if (!$comandoHardware) {
            $comandoHardware = $comandosHardware->firstWhere('comando', '{"actions":["pump1:off","pump2:off"]}');
        }

        // Crear una nueva entrada s3 con la información proporcionada en el request y el comando del antecesor
        $s3Nueva = array_merge($validatedData, [
            'comando_id' => $s3Actual ? $s3Actual['comando_id'] : $comandoHardware,
            'estado' => 'funcionando',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Asignar un UUID a la nueva entrada s3
        $s3Nueva['id'] = (string) Str::uuid();

        // Actualizar el estado del sistema con la nueva entrada s3
        $estadoSistemaActualizado = $estadoSistema;
        $estadoSistemaActualizado['s3_id'] = $s3Nueva['id'];

        // Actualizar la caché con los nuevos valores
        Cache::forever('estado_sistema', $estadoSistemaActualizado);
        Cache::forever('estado_s3_actual', $s3Nueva);

        // Despachar los trabajos para escribir en la base de datos
        Archivador::dispatch('s3', $s3Nueva);
        Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);

        Log::info('Request received for reportImpulsoresState:', $s3Nueva);

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportImpulsoresShutdown(Request $request)
    {
        // Obtener el estado actual del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });

        if ($estadoSistema) {
            // Obtener la entrada s3 actual desde la caché
            $s3Actual = Cache::rememberForever('estado_s3_actual', function () use ($estadoSistema) {
                return S3::find($estadoSistema['s3_id']);
            });

            // Obtener los comandos hardware desde la caché
            $comandosHardware = Cache::get('comandos_hardware');

            // Buscar el comando en la caché
            $comandoAccion = '{"actions":["pump1:off","pump2:off"]}';
            $comando = $comandosHardware->firstWhere('comando', $comandoAccion);

            // Generar un nuevo UUID para la nueva entrada s3
            $s3NuevaId = (string) Str::uuid();

            // Crear una nueva entrada s3 con el estado inactivo y el comando del antecesor
            $s3Nueva = array_merge(
                $request->all(),
                [
                    'id' => $s3NuevaId,
                    'comando_id' => $comando ? $comando['id'] : ($s3Actual ? $s3Actual['comando_id'] : null),
                    'estado' => 'apagado',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Actualizar el estado del sistema con la nueva entrada s3
            $estadoSistemaActualizado = $estadoSistema;
            $estadoSistemaActualizado['s3_id'] = $s3NuevaId;

            // Actualizar la caché con los nuevos valores
            Cache::forever('estado_sistema', $estadoSistemaActualizado);
            Cache::forever('estado_s3_actual', $s3Nueva);

            // Despachar los trabajos para escribir en la base de datos
            Archivador::dispatch('s3', $s3Nueva);
            Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);

            return response()->json(['message' => 'Apagado con éxito'], 200);
        }

        // Retornar un mensaje de error si no se encuentra el estadoSistema
        return response()->json(['message' => 'Estado del sistema no encontrado'], 404);
    }


    public function getInyectoresCommand()
    {
        // Obtener el estado actual del sistema desde la caché
        $estado = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });

        if ($estado) {
            // Obtener la entrada s4 actual desde la caché
            $s4Actual = Cache::rememberForever('estado_s4_actual', function () use ($estado) {
                return S4::find($estado['s4_id']);
            });

            // Obtener los comandos hardware desde la caché
            $comandosHardware = Cache::get('comandos_hardware');

            // Obtener el comando desde la caché utilizando el comando_id de s4
            $comando = null;
            if ($s4Actual && isset($s4Actual['comando_id'])) {
                $comando = $comandosHardware->firstWhere('id', $s4Actual['comando_id']);
            }

            // Verificar si existe el comando
            if ($comando) {
                $actions = json_decode($comando['comando'], true)['actions'];
                return response()->json(['actions' => $actions], 200);
            }
        }

        // Retornar un mensaje de error si no se encuentra el comando
        return response()->json(['message' => 'Comando no encontrado'], 404);
    }

    public function reportInyectoresState(Request $request)
    {
        // Obtener el estado actual del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });

        // Obtener la entrada s4 actual desde la caché
        $s4Actual = Cache::rememberForever('estado_s4_actual', function () use ($estadoSistema) {
            return S4::find($estadoSistema['s4_id']);
        });

        // Validar los datos del request
        $validatedData = $request->validate([
            'pump3' => 'required|string',
            'pump4' => 'required|string',
            // Agrega aquí otros campos que sean necesarios
        ]);

        // Obtener los comandos hardware desde la caché
        $comandosHardware = Cache::get('comandos_hardware');

        // Obtener el comando desde la caché utilizando el comando_id de s4
        $comandoHardware = null;
        if ($s4Actual && isset($s4Actual['comando_id'])) {
            $comandoHardware = $comandosHardware->firstWhere('id', $s4Actual['comando_id']);
        }

        // Si no se encuentra el comando hardware, usar el comando por defecto 'off:pump3' y 'off:pump4'
        if (!$comandoHardware) {
            $comandoHardware = $comandosHardware->firstWhere('comando', '{"actions":["pump3:off","pump4:off"]}');
        }

        // Generar un nuevo UUID para la nueva entrada s4
        $s4NuevaId = (string) Str::uuid();

        // Crear una nueva entrada s4 con la información proporcionada en el request y el comando del antecesor
        $s4Nueva = array_merge($validatedData, [
            'id' => $s4NuevaId,
            'comando_id' => $s4Actual ? $s4Actual['comando_id'] : $comandoHardware,
            'estado' => 'funcionando',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Actualizar el estado del sistema con la nueva entrada s4
        $estadoSistemaActualizado = $estadoSistema;
        $estadoSistemaActualizado['s4_id'] = $s4NuevaId;

        // Actualizar la caché con los nuevos valores
        Cache::forever('estado_sistema', $estadoSistemaActualizado);
        Cache::forever('estado_s4_actual', $s4Nueva);

        // Despachar los trabajos para escribir en la base de datos
        Archivador::dispatch('s4', $s4Nueva);
        Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);

        Log::info('Request received for ReportInyectoresState:', $s4Nueva);

        return response()->json(['message' => 'Estado reportado exitosamente'], 200);
    }

    public function reportInyectoresShutdown(Request $request)
    {

            Log::info('Request received for reportInyectoresShutdown:', $request->all());

        // Obtener el estado actual del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });

        // Obtener la entrada s4 actual desde la caché
        $s4Actual = Cache::rememberForever('estado_s4_actual', function () use ($estadoSistema) {
            return S4::find($estadoSistema['s4_id']);
        });

        // Obtener los comandos hardware desde la caché
        $comandosHardware = Cache::get('comandos_hardware');

        // Obtener el comando desde la caché utilizando el comando_id de s4
        $comandoHardware = null;
        if ($s4Actual && isset($s4Actual['comando_id'])) {
            $comandoHardware = $comandosHardware->firstWhere('id', $s4Actual['comando_id']);
        }

        // Si no se encuentra el comando hardware, usar el comando por defecto 'off:pump3' y 'off:pump4'
        if (!$comandoHardware) {
            $comandoHardware = $comandosHardware->firstWhere('comando', '{"actions":["pump3:off","pump4:off"]}');
        }

        // Generar un nuevo UUID para la nueva entrada s4
        $s4NuevaId = (string) Str::uuid();

        // Crear una nueva entrada s4 con el estado inactivo y el comando del antecesor
        $s4Nueva = [
            'id' => $s4NuevaId,
            'estado' => 'apagado',
            'comando_id' => $comandoHardware ? $comandoHardware['id'] : null,
            'pump3' => 'apagado',
            'pump4' => 'apagado',
            'created_at' => now(),
            'updated_at' => now()
        ];

        // Actualizar el estado del sistema con la nueva entrada s4
        $estadoSistemaActualizado = $estadoSistema;
        $estadoSistemaActualizado['s4_id'] = $s4NuevaId;

        // Actualizar la caché con los nuevos valores
        Cache::forever('estado_sistema', $estadoSistemaActualizado);
        Cache::forever('estado_s4_actual', $s4Nueva);

        // Despachar los trabajos para escribir en la base de datos
        Archivador::dispatch('s4', $s4Nueva);
        Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);

        return response()->json(['message' => 'Apagado con éxito'], 200);
    }

    public function reportFlujoConteo(Request $request)
    {
        Log::info('Request received for reportFlujoConteo:', $request->all());

        // Obtener el estado actual del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });
    
        // Obtener la entrada s5 actual desde la caché
        $s5Actual = Cache::rememberForever('estado_s5_actual', function () use ($estadoSistema) {
            return S5::find($estadoSistema['s5_id']);
        });
    
        // Validar los datos del request
        $validatedData = $request->validate([
            'flux1' => 'required|integer',
            'flux2' => 'required|integer',
        ]);
    
        // Generar un nuevo UUID para la nueva entrada s5
        $s5NuevaId = (string) Str::uuid();
    
        // Crear una nueva entrada s5 con la información proporcionada en el request y el comando del antecesor
        $s5Nueva = array_merge($validatedData, [
            'id' => $s5NuevaId,
            'estado' => $request->input('status', 'Apagado con exito'),
            'comando_id' => $s5Actual ? $s5Actual['comando_id'] : null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    
        // Actualizar el estado del sistema con la nueva entrada s5
        $estadoSistemaActualizado = $estadoSistema;
        $estadoSistemaActualizado['s5_id'] = $s5NuevaId;
    
        // Actualizar la caché con los nuevos valores
        Cache::forever('estado_sistema', $estadoSistemaActualizado);
        Cache::forever('estado_s5_actual', $s5Nueva);
    
        // Despachar los trabajos para escribir en la base de datos
        Archivador::dispatch('s5', $s5Nueva);
        Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);
    
        return response()->json(['message' => 'Conteo reportado exitosamente'], 200);
    }

    public function reportFlujoApagado(Request $request)
    {

        Log::info('Request received for reportFLujoApagado:', $request->all());

        // Obtener el estado actual del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::firstOrCreate(['id' => 1]);
        });

        // Obtener la entrada s5 actual desde la caché
        $s5Actual = Cache::rememberForever('estado_s5_actual', function () use ($estadoSistema) {
            return S5::find($estadoSistema['s5_id']);
        });

        // Generar un nuevo UUID para la nueva entrada s5
        $s5NuevaId = (string) Str::uuid();

        // Crear una nueva entrada s5 con el estado inactivo y el comando del antecesor
        $s5Nueva = [
            'id' => $s5NuevaId,
            'estado' => $request->input('status', 'Apagado con exito'),
            'comando_id' => $s5Actual ? $s5Actual['comando_id'] : null,
            'flux1' => '0',
            'flux2' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ];

        // Actualizar el estado del sistema con la nueva entrada s5
        $estadoSistemaActualizado = $estadoSistema;
        $estadoSistemaActualizado['s5_id'] = $s5NuevaId;

        // Actualizar la caché con los nuevos valores
        Cache::forever('estado_sistema', $estadoSistemaActualizado);
        Cache::forever('estado_s5_actual', $s5Nueva);

        // Despachar los trabajos para escribir en la base de datos
        Archivador::dispatch('s5', $s5Nueva);
        Archivador::dispatch('estado_sistemas', $estadoSistemaActualizado, 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);

        return response()->json(['message' => 'Apagado con exito'], 200);
    }
}
