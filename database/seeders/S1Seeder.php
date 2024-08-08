<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S1;

class S1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Elimina cualquier registro existente con ID 1 para evitar conflictos
        S1::where('id', 1)->delete();

        // Usar la factory para crear un registro con ID 1 y valores específicos
        S1::factory()->create([
            'id' => 1,
            'estado' => false, // Asigna el estado inicial
            'comando_id' => null, // Asigna el comando_id inicial
            'sensor1' => false, // Asigna el valor inicial de sensor1
            'sensor2' => false, // Asigna el valor inicial de sensor2
            'valvula14' => false, // Asigna el valor inicial de valvula14
        ]);
    }
}
