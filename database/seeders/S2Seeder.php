<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S2;

class S2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Elimina cualquier registro existente con ID 1 para evitar conflictos
        S2::where('id', 1)->delete();

        // Usar la factory para crear un registro con ID 1 y valores específicos
        S2::factory()->create([
            'id' => 1,
            'estado' => 'inicial', // Asigna el estado inicial
            'comando_id' => null, // Asigna el comando_id inicial
            'valvula1' => 'off', // Asigna el valor inicial de valvula1
            'valvula2' => 'off', // Asigna el valor inicial de valvula2
            'valvula3' => 'off', // Asigna el valor inicial de valvula3
            'valvula4' => 'off', // Asigna el valor inicial de valvula4
            'valvula5' => 'off', // Asigna el valor inicial de valvula5
            'valvula6' => 'off', // Asigna el valor inicial de valvula6
            'valvula7' => 'off', // Asigna el valor inicial de valvula7
            'valvula8' => 'off', // Asigna el valor inicial de valvula8
            'valvula9' => 'off', // Asigna el valor inicial de valvula9
            'valvula10' => 'off', // Asigna el valor inicial de valvula10
            'valvula11' => 'off', // Asigna el valor inicial de valvula11
            'valvula12' => 'off', // Asigna el valor inicial de valvula12
            'valvula13' => 'off', // Asigna el valor inicial de valvula13
        ]);
    }
}
