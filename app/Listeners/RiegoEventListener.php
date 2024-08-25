<?php

namespace App\Listeners;

use App\Events\RiegoEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Models\S2;
use App\Models\S3;
use App\Models\S4;
use App\Models\S5;
use App\Models\S1;
use App\Models\ComandoHardware;
use App\Models\Comando;
use App\Models\EstadoSistema;
use App\Models\Programacion;
use Carbon\Carbon;
use Database\Factories\S2Factory;
use Illuminate\Support\Facades\Cache;
use App\Jobs\Archivador;
use Illuminate\Support\Str;


class RiegoEventListener implements ShouldQueue
{
    protected $timeout = 180; // Tiempo límite en segundos para completar el riego

    /**
     * Handle the event.
     */
    public function handle(RiegoEvent $event)
    {
        Log::info('Riego event handled', ['descripcion' => $event->programacion]);

        $programacion = Cache::get("programacion_{$event->programacion['id']}");

        $startTime = Carbon::now();
        $timeoutTime = $startTime->addSeconds($this->timeout);

        try {
            //Log::info('si esta entrando al try');
            // Encender electrovalvulas
            Log::info('En zona 1');

            $this->encenderElectrovalvulas($programacion);
            Log::info('En zona 2');

            // Encender motor principal
            $this->encenderMotorPrincipal();
            Log::info('En zona 3');

            // Inyectar fertilizante
            $this->inyectarFertilizante($programacion);
            Log::info('En zona 4');

            // Monitorear flujo
            $resultado = $this->monitorearFlujo($timeoutTime, $programacion);
            Log::info('En zona 5');

            // Llenar tanques si el riego fue exitoso
            if ($resultado) {
                $this->llenarTanques();
                Log::info('En zona 6');

                $this->marcarEventoExitoso($programacion);
            } else {
                $this->marcarEventoFallido($programacion);
                
            }
            Log::info('En zona 7');

            // Apagar todos los sistemas después del riego
            $this->apagarElectrovalvulas($programacion);
            Log::info('En zona 8');

            $this->apagarMotorPrincipal();
            Log::info('En zona 9');

            $this->apagarInyectores();
            Log::info('En zona 10');


        } catch (\Exception $e) {
            Log::error('Error en el manejo del evento de riego', ['descripcion' => $programacion, 'error' => $e->getMessage()]);
            $this->marcarEventoFallido($programacion);

            // Asegurarse de apagar todos los sistemas en caso de error
            $this->apagarElectrovalvulas($programacion);
            $this->apagarMotorPrincipal();
            $this->apagarInyectores();
        }
    }

