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
    public function boot(): void
    {
         // Registrar el evento de conexión para SQLite
         DB::connection()->getPdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

         DB::connection('sqlite')->getPdo()->exec('PRAGMA synchronous = NORMAL;');
         DB::connection('sqlite')->getPdo()->exec('PRAGMA journal_mode = WAL;');
         DB::connection('sqlite')->getPdo()->exec('PRAGMA cache_size = 10000;');
         DB::connection('sqlite')->getPdo()->exec('PRAGMA locking_mode = EXCLUSIVE;');
         DB::connection('sqlite')->getPdo()->exec('PRAGMA temp_store = MEMORY;');
         DB::connection('sqlite')->getPdo()->exec('PRAGMA mmap_size = 268435456;');
    }
}
