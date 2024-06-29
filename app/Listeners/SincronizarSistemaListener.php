<?php

namespace App\Listeners;

use App\Events\SincronizarSistema;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SincronizarSistemaListener
{
    /**
     * Handle the event.
     */
    public function handle(SincronizarSistema $event)
    {
        $data = $event->data;

        // Implement the logic for synchronization here
        // For example, make an HTTP request to synchronize data
        try {
            $response = Http::withToken($data['token'])->post($data['url'], $data['payload']);

            if ($response->successful()) {
                Log::info('Synchronization successful.', $response->json());
            } else {
                Log::error('Synchronization failed.', ['status' => $response->status()]);
            }
        } catch (\Exception $e) {
            Log::error('Synchronization error.', ['message' => $e->getMessage()]);
        }
    }
}