    protected function encenderElectrovalvulas($programacion)
    {

        // Obtener el comando desde la caché
        $comando = Cache::rememberForever("comando_{$programacion['comando_id']}", function () use ($programacion) {
            return Comando::find($programacion['comando_id']);
        });

        // Parsear la descripción del comando para obtener el camellon
        parse_str(str_replace(',', '&', $comando['descripcion']), $params);
        $camellon = $params['camellon'];

        // Obtener el estado del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::first()->toArray();
        });

         // Obtener la configuración actual de s2 desde la caché como objeto
        $s2Actual = Cache::rememberForever("estado_s2_actual", function () use ($estadoSistema) {
            return S2::find($estadoSistema["s2_id"])->toArray();
        });
        

        // Clonar la configuración actual para crear un nuevo estado de S2
        $s2Final = $s2Actual;
        $nuevoS2Id = (string) Str::uuid();  // Generar un nuevo UUID
        $s2Final['id'] = $nuevoS2Id;

        // Buscar el comando de hardware correcto en la caché como objeto
        $comandoBuscado = 'on:valvula' . $camellon;
        $comandoHardware = Cache::rememberForever("comando_hardware_{$comandoBuscado}", function () use ($comandoBuscado) {
            return ComandoHardware::where('sistema', 's2')
                                ->where('comando', $comandoBuscado)
                                ->first()->toArray();
        });


        if ($comandoHardware) {
            $s2Final['comando_id'] = $comandoHardware['id'];
        } else {
            // Registrar un mensaje en el log si no se encuentra el comando
            //Log::info("El comando para encender la electrovalvula del camellon $camellon no pudo ser encontrado");
        }
    
        // Guardar el nuevo estado en la caché
        Cache::forever('estado_s2_actual', $s2Final);
    
        // Despachar los trabajos para actualizar la base de datos
        Archivador::dispatch('s2', $s2Final);

        Archivador::dispatch('estado_sistemas', ['s2_id' => $s2Final['id']], 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);
    
        //Log::info('Electrovalvula del camellon ' . $camellon . ' encendida');
    }

    protected function encenderMotorPrincipal()
{

    $estadoSistema = Cache::rememberForever('estado_sistema', function () {
        return EstadoSistema::first()->toArray();
    });

    // Obtener la configuración actual de s3 desde la caché
    $s3Actual = Cache::rememberForever('estado_s3_actual', function () use ($estadoSistema) {
        return S3::find($estadoSistema['s3_id'])->toArray();
    });

    // Replicar el estado actual de s3 para modificarlo
    $s3Final = $s3Actual;
    $nuevoS3Id = (string) Str::uuid();  // Generar un nuevo UUID
    $s3Final['id'] = $nuevoS3Id;

    // Buscar el comando de hardware correcto
    $comandoBuscado = '{"actions":["pump1:on","pump2:on"]}';
    $comandoHardware = Cache::rememberForever("comando_hardware_s3_{$comandoBuscado}", function () use ($comandoBuscado) {
        return ComandoHardware::where('sistema', 's3')
                              ->where('comando', $comandoBuscado)
                              ->first();
    });

    // Si se encuentra el comando de hardware, asignar el comando_id
    if ($comandoHardware) {
        $s3Final['comando_id'] = $comandoHardware['id'];
    } else {
        // Registrar un mensaje en el log si no se encuentra el comando
        //Log::info("El comando para encender las bombas principales no pudo ser encontrado");
    }

    // Actualizar la caché con los nuevos valores
    Cache::forever('estado_s3_actual', $s3Final);

    // Actualizar el estado del sistema en caché
    $estadoSistema['s3_id'] = $nuevoS3Id;
    Cache::forever('estado_sistema', $estadoSistema);

    // Despachar los trabajos para escribir en la base de datos
    Archivador::dispatch('s3', $s3Final);
    Archivador::dispatch('estado_sistemas',  ['s3_id' => $estadoSistema['s3_id']], 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


    //Log::info('Motor principal encendido');
}

protected function inyectarFertilizante($programacion)
{
    Log::info('En zona 11');

    // Obtener el comando desde la caché
    $comando = Cache::rememberForever("comando_{$programacion['comando_id']}", function () use ($programacion) {
        return Comando::find($programacion['comando_id']);
    });
    Log::info('En zona 12');

    // Parsear la descripción del comando para obtener la concentración
    parse_str(str_replace(',', '&', $comando['descripcion']), $params);
    $concentracion = $params['concentracion'];
    Log::info('En zona 13');

    // Obtener el estado del sistema desde la caché
    $estadoSistema = Cache::rememberForever('estado_sistema', function () {
        return EstadoSistema::first()->toArray();
    });
    Log::info('En zona 14');

    // Obtener la configuración actual de s4 desde la caché
    $s4Actual = Cache::rememberForever("estado_s4_actual", function () use ($estadoSistema) {
        return S4::find($estadoSistema["s4_id"]);
    });
    Log::info('En zona 15');

    // Clonar la configuración actual para crear un nuevo estado de S4
    $s4Final = $s4Actual;
    $nuevoS4Id = (string) Str::uuid();  // Generar un nuevo UUID
    $s4Final['id']= $nuevoS4Id;
    Log::info('En zona 16');

    // Calcular el comando para los inyectores basado en la concentración
    $comandoHardware = $this->calcularComandoInyectores($concentracion);
    Log::info('El comando hardware leleccionado por el calculador de inyectores fue:' . json_encode($comandoHardware));
    
    $s4Final['comando_id'] = $comandoHardware['id'];
    Log::info('En zona 16.2');

    $s4Final['estado'] = 'inyectando';
    Log::info('En zona 16.3');

    $s4Final['pump3'] = true;
    Log::info('En zona 16.4');

    $s4Final['pump4'] = false;
    Log::info('En zona 17');

    // Actualizar la caché con el nuevo estado
    Cache::forever('estado_s4_actual', $s4Final);

    // Actualizar el estado del sistema en la caché
    $estadoSistema['s4_id'] = $nuevoS4Id;
    Cache::forever('estado_sistema', $estadoSistema);
    Log::info('En zona 18');

    // Despachar los trabajos para actualizar la base de datos
    Archivador::dispatch('s4', $s4Final);

    Log::info('En zona 19');

    Archivador::dispatch('estado_sistemas', ['s4_id' => $estadoSistema['s4_id']], 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


    //Log::info('Fertilizante inyectado con concentración ' . $concentracion);

}

    protected function calcularComandoInyectores($concentracion)
    {


        $comandoBuscado = '{"actions":["pump3:on:' . ($concentracion * 10) . '","pump4:off:1"]}';
        Log::info('el comando de hardware buscado es: ' . $comandoBuscado);
        // Buscar el comando de hardware correspondiente en la caché
        $comandoHardware = Cache::rememberForever("comando_hardware_s4_{$comandoBuscado}", function () use ($comandoBuscado) {
            return ComandoHardware::where('sistema', 's4')
                                  ->where('comando', $comandoBuscado)
                                  ->first();
        });

        Log::info('el comando de hardware encontrado fue: '. json_encode($comandoHardware));
    
        if ($comandoHardware) {
            return $comandoHardware;
        } else {
            // Manejar el caso donde no se encuentra el comando, si es necesario
            //Log::warning("El comando de inyectores para la concentración {$concentracion} no pudo ser encontrado");
        }
    }

    //TODO: revisar el sleep que hay en el siguiente metodo
    protected function monitorearFlujo($timeoutTime, $programacion)
    {

        // Obtener el comando desde la caché
        $comando = Cache::rememberForever("comando_{$programacion['comando_id']}", function () use ($programacion) {
            return Comando::find($programacion['comando_id']);
        });

        // Parsear la descripción del comando para obtener el volumen
        parse_str(str_replace(',', '&', $comando['descripcion']), $params);
        $volumen = $params['volumen'];

        // Calcular el volumen esperado en términos de cuentas
        $cuentasEsperadas = $volumen * 30;

        // Inicializar el contador de flujo
        $flujoAcumulado = 0;
        
        while (Carbon::now()->lessThan($timeoutTime)) {
            // Obtener el estado del sistema desde la caché
            $estadoSistema = Cache::rememberForever('estado_sistema', function () {
                return EstadoSistema::first()->toArray();
            });
    
            // Obtener la configuración actual de s5 desde la caché como objeto
            $s5Actual = Cache::rememberForever("estado_s5_actual", function () use ($estadoSistema) {
                return S5::find($estadoSistema["s5_id"]);
            });
    
            // Obtener el estado actual del flujo
            $flujoActual1 = $s5Actual['flux1'];
            $flujoActual2 = $s5Actual['flux2'];
            $flujoActual = $flujoActual1 + $flujoActual2;
    
            // Acumular el flujo
            $flujoAcumulado += $flujoActual;
    
        // Verificar si el flujo acumulado cumple con los requisitos
        if ($flujoAcumulado >= $cuentasEsperadas) {
                // Crear una nueva instancia de S5 para el nuevo estado
                $s5Final = $s5Actual;
                $s5Final['flux1'] = 0;
                $s5Final['flux2'] = 0;
                $s5Final['id'] = (string) Str::uuid();

                // Guardar el nuevo estado en la caché
                Cache::forever('estado_s5_actual', $s5Final);

                // Actualizar el estado del sistema en la caché
                $estadoSistema['s5_id'] = $s5Final['id'];
                Cache::forever('estado_sistema', $estadoSistema);

                // Despachar los trabajos para actualizar la base de datos
                Archivador::dispatch('s5', $s5Final);
                Archivador::dispatch('estado_sistemas', ['s5_id' => $estadoSistema['s5_id']], 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


                Log::info('El flujo cumple con los requisitos', ['flujo_acumulado' => $flujoAcumulado, 'cuentas_esperadas' => $cuentasEsperadas]);
                return true;
            }
            // Esperar 2 segundos antes de la siguiente verificación
            sleep(2);
        }

        // Si el flujo no cumple con los requisitos en el tiempo límite, retornar false
        $s5Final = $s5Actual;
        $s5Final['flux1'] = 0;
        $s5Final['flux2'] = 0;
        $s5Final['id'] = (string) Str::uuid();

        // Guardar el nuevo estado en la caché
        Cache::forever('estado_s5_actual', $s5Final);

        // Actualizar el estado del sistema en la caché
        $estadoSistema['s5_id'] = $s5Final['id'];
        Cache::forever('estado_sistema', $estadoSistema);

        // Despachar los trabajos para actualizar la base de datos
        Archivador::dispatch('s5', $s5Final);
        Archivador::dispatch('estado_sistemas', ['s5_id' => $estadoSistema['s5_id']], 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


        Log::info('El flujo no cumple con los requisitos', ['flujo_acumulado' => $flujoAcumulado, 'cuentas_esperadas' => $cuentasEsperadas]);
        return false;
    }
    protected function llenarTanques()
    {
        // Obtener la configuración actual de s1 desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::first()->toArray();
        });

        // Obtener la configuración actual de s1 desde la caché
        $s1Actual = Cache::rememberForever("estado_s1_actual", function () use ($estadoSistema) {
            return S1::find($estadoSistema["s1_id"]);
        });

        // Clonar la configuración actual para crear un nuevo estado de S1
        $s1Final = $s1Actual;
        $s1Final->id = (string) Str::uuid();  // Asignar un nuevo UUID

        // Buscar el comando de hardware correcto en la caché
        $comandoBuscado = 'llenar';
        $comandoHardware = Cache::rememberForever("comando_hardware_{$comandoBuscado}", function () use ($comandoBuscado) {
            return ComandoHardware::where('sistema', 's1')
                                ->where('comando', $comandoBuscado)
                                ->first();
        });

        // Si se encuentra el comando de hardware, asignar el comando_id
        if ($comandoHardware) {
            $s1Final['comando_id'] = $comandoHardware['id'];
        } else {
            // Registrar un mensaje en el log si no se encuentra el comando
            Log::error("El comando de llenado de tanques no pudo ser encontrado");
        }

        // Guardar el nuevo estado en la caché
        Cache::forever('estado_s1_actual', $s1Final);


        // Actualizar el estado del sistema con la nueva entrada s1
        $estadoSistemaActualizado = $estadoSistema;
        $estadoSistemaActualizado['s1_id'] = $s1Final['id'];

        // Guardar la nueva configuración del sistema en la caché
        Cache::forever('estado_sistema', $estadoSistemaActualizado);

        // Despachar los trabajos para actualizar la base de datos
        Archivador::dispatch('s1', $s1Final);
        Archivador::dispatch('estado_sistemas', ['s1_id' => $estadoSistemaActualizado['s1_id']], 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


        //Log::info('Tanques llenando');
    }

    protected function marcarEventoExitoso($programacion)
    {
        // Cargar la programación desde la caché utilizando su ID
        $programacionCacheKey = "programacion_{$programacion['id']}";
        $programacionActual = Cache::get($programacionCacheKey);

        if ($programacionActual) {
            // Actualizar el estado de la programación
            $programacionActual['estado'] = 'ejecutado_exitosamente';
            $programacionActual['updated_at'] = now();

            // Guardar la programación actualizada en la caché
            Cache::forever($programacionCacheKey, $programacionActual);

            // Despachar la actualización para que se refleje en la base de datos
            Archivador::dispatch('programacions',$programacionActual , 'update', ['column' => 'id', 'value' => $programacion['id']]);


            // Registrar en el log el evento exitoso
            Log::info('Evento de riego exitoso', [
                'programacion_id' => $programacionActual['id'],
        ]);
        } else {
            Log::error('No se pudo encontrar la programación en la caché', ['programacion_id' => $programacion['id']]);
        }
    }

    protected function marcarEventoFallido($programacion)
    {
         // Cargar la programación desde la caché utilizando su ID
        $programacionCacheKey = "programacion_{$programacion['id']}";
        $programacionActual = Cache::get($programacionCacheKey);

        if ($programacionActual) {
            // Actualizar el estado de la programación
            $programacionActual['estado'] = 'fallido';
            $programacionActual['updated_at'] = now();

            // Actualizar la caché con el estado modificado
            Cache::put($programacionCacheKey, $programacionActual);

            // Guardar la programación actualizada en la caché
            Cache::forever($programacionCacheKey, $programacionActual);

            // Despachar la actualización para que se refleje en la base de datos
            Archivador::dispatch('programacions',$programacionActual , 'update', ['column' => 'id', 'value' => $programacion['id']]);
            
            // Registrar en el log el evento fallido
            Log::info('Evento de riego fallo', [
                'programacion_id' => $programacionActual['id'],
            ]);
        } else {
            Log::error('No se pudo encontrar la programación en la caché', ['programacion_id' => $programacion['id']]);
        }

    }
    protected function apagarElectrovalvulas($programacion)
    {

         // Obtener el comando desde la caché
        $comando = Cache::rememberForever("comando_{$programacion['comando_id']}", function () use ($programacion) {
            return Comando::find($programacion['comando_id']);
        });

        // Parsear la descripción del comando para obtener el camellon
        parse_str(str_replace(',', '&', $comando['descripcion']), $params);
        $camellon = $params['camellon'];

        // Obtener el estado del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::first()->toArray();
        });

        // Obtener la configuración actual de s2 desde la caché
        $s2Actual = Cache::rememberForever("estado_s2_actual", function () use ($estadoSistema) {
            return S2::find($estadoSistema['s2_id'])->toArray();
        });
       

        // Clonar la configuración actual para crear un nuevo estado de S2
        $s2Final = $s2Actual;
        $nuevoS2Id = (string) Str::uuid();  // Generar un nuevo UUID
        $s2Final['id'] = $nuevoS2Id;

        // Buscar el comando de hardware correcto en la caché
        $comandoBuscado = 'off:valvula' . $camellon;
        $comandoHardware = Cache::rememberForever("comando_hardware_{$comandoBuscado}", function () use ($comandoBuscado) {
            return ComandoHardware::where('sistema', 's2')
                                ->where('comando', $comandoBuscado)
                                ->first()->toArray();
        });

        // Si se encuentra el comando de hardware, asignar el comando_id
        if ($comandoHardware) {
            $s2Final['comando_id'] = $comandoHardware['id'];
        } else {
            Log::error("El comando de apagado de válvulas para el camellon $camellon no pudo ser encontrado");
        }
       

                
        // Guardar el nuevo estado en la caché
        Cache::forever('estado_s2_actual', $s2Final);

        // Actualizar el estado del sistema con la nueva entrada s2
        $estadoSistema['s2_id'] = $nuevoS2Id;
        Cache::forever('estado_sistema', $estadoSistema);
        // Despachar los trabajos para actualizar la base de datos
        Archivador::dispatch('s2', $s2Final);
        Archivador::dispatch('estado_sistemas', ['s2_id' => $s2Final['id']], 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


        //Log::info('Electrovalvula del camellon ' . $camellon . ' apagada');
    }
    

    protected function apagarMotorPrincipal()
    {

        // Obtener el estado del sistema desde la caché
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::first()->toArray();
        });

        // Obtener la configuración actual de s3 desde la caché
        $s3Actual = Cache::rememberForever("estado_s3_actual", function () use ($estadoSistema) {
            return S3::find($estadoSistema['s3_id']);
        });

        // Clonar la configuración actual para crear un nuevo estado de S3
        $s3Final = $s3Actual;
        $nuevoS3Id = (string) Str::uuid();  // Generar un nuevo UUID
        $s3Final['id'] = $nuevoS3Id;

        // Buscar el comando de hardware correcto en la caché
        $comandoBuscado = '{"actions":["pump1:off","pump2:off"]}';
        $comandoHardware = Cache::rememberForever("comando_hardware_s3_{$comandoBuscado}", function () use ($comandoBuscado) {
            return ComandoHardware::where('sistema', 's3')
                                ->where('comando', $comandoBuscado)
                                ->first();
        });


        // Si se encuentra el comando de hardware, asignar el comando_id
        if ($comandoHardware) {
            $s3Final['comando_id'] = $comandoHardware['id'];
        } else {
            Log::error('El comando de apagado de las bombas no fue encontrado');
        }

        // Guardar el nuevo estado en la caché
        Cache::forever('estado_s3_actual', $s3Final);

        // Actualizar el estado del sistema con la nueva entrada s3
        $estadoSistema['s3_id'] = $nuevoS3Id;
        Cache::forever('estado_sistema', $estadoSistema);


        // Despachar los trabajos para actualizar la base de datos
        Archivador::dispatch('s3', $s3Final);
        Archivador::dispatch('estado_sistemas', ['s3_id' => $estadoSistema['s3_id']] , 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


        //Log::info('Motor principal apagado');
    }
    

    protected function apagarInyectores()
        {
            // Obtener el estado del sistema desde la caché
            $estadoSistema = Cache::rememberForever('estado_sistema', function () {
                return EstadoSistema::first()->toArray();
            });

            // Obtener la configuración actual de s4 desde la caché
            $s4Actual = Cache::rememberForever("estado_s4_actual", function () use ($estadoSistema) {
                return S4::find($estadoSistema["s4_id"]);
            });

            // Clonar la configuración actual para crear un nuevo estado de S4
            $s4Final = $s4Actual;
            $nuevoS4Id = (string) Str::uuid();  // Generar un nuevo UUID
            $s4Final['id'] = $nuevoS4Id;

            // Buscar el comando de hardware correcto en la caché
            $comandoBuscado = '{"actions":["pump1:off:1","pump2:off:1"]}';
            $comandoHardware = Cache::rememberForever("comando_hardware_s4_{$comandoBuscado}", function () use ($comandoBuscado) {
                return ComandoHardware::where('sistema', 's4')
                                    ->where('comando', $comandoBuscado)
                                    ->first();
            });
                    
            // Si se encuentra el comando de hardware, asignar el comando_id
            if ($comandoHardware) {
                $s4Final['comando_id'] = $comandoHardware['id'];
                $s4Final['estado'] = "apagando";
            } else {
                //Log::info('El comando de apagado de inyectores no pudo ser encontrado');
            }

            // Guardar el nuevo estado en la caché
            Cache::forever('estado_s4_actual', $s4Final);

            // Actualizar el estado del sistema con la nueva entrada s4
            $estadoSistema['s4_id'] = $nuevoS4Id;
            Cache::forever('estado_sistema', $estadoSistema);

            // Despachar los trabajos para actualizar la base de datos
            Archivador::dispatch('s4', $s4Final);
        Archivador::dispatch('estado_sistemas', ['s4_id' => $estadoSistema['s4_id']] , 'update', ['column' => 'id', 'value' => $estadoSistema['id']]);


            //Log::info('Inyectores de fertilizante apagados');
        }
    
}
