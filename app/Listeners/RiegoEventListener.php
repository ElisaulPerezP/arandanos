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
use App\Models\EstadoSistema;
use App\Models\Programacion;
use Carbon\Carbon;
use Database\Factories\S2Factory;

class RiegoEventListener implements ShouldQueue
{
    protected $timeout = 300; // Tiempo límite en segundos para completar el riego

    /**
     * Handle the event.
     */
    public function handle(RiegoEvent $event)
    {
        Log::info('Riego event handled', ['descripcion' => $event->programacion]);

        $startTime = Carbon::now();
        $timeoutTime = $startTime->addSeconds($this->timeout);

        try {
            Log::info('si esta entrando al try');
            // Encender electrovalvulas
            $this->encenderElectrovalvulas($event->programacion);

            // Encender motor principal
            $this->encenderMotorPrincipal();

            // Inyectar fertilizante
            $this->inyectarFertilizante($event->programacion);

            // Monitorear flujo
            $resultado = $this->monitorearFlujo($timeoutTime, $event->programacion);

            // Llenar tanques si el riego fue exitoso
            if ($resultado) {
                $this->llenarTanques();
                $this->marcarEventoExitoso($event->programacion);
            } else {
                Log::info('no esta esperando respuesta');
                $this->marcarEventoFallido($event->programacion);
                
            }

            // Apagar todos los sistemas después del riego
            $this->apagarElectrovalvulas($event->programacion);
            $this->apagarMotorPrincipal();
            $this->apagarInyectores();

        } catch (\Exception $e) {
            Log::error('Error en el manejo del evento de riego', ['descripcion' => $event->programacion, 'error' => $e->getMessage()]);
            $this->marcarEventoFallido($event->programacion);

            // Asegurarse de apagar todos los sistemas en caso de error
            $this->apagarElectrovalvulas($event->programacion);
            $this->apagarMotorPrincipal();
            $this->apagarInyectores();
        }
    }

    protected function encenderElectrovalvulas($programacion)
    {
        // Parsear la descripcion para obtener el camellon
        parse_str(str_replace(',', '&', $programacion->comando->descripcion), $params);
        $camellon = $params['camellon'];

        // Obtener la configuración actual de s2 y encender la electrovalvula correspondiente
        $estadoSistema = EstadoSistema::first();
        $s2Actual = $estadoSistema->s2;
        $s2Final = new S2;
        $s2Final->fill($s2Actual->toArray());

        $comandoBuscado = 'on:valvula' . $camellon;
        $comandoHardware = ComandoHardware::where('sistema', 's2')
                                           ->where('comando', $comandoBuscado)
                                           ->first();
    
        // Si se encuentra el comando de hardware, asignar el comando_id
        if ($comandoHardware) {
            $s2Final->comando_id = $comandoHardware->id;
        } else {
            // Registrar un mensaje en el log si no se encuentra el comando
            Log::info("El comando para encender la electrovalvula del camellon $camellon no pudo ser encontrado");

        }
    
        $s2Final->save();

        $estadoSistema->update(['s2' => $s2Final->id]);

        Log::info('Electrovalvula del camellon ' . $camellon . ' encendida');
    }

    protected function encenderMotorPrincipal()
{
    // Obtener la configuración actual de s3 y encender las bombas principales
    $estadoSistema = EstadoSistema::first();
    $s3Actual = $estadoSistema->s3;
    $s3Final = new S3;
    $s3Final->fill($s3Actual->toArray());

    // Buscar el comando de hardware correcto
    $comandoBuscado = '{"actions":["pump1:on","pump2:on"]}';
    $comandoHardware = ComandoHardware::where('sistema', 's3')
                                       ->where('comando', $comandoBuscado)
                                       ->first();

    // Si se encuentra el comando de hardware, asignar el comando_id
    if ($comandoHardware) {
        $s3Final->comando_id = $comandoHardware->id;
    } else {
        // Registrar un mensaje en el log si no se encuentra el comando
        Log::info("El comando para encender las bombas principales no pudo ser encontrado");
    }

    // Guardar el nuevo estado
    $s3Final->save();

    // Actualizar el estado del sistema
    $estadoSistema->update(['s3' => $s3Final->id]);

    Log::info('Motor principal encendido');
}

protected function inyectarFertilizante($programacion)
{
    // Parsear la descripcion para obtener la concentracion
    parse_str(str_replace(',', '&', $programacion->comando->descripcion), $params);
    $concentracion = $params['concentracion'];

    // Obtener la configuración actual de s4 y activar los inyectores de fertilizante
    $estadoSistema = EstadoSistema::first();
    $s4Actual = S4::find($estadoSistema->s4);

    // Crear una nueva instancia de S4 para el nuevo estado
    $s4Final = new S4;
    $s4Final->fill($s4Actual->toArray());

    // Calcular el comando para los inyectores basado en la concentracion
    $comando = $this->calcularComandoInyectores($concentracion);
    $s4Final->comando_id = $comando->id;
    $s4Final->estado = 'inyectando';
    $s4Final->pump3 = true;
    $s4Final->pump4 = false;


    // Guardar el nuevo estado
    $s4Final->save();

    // Actualizar el estado del sistema
    $estadoSistema->update(['s4' => $s4Final->id]);

    Log::info('Fertilizante inyectado con concentracion ' . $concentracion);
}

