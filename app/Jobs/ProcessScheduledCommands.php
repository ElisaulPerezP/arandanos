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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProcessScheduledCommands implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $oneMinuteAgo = now()->subMinute()->timestamp;
            $now = now()->timestamp;

            $programaciones = Cache::remember('programaciones_pendientes', 600, function () use ($oneMinuteAgo, $now) {
                return Programacion::where('hora_unix', '>=', $oneMinuteAgo)
                    ->where('hora_unix', '<=', $now)
                    ->whereNotIn('estado', ['ejecutado_exitosamente', 'ejecutandose', 'cancelado'])
                    ->with('comando')
                    ->get()
                    ->toArray();
            });

            foreach ($programaciones as $programacion) {
                try {
                    $comando = $programacion['comando'];

                    $event = match($comando['nombre']) {
                        'sincronizar' => SincronizarSistema::class,
                        'parar' => StopSystem::class,
                        'iniciar' => InicioDeAplicacion::class,
                        'revisar' => CheckEvent::class,
                        'reiniciar' => RestartEvent::class,
                        'riego' => RiegoEvent::class,
                        default => null,
                    };



                    if ($event) {
                        event(new $event((object) $programacion));
                        $programacion['estado'] = 'ejecutandose';
                        $programacion['updated_at'] = now();

                        // Actualizar la caché
                        Cache::put("programacion_{$programacion['id']}", $programacion, 60);

                        // Despachar la actualización a la base de datos
                        Archivador::dispatch('programaciones', $programacion);

                        Log::info('Emitiendo evento: ' . $comando['nombre'], ['programacion_id' => $programacion['id'], 'event' => $event]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error procesando la programación.', ['programacion_id' => $programacion['id'], 'exception' => $e->getMessage()]);

                    $programacion['estado'] = 'fallido';
                    $programacion['updated_at'] = now();

                    // Actualizar la caché
                    Cache::put("programacion_{$programacion['id']}", $programacion, 60);

                    // Despachar la actualización a la base de datos
                    Archivador::dispatch('programaciones', $programacion);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en ProcessScheduledCommands job.', ['exception' => $e->getMessage()]);
        }
    }
}
