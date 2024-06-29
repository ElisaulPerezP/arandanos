<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Cultivo;
use App\Models\Comando;
use App\Events\SincronizarSistema;
use App\Events\CultivoInactivo;
use App\Events\InicioDeAplicacion;
use App\Events\CheckEvent;
use App\Events\RestartEvent;
use App\Events\RiegoEvent;

class FetchRevistaData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cultivo;

    /**
     * Create a new job instance.
     */
    public function __construct(Cultivo $cultivo)
    {
        $this->cultivo = $cultivo;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $propietario = $this->cultivo->propietario;

        if (!$propietario || !$propietario->api_token) {
            Log::error('No valid api_token found for the propietario.');
            return;
        }

        $token = $propietario->api_token;
        $baseUrl = env('API_URL', 'https://default-url.com');
        $url = $baseUrl . '/api/revista';

        $response = Http::withToken($token)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            Log::info('Data fetched successfully.', $data);

            if ($data['message'] === 'Conexion registrada con exito.') {
                $commandId = $data['command'];
                $comando = Comando::find($commandId);

                if ($comando) {
                    $data['descripcion'] = $comando->descripcion;
                } else {
                    Log::error('Command not found in database.', ['commandId' => $commandId]);
                    return;
                }

                switch ($commandId) {
                    case 0:
                        // No hacer nada
                        break;
                    case 1:
                        event(new SincronizarSistema());
                        break;
                    case 2:
                        event(new CultivoInactivo());
                        break;
                    case 3:
                        event(new InicioDeAplicacion());
                        break;
                    case 4:
                        event(new CheckEvent());
                        break;
                    case 5:
                        event(new RestartEvent());
                        break;
                    default:
                        if ($commandId >= 6) {
                            event(new RiegoEvent($data['descripcion']));
                        }
                        break;
                }
            }
        } else {
            Log::error('Failed to fetch data.', ['status' => $response->status()]);
        }
    }
}
