<?php

namespace App\Listeners;

use App\Events\SincronizarSistema;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Comando;


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
//TODO ESTA MIERDA HAY QUE CAMBIARLA, COMO QUE 1 EN LA URL? 
        // Enviar programaciones (modificar según el ID del cultivo, aquí es 2 como ejemplo)
        Log::info('Programaciones data:', ['programaciones' => $event->programaciones]);

        // Realizar la solicitud HTTP
        $programacionesResponse = Http::withToken($token)->post("$baseUrl/api/cultivos/{$cultivo->id}/programaciones/sincronizar", $event->programaciones);
    
        // Verificar si la solicitud falló
        if ($programacionesResponse->failed()) {
            Log::error('Failed to report programaciones', ['response' => $programacionesResponse->body()]);
            return;
        }
    
        // Procesar la respuesta si la solicitud es exitosa
        $responseData = $programacionesResponse->json();
        if (isset($responseData['programaciones'])) {
            // Borrar todos los eventos programados existentes para el cultivo
            $cultivo->programaciones()->delete();
    
            // Escribir los nuevos eventos recibidos
            foreach ($responseData['programaciones'] as $programacionData) {
                $comandoId = $programacionData['comando_id'];
    
                // Verificar si el comando_id existe en la tabla comandos
                $comando = Comando::find($comandoId);
                if (!$comando) {
                    Log::error("Comando with ID {$comandoId} does not exist.");
                    continue;
                }
    
                // Crear nueva programación
                try {
                    $cultivo->programaciones()->create([
                        'comando_id' => $comandoId,
                        'hora_unix' => $programacionData['hora_unix'],
                        'estado' => $programacionData['estado']
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create programacion', [
                        'comando_id' => $comandoId,
                        'hora_unix' => $programacionData['hora_unix'],
                        'estado' => $programacionData['estado'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
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
            Log::error('Failed to report estado actual', ['response' => $estadoActualResponse->body()]);
        }
    }
}