    protected function calcularComandoInyectores($concentracion)
    {
        $comandoBuscado = '{"actions":["pump1:on:' . ($concentracion * 10) . '","pump2:off:1"]}';
        
        // Buscar el comando de hardware correspondiente en la base de datos
        $comandoHardware = ComandoHardware::where('sistema', 's4')
                                        ->where('comando', $comandoBuscado)
                                        ->first();
        
        if ($comandoHardware) {
            return $comandoHardware;
        } else {
            // Manejar el caso donde no se encuentra el comando, si es necesario
            Log::warning("El comando de inyectores para la concentración {$concentracion} no pudo ser encontrado");
        }
    }

    protected function monitorearFlujo($timeoutTime, $programacion)
    {
        // Parsear la descripción del evento para obtener el volumen
        parse_str(str_replace(',', '&', $programacion->comando->descripcion), $params);
        $volumen = $params['volumen'];
    
        // Calcular el volumen esperado en términos de cuentas
        $cuentasEsperadas = $volumen * 30;
    
        // Inicializar el contador de flujo
        $flujoAcumulado = 0;
    
        while (Carbon::now()->lessThan($timeoutTime)) {
            $estadoSistema = EstadoSistema::first();
            $s5Actual = $estadoSistema->s5;
    
        // Obtener el estado actual del flujo
        $flujoActual1 = $s5Actual->flux1;
        $flujoActual2 = $s5Actual->flux2;
        $flujoActual = $flujoActual1 + $flujoActual2;

        // Acumular el flujo
        $flujoAcumulado += $flujoActual;
    //TODO: AQUI HAY QUE CORREGIR UN DETALL PARA QUE SIRVA DE CONTADOR DE AGUA
            // Verificar si el flujo acumulado cumple con los requisitos
            if ($flujoAcumulado >= $cuentasEsperadas) {
                $s5Actual->update(['flux1' => 0, 'flux2' => 0]);
                $s5Final=new S5;
                $s5Final->fill($s5Actual->toArray());
                $estadoSistema->udpate(['s5'=>$s5Final->id]);
                Log::info('El flujo cumple con los requisitos', ['flujo_acumulado' => $flujoAcumulado, 'cuentas_esperadas' => $cuentasEsperadas]);
               
                return true;
            }
    
            // Esperar 2 segundos antes de la siguiente verificación
            sleep(2);
        }
    
        // Si el flujo no cumple con los requisitos en el tiempo límite, retornar false
        $s5Actual->update(['flux1' => 0, 'flux2' => 0]);
        $s5Final=new S5;
        $s5Final->fill($s5Actual->toArray());
        $estadoSistema->udpate(['s5'=>$s5Final->id]);
        Log::info('El flujo no cumple con los requisitos', ['flujo_acumulado' => $flujoAcumulado, 'cuentas_esperadas' => $cuentasEsperadas]);
        return false;
    }
    protected function llenarTanques()
    {
        // Obtener la configuración actual de s1 y activar el llenado de tanques
        $estadoSistema = EstadoSistema::first();
        $s1Actual = $estadoSistema->s1;
    
        // Crear una nueva instancia de S1 para el nuevo estado
        $s1Final = new S1;
        $s1Final->fill($s1Actual->toArray());
    
        $comandoBuscado = 'llenar';

        $comandoHardware = ComandoHardware::where('sistema', 's1')
                                        ->where('comando', $comandoBuscado)
                                        ->first();

        // Si se encuentra el comando de hardware, asignar el comando_id
        if ($comandoHardware) {
            $s1Final->comando_id = $comandoHardware->id;
        } else {
            // Registrar un mensaje en el log si no se encuentra el comando
            Log::info("El comando de llenado de tanques no pudo ser encontrado");
        }
        // Guardar el nuevo estado
        $s1Final->save();
    
        // Actualizar el estado del sistema
        $estadoSistema->update(['s1' => $s1Final->id]);
    
        Log::info('Tanques llenando');
    }

    protected function marcarEventoExitoso($programacion)
    {
        // Marcar el evento como exitoso en la base de datos
        //$programacion = Programacion::find($programacion);
        $programacion->update(['estado' => 'ejecutado_exitosamente']);
        Log::info('Evento de riego completado exitosamente', ['descripcion' => $programacion->comando->descripcion]);
    }

    protected function marcarEventoFallido($programacion)
    {
        // Marcar el evento como fallido en la base de datos
        //$programacion = Programacion::find($programacion);
        $programacion->update(['estado' => 'fallido']);
        Log::info('Evento de riego fallido', ['descripcion' => $programacion->comando->descripcion]);
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
