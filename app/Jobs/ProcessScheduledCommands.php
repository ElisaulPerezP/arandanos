<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Programacion;
use Carbon\Carbon;
use App\Events\SincronizarSistema;
//use App\Events\CultivoInactivo;
use App\Events\InicioDeAplicacion;
use App\Events\CheckEvent;
use App\Events\RestartEvent;
use App\Events\RiegoEvent;
//use App\Events\Revista;
use App\Events\StopSystem;

class ProcessScheduledCommands implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle()
    {
        $oneMinuteAgo = now()->subMinute()->timestamp;
        $now = now()->timestamp;
        
        $programaciones = Programacion::where('hora_unix', '>=', $oneMinuteAgo)
            ->where('hora_unix', '<=', $now)
            ->whereNotIn('estado', ['ejecutado_exitosamente', 'ejecutandose', 'cancelado'])
            ->with('comando') 
            ->get();

        foreach ($programaciones as $programacion) {
            $comando = $programacion->comando;

            $event = match($comando->nombre) {
                //'revista' => Revista::class,
                'sincronizar' => SincronizarSistema::class,
                'parar' => StopSystem::class,
                'iniciar' => InicioDeAplicacion::class,
                'revisar' => CheckEvent::class,
                'reiniciar' => RestartEvent::class,
                'riego' => RiegoEvent::class,
                default => null,
            };

            if ($event) {
                event(new $event($programacion));  // Pasar la instancia completa del modelo
                $programacion->update(['estado' => 'ejecutandose']);
                Log::info('Emitiendo evento: ' . $comando->nombre, ['programacion_id' => $programacion->id, 'event' => $event]);
            }
        }
    }
}
 