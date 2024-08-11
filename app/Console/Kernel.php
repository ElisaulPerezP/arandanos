<?php
namespace App\Console;

use App\Jobs\FetchRevistaData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessScheduledCommands;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            // Obtener el cultivo desde la caché
            $cultivo = Cache::get('culvivo');
            
            // Asegúrate de que $cultivo no sea nulo antes de despachar el trabajo
            if ($cultivo) {
                FetchRevistaData::dispatch($cultivo);
            } else {
                \Log::warning('No se encontró un cultivo en la caché.');
            }
        })->everyMinute();

        $schedule->job(new ProcessScheduledCommands)->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
