<?php

namespace App\Listeners;

use App\Events\SincronizarSistema;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Comando;
use App\Jobs\Archivador;
use Illuminate\Support\Str;

class SincronizarSistemaListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SincronizarSistema $event)
    {
        $baseUrl = env('API_URL');
        $cultivo = $event->cultivo;
        $token = $cultivo->api_token;

        // Enviar comandos
        $comandosResponse = Http::withToken($token)->post("$baseUrl/api/comandos/reportar", $event->comandos);
        if ($comandosResponse->failed()) {
            Log::error('Failed to report comandos', ['response' => $comandosResponse->body()]);
        }

        // Enviar mensajes
        foreach ($event->mensajes as $mensaje) {
            $mensajeResponse = Http::withToken($token)->post("$baseUrl/api/mensaje/reportar", $mensaje);
            if ($mensajeResponse->failed()) {
                Log::error('Failed to report mensaje', ['response' => $mensajeResponse->body()]);
            }
        }

        //Log::info('Programaciones data:', ['programaciones' => $event->programaciones]);

        // Realizar la solicitud HTTP
        $programacionesResponse = Http::withToken($token)->post("$baseUrl/api/cultivos/programaciones/sincronizar", $event->programaciones);

        if ($programacionesResponse->failed()) {
            Log::error('Failed to report programaciones', ['response' => $programacionesResponse->body()]);
            return;
        }

        $responseData = $programacionesResponse->json();
        if (isset($responseData['programaciones'])) {
            // Borrar todas las programaciones existentes del cultivo en la caché
            $cultivoProgramaciones = Cache::rememberForever("cultivo_{$cultivo->id}_programaciones", function () use ($cultivo) {
                return $cultivo->programaciones()->get()->toArray();
            });
            Cache::forget("cultivo_{$cultivo->id}_programaciones");

            // Preparar las nuevas programaciones
            $nuevasProgramaciones = [];
            foreach ($responseData['programaciones'] as $programacionData) {
                $comandoId = $programacionData['comando_id'];

                $comando = Cache::rememberForever("comando_{$comandoId}", function () use ($comandoId) {
                    return Comando::find($comandoId);
                });

                if (!$comando) {
                    Log::error("Comando with ID {$comandoId} does not exist.");
                    continue;
                }

                $nuevaProgramacion = [
                    'id' => (string) Str::uuid(),
                    'cultivo_id' => $cultivo->id,
                    'comando_id' => $comandoId,
                    'hora_unix' => $programacionData['hora_unix'],
                    'estado' => $programacionData['estado'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $nuevasProgramaciones[] = $nuevaProgramacion;
                //Cache::forever("programacion_{$nuevaProgramacion['id']}", $nuevaProgramacion);
            }
            // Obtener la lista de programaciones pendientes desde la caché
            $programacionesPendientes = Cache::get('programaciones_pendientes', []);

            // Agregar la nueva programación a la lista
            $programacionesPendientes[] = $nuevasProgramaciones;

            // Guardar la lista actualizada en la caché
            Cache::put('programaciones_pendientes', $programacionesPendientes, 1000);
            // Actualizar la caché con las nuevas programaciones
            Cache::forever("cultivo_{$cultivo->id}_programaciones", $nuevasProgramaciones);

            // Despachar los trabajos para escribir en la base de datos
            Archivador::dispatch('programacions', $nuevasProgramaciones);
        }

        Log::info('Successfully synchronized the system for cultivo ID: ' . $cultivo->id);

        // Enviar estados
        $estadosResponse = Http::withToken($token)->post("$baseUrl/api/estados/reportar", $event->estados);
        if ($estadosResponse->failed()) {
            Log::error('Failed to report estados', ['response' => $estadosResponse->body()]);
        }

        // Enviar estado actual
        $estadoActualResponse = Http::withToken($token)->post("$baseUrl/api/estado/reportar", $event->estadoActual);
        
        if ($estadoActualResponse->failed()) {
            Log::error('Failed to report estado actual', [
                'response' => $estadoActualResponse->body(),
                'status' => $estadoActualResponse->status()
            ]);
        } else {
            //Log::info('Estado actual reportado exitosamente', [
            //    'response' => $estadoActualResponse->body(),
            //    'status' => $estadoActualResponse->status()
            //]);
        }
    }
}
