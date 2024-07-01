<?php

namespace App\Listeners;

use App\Events\SincronizarSistema;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $programacionesResponse = Http::withToken($token)->post("$baseUrl/api/cultivos/1/programaciones/sincronizar", $event->programaciones);
        if ($programacionesResponse->failed()) {
            Log::error('Failed to report programaciones', ['response' => $programacionesResponse->body()]);
        }

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
