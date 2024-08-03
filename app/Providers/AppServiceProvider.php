<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        if (config('database.default') === 'sqlite' && file_exists(config('database.connections.sqlite.database'))) {
            DB::connection()->getPdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Ejecutar las PRAGMAs
            DB::connection('sqlite')->getPdo()->exec('PRAGMA synchronous = NORMAL;');
            DB::connection('sqlite')->getPdo()->exec('PRAGMA journal_mode = WAL;');
            DB::connection('sqlite')->getPdo()->exec('PRAGMA cache_size = 10000;');
            DB::connection('sqlite')->getPdo()->exec('PRAGMA locking_mode = EXCLUSIVE;');
            DB::connection('sqlite')->getPdo()->exec('PRAGMA temp_store = MEMORY;');
            DB::connection('sqlite')->getPdo()->exec('PRAGMA mmap_size = 268435456;');
            DB::connection('sqlite')->getPdo()->exec('PRAGMA busy_timeout = 30000;'); // 30 segundos de tiempo de espera
        } else {
            // Puedes lanzar una excepción o simplemente ignorar si el archivo no existe
            // throw new \Exception('Database file does not exist.');
        }
    }
}
