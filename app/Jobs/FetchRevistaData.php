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
            // Procesa los datos aquí
            Log::info('Data fetched successfully.', $data);
        } else {
            Log::error('Failed to fetch data.', ['status' => $response->status()]);
        }
    }
}
