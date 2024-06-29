<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comando;

class ComandoSeeder extends Seeder
{
    public function run(): void
    {
        Comando::create([
            'nombre' => 'revista',
            'descripcion' => 'Consultará de nuevo el endpoint de revista en un minuto.',
        ]);

        Comando::create([
            'nombre' => 'sincronizar',
            'descripcion' => 'Ejecutará el protocolo de sincronización.',
        ]);

        Comando::create([
            'nombre' => 'parar',
            'descripcion' => 'Ejecutará el protocolo de detención.',
        ]);

        Comando::create([
            'nombre' => 'iniciar',
            'descripcion' => 'Ejecutará el protocolo de inicio.',
        ]);

        Comando::create([
            'nombre' => 'revisar',
            'descripcion' => 'Ejecutará el protocolo de revisión.',
        ]);

        Comando::create([
            'nombre' => 'reiniciar',
            'descripcion' => 'Ejecutará el protocolo de reinicio.',
        ]);

        $camellones = range(1, 12);
        $concentraciones = range(0, 10);
        $volumenes = range(1, 10);

        // Generar variaciones para 'riego'
        foreach ($camellones as $camellon) {
            foreach ($concentraciones as $concentracion) {
                foreach ($volumenes as $volumen) {
                    Comando::create([
                        'nombre' => 'riego',
                        'descripcion' => "camellon=$camellon, concentracion=$concentracion, volumen=$volumen",
                    ]);
                }
            }
        }
    }
}

