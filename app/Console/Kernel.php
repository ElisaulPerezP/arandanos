<?php

namespace App\Console;

use App\Jobs\FetchRevistaData;
use App\Models\Cultivo;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ProcessScheduledCommands;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $cultivos = Cultivo::all();
            foreach ($cultivos as $cultivo) {
                FetchRevistaData::dispatch($cultivo);
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
