<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessScheduledCommands;
use App\Jobs\FetchRevistaData;
use Illuminate\Support\Facades\Log;


class Minutero extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:minutero';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
    // Programa el trabajo FetchRevistaData como un job
    $idRevita=dispatch(new FetchRevistaData())->id();
    $idScheduled=dispatch(new ProcessScheduledCommands())->id();
    
    Log::info('los id de revista y scheduled son:.',[$idRevita, $idScheduled] );

    }
}
