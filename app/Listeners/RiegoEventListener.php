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
            Log::info('si esta entrando al try');
            // Encender electrovalvulas
            $this->encenderElectrovalvulas($programacion);

            // Encender motor principal
            $this->encenderMotorPrincipal();

            // Inyectar fertilizante
            $this->inyectarFertilizante($programacion);

            // Monitorear flujo
            $resultado = $this->monitorearFlujo($timeoutTime, $programacion);

            // Llenar tanques si el riego fue exitoso
            if ($resultado) {
                $this->llenarTanques();
                $this->marcarEventoExitoso($programacion);
            } else {
                $this->marcarEventoFallido($programacion);
                
            }

            // Apagar todos los sistemas después del riego
            $this->apagarElectrovalvulas($programacion);
            $this->apagarMotorPrincipal();
            $this->apagarInyectores();

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
            return EstadoSistema::first();
        });


         // Obtener el estado del sistema desde la caché como objeto
        $estadoSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::first();
        });

         // Obtener la configuración actual de s2 desde la caché como objeto
        $s2Actual = Cache::rememberForever("estado_s2_actual", function () use ($estadoSistema) {
            return S2::find($estadoSistema["s2_id"]);
        });
        

        // Clonar la configuración actual para crear un nuevo estado de S2
        $s2Final = $s2Actual->replicate();
        $nuevoS2Id = (string) Str::uuid();  // Generar un nuevo UUID
        $s2Final->id = $nuevoS2Id;

        // Buscar el comando de hardware correcto en la caché como objeto
        $comandoBuscado = 'on:valvula' . $camellon;
        $comandoHardware = Cache::rememberForever("comando_hardware_{$comandoBuscado}", function () use ($comandoBuscado) {
            return ComandoHardware::where('sistema', 's2')
                                ->where('comando', $comandoBuscado)
                                ->first();
        });


        if ($comandoHardware) {
            $s2Final->comando_id = $comandoHardware->id;
        } else {
            // Registrar un mensaje en el log si no se encuentra el comando
            Log::info("El comando para encender la electrovalvula del camellon $camellon no pudo ser encontrado");
        }
    
        // Guardar el nuevo estado en la caché
        Cache::forever('estado_s2_actual', $s2Final);
    
        // Despachar los trabajos para actualizar la base de datos
        Archivador::dispatch('s2', $s2Final->toArray());
        Archivador::dispatch('estado_sistema', ['s2_id' => $s2Final->id] + $estadoSistema);
    
        Log::info('Electrovalvula del camellon ' . $camellon . ' encendida');
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
    $s3Final = $s3Actual->replicate();
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
        $s3Final->comando_id = $comandoHardware->id;
    } else {
        // Registrar un mensaje en el log si no se encuentra el comando
        Log::info("El comando para encender las bombas principales no pudo ser encontrado");
    }

    // Actualizar la caché con los nuevos valores
    Cache::forever('estado_s3_actual', $s3Final->toArray());

    // Actualizar el estado del sistema en caché
    $estadoSistema['s3_id'] = $nuevoS3Id;
    Cache::forever('estado_sistema', $estadoSistema);

    // Despachar los trabajos para escribir en la base de datos
    Archivador::dispatch('s3', $s3Final->toArray());
    Archivador::dispatch('estado_sistemas', $estadoSistema);

    Log::info('Motor principal encendido');
}

