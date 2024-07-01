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
        $cultivo= $this->cultivo;

        if (!$propietario || !$propietario->api_token) {
            Log::error('No valid api_token found for the propietario.');
            return;
        }

        $token = $propietario->api_token;
        $baseUrl = env('API_URL', '');

        $url = $baseUrl . '/api/revista';
        Log::info('URL', ['Url' => $url]);

        $response = Http::withToken($token)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'PostmanRuntime/7.32.3',
            ])
            ->get($url);
        Log::info('response', ['response' => $response]);
        if ($response->successful()) {
            $data = $response->json();
            Log::info('Data fetched successfully.', $data);

            if ($data['message'] === 'Conexion registrada con exito.') {
                $commandId = $data['command'];

                // Buscar el comando en la base de datos y obtener la descripción
                $comando = Comando::find($commandId);
                $descripcion = $comando ? $comando->descripcion : '';

                switch ($commandId) {
                    case 1:
                        // No hacer nada
                        break;
                    case 2:
                        event(new SincronizarSistema($token, $cultivo));
                        break;
                    case 3:
                        event(new CultivoInactivo());
                        break;
                    case 4:
                        event(new InicioDeAplicacion());
                        break;
                    case 5:
                        event(new CheckEvent());
                        break;
                    case 6:
                        event(new RestartEvent());
                        break;
                    default:
                        if ($commandId >= 7) {
                            event(new RiegoEvent($descripcion));
                        }
                        break;
                }
            }
        } else {
            Log::error('Failed to fetch data.', ['status' => $response->status()]);
        }
    }
}
