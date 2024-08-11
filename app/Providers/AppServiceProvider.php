<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use App\Models\ComandoHardware;
use App\Models\Cultivo;
use App\Models\EstadoSistema;

use App\Models\S0;
use App\Models\S1;
use App\Models\S2;
use App\Models\S3;
use App\Models\S4;
use App\Models\S5;





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
        $estadosDelSistema = Cache::rememberForever('estado_sistema', function () {
            return EstadoSistema::find(1);
        });

        $s0Actual = Cache::rememberForever('estado_s0_actual', function () use ($estadosDelSistema) {
            return S0::find($estadosDelSistema['s0_id']);
        });

        $s0Actual = Cache::rememberForever('estado_s1_actual', function () use ($estadosDelSistema) {
            return S1::find($estadosDelSistema['s1_id']);
        });

        $s0Actual = Cache::rememberForever('estado_s2_actual', function () use ($estadosDelSistema) {
            return S2::find($estadosDelSistema['s2_id']);
        });

        $s0Actual = Cache::rememberForever('estado_s3_actual', function () use ($estadosDelSistema) {
            return S3::find($estadosDelSistema['s3_id']);
        });
        
        $s0Actual = Cache::rememberForever('estado_s4_actual', function () use ($estadosDelSistema) {
            return S4::find($estadosDelSistema['s4_id']);
        });

        $s0Actual = Cache::rememberForever('estado_s5_actual', function () use ($estadosDelSistema) {
            return S5::find($estadosDelSistema['s5_id']);
        });

        
    }
}