protected function inyectarFertilizante($programacion)
{

    // Obtener el comando desde la caché
    $comando = Cache::rememberForever("comando_{$programacion['comando_id']}", function () use ($programacion) {
        return Comando::find($programacion['comando_id']);
    });

    // Parsear la descripción del comando para obtener la concentración
    parse_str(str_replace(',', '&', $comando['descripcion']), $params);
    $concentracion = $params['concentracion'];

    // Obtener el estado del sistema desde la caché
    $estadoSistema = Cache::rememberForever('estado_sistema', function () {
        return EstadoSistema::first()->toArray();
    });

    // Obtener la configuración actual de s4 desde la caché
    $s4Actual = Cache::rememberForever("estado_s4_actual", function () use ($estadoSistema) {
        return S4::find($estadoSistema["s4_id"]);
    });

    // Clonar la configuración actual para crear un nuevo estado de S4
    $s4Final = $s4Actual->replicate();
    $nuevoS4Id = (string) Str::uuid();  // Generar un nuevo UUID
    $s4Final->id = $nuevoS4Id;

    // Calcular el comando para los inyectores basado en la concentración
    $comandoHardware = $this->calcularComandoInyectores($concentracion);
    $s4Final->comando_id = $comandoHardware->id;
    $s4Final->estado = 'inyectando';
    $s4Final->pump3 = true;
    $s4Final->pump4 = false;

    // Actualizar la caché con el nuevo estado
    Cache::forever('estado_s4_actual', $s4Final->toArray());

    // Actualizar el estado del sistema en la caché
    $estadoSistema['s4_id'] = $nuevoS4Id;
    Cache::forever('estado_sistema', $estadoSistema);

    // Despachar los trabajos para actualizar la base de datos
    Archivador::dispatch('s4', $s4Final->toArray());
    Archivador::dispatch('estado_sistemas', $estadoSistema);

    Log::info('Fertilizante inyectado con concentración ' . $concentracion);

}

    protected function calcularComandoInyectores($concentracion)
    {


        $comandoBuscado = '{"actions":["pump1:on:' . ($concentracion * 10) . '","pump2:off:1"]}';

        // Buscar el comando de hardware correspondiente en la caché
        $comandoHardware = Cache::rememberForever("comando_hardware_s4_{$comandoBuscado}", function () use ($comandoBuscado) {
            return ComandoHardware::where('sistema', 's4')
                                  ->where('comando', $comandoBuscado)
                                  ->first();
        });
    
        if ($comandoHardware) {
            return $comandoHardware;
        } else {
            // Manejar el caso donde no se encuentra el comando, si es necesario
            Log::warning("El comando de inyectores para la concentración {$concentracion} no pudo ser encontrado");
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
            $flujoActual1 = $s5Actual->flux1;
            $flujoActual2 = $s5Actual->flux2;
            $flujoActual = $flujoActual1 + $flujoActual2;
    
            // Acumular el flujo
            $flujoAcumulado += $flujoActual;
    
        // Verificar si el flujo acumulado cumple con los requisitos
        if ($flujoAcumulado >= $cuentasEsperadas) {
                // Crear una nueva instancia de S5 para el nuevo estado
                $s5Final = $s5Actual->replicate();
                $s5Final->flux1 = 0;
                $s5Final->flux2 = 0;
                $s5Final->id = (string) Str::uuid();

                // Guardar el nuevo estado en la caché
                Cache::forever('estado_s5_actual', $s5Final);

                // Actualizar el estado del sistema en la caché
                $estadoSistema['s5_id'] = $s5Final->id;
                Cache::forever('estado_sistema', $estadoSistema);

                // Despachar los trabajos para actualizar la base de datos
                Archivador::dispatch('s5', $s5Final->toArray());
                Archivador::dispatch('estado_sistema', $estadoSistema);

                Log::info('El flujo cumple con los requisitos', ['flujo_acumulado' => $flujoAcumulado, 'cuentas_esperadas' => $cuentasEsperadas]);
                return true;
            }
            // Esperar 2 segundos antes de la siguiente verificación
            sleep(2);
        }

        // Si el flujo no cumple con los requisitos en el tiempo límite, retornar false
        $s5Final = $s5Actual->replicate();
        $s5Final->flux1 = 0;
        $s5Final->flux2 = 0;
        $s5Final->id = (string) Str::uuid();

        // Guardar el nuevo estado en la caché
        Cache::forever('estado_s5_actual', $s5Final);

        // Actualizar el estado del sistema en la caché
        $estadoSistema['s5_id'] = $s5Final->id;
        Cache::forever('estado_sistema', $estadoSistema);

        // Despachar los trabajos para actualizar la base de datos
        Archivador::dispatch('s5', $s5Final->toArray());
        Archivador::dispatch('estado_sistema', $estadoSistema);

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
        $s1Final = $s1Actual->replicate();
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
            $s1Final->comando_id = $comandoHardware->id;
        } else {
            // Registrar un mensaje en el log si no se encuentra el comando
            Log::info("El comando de llenado de tanques no pudo ser encontrado");
        }

        // Guardar el nuevo estado en la caché
        Cache::forever('estado_s1_actual', $s1Final->toArray());


        // Actualizar el estado del sistema con la nueva entrada s1
        $estadoSistemaActualizado = $estadoSistema;
        $estadoSistemaActualizado['s1_id'] = $s1Final->id;

        // Guardar la nueva configuración del sistema en la caché
        Cache::forever('estado_sistema', $estadoSistemaActualizado);

        // Despachar los trabajos para actualizar la base de datos
        Archivador::dispatch('s1', $s1Final->toArray());
        Archivador::dispatch('estado_sistema', $estadoSistemaActualizado);

        Log::info('Tanques llenando');
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
            Archivador::dispatch('programaciones', $programacionActual);

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
            Archivador::dispatch('programaciones', $programacionActual);
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
        //$programacion = Programacion::find($descripcion);
        // Parsear la descripcion para obtener el camellon
        parse_str(str_replace(',', '&', $programacion->comando->descripcion), $params);
        $camellon = $params['camellon'];
        
        // Obtener la configuración actual de s2 y apagar la electrovalvula correspondiente
        $estadoSistema = EstadoSistema::first();
        $s2Actual = $estadoSistema->s2;
        
        // Crear una nueva instancia de S2 para el nuevo estado
        $s2Final = new S2;
        $s2Final->fill($s2Actual->toArray());

        // Buscar el comando de hardware correcto
        $comandoBuscado = 'off:valvula' . $camellon;
        $comandoHardware = ComandoHardware::where('sistema', 's2')
                                           ->where('comando', $comandoBuscado)
                                           ->first();

                                
     // Si se encuentra el comando de hardware, asignar el comando_id
        if ($comandoHardware) {
            $s2Final->comando_id = $comandoHardware->id;
        } else {
            Log::info('El comando de apagado de valvulas para el camellon $camellon  no pudo ser encontrado');
        }

        // Actualizar el comando para apagar la electrovalvula del camellon
        $s2Final->comando->descripcion = 'off:valvula' . $camellon;
    
        // Guardar el nuevo estado
        $s2Final->save();
    
        // Actualizar el estado del sistema
        $estadoSistema->update(['s2' => $s2Final->id]);
    
        Log::info('Electrovalvula del camellon ' . $camellon . ' apagada');
    }
    

    protected function apagarMotorPrincipal()
    {
        // Obtener la configuración actual de s3 y apagar las bombas principales
        $estadoSistema = EstadoSistema::first();
        $s3Actual = $estadoSistema->s3;

        // Crear una nueva instancia de S3 para el nuevo estado
        $s3Final = new S3;
        $s3Final->fill($s3Actual->toArray());

        // Buscar el comando de hardware correcto
        $comandoBuscado = '{"actions":["pump1:off","pump2:off"]}';
        $comandoHardware = ComandoHardware::where('sistema', 's3')
                                            ->where('comando', $comandoBuscado)
                                            ->first();

        // Si se encuentra el comando de hardware, asignar el comando_id
        if ($comandoHardware) {
            $s3Final->comando_id = $comandoHardware->id;
        } else {
            Log::info('El comando de apagado de las bombas no fue encontrado');

        }

        // Guardar el nuevo estado
        $s3Final->save();
    
        // Actualizar el estado del sistema
        $estadoSistema->update(['s3' => $s3Final->id]);
    
        Log::info('Motor principal apagado');
    }
    

    protected function apagarInyectores()
        {
            // Obtener la configuración actual de s4 y apagar los inyectores de fertilizante
            $estadoSistema = EstadoSistema::first();
            $s4Actual =$estadoSistema->s4;

            // Crear una nueva instancia de S4 para el nuevo estado
            $s4Final = new S4;
            $s4Final->fill($s4Actual->toArray());

            // Buscar el comando de hardware correcto
            $comandoBuscado = '{"actions":["pump1:off:1","pump2:off:1"]}';
            $comandoHardware = ComandoHardware::where('sistema', 's4')
                                            ->where('comando', $comandoBuscado)
                                            ->first();

            // Si se encuentra el comando de hardware, asignar el comando_id
            if ($comandoHardware) {
                $s4Final->comando_id = $comandoHardware->id;
                $s4Final->estado = "apagando";

            } else {
                Log::info('El comando de apagado de inyectores no pudo ser encontrado');
            }

            // Guardar el nuevo estado
            $s4Final->save();

            // Actualizar el estado del sistema
            $estadoSistema->update(['s4' => $s4Final->id]);

            Log::info('Inyectores de fertilizante apagados');
        }
    
}
