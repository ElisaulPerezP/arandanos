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
        ProcessScheduledCommands::dispatch()->onQueue('manejador');
        FetchRevistaData::dispatch()->onQueue('telegrafo');
    }
}
