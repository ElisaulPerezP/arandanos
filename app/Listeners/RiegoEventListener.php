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
        Log::info('Riego event handled', ['descripcion' => $event->descripcion]);

        $startTime = Carbon::now();
        $timeoutTime = $startTime->addSeconds($this->timeout);

        try {
            // Encender electrovalvulas
            $this->encenderElectrovalvulas($event->descripcion);

            // Encender motor principal
            $this->encenderMotorPrincipal();

            // Inyectar fertilizante
            $this->inyectarFertilizante($event->descripcion);

            // Monitorear flujo
            $resultado = $this->monitorearFlujo($timeoutTime, $event->descripcion);

            // Llenar tanques si el riego fue exitoso
            if ($resultado) {
                $this->llenarTanques();
                $this->marcarEventoExitoso($event->descripcion);
            } else {
                $this->marcarEventoFallido($event->descripcion);
            }

            // Apagar todos los sistemas después del riego
            $this->apagarElectrovalvulas($event->descripcion);
            $this->apagarMotorPrincipal();
            $this->apagarInyectores();

        } catch (\Exception $e) {
            Log::error('Error en el manejo del evento de riego', ['descripcion' => $event->descripcion, 'error' => $e->getMessage()]);
            $this->marcarEventoFallido($event->descripcion);

            // Asegurarse de apagar todos los sistemas en caso de error
            $this->apagarElectrovalvulas($event->descripcion);
            $this->apagarMotorPrincipal();
            $this->apagarInyectores();
        }
    }

    protected function encenderElectrovalvulas($descripcion)
    {
        // Parsear la descripcion para obtener el camellon
        parse_str(str_replace(',', '&', $descripcion), $params);
        $camellon = $params['camellon'];

        // Obtener la configuración actual de s2 y encender la electrovalvula correspondiente
        $estadoSistema = EstadoSistema::first();
        $s2Actual = $estadoSistema->s2;
        $s2Final = new S2;
        $s2Final->fill($s2Actual->toArray());

        // Actualizar el comando para encender la electrovalvula del camellon
        $s2Final->comando = 'on:valvula' . $camellon;
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

    // Actualizar el comando para encender las bombas principales
    $s3Final->comando = '{"actions":["pump1:on","pump2:on"]}';

    // Guardar el nuevo estado
    $s3Final->save();

    // Actualizar el estado del sistema
    $estadoSistema->update(['s3' => $s3Final->id]);

    Log::info('Motor principal encendido');
}

