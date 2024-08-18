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
    protected function schedule(Schedule $schedule)
{
    // Programa el trabajo FetchRevistaData como un job
    $schedule->job(new FetchRevistaData())->everyMinute();

    // Programa el trabajo ProcessScheduledCommands como un job
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
