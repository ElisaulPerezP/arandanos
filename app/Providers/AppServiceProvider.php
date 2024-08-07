<?php

namespace App\Providers;
use Illuminate\Support\Facades\Cache;
use App\Models\ComandoHardware;
use App\Models\Comando;

use Illuminate\Support\ServiceProvider;

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
        Cache::rememberForever('comandos_hardware', function () {
            return ComandoHardware::all();
        });

        Cache::rememberForever('comandos', function () {
            return Comando::all();
        });
    }
}