protected function inyectarFertilizante($descripcion)
{
    // Parsear la descripcion para obtener la concentracion
    parse_str(str_replace(',', '&', $descripcion), $params);
    $concentracion = $params['concentracion'];

    // Obtener la configuración actual de s4 y activar los inyectores de fertilizante
    $estadoSistema = EstadoSistema::first();
    $s4Actual = S4::find($estadoSistema->s4);

    // Crear una nueva instancia de S4 para el nuevo estado
    $s4Final = new S4;
    $s4Final->fill($s4Actual->toArray());

    // Calcular el comando para los inyectores basado en la concentracion
    $comando = $this->calcularComandoInyectores($concentracion);
    $s4Final->comando = $comando;

    // Guardar el nuevo estado
    $s4Final->save();

    // Actualizar el estado del sistema
    $estadoSistema->update(['s4' => $s4Final->id]);

    Log::info('Fertilizante inyectado con concentracion ' . $concentracion);
}

    protected function calcularComandoInyectores($concentracion)
    {
        // Implementar la lógica para calcular el comando basado en la concentracion
        // Por ejemplo:
        return '{"actions":["pump1:on:' . $concentracion * 10 . '","pump2:on:' . $concentracion * 10 . '"]}';
    }

    protected function monitorearFlujo($timeoutTime, $descripcion)
    {
        // Parsear la descripción del evento para obtener el volumen
        parse_str(str_replace(',', '&', $descripcion), $params);
        $volumen = $params['volumen'];
    
        // Calcular el volumen esperado en términos de cuentas
        $cuentasEsperadas = $volumen * 100;
    
        // Inicializar el contador de flujo
        $flujoAcumulado = 0;
    
        while (Carbon::now()->lessThan($timeoutTime)) {
            $estadoSistema = EstadoSistema::first();
            $s5Actual = $estadoSistema->s5;
    
            // Obtener el estado actual del flujo
            $flujoActual = $s5Actual['flujo'];
    
            // Acumular el flujo
            $flujoAcumulado += $flujoActual;
    
            // Verificar si el flujo acumulado cumple con los requisitos
            if ($flujoAcumulado >= $cuentasEsperadas) {
                Log::info('El flujo cumple con los requisitos', ['flujo_acumulado' => $flujoAcumulado, 'cuentas_esperadas' => $cuentasEsperadas]);
                $s5Final=new S5;
                $s5Final->fill($s5Actual->toArray());
                $s5Final->flujo=0;
                $estadoSistema->udpate(['s5'=>$s5Final->id]);
                return true;
            }
    
            // Esperar 2 segundos antes de la siguiente verificación
            sleep(2);
        }
    
        // Si el flujo no cumple con los requisitos en el tiempo límite, retornar false
        Log::warning('El flujo no cumple con los requisitos en el tiempo límite', ['flujo_acumulado' => $flujoAcumulado, 'cuentas_esperadas' => $cuentasEsperadas]);
        $s5Final=new S5;
        $s5Final->fill($s5Actual->toArray());
        $s5Final->flujo=0;
        $estadoSistema->udpate(['s5'=>$s5Final->id]);
        return false;
    }
    protected function llenarTanques()
    {
        // Obtener la configuración actual de s1 y activar el llenado de tanques
        $estadoSistema = EstadoSistema::first();
        $s1Actual = S1::find($estadoSistema->s1);
    
        // Crear una nueva instancia de S1 para el nuevo estado
        $s1Final = new S1;
        $s1Final->fill($s1Actual->toArray());
    
        // Actualizar el comando para llenar los tanques
        $s1Final->comando = 'llenar';
    
        // Guardar el nuevo estado
        $s1Final->save();
    
        // Actualizar el estado del sistema
        $estadoSistema->update(['s1' => $s1Final->id]);
    
        Log::info('Tanques llenando');
    }

    protected function marcarEventoExitoso($descripcion)
    {
        // Marcar el evento como exitoso en la base de datos
        $programacion = Programacion::find($descripcion);
        $programacion->update(['estado' => 'ejecutado_exitosamente']);
        Log::info('Evento de riego completado exitosamente', ['descripcion' => $descripcion]);
    }

    protected function marcarEventoFallido($descripcion)
    {
        // Marcar el evento como fallido en la base de datos
        $programacion = Programacion::find($descripcion);
        $programacion->update(['estado' => 'fallido']);
        Log::info('Evento de riego fallido', ['descripcion' => $programacion->comando->descripcion]);
    }
    protected function apagarElectrovalvulas($descripcion)
    {
        $programacion = Programacion::find($descripcion);
        // Parsear la descripcion para obtener el camellon
        parse_str(str_replace(',', '&', $programacion->comando->descripcion), $params);
        $camellon = $params['camellon'];
        $factoryDefaults = S2::factory()->make()->toArray();
        // Obtener la configuración actual de s2 y apagar la electrovalvula correspondiente
        $estadoSistema = EstadoSistema::first();
        $attributes = array_merge($factoryDefaults, [
            'estado' => 'apagado' // O cualquier valor por defecto que necesites
        ]);
    
        $s2Actual = S2::firstOrCreate(['id' => $estadoSistema->s2], $attributes);
        // Crear una nueva instancia de S2 para el nuevo estado
        $s2Final = new S2;
        $s2Final->fill($s2Actual->toArray());
    
        // Actualizar el comando para apagar la electrovalvula del camellon
        $s2Final->comando = 'off:valvula' . $camellon;
    
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
        $s3Actual = S3::find($estadoSistema->s3);
    
        // Crear una nueva instancia de S3 para el nuevo estado
        $s3Final = new S3;
        $s3Final->fill($s3Actual->toArray());
    
        // Actualizar el comando para apagar las bombas principales
        $s3Final->comando = '{"actions":["pump1:off","pump2:off"]}';
    
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
        $s4Actual = S4::find($estadoSistema->s4);
    
        // Crear una nueva instancia de S4 para el nuevo estado
        $s4Final = new S4;
        $s4Final->fill($s4Actual->toArray());
    
        // Actualizar el comando para apagar los inyectores
        $s4Final->comando = '{"actions":["pump1:off","pump2:off"]}';
    
        // Guardar el nuevo estado
        $s4Final->save();
    
        // Actualizar el estado del sistema
        $estadoSistema->update(['s4' => $s4Final->id]);
    
        Log::info('Inyectores de fertilizante apagados');
    }
    
}
