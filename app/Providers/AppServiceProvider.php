<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use App\Models\ComandoHardware;
use App\Models\Cultivo;

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
        // Cargar comandos hardware en la caché si no están cacheados
        Cache::rememberForever('comandos_hardware', function () {
            return ComandoHardware::all();
        });

        Cache::rememberForever('culvivo', function () {
            return Cultivo::first();
        });

    }
}
